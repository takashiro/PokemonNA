<?php
require_once './core/init.inc.php';
include S_ROOT.'data/cache_contest.php';

if(!$g = $_CONFIG['contest'][$gid]) showmsg('抱歉，本次华丽大赛已经结束了！', 'back');
else $g['banneduser'] = explode(',', $g['banneduser']);

if($timestamp >= $g['entertime1'] && $timestamp <= $g['entertime2']){//报名时间
	if(!submitcheck('applypassport')){
		$limit = 12;
		$page = intval($page);
		$offset = ($page - 1) * $limit;
		$userlist = array();
		$query = $db->query("SELECT id,username,pokenum,badgenum,ribbonnum FROM {$tpre}trainer WHERE ctsid=$gid LIMIT $offset,$limit");
		while($u = $db->fetch_array($query)){
			$userlist[] = $u;
		}
		$totalusernum = $db->result_first("SELECT COUNT(*) FROM {$tpre}trainer WHERE ctsid=$gid");
		$multipage = multi($totalusernum, $limit, $page, 'contest.php?gid='.$gid);
	}else{
		if($action == 'join'){
			if(in_array($_USER[id], $g['banneduser'])) showmsg('我们很抱歉地通知您您不能参加本次华丽大赛。', 'back');
			$db->query("UPDATE {$tpre}trainer SET ctsid=$gid WHERE id=$_USER[id]");
			showmsg('成功报名参加'.$g['name'].'华丽大赛！您之前报名的任何华丽大赛都自动取消！', 'back');
		}elseif($action == 'exit'){
			$db->query("UPDATE {$tpre}trainer SET ctsid=0 WHERE id=$_USER[id]");
			if($db->affected_rows() != 1) showmsg('您没有报名任何华丽大赛！', 'back');
			else showmsg('我们很遗憾您不能参加'.$g['name'].'华丽大赛！', 'back');
		}
	}
	foreach(array('entertime1','entertime2','starttime1','endtime1','starttime2','endtime2') as $tk){
		$g[$tk] = gmdate('Y-m-d H:i:s', $g[$tk] + $timeoffset * 3600);
	}
	include view('contest_enter');

}elseif($timestamp >= $g['starttime1'] && $timestamp <= $g['endtime1']){//一级审查
	include view('contest_ex1');

}elseif($timestamp >= $g['starttime2'] && $timestamp <= $g['endtime2']){//二级审查
	include view('contest_ex2');
}
?>