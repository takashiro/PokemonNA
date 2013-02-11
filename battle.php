<?php
require_once './core/init.inc.php';

if(!$revid = intval($revid)){
	$monlist = '';
	$noavailuablemon = TRUE;
	$query = $db->query("SELECT id,pokeid,shape,name FROM {$tpre}mymon WHERE owner='$discuz_user' AND status!=0 AND status!=2 AND status < 9");
	while($m = $db->fetch_array($query)){
		$m['picid'] = $m['shape']?"$m[pokeid]_$m[shape]":$m['pokeid'];
		$monlist.= '<a href="battle.php?obvid='.$obvid.'&revid='.$m['id'].'"><img src="'.$localpath.'/pokemon/'.$m['picid'].'.gif" alt="'.$mon['name'].'" width="130" height="120" /></a>';
		$noavailuablemon = FALSE;
	}
	if(!$noavailuablemon) showmsg('请选择一个精灵！<br />'.$monlist);
	else showmsg('您没有可战斗的精灵了！', 'pkw.php');
}else{
	$rev = $db->fetch_first("SELECT m.*,u.cash,u.weather,u.obvid,u.battleon,u.actiontime FROM {$tpre}mymon m LEFT JOIN {$tpre}trainer u ON u.id=$_USER[id] WHERE m.id=$revid");
	$rev['tmpstatus'] && $rev['tmpstatus'] = unserialize($rev['tmpstatus']);
	$ori_rev = $rev = pkw_ShowInfo($rev);
	if(!$rev['battleon']) showmsg('请您开启您的接受挑战，否则无法发出挑战！', 'back');
}

if($rev['status'] == 0){
	if(!$kid) showmsg('您的精灵已经不能战斗了！', 'pkw.php');
	else pkw_reload();
}
elseif(empty($rev['skill'])) showmsg('您的精灵未学会任何技能！不能战斗！', 'back');
elseif($obvid == $_USER[id] || $rev['obvid'] == $_USER[id]) showmsg('请不要打自己了！！！', 'back');
elseif(!$rev['obvid'] && !$obvid = intval($obvid)) showmsg('请选择一个对手！', 'back');
elseif(!$rev['obvid']){
	$db->query("UPDATE {$tpre}trainer SET monid=$revid,obvid=$obvid WHERE id=$_USER[id]");
	$rev['obvid'] = $obvid;
}

//需要初始化的变量
$undermsg = $rev_topmsg = $rev_sidemsg = $rev_undermsg = $obv_topmsg = $obv_sidemsg = $obv_undermsg = $revsql = $revusql = $obvsql = $obvusql = $battlesql = $extrajs = '';
$rev_hideskill = $obv_hideskill = $endbattle = FALSE;
$rev_skill_back = TRUE;

$revusql.= ',actiontime='.$timestamp;//加入动作时间

$obv = $db->fetch_first("SELECT u.id AS uid,u.username,u.cash,u.monkid,u.obvid,u.obvhurt,u.battleon,m.* FROM {$tpre}trainer u LEFT JOIN {$tpre}mymon m ON m.id=u.monid WHERE u.id=$rev[obvid]");//取出对手数据
$obv['tmpstatus'] && $obv['tmpstatus'] = unserialize($obv['tmpstatus']);
$ori_obv = $obv = pkw_ShowInfo($obv);

if(!$obv['battleon']) showmsg('对方不接受任何挑战！', 'back');
elseif($receiver && !$rev['obvid'] && !$rev['gymid']){
	$message = $discuz_user.'向您发出挑战，您要[url='.$boardurl.'battle.php?obvid='.$_USER[id].'&receiver=1]接受请点这里[/url]，不接受请忽略掉此信息。';
	include DISCUZ_ROOT.'uc_client/client.php';
	$pmid = uc_pm_send($_USER[id], $obvid, '', $message);
	if($pmid >= 0){
		showmsg('等待对方回应中，您可以随时取消此次挑战。', 'refresh');
	}elseif($pmid == -1){
		showmessage('pm_send_limit1day_error');
	}elseif($pmid == -2){
		showmessage('pm_send_floodctrl_error');
	}elseif($pmid == -3){
		showmessage('pm_send_batnotfriend_error');
	}elseif($pmid == -4){
		showmessage('pm_send_pmsendregdays_error');
	}else{
		showmessage('pm_send_invalid');
	}
}

if($obv['obvid'] != $_USER[id]) showmsg('等待对方回应中，您可以随时取消此次挑战。', 'refresh');

$g['weather'] = ($timestamp <= $rev['weatherexpiry'])?$rev['weather']:0;

if(!$kid || !$action){
	$skillname = array();//技能名称显示
	$selected_skill_ids = $rev['skill'];
	$selected_skill_ids.= !empty($obv['monkid'])?','.$obv['monkid']:'';
	$query = $db->query("SELECT id,name_c FROM {$tpre}skill WHERE id IN ($selected_skill_ids)");
	while($k = $db->fetch_array($query)){
		$skillname[$k['id']] = $k['name_c'];
	}
}

