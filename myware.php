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
	if($type = intval($_GET['type'])) $extrac = "AND w.type=$type";
	$query = $db->query("SELECT m.id,m.wareid,m.nums,w.ico,w.name_c,w.intro FROM {$tpre}backpack m LEFT JOIN {$tpre}ware w ON w.id=m.wareid WHERE m.ownerid=$_USER[id] AND m.nums>0 $extrac LIMIT $offset,$limit");
	$warelist = array();
	while($ware = $db->fetch_array($query)){
		$warelist[] = $ware;
	}
	$warenums = $db->result_first("SELECT COUNT(*) FROM {$tpre}backpack m LEFT JOIN {$tpre}ware w ON w.id=m.wareid WHERE m.ownerid=$_USER[id] $extrac");
	$multipage = multi($warenums, $limit, $page, "myware.php?type=$type");

}elseif(submitcheck('waresubmit')){
	$nums = intval($_POST['nums']);
	if($usedid = intval($_POST['usedid'])){
		$ware = $db->fetch_first("SELECT m.wareid,m.nums,w.name_c,w.type,w.price,w.result,w.require FROM {$tpre}backpack m LEFT JOIN {$tpre}ware w ON w.id=m.wareid WHERE m.ownerid=$_USER[id] AND m.nums>0 AND m.id=$usedid");
		$nums > $ware['nums'] && showmsg("您没有那么多{$ware[name_c]}！", 'back');
	}
	if(!$ware && $action != 'disarm') exit();

	$action = &$_POST['action'];
	if($action == 'use'){
		$ajax = intval($_GET['ajax']);
		$receiver = intval($_POST['receiver']);
		if($nums <= 0){
			showmsg('请输入正确的数量！', 'back');
		}
		
		$result = '';
		if($ware['result']){
			for($i=1;$i<=$nums;$i++){
				$result.= ','.$ware['result'];
			}
			$result = substr($result, 1);
		}else{
			showmsg('此道具无法直接使用。', 'back');
		}

		if($ware['result'] == 'phpcode'){
			$backpack = new Backpack($_USER[id]);
$backpack->updateStorage($ware['wareid'], -$nums);
			writelog('ware', "$ware[wareid]\t$nums\tUse\t$receiver");
			@include S_ROOT.'system/ware_'.$ware['wareid'].'.inc.php';
			sendpm($_CONFIG['founder_id'], 'ERROR', 'myware_phpcode_'.$ware['wareid']);
			showmsg('程序错误！请等待管理员处理！');
		}
		$extrac = $ware['require']?'AND '.$ware['require']:'';
		switch($ware['type']){
			case 1:$db->query("UPDATE {$tpre}trainer SET $result WHERE id=$_USER[id] $extrac");break;
			case 2:$db->query("UPDATE {$tpre}pokemon SET $result WHERE id=$receiver AND ownerid=$_USER[id] $extrac");break;
			case 4:
				$mymon = $db->fetch_first("SELECT m.skill FROM {$tpre}pokemon m LEFT JOIN {$tpre}pokemoninfoskill k ON k.pokeid=m.pokeid WHERE k.skillid=$ware[result] AND k.type=3 AND m.id=$receiver AND m.ownerid=$_USER[id]");
				if(!$mymon) showmsg('此精灵不能通过技能机器学习此技能！', 'back');
				$mymon['skill'] = explode(',', $mymon['skill']);
				if(count($mymon['skill']) >= 7) showmsg('所学技能已经达到7个了！', 'back');
				if(!$ware['result'] || in_array($ware['result'], $mymon['skill'])) showmsg('不能重复学习同一技能！', 'back');
				$mymon['skill'][] = $ware['result'];
				$mymon['skill'] = implode(',', $mymon['skill']);
				$db->query("UPDATE {$tpre}pokemon SET skill='$mymon[skill]' WHERE id=$receiver AND ownerid=$_USER[id]");
			break;
			case 5:$db->query("UPDATE {$tpre}pokemon SET $result WHERE id=$receiver AND ownerid=$_USER[id] $extrac");break;
		}
		$affected_rows = $db->affected_rows();
		if($affected_rows > 0 && !empty($result)){
			$backpack = new Backpack($_USER[id]);
$backpack->updateStorage($ware['wareid'], -$nums);
			writelog('ware', "$ware[wareid]\t$nums\tUse\t$receiver");
			if($ajax) showmsg('成功使用道具！', '', 'float_confirm_content');
			else showmsg('成功使用道具！', 'refresh');
		}else{
			showmsg('这样无法使用此道具！', $ajax?'':'back', $ajax?'float_confirm_content':'');
		}

	}elseif($action == 'present'){
		$receiver = &$_POST['receiver'];
		if($receiverid = $db->result_first("SELECT id FROM {$tpre}trainer WHERE username='$receiver'")){
			$backpack1 = new Backpack($_USER['id']);
			$backpack1->updateStorage($ware['wareid'], -$nums);
			$backpack2 = new Backpack($receiverid);
			$backpack2->updateStorage($ware['wareid'], $nums);
			writelog('ware', "$ware[wareid]\t$nums\tPresent\t$receiverid");
			showmsg("成功赠送道具给{$receiver}！", 'refresh');
		}else{
			showmsg('没有这个人或者此人没有领取最初的口袋怪兽！', 'back');
		}

	}elseif($action == 'recycle'){
		$backpack = new Backpack($_USER['id']);
		$backpack->updateStorage($ware['wareid'], -$nums);
		$earned = floor($ware['price'] / 2);
		$db->query("UPDATE {$tpre}trainer SET cash=cash+$earned WHERE id=$_USER[id]");
		writelog('ware', "$ware[wareid]\t$nums\tRecycle");
		showmsg('成功回收道具！', 'refresh');
	}elseif($action == 'carry'){
		if(!$receiver = intval($_POST['receiver'])){
			showmsg('请选择一只口袋怪兽！', 'back');
		}
		if($mymon_equip = $db->result_first("SELECT equip FROM {$tpre}pokemon WHERE id=$receiver AND ownerid=$_USER[id]")){
			$backpack = new Backpack($_USER[id]);
			$backpack->updateStorage($mymon_equip, 1);
			writelog('ware', "$mymon_equip\t1\tDisarmed\t$receiver");
		}
		$db->query("UPDATE {$tpre}pokemon SET equip=$ware[wareid] WHERE id=$receiver AND ownerid=$_USER[id]");
		$backpack = new Backpack($_USER['id']);
		$backpack->updateStorage($ware['wareid'], -1);
		writelog('ware', "$ware[wareid]\t1\tEquip\t$receiver");
		showmsg('成功给精灵携带！', 'refresh');
	}elseif($action == 'disarm'){
		if(!$receiver = intval($receiver)){
			showmsg('请选择一只口袋怪兽！', 'back');
		}
		if($mymon_equip = $db->result_first("SELECT equip FROM {$tpre}pokemon WHERE id=$receiver AND ownerid=$_USER[id]")){
			$backpack = new Backpack($_USER[id]);
$backpack->updateStorage($mymon_equip, 1);
			writelog('ware', "$mymon_equip\t1\tDisarmed\t$receiver");
		}
		$db->query("UPDATE {$tpre}pokemon SET equip=0 WHERE id=$receiver AND ownerid=$_USER[id]");
		showmsg('成功给精灵卸下！', 'refresh');
	}
}

if(!$ajax){
	unset(Ware::$Type[0]);
	include view('myware');
}else include view('myware_ajax');
?>