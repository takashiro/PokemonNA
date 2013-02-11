<?php
require_once './core/init.inc.php';

$mymonlist = array();
$query = $db->query("SELECT id,pokeid,shape,name FROM {$tpre}pokemon WHERE ownerid='$_USER[id]' AND status!=0 AND status!=2 AND status<9 LIMIT 6");
while($m = $db->fetch_array($query)){
	$m['picid'] = $m['shape']?"$m[pokeid]_$m[shape]":$m['pokeid'];
	$mymonlist[] = $m;
}

include S_ROOT.'data/cache_contest.php';
include S_ROOT.'data/data_city.php';
$ctsmap = array();
foreach($_CONFIG['contest'] as $gid => $g){
	$g['id'] = $gid;
	list($g['left'], $g['top']) = explode(',', $_CONFIG['city'][$g['place']][2]);
	$ctsmap[$g['landid']][] = $g;
}
unset($_CONFIG['contest'], $_CONFIG['city']);

include view('map');
?>
