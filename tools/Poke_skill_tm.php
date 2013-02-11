<?php
include './include/common.inc.php';
include S_ROOT.'Pokemon_data/dp_attack_data3.php';
include S_ROOT.'Pokemon_data/dp_attack_data2.php';
foreach($c as $k => $v){
	$c[strtolower($k)] = $v;
}
foreach($skill as $k => $v){
	$skill[$k] = strtolower($v);
}
$a = file(S_ROOT.'Pokemon_data/dp_moves_tm.txt');
for($i=0;$i<=count($a)-1;$i++){
	$skillid = explode(',', $a[$i]);
	$pokeid = $skillid[0];
	if($pokeid <= 493)
	for($j=1;$j<=count($skillid)-1;$j++){
		if(intval(trim($skillid[$j])))
			$querydata.= ",($pokeid, ".$c[$skill[$j]].", 3)";
	}
}
$querydata = substr($querydata, 1);
$db->query("INSERT INTO pkw_monskill (`pokeid`,`skillid`,`type`) VALUES $querydata");
?>
