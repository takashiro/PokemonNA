<?php
include '../../include/common.inc.php';

$item = array();
$query = $db->query("SELECT item1,item2 FROM `pkw_monext`");
while($m = $db->fetch_array($query)){
	$item[] = $m['item1'];
	$item[] = $m['item2'];
}
$item = array_values(array_unique($item));

$oldname = file('dp_list_items.txt');
$oldname = explode('\',\'', substr($oldname[0], 1, strlen($oldname[0])-1));

$newids = array();
$query = $db->query("SELECT id,name_e FROM `pkw_ware`");
while($w = $db->fetch_array($query)){
	$newids[str_replace(' ', '', strtolower($w['name_e']))] = $w['id'];
}

foreach($item as $id){
	$updateid = $newids[str_replace(' ', '', strtolower($oldname[$id]))];
	if($updateid){
		$db->query("UPDATE `pkw_monext` SET item1=$updateid WHERE item1=$id");
		$db->query("UPDATE `pkw_monext` SET item2=$updateid WHERE item2=$id");
	}
}
?>
