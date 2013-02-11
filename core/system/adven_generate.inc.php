<?
if(!defined('IN_PKW') || $index != 'adven') exit('Access Denied');

if(!$map['normalmonids']) showmsg('这里没有野生精灵！','back');
else{
	if($map['raremonids'] && rand(1, 50) == 1) $map['monids'] = $map['raremonids'];
	else $map['monids'] = $map['normalmonids'];
	$hour_now = intval(gmdate('H', $timestamp + 3600 * $timeoffset));
	if($map['daymonids'] && $hour_now >= 6 && $hour_now <= 18)
		$map['monids'].= $map['daymonids'];
	elseif($map['nightmonids'] && ($hour_now <=6 || $hour_now >= 18))
		$map['monids'].= $map['nightmonids'];

	$map['mon'] = explode(',', $map['monids']);
	$obvid = $map['mon'][array_rand($map['mon'])];
}

if($rev['ustatus'][1] >= $timestamp){
	if($map['maxlevel'] < $rev['level']) showmsg('这里没有野生精灵！','refresh');
	else $obv['level'] = rand($rev['level'], $map['maxlevel']);
}else{
	$obv['level'] = rand($map['minlevel'], $map['maxlevel']);
}
$obv = pkw_generateMon($obvid, $obv['level']);

$obv_skill = array();
$query = $db->query("SELECT skillid FROM {$PKWpre}monskill WHERE pokeid=$obvid AND type=1 AND reqlevel<=$obv[level] ORDER BY reqlevel LIMIT 7");
while($k = $db->fetch_array($query)){
	$obv_skill[] = $k['skillid'];
}
$obv_skill = implode(',', array_unique($obv_skill));

$temp = $db->query("SELECT item1,item2 FROM {$PKWpre}monext WHERE id=$obvid");
$itemrand = rand(1, 20);
if($temp['item2'] && $itemrand == 1) $obv['equip'] = $temp['item2'];
elseif($temp['item1'] && $itemrand >= 10) $obv['equip'] = $item['item1'];
else $obv['equip'] = 0;
unset($temp);

$db->query("INSERT INTO {$PKWpre}adventure (id,pokeid,shape,name,atb1,atb2,gender,trait,natureid,godev,level,status,hp,maxhp,mp,maxmp,height,weight,atk,def,stk,sdf,spd,iv_hp,iv_atk,iv_def,iv_stk,iv_sdf,iv_spd,equip,skill)
VALUES ($_USER[id],$obv[id],$obv[shape],'$obv[name_c]',$obv[atb1],$obv[atb2],$obv[gender],$obv[trait],$obv[natureid],$obv[godev],$obv[level],$obv[status],$obv[hp],$obv[hp],$obv[mp],$obv[mp],$obv[height],$obv[weight],$obv[atk],$obv[def],$obv[stk],$obv[sdf],$obv[spd],$obv[iv_hp],$obv[iv_atk],$obv[iv_def],$obv[iv_stk],$obv[iv_sdf],$obv[iv_spd],$obv[equip],'$obv_skill')");
$db->query("UPDATE {$PKWpre}myprofile SET obvid=$obvid,mapid=$gid WHERE id=$_USER[id]");
header("Location: pkw.php?index={$index}&gid={$gid}&revid={$rev[id]}");
?>
