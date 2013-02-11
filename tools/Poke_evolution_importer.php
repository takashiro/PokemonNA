<?php
//Evolution data: 0 game number, 1 evo type, (2 level/ item), (3 evolves to), (4 evolves from), [5 baby form item]
//1,0,1,4,16,2,1

include './include/common.inc.php';
$a = file(S_ROOT.'Pokemon_data/dp_data_evolution.txt');

$querydata = '';
foreach($a as $v){
	$d = explode(',', $v);
	$original = intval($d[2]);
	$evotype = intval($d[3]);
	$evoluted = intval($d[5]);
	$require = intval($d[4]);
	if($evotype) $querydata.= ",($original,$evotype,$require,$evoluted)";
}
$querydata = substr($querydata, 1);

$db->query("INSERT INTO `pkw_evolution` (`original`,`evotype`,`require`,`evoluted`) VALUES $querydata");
echo 'Pokemon Evolution Data Imported!';
?>
