<?php
require_once './core/init.inc.php';

if(!$_POST){
	$query = $db->query("SELECT id,pokeid,name,shape FROM {$tpre}pokemon WHERE ownerid=$_USER[id] AND status<9 LIMIT 6");
	$mymon = array();
	while($mon = $db->fetch_array($query)){
		$mon['picid'] = $mon['shape'] ? ($mon['pokeid'].'_'.$mon['shape']) : $mon['pokeid'];
		$mymon[] = $mon;
	}

	$extrac = '';
	$limit = 10;
	$page = intval($page);
	$offset = ($page - 1) * $limit;
	if($type = intval($type)) $extrac = "AND w.type=$type";
	$query = $db->query("SELECT m.id,m.wareid,m.nums,w.ico,w.name_c,w.intro FROM {$tpre}backpack m LEFT JOIN {$tpre}ware w ON w.id=m.wareid WHERE m.ownerid=$_USER[id] AND m.nums>0 $extrac LIMIT $offset,$limit");
	$warelist = array();
	while($ware = $db->fetch_array($query)){
		$warelist[] = $ware;
	}
	$warenums = $db->result_first("SELECT COUNT(*) FROM {$tpre}backpack m LEFT JOIN {$tpre}ware w ON w.id=m.wareid WHERE m.ownerid=$_USER[id] $extrac");
	$multipage = multi($warenums, $limit, $page, "myware.php?type=$type");

}elseif(submitcheck('waresubmit')){
	$nums = intval($nums);
	if($usedid){
		$ware = $db->fetch_first("SELECT m.wareid,m.nums,w.name_c,w.type,w.price,w.result,w.require FROM {$tpre}backpack m LEFT JOIN {$tpre}ware w ON w.id=m.wareid WHERE m.ownerid=$_USER[id] AND m.nums>0 AND m.id=$usedid");
		$nums > $ware['nums'] && showmsg("您没有那么多{$ware[name_c]}！", 'back');
	}
	if(!$ware && $action != 'disarm') exit();

	if($action == 'use'){
		$ajax = intval($ajax);
		$receiver = intval($receiver);
		$nums = intval($nums);
		if($nums <= 0) showmsg('请输入正确的数量！', 'back');
		$result = '';
		if($ware['result']){
			for($i=1;$i<=$nums;$i++){
				$result.= ','.$ware['result'];
			}
			$result = substr($result, 1);
		}else{
			showmsg('此道具无法使用！可能携带于精灵身上才能发挥作用！', 'back');
		}

		if($ware['result'] == 'phpcode'){
			pkw_storage($ware['wareid'], -$nums, $_USER[id]);
			writelog('ware', "$ware[wareid]\t$nums\tUse\t$receiver");
			@include S_ROOT.'system/ware_'.$ware['wareid'].'.inc.php';
			sendpm($_CONFIG['founder_id'], 'ERROR', 'myware_phpcode_'.$ware['wareid']);
			showmsg('程序错误！请等待管理员处理！');
		}
		$extrac = $ware['require']?'AND '.$ware['require']:'';
		switch($ware['type']){
			case 1:$db->query("UPDATE {$tpre}trainer SET $result WHERE id=$_USER[id] $extrac");break;
			case 2:$db->query("UPDATE {$tpre}mymon SET $result WHERE id=$receiver AND owner='$discuz_user' $extrac");break;
			case 4:
				$mymon = $db->fetch_first("SELECT m.skill FROM {$tpre}mymon m LEFT JOIN {$tpre}pokemoninfoskill k ON k.pokeid=m.pokeid WHERE k.skillid=$ware[result] AND k.type=3 AND m.id=$receiver AND m.owner='$discuz_user'");
				if(!$mymon) showmsg('此精灵不能通过技能机器学习此技能！', 'back');
				$mymon['skill'] = explode(',', $mymon['skill']);
				if(count($mymon['skill']) >= 7) showmsg('所学技能已经达到7个了！', 'back');
				if(!$ware['result'] || in_array($ware['result'], $mymon['skill'])) showmsg('不能重复学习同一技能！', 'back');
				$mymon['skill'][] = $ware['result'];
				$mymon['skill'] = implode(',', $mymon['skill']);
				$db->query("UPDATE {$tpre}mymon SET skill='$mymon[skill]' WHERE id=$receiver AND owner='$discuz_user'");
			break;
			case 5:$db->query("UPDATE {$tpre}mymon SET $result WHERE id=$receiver AND owner='$discuz_user' $extrac");break;
		}
		$affected_rows = $db->affected_rows();
		if($affected_rows > 0 && !empty($result)){
			pkw_storage($ware['wareid'], -$nums, $_USER[id]);
			writelog('ware', "$ware[wareid]\t$nums\tUse\t$receiver");
			if($ajax) showmsg('成功使用道具！', '', 'float_confirm_content');
			else showmsg('成功使用道具！', 'referrer');
		}else{
			showmsg('这样无法使用此道具！', $ajax?'':'back', $ajax?'float_confirm_content':'');
		}
	}elseif($action == 'present'){
		if($receiverid = $db->result_first("SELECT id FROM {$tpre}trainer WHERE username='$receiver'")){
			pkw_storage($ware['wareid'], -$nums, $_USER[id]);
			pkw_storage($ware['wareid'], $nums, $receiverid);
			writelog('ware', "$ware[wareid]\t$nums\tPresent\t$receiverid");
			showmsg("成功赠送道具给{$receiver}！", 'referrer');
		}else showmsg('没有这个人或者此人没有领取最初的口袋怪兽！', 'back');
	}elseif($action == 'recycle'){
		pkw_storage($ware['wareid'], -$nums, $_USER[id]);
		$earned = floor($ware['price'] / 2);
		$db->query("UPDATE {$tpre}trainer SET cash=cash+$earned WHERE id=$_USER[id]");
		writelog('ware', "$ware[wareid]\t$nums\tRecycle");
		showmsg('成功回收道具！', 'referrer');
	}elseif($action == 'carry'){
		if(!$receiver = intval($receiver)){
			showmsg('请选择一只口袋怪兽！', 'back');
		}
		if($mymon_equip = $db->result_first("SELECT equip FROM {$tpre}mymon WHERE id=$receiver AND owner='$discuz_user'")){
			pkw_storage($mymon_equip, 1, $_USER[id]);
			writelog('ware', "$mymon_equip\t1\tDisarmed\t$receiver");
		}
		$db->query("UPDATE {$tpre}mymon SET equip=$ware[wareid] WHERE id=$receiver AND owner='$discuz_user'");
		pkw_storage($ware['wareid'], -1, $_USER[id]);
		writelog('ware', "$ware[wareid]\t1\tEquip\t$receiver");
		showmsg('成功给精灵携带！', 'referrer');
	}elseif($action == 'disarm'){
		if(!$receiver = intval($receiver)){
			showmsg('请选择一只口袋怪兽！', 'back');
		}
		if($mymon_equip = $db->result_first("SELECT equip FROM {$tpre}mymon WHERE id=$receiver AND owner='$discuz_user'")){
			pkw_storage($mymon_equip, 1, $_USER[id]);
			writelog('ware', "$mymon_equip\t1\tDisarmed\t$receiver");
		}
		$db->query("UPDATE {$tpre}mymon SET equip=0 WHERE id=$receiver AND owner='$discuz_user'");
		showmsg('成功给精灵卸下！', 'referrer');
	}
}

if(!$ajax){
	unset($_CONFIG['waretype'][0]);
	include view('myware');
}else include view('myware_ajax');
?>