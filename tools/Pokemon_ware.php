<?php
include './include/common.inc.php';

$hardarray = array(''=>0,'特软'=>1,'很软'=>2,'软'=>3,'硬'=>4,'很硬'=>5,'特硬'=>6);
$powertypearray = array(0,9,10,11,13,14,15,12,17,16,1,2,4,3,6,5,7,8);
function Line($line){
	global $array;
	return trim(str_replace(array("\r\n","\r","\n"), '', addslashes($array[$line])));
}
$beforenumber = 0;
for($j=1;$j<=7;$j++){
	$type = intval($j);
	$array = file(S_ROOT.'data/ware/'.$type.'.txt');
	
	if($type!=4 && $type!=5){
		for($i=0;$i+8<=count($array);$i+=8){
			$ico = Line($i);
			$name_c = Line($i+1);
			$name_j = Line($i+2);
			$name_e = Line($i+3);
			$price = Line($i+4);
			$intro = Line($i+6);
			$querydata1.= ",('$ico','$type','$name_c','$name_j','$name_e','$price','$intro')";
			$beforenumber++;
		}
	}elseif($type == 4){
		for($i=0;$i+4<=count($array);$i+=4){
			$ico = Line($i);
			$name_c = Line($i+1);
			$name_j = Line($i+2);
			$name_e = Line($i+3);
			$querydata2.= ",('$ico','$type','$name_c','$name_j','$name_e')";
			$beforenumber++;
		}
	}elseif($type == 5){
		for($i=0;$i+18<=count($array);$i+=18){
			$ico = substr(Line($i), 3);
			$name_c = substr(Line($i+1), 3);
			$name_j = substr(Line($i+2), 3);
			$name_e = substr(Line($i+3), 3);
			$length = substr(Line($i+4), 3);
			$hot = substr(Line($i+5), 3);
			$puc = substr(Line($i+6), 3);
			$swt = substr(Line($i+7), 3);
			$bit = substr(Line($i+8), 3);
			$tar = substr(Line($i+9), 3);
			$smooth = substr(Line($i+10), 3);
			$hard = $hardarray[substr(Line($i+11), 3)];
			$growtime = str_replace(array('约','小时'),'',substr(Line($i+12), 3));
			$maxnum = substr(Line($i+13), 3);
			$power = substr(Line($i+14), 3);
			$powertype = $powertypearray[intval(str_replace(array('16.type','.png'), '', Line($i+15)))];
			$intro = substr(Line($i+16), 3);
			$querydata4.= ",('$ico','$type','$name_c','$name_j','$name_e','$price','$intro')";
			$querydata3.=  ",('$beforenumber','$length','$hot','$puc','$swt','$bit','$tar','$smooth','$hard','$growtime','$maxnum','$powertype','$power')";
			$beforenumber++;
		}
	}
}

$querydata1 = substr($querydata1, 1);
$querydata2 = substr($querydata2, 1);
$querydata3 = substr($querydata3, 1);
$querydata4 = substr($querydata4, 1);
$db->query("INSERT INTO `pkw_ware` (`ico`,`type`,`name_c`,`name_j`,`name_e`,`price`,`intro`) VALUES $querydata1");
$db->query("INSERT INTO `pkw_ware` (`ico`,`type`,`name_c`,`name_j`,`name_e`) VALUES $querydata2");
$db->query("INSERT INTO `pkw_ware` (`ico`,`type`,`name_c`,`name_j`,`name_e`,`price`,`intro`) VALUES $querydata4");
$db->query("INSERT INTO `pkw_fruit` (`id`,`length`,`hot`,`puc`,`swt`,`bit`,`tar`,`smooth`,`hard`,`growtime`,`maxnum`,`powertype`,`power`) VALUES $querydata3");
?>
