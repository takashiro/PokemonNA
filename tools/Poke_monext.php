<?php
include '../../include/common.inc.php';

//Base stats data: 0 game number, 1 HP, 2 ATK, 3 DEF, 4 SPD, 5 SPATK, 6 SPDEF, 7 type, 8 type, 9 catch rate, 10 base EXP, 
// 11 HP EP, 12 ATK EP, 13 DEF EP, 14 SPD EP, 15 SPATK EP, 16 SPDEF EP, 17 gender, 18 base egg step,
//19 base tameness, 20 growth group, 21 egg group, 22 egg group, 23 ability, 24 ability, 25 color,
//26 D/P item 50%, 27 D/P item 5%, 28 Safari Zone Flag(?)
//1,1,45,49,49,45,65,65,12,3,45,64,0,0,0,0,1,0,31,20,70,3,1,7,65,0,3,0,0,0
$data = file('dp_data_base.txt');
unset($data[0], $data[1], $data[2], $data[3]);
$items = file('dp_list_items.txt');
eval('$itemname = array('.$items[0].');');

$exps = array();
foreach($data as $row){
	$t = explode(',', $row);
	$pokeid = intval($t[1]);
	$item1 = intval($t[27]);
	$item2 = intval($t[28]);
	$db->query("UPDATE `pkw_monext` SET item1=$item1,item2=$item2 WHERE id=$pokeid");
}
?>
