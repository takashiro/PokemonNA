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
$a = file(S_ROOT.'Pokemon_data/dp_moves_egg.txt');
for($i=0;$i<=count($a)-1;$i++){
	$skillid = explode(',', $a[$i]);
	$pokeid = $skillid[0];
	for($j=1;$j<=count($skillid)-1;$j++){
		$querydata.= ",($pokeid, ".$c[$skill[intval(trim($skillid[$j]))]].", 2)";
		//if(!$c[$skill[intval(trim($skillid[$j]))]]) echo $skillid[$j];
	}
}
$querydata = substr($querydata, 1);
$db->query("INSERT INTO pkw_monskill (`pokeid`,`skillid`,`type`) VALUES $querydata");
?>