if($action == 'fight'){
	include S_ROOT.'system/fight.inc.php';

	$kid = in_array($kid, $rev['skilllist'])?$kid:131;
	$query = $db->query("SELECT id,spr,name_c,power,atb,ext,cmp FROM {$tpre}skill WHERE id=$kid OR id=$obv[monkid]");
	while($k = $db->fetch_array($query)){
		if($k['id'] == $kid && $k['id'] != $obv_kid) $rev_skill = $k;
		elseif($k['id'] != $kid && $k['id'] == $obv_kid) $obv_skill = $k;
		else $rev_skill = $obv_skill = $k;
	}
	unset($k);

	//动作时限&ext25-先制攻击
	if(($timestamp - $rev['actiontime'] < 5 && $rev_skill['ext'] != 25) || $timestamp - $rev['actiontime'] < 2){
		exit('$(\'undermsg\').innerHTML += \'请勿非法操作！出招超前！\';');
	}

	$obv_topmsg = $obv['name'].'的'.$obv_skill['name_c'].'！';
	$rev_topmsg = $rev['name'].'的'.$rev_skill['name_c'].'！';
	
	if(in_array($rev_skill['ext'], $foreext)){skillext_effect($rev_skill['ext'], 'rev');$rev_skill_back = FALSE;}//技能前置特殊效果

	skill_effect('rev');//技能效果

	if($rev_skill_back) skillext_effect($rev_skill['ext'], 'rev');//技能后置特殊效果

	other_effect('rev');//其他效果

	caculate_point('rev');//重新计算数值
	

	if($obv['hp'] <= 0){
		$query = $db->query("SELECT baseexp FROM {$tpre}pokemoninfoext WHERE id=$obv[pokeid]");
		$baseexp = $db->result($query, 0);
		$rev['exp'] += floor($baseexp / 7 * $obv['level']) * 1.5;
		$revsql.= pkw_exp_incre($rev['level'], $rev['exp'], $rev['id'], $rev['pokeid'], $rev['natureid']);
		$obvsql.= ',status=0,hp=0';
		writelog('battle', "$rev[id]\t$obv[uid]\tMemberBattle\t$_USER[id]");
		$endbattle = TRUE;
	}
	if($rev['hp'] <= 0){
		$revsql.= 'status=0,hp=0';
		writelog('battle', "$rev[id]\t$obv[uid]\tMemberBattle\t$obv[uid]");
		$endbattle = TRUE;
	}
	if($endbattle){
		$revsql.= $clear_temp_point;
		$obvsql.= $clear_temp_point;
		$battlesql.= ',obvid=0,weather=0,weatherexpiry=0';
	}
	if($rev['gymid']) $battlesql.= ',gymid=0';

	$rev = pkw_ShowInfo($rev);//重新计算双方数值
	$obv = pkw_ShowInfo($obv);

	$rev['tmpstatus'] && $rev['tmpstatus'] = serialize($rev['tmpstatus']);
	$obv['tmpstatus'] && $obv['tmpstatus'] = serialize($obv['tmpstatus']);


	$rev_change = array_intersect_key(pkw_array_diff($ori_rev, $rev), $skillext_allowed_key);unset($ori_rev);
	$obv_change = array_intersect_key(pkw_array_diff($ori_obv, $obv), $skillext_allowed_key);unset($ori_rev);
	foreach($rev_change as $k => $v) $revsql.= ','.$k.'='.$v;
	foreach($obv_change as $k => $v) $obvsql.= ','.$k.'='.$v;

	$revsql = substr($revsql, 1);
	if(!empty($revsql))
		$db->query("UPDATE {$tpre}mymon SET $revsql WHERE id=$rev[id]");
	$obvsql = substr($obvsql, 1);
	if(!empty($obvsql))
		$db->query("UPDATE {$tpre}mymon SET $obvsql WHERE id=$obv[id]");
	$battlesql = substr($battlesql, 1);
	if(!empty($battlesql))
		$db->query("UPDATE {$tpre}trainer SET $battlesql WHERE id=$_USER[id] OR id=$obv[uid]");

	$db->query("UPDATE {$tpre}trainer SET monid=$rev[id],monkid=$kid,obvhurt=$obvhurt $revusql WHERE id=$_USER[id]");
	$obvusql = substr($obvusql, 1);
	if(!empty($obvusql))
		$db->query("UPDATE {$tpre}trainer SET $obvusql WHERE id=$obv[uid]");

	header("Cache-Control: no-cache, must-revalidate");
	header('Content-type: text/plain;charset=GBK');
	include view('battle_ajax');
	exit();

}elseif($action == 'escape'){
	if($obv['spd'] - $rev['spd'] >= rand(-120, 120)){
		showmsg('逃离失败！', 'back');
	}else{
		$db->query("UPDATE {$tpre}trainer SET obvid=0,weather=0,weatherexpiry=0 WHERE id=$_USER[id] OR id=$rev[obvid]");
		writelog('battle', "$rev[id]\t$obv[pokeid]\tAdventure\tObverse");
		showmsg('逃离成功！', 'map.php');
	}
}
include view('battle');
?>
