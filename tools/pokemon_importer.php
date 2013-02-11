<?php
include './include/common.inc.php';

if(!$id) $id = 1;elseif($id == 494) exit('task completed!');

$array = file(S_ROOT.'data/mon/'.$id.'.txt');
function getline($line){
	return preg_replace("/^\<.*\>$/", '', $array[$line]);
}

$nationalnumber = $array[0];
$name_c = str_replace(array("\r\n","\r","\n","\t","\s",'\'', '\"'), '',$array[1]);
$name_j = str_replace(array("\r\n","\r","\n","\t","\s",'\'', '\"'), '',$array[2]);
$name_e = str_replace(array("\r\n","\r","\n","\t","\s",'\'', '\"'), '',$array[3]);
$atb1 = $array[4];
$atb2 = $array[5];
$gender = $array[6];
$trait1 = $array[7];
$trait2 = $array[8];
$eggtype1 = $array[9];
$eggtype2 = $array[10];
$height = $array[11];
$weight = $array[12];
$frd = $array[13];
$evostatus = $array[14];
$hp = $array[15];
$atk = $array[16];
$def = $array[17];
$spd = $array[18];
$stk = $array[19];
$sdf = $array[20];

//$db->query("INSERT INTO `pkw_mon` (`id`,`name_c`,`name_j`,`name_e`,`atb1`,`atb2`,`gender`,`trait1`,`trait2`,`eggtype1`,`eggtype2`,`height`,`weight`,`frd`,`evostatus`,`hp`,`atk`,`def`,`spd`,`stk`,`sdf`) VALUES ('$nationalnumber','$name_c','$name_j','$name_e','$atb1','$atb2','$gender','$trait1','$trait2','$eggtype1','$eggtype2','$height','$weight','$frd','$evostatus','$hp','$atk','$def','$spd','$stk','$sdf')");
$db->query("UPDATE `pkw_mon` SET name_e='$name_e',name_j='$name_j',name_c='$name_c' WHERE id=$nationalnumber");

$id++;
echo "正在读取第{$id}个！<meta http-equiv=\"refresh\" content=\"0;url=pokemon_importer.php?id=$id\" />";
?>
