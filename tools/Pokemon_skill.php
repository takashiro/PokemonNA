<?php
include './include/common.inc.php';

function array_k2v($array){
	$returnarray = array();
	foreach($array as $key => $value){
		$returnarray[$value] = $key;
	}
	return $returnarray;
}

$btytypearray = array_k2v(array('', '出色','美丽','可爱','聪明','坚强'));
$atbarray = array_k2v(array('','火','水','电','草','冰','超能','龙','邪恶','普通','格斗','飞行','虫','毒','地面','岩石','钢','鬼'));
$typearray = array_k2v(array('','物理','特殊','其它'));
$aftarray = array_k2v(array('','选择','敌二体','自身','不定','己方全场','自身以外','全场','敌随机','队友'));

$querydata = '';
$extarray = $btyextarray = array('');

for($id=1;$id<=5;$id++){
	$array = file(S_ROOT.'data/skill/'.$id.'.txt');
	for($i=0;$i+18<=count($array);$i+=20){
		$name_c = trim($array[$i]);
		$name_j = trim($array[$i+13]);
		$name_e = trim($array[$i+15]);
		
		$temp = explode('：', $array[$i+1]);
		$priority = trim($temp[1]);
		
		$bln = '';
		$temp = explode('：', $array[$i+2]);
		if(trim($temp[1]) == '○') $bln.= 'A';
		$temp = explode('：', $array[$i+3]);
		if(trim($temp[1]) == '○') $bln.= 'B';
		$temp = explode('：', $array[$i+4]);
		if(trim($temp[1]) == '○') $bln.= 'C';
		$temp = explode('：', $array[$i+5]);
		if(trim($temp[1]) == '○') $bln.= 'D';
		$temp = explode('：', $array[$i+6]);
		if(trim($temp[1]) == '○') $bln.= 'E';
		
		$atb = $atbarray[trim($array[$i+7])];
		$type = $typearray[trim($array[$i+8])];
		$power = trim($array[$i+9]);
		$spr = trim($array[$i+10]);
		$cost = trim($array[$i+11]);
		$aft = $aftarray[trim($array[$i+12])];
	
		$ext = trim($array[$i+14]);
		if(in_array($ext, $extarray)){
			foreach($extarray as $key => $value){
				if($value == $ext) $ext = $key;
			}
		}else{
			$extarray[] = $ext;
			$ext = count($extarray) - 1;
		}
	
		$btytype = $btytypearray[trim($array[$i+16])];
		$bty = trim($array[$i+17]);
		$btyext = trim($array[$i+18]);
		if(in_array($btyext, $btyextarray)){
			foreach($btyextarray as $key => $value){
				if($value == $btyext) $btyext = $key;
			}
		}else{
			$btyextarray[] = $btyext;
			$btyext = count($btyextarray) - 1;
		}
		
		$querydata.= ", ('$name_c','$name_j','$name_e','$priority','$bln','$atb','$type','$power','$spr','$cost','$aft','$ext','$btytype','$bty','$btyext')";
	}
}
$querydata = substr($querydata, 1);
$db->query("TRUNCATE `pkw_skill`");
$db->query("INSERT INTO `pkw_skill` (`name_c`,`name_j`,`name_e`,`priority`,`bln`,`atb`,`type`,`power`,`spr`,`cmp`,`aft`,`ext`,`btytype`,`bty`,`btyext`) VALUES $querydata");

$ext_text = '';
foreach($extarray as $key => $value){
	$ext_text.= "{$key}_{$value}\r\n";
}
$fp = fopen(S_ROOT.'data/skill_ext.txt', 'w');
fwrite($fp, $ext_text);
fclose($fp);

$btyext_text = '';
foreach($btyextarray as $key => $value){
	$btyext_text.= "{$key}_{$value}\r\n";
}
$fp = fopen(S_ROOT.'data/skill_btyext.txt', 'w');
fwrite($fp, $btyext_text);
fclose($fp);

echo 'Task Completed!!';
?>
