<?php
require_once './core/init.inc.php';
if(!$revid) showmsg('请选择一个精灵！');
else{
	$rev = $db->fetch_first("SELECT u.weather,u.obvid,u.mapid,u.status AS ustatus, u.actiontime,m.* FROM {$tpre}pokemon m
	LEFT JOIN {$tpre}trainer u ON u.id=m.ownerid WHERE m.id=$revid AND m.ownerid=$_USER[id]");
	$ori_rev = $rev = pkw_ShowInfo($rev);
	$rev['tmpstatus'] && $rev['tmpstatus'] = unserialize($rev['tmpstatus']);
	$rev['ustatus'] && $rev['ustatus'] = unserialize($rev['ustatus']);
}

if($rev['hp']<= 0 || $rev['status'] == 0 || $rev['status'] == 2) showmsg('您的精灵已经不能战斗了！', 'map.php');
elseif(empty($rev['skill'])) showmsg('您的精灵未学会任何技能！不能战斗！', 'back');
elseif($rev['status'] > 9) showmsg('此精灵还暂存在研究所！', 'back');

//需要初始化的变量
$undermsg = $rev_topmsg = $rev_undermsg = $obv_topmsg = $obv_undermsg = $revsql = $obvsql = $battlesql = $extrajs = '';
$rev_hideskill = $obv_hideskill = $endbattle = FALSE;
$rev_skill_back = $obv_skill_back = TRUE;
$gid = $rev['mapid']?$rev['mapid']:intval($gid);

$battlesql.= ',actiontime='.$timestamp;//初始化并加入动作时间

if($gid){
	$cachenum = ceil($gid / $_CONFIG['mapcache_per']);
	include S_ROOT.'data/cache/map_'.$cachenum.'.php';
	$map = $_CONFIG['map'][$gid];
	unset($_CONFIG['map']);
	$map['land_c'] = $_CONFIG['land'][$map['land']];
	if(!$rev['obvid']){//没有对手时生成
		include S_ROOT.'system/adven_generate.inc.php';

	}else{
		if($rev['ustatus'][1] >= $timestamp){
			$undermsg.= '喷雾器剩余时间：'.($rev['ustatus'][1] - $timestamp).'秒';
		}

		$obv = $db->fetch_first("SELECT * FROM {$tpre}adventure WHERE id=$_USER[id]");//取出对手数据
		if(!$obv){
			$db->query("UPDATE {$tpre}trainer SET obvid=0 WHERE id=$_USER[id]");
			pkw_reload();
		}
		$ori_obv = $obv = pkw_ShowInfo($obv);
		$obv['tmpstatus'] && $obv['tmpstatus'] = unserialize($obv['tmpstatus']);
		$map['weather'] = ($timestamp <= $obv['weatherexpiry'])?$rev['weather']:$map['weather'];
		
		if(!$kid || !$action){
			$skillname = array();//技能名称显示
			$selected_skill_ids = $rev['skill'];
			$selected_skill_ids.= !empty($obv['skill'])?','.$obv['skill']:'';
			$query = $db->query("SELECT id,name_c FROM {$tpre}skill WHERE id IN ($selected_skill_ids)");
			while($k = $db->fetch_array($query)){
				$skillname[$k['id']] = $k['name_c'];
			}
		}

		if($action == 'fight'){
			include S_ROOT.'system/fight.inc.php';

			$kid = in_array($kid, $rev['skilllist'])?$kid:131;
			$obv_kid = $obv['skilllist'][array_rand($obv['skilllist'])];
			$obv_kid = $obv_kid?$obv_kid:98;

			$query = $db->query("SELECT id,spr,name_c,power,atb,ext,cmp FROM {$tpre}skill WHERE id=$kid OR id=$obv_kid");
			while($k = $db->fetch_array($query)){
				$k['id'] == $kid && $rev_skill = $k;
				$k['id'] == $obv_kid && $obv_skill = $k;
			}
			unset($k);

			//动作时限&ext25-先制攻击
			if(($rev_skill['ext'] != 25 && $timestamp - $rev['actiontime'] < $_CONFIG['battlewaittime']) || $timestamp - $rev['actiontime'] < 2){
				exit('alert(\'请勿非法操作！出招超前！\');');
			}

			$obv_topmsg.= "{$obv[name]}的{$obv_skill[name_c]}！";
			$rev_topmsg.= "{$rev[name]}的{$rev_skill[name_c]}！";

			//技能前置特殊效果
			if(in_array($rev_skill['ext'], $foreext)){skillext_effect($rev_skill['ext'], 'rev');$rev_skill_back = FALSE;}
			if(in_array($obv_skill['ext'], $foreext)){skillext_effect($obv_skill['ext'], 'obv');$obv_skill_back = FALSE;}

			//技能效果
			skill_effect('rev');
			skill_effect('obv');

			//技能后置特殊效果
			$rev_skill_back && skillext_effect($rev_skill['ext'], 'rev');
			$obv_skill_back && skillext_effect($obv_skill['ext'], 'obv');

			//其他效果
			other_effect('rev');
			other_effect('obv');

			//重新计算双方数值
			caculate_point('rev');
			caculate_point('obv');

			$rev['tmpstatus'] && $rev['tmpstatus'] = serialize($rev['tmpstatus']);
			$obv['tmpstatus'] && $obv['tmpstatus'] = serialize($obv['tmpstatus']);

			if($obv['hp'] <= 0){
				$query = $db->fetch_first("SELECT baseexp,ep FROM {$tpre}pokemoninfoext WHERE id=$obv[pokeid]");
				$baseexp = $query['baseexp'];
				$extep = preg_replace("/ep_([a-z]+)\+([\w]+)/", "ep_\\1=ep_\\1+\\2", $query['ep']);
				$rev['exp'] += floor($baseexp / 7 * $obv['level']);
				$revsql.= pkw_exp_incre($rev['level'], $rev['exp'], $rev['id'], $rev['pokeid'], $rev['natureid']);
				$db->query("UPDATE {$tpre}pokemonext SET $extep WHERE id=$rev[id]");
				$db->query("DELETE FROM {$tpre}adventure WHERE id=$_USER[id]");
				writelog('adven', "$rev[id]\t$obv[pokeid]\tReverse");
				$endbattle = TRUE;
			}
			if($rev['hp'] <= 0){
				$revsql = '';
				$db->query("UPDATE {$tpre}pokemon SET status=0,hp=0 $clear_temp_point WHERE id=$rev[id]");
				writelog('adven', "$rev[id]\t$obv[pokeid]\tObverse");
				$endbattle = TRUE;
			}
			if($endbattle){
				$db->query("DELETE FROM {$tpre}adventure WHERE id=$_USER[id]");
				$db->query("UPDATE {$tpre}trainer SET obvid=0,mapid=0 WHERE id=$_USER[id]");
			}

			$rev = pkw_ShowInfo($rev);//重新计算双方数值
			$obv = pkw_ShowInfo($obv);

			$rev_change = array_intersect_key(pkw_array_diff($ori_rev, $rev), $skillext_allowed_key);unset($ori_rev);
			$obv_change = array_intersect_key(pkw_array_diff($ori_obv, $obv), $skillext_allowed_key);unset($ori_obv);
			foreach($rev_change as $k => $v) $revsql.= ','.$k.(is_numeric($v)?'='.$v:"='$v'");
			foreach($obv_change as $k => $v) $obvsql.= ','.$k.(is_numeric($v)?'='.$v:"='$v'");

			$revsql && $revsql = substr($revsql, 1);
			empty($revsql) || $db->query("UPDATE {$tpre}pokemon SET $revsql WHERE id=$rev[id]");
			$obvsql = substr($obvsql, 1);
			empty($obvsql) || $db->query("UPDATE {$tpre}adventure SET $obvsql WHERE id=$_USER[id]");

			$battlesql && $battlesql = substr($battlesql, 1);
			empty($battlesql) || $db->query("UPDATE {$tpre}trainer SET $battlesql WHERE id=$_USER[id]");

			header('Cache-Control: no-cache, must-revalidate');
			header('Content-type: text/plain;charset=GBK');
			include view('adven_ajax');
			exit();
		
		}elseif($action == 'escape'){
			if($obv['spd'] - $rev['spd'] >= rand(-120, 120)){
				showmsg('逃离失败！', 'back');
			}else{
				$db->query("DELETE FROM {$tpre}adventure WHERE id=$_USER[id]");
				$db->query("UPDATE {$tpre}trainer SET obvid=0,mapid=0 WHERE id=$_USER[id]");
				writelog('adven', "$rev[id]\t$obv[pokeid]\tObverse");
				showmsg('逃离成功！', 'map.php');
			}
		}elseif($action == 'catch' && $ballid = intval($ballid))
			include S_ROOT.'system/adven_catch.inc.php';
		include view('adven');
	}
}
?>
