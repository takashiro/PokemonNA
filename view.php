<?php
require_once './core/init.inc.php';

$table = '';
$action = $action?$action:'member';
$limit = 15;
$page = intval($page);
$offset = $limit * ($page - 1);
$list = array();

if($username = rhtmlspecialchars($username)){
	$query = $db->query("SELECT * FROM {$tpre}trainer WHERE username='$username'");
	$user = $db->fetch_array($query);
	$monlist = array();
	$query = $db->query("SELECT id,pokeid,name,shape,level FROM {$tpre}pokemon WHERE owner='$username' AND status!=9");
	while($mon = $db->fetch_array($query)){
		$mon['picid'] = $mon['shape']?$mon['pokeid'].'_'.$mon['shape']:$mon['pokeid'];
		$monlist[] = $mon;
	}
}elseif($action == 'pokemon'){
	$headline = '精灵';
	include S_ROOT.'data/data_nature.php';
	$query = $db->query("SELECT id,name,owner,pokeid,natureid,atb1,atb2,level,gender,height,weight FROM {$tpre}pokemon WHERE 1 LIMIT $offset, $limit");
	while($m = $db->fetch_array($query)){
		$m['gender_c'] = Pokemon::$Gender[$m['gender']];
		$m['nature'] = $_CONFIG['nature'][$m['natureid']]['name_c'];
		$list[] = $m;
	}
	unset($_CONFIG['nature']);
	$nums = $db->result_first("SELECT COUNT(*) AS num FROM {$tpre}pokemon");
	$multipage = multi($nums, $limit, $page, 'memberlist.php?action='.$action);
}elseif($action == 'member'){
	$headline = '训练师';
	$query = $db->query("SELECT * FROM {$tpre}trainer WHERE 1 LIMIT $offset, $limit");
	while($u = $db->fetch_array($query)){
		$u['badgelist'] = explode(',', $u['badge']);
		$u['badgenum'] = $u['badge']?count($u['badgelist']):0;
		$u['challenged'] = in_array($g['id'], $u['badgelist'])?TRUE:FALSE;
		$u['ribbonlist'] = explode(',', $u['ribbon']);
		$u['ribbonnum'] = $u['ribbon']?count($u['ribbonlist']):0;
		$list[] = $u;
	}
	$nums = $db->result_first("SELECT COUNT(*) AS num FROM {$tpre}trainer WHERE 1");
	$multipage = multi($nums, $limit, $page, 'memberlist.php?action='.$action);
}elseif($action == 'ajax'){
	header("Cache-Control: no-cache, must-revalidate");
	header('Content-type: text/plain;charset=GBK');
	$query = $db->query("SELECT id,name,pokeid,shape FROM {$tpre}pokemon WHERE owner='$discuz_user' AND status!=0 AND status!=9");
	while($m = $db->fetch_array($query)){
		$m['picid'] = $m['shape']?$m['pokeid'].'_'.$m['shape']:$m['pokeid'];
		echo '<a href="adven.php?gid=1&revid='.$m['id'].'" title="'.$m['name'].'"><img src="'.$imgdir.'/pokemon/'.$m['picid'].'.gif" width="130" height="120" /></a>';
	}
	exit();
}

include view('view');
?>