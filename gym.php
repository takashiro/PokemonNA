<?php
require_once './core/init.inc.php';

$gid = intval($gid);

if($groupid == $_CONFIG['gymleader_id']){
	include S_ROOT.'data/cache/gym.php';
	foreach($_CONFIG['gym'] as $tempg){
		if($tempg['leader'] == $discuz_user) $g = $tempg;break;
	}
	unset($_CONFIG['gym'], $tempg);

	$challengerlist = array();
	$query = $db->query("SELECT id,username,pokenum,badge,ribbon FROM {$tpre}trainer WHERE gymid=$g[id]");
	while($u = $db->fetch_array($query)){
		$u['badgelist'] = explode(',', $u['badge']);
		$u['badgenum'] = $u['badge']?count($u['badgelist']):0;
		$u['challenged'] = in_array($g['id'], $u['badgelist'])?TRUE:FALSE;
		$u['ribbonlist'] = explode(',', $u['ribbon']);
		$u['ribbonnum'] = $u['ribbon']?count($u['ribbonlist']):0;
		$challengerlist[] = $u;
	}
}else{
	$my = $db->fetch_first("SELECT gymid FROM {$tpre}trainer WHERE id=$_USER[id]");
	if($my['gymid'] != $gid){
		$db->query("UPDATE {$tpre}trainer SET gymid=$gymid WHERE id=$_USER[id]");
		showmsg('您的挑战申请已经送出，请耐心等待回应！', 'refresh');
	}else{
		include S_ROOT.'data/cache/gym.php';
		$g = $_CONFIG['gym'][$gid];
		unset($_CONFIG['gym']);

		$leader = $db->fetch_first("SELECT id AS uid,obvid FROM {$tpre}trainer WHERE username='$g[leader]'");
		if($leader['obvid'] != $_USER[id])
			showmsg('您的挑战申请已经送出，请耐心等待回应！', 'refresh');
		else
			showmsg('轮到您向馆主挑战了，期待您获得胜利！', "battle.php?obvid=$leader[uid]&receiver=1");
	}
}

include view('gym');
?>