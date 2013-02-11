<?php
include './include/common.inc.php';
include S_ROOT.'Pokemon_data/dp_attack_data.php';
include S_ROOT.'Pokemon_data/dp_attack_data2.php';
foreach($c as $k => $v){
	$c[strtolower($k)] = $v;
}
foreach($skill as $k => $v){
	$skill[$k] = strtolower($v);
}
$a = file(S_ROOT.'Pokemon_data/dp_moves_level.txt');
for($i=0;$i<=count($a)-1;$i+=2){
	$reqlevel = explode(',', $a[$i]);
	$skillid = explode(',', $a[$i+1]);
	$pokeid = $reqlevel[0];
	for($j=3;$j<=count($reqlevel)-1;$j++){
		$querydata.= ",($pokeid, ".$c[$skill[intval(trim($skillid[$j]))]].", $reqlevel[$j], 1)";
	}
}
$querydata = substr($querydata, 1);
$db->query("INSERT INTO pkw_monskill (`pokeid`,`skillid`,`reqlevel`,`type`) VALUES $querydata");
?>
