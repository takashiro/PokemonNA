<?php
function array_k2v($array){
	$returnarray = array();
	foreach($array as $key => $value){
		$returnarray[$value] = $key;
	}
	return $returnarray;
}
$atbarray = array_k2v(array('','火','水','电','草','冰','超','龙','恶','普','格','飞','虫','毒','地','岩','钢','鬼'));
$eggarray = array_k2v(array('', '???','怪兽','水中1','水中2','水中3','虫','飞行','陆上','妖精','植物','矿物','人形','不定形','百变怪','龙','未发现'));
$eggarray['无定形'] = $eggarray['不定形'];
$eggarray['性别不明'] = $eggarray['矿物'];

$evostatus = array_k2v(array('', '初级型态', '中等型态', '最终型态','神兽'));
include './include/common.inc.php';
if($_GET['id']) $id = $_GET['id'];else $id = 0;
if($id==493) exit('<meta http-equiv="refresh" content="0;url=pokemon_importer.php" />');
$array = file(S_ROOT.'Pokemon_data/Pokemon/'.$id.'.htm');
function convert($line){
	global $array;
	return trim(preg_replace("/\<.*?\>/s", '', (iconv('utf-8', 'gbk', $array[$line-1]))));
}
$nationalnumber = convert(558);
$name_c = convert(567);
$name_j = convert(569);
$name_e = convert(571);

$atb = explode('/', convert(576));
$atb1 = $atbarray[trim($atb[0])];
$atb2 = $atbarray[trim($atb[1])];

$fixnumber = 0;

$temp1 = explode("DPTrait.asp?ID=", iconv('utf-8', 'gbk', $array[577]));
$temp2 = explode('&', $temp1[1]);
$trait1 = intval($temp2[0]);
if($trait1 == 0) $fixnumber -= 1;

$temp1 = explode("DPTrait.asp?ID=", iconv('utf-8', 'gbk', $array[580]));
$temp2 = explode('&', $temp1[1]);
$trait2 = intval($temp2[0]);
if($trait2 != 0) $fixnumber += 1;

$eggtype1 = $eggarray[str_replace('组', '', convert(586+$fixnumber))];
$eggtype2 = $eggarray[str_replace('组', '', convert(588+$fixnumber))];

$size = explode('/', str_replace(array('kg', 'm'), '', convert(595+$fixnumber)));
$height = trim($size[0]);
$weight = trim($size[1]);

$frd = convert(604+$fixnumber);

$evoarray = array('初级型态'=>1, '中等型态'=>2, '最终形态'=>3, '神兽'=>4);
$evostatus = $evostatus[convert(619+$fixnumber)];

$hp = trim(preg_replace("/（.*?）/s", '', convert(657+$fixnumber)));
$atk = trim(preg_replace("/（.*?）/s", '', convert(666+$fixnumber)));
$def = trim(preg_replace("/（.*?）/s", '', convert(675+$fixnumber)));
$spd = trim(preg_replace("/（.*?）/s", '', convert(684+$fixnumber)));
$stk = trim(preg_replace("/（.*?）/s", '', convert(693+$fixnumber)));
$sdf = trim(preg_replace("/（.*?）/s", '', convert(702+$fixnumber)));

$gender = explode(':', convert(597+$fixnumber));
$gender[0] = intval($gender[0]);$gender[1]=intval($gender[1]);
if($gender[0] == 0 && $gender[1] == 100) $gen = 2;
elseif($gender[0] == 100 && $gender[1] == 0) $gen = 1;
elseif($gender[0] == 0 && $gender[1] == 0) $gen = 3;
else $gen = 0;

$db->query("UPDATE `pkw_mon` SET gender=$gen WHERE id=$nationalnumber");
$id++;
echo "<meta http-equiv=\"refresh\" content=\"0;url=pokemon_mon.php?id={$id}\" />正在处理No.{$id}……";

@$fp = fopen(S_ROOT.'data/mon/'.$nationalnumber.'.txt', 'w');
$txt = "$nationalnumber
$name_c
$name_j
$name_e
$atb1
$atb2
$gen
$trait1
$trait2
$eggtype1
$eggtype2
$height
$weight
$frd
$evostatus
$hp
$atk
$def
$spd
$stk
$sdf";
@fwrite($fp, $txt);
@fclose($fp);
/*全国编号：<?=$nationalnumber?><br />
名字：<?=$name_c?> <?=$name_j?> <?=$name_e?><br />
属性1：<?=$atb1?><br />
属性2：<?=$atb2?><br />
父体比率：<?=$gender?><br />
特性：<?=$trait1?> | <?=$trait2?><br />
生蛋组1：<?=$eggtype1?><br />
生蛋组2：<?=$eggtype2?><br />
身高：<?=$height?><br />
体重：<?=$weight?><br />
友好度：<?=$frd?><br />
进化状态：<?=$evostatus?><br />
HP：<?=$hp?><br />
攻击：<?=$atk?><br />
防御：<?=$def?><br />
速度：<?=$spd?><br />
特攻：<?=$stk?><br />
特防：<?=$sdf?><br />*/
?>
