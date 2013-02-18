<?php
if(!defined('IN_PKW')) exit('Access Denied');

pkw_storage($ballid, -1, $_USER[id]);

$query = $db->fetch_first("SELECT e.catchrate,m.growthtype FROM {$PKWpre}monext e,{$PKWpre}mon m WHERE e.id=$obv[pokeid] AND m.id=$obv[pokeid]");
$catchrate = $query['catchrate'];
$obv['growthtype'] = $query['growthtype'];
unset($query);

switch($ballid){
	case 198:$catchrate*= 1;break;
	case 199:$catchrate*= 1.5;break;
	case 200:$catchrate*= 2;break;
	case 201:$catchrate*= ($obv['atb1'] == 2 || $obv['atb1'] == 12 || $obv['atb2'] == 2 || $obv['atb2'] == 12)?3:1;break;
	case 202:$catchrate*= ($obv['atb1'] == 2 || $obv['atb2'] == 2)?3.5:1;break;
	case 203:$catchrate*= ($obv['level'] <= 29)?(rand(11, 39) / 10):1;break;
	case 204:$catchrate*= ($map['type'] == 6)?4:1;break;
	case 205:$catchrate*= ($obv['hp'] / $obv['maxhp']) * 3;break;
	case 207:$catchrate*= ($rev['hp'] / $rev['maxhp']) * 3;break;
	case 208:$catchrate*= 1;break;
	case 209:$catchrate*= 1;$obv['hp'] = $obv['maxhp'];break;
	case 210:$catchrate*= 1;break;
	case 211:$catchrate*= 1;break;
	default:$catchrate*= 0;
}
$catchrate = ($catchrate >= 256)?255:$catchrate;
switch($obv['status']){
	case 3:$fix_effect = 5;
	case 4:$fix_effect = 10;
	case 5:$fix_effect = 5;
	case 6:$fix_effect = 5;
	case 7:$fix_effect = 10;
	default:$fix_effect = 0;
}
$catchrate = ((1 - $obv['hp'] / $obv['maxhp'] * 2 / 3) * $catchrate + $fix_effect +1) / 2.56;

if(rand(1, 100) <= $catchrate){
	$obv['exp'] = pkw_lv2exp($obv['level'], $obv['growthtype']);
	$db->query("DELETE FROM {$PKWpre}adventure WHERE id=$_USER[id]");

	$my_pokenum = $db->result_first("SELECT pokenum FROM {$PKWpre}myprofile WHERE id=$_USER[id]");
	$my_pokenum || showmsg('Error: You own no pokemon but entered the maps');
	$my_pokenum >= 6 && $obv['status'] += 10;

	$db->query("UPDATE {$PKWpre}myprofile SET pokenum=pokenum+1,obvid=0,mapid=0 WHERE id=$_USER[id]");
	$db->query("INSERT INTO {$PKWpre}pokemon (`pokeid`,`shape`,`ownerid`,`owner`,`name`,`regdate`,`atb1`,`atb2`,`level`,`exp`,`status`,`gender`,`natureid`,`trait`,`height`,`weight`,`godev`,`frd`,`hp`,`maxhp`,`mp`,`maxmp`,`atk`,`def`,`stk`,`sdf`,`spd`,`equip`,`skill`) VALUES ('$obv[pokeid]','$obv[shape]','$_USER[id]','$discuz_user','$obv[name]','$timestamp','$obv[atb1]','$obv[atb2]','$obv[level]','$obv[exp]','$obv[status]','$obv[gender]','$obv[natureid]','$obv[trait]','$obv[height]','$obv[weight]','$obv[godev]','$obv[frd]','$obv[hp]','$obv[maxhp]','$obv[mp]','$obv[maxmp]','$obv[atk]','$obv[def]','$obv[stk]','$obv[sdf]','$obv[spd]','$obv[equip]','$obv[skill]')");
	$db->query("INSERT INTO {$PKWpre}pokemonext (`iv_hp`,`iv_atk`,`iv_def`,`iv_stk`,`iv_sdf`,`iv_spd`) VALUES ($obv[iv_hp],$obv[iv_atk],$obv[iv_def],$obv[iv_stk],$obv[iv_sdf],$obv[iv_spd])");
	showmsg('²¶×½³É¹¦£¡', 'pkw.php?index=mypokemon');
}else showmsg('²¶×½Ê§°Ü£¡', 'back');
?>