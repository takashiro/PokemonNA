<?php
if(!defined('IN_PKW')) exit('Access Denied');
switch($skillext){
	case 1:
	if(rand(1, 10) == 1) $obv['status'] = 6;
break;
	case 2:
	$obv['tmpstatus'][6] = $obv['tmpstatus'][11] = $timestamp + rand(2, 5) * $_CONFIG['battlewaittime'];
break;
	case 3:
	if(rand(1, 10) == 1) $obv['status'] = 6;
	if($rev['status'] == 7) $rev['status'] = 1;
break;
	case 4:
	if(rand(1, 2) == 1) $obv['status'] = 6;
	if($rev['status'] == 7) $rev['status'] = 1;
break;
	case 5:
	setweather(1);
break;
	case 6:
	$obv['status'] = 6;
break;
	case 7:
	$rev_skill['power'] = $rev['hp'] / 20;//尚待完善
break;
	case 8:
	if(rand(1, 10) == 1) $obv['status'] = 6;
	$vitalrand = 5;
break;
	case 9:
	runJS('waittime+=5;');
break;
	case 10:
	$rev['tmpstk']-= floor($rev['stk'] / $rev['level']) * 2;
break;
	case 11:
	if(rand(1, 10) == 1) $obv['status'] = 7;
	$revhurt+= $obvhurt / 3;
break;
	case 12:
	if(rand(1, 10) == 1) $obv['status'] = 6;
	elseif($randtemp == 2){
		$revhurt = 0;
		$obv_undermsg.= '畏缩而没有听训练师的命令出招！';
	}
break;
	case 13:
	if(rand(1, 10) >= 3) $obv['status'] = 6;
break;
	case 14:
	if(rand(1, 10) == 1) $obv['status'] = 7;
break;
	case 15:
	tmpstatus_massedit('add', 1, $timestamp + 5 * $_CONFIG['battlewaittime']);
break;
	case 16:
	if(rand(1, 10) == 1) $obv['status'] = 7;
	if($map['weather'] == 3) $rev_skill['spr'] = 100;
break;
	case 17:
	if(rand(1, 10) == 1) $obv['tmpatk']-= $obv['atk'] * 0.1;
break;
	case 18:
	foreach(array('atk','def','stk','sdf','spd','spr') as $p){
		$rev['tmp'.$p] = 0;
		$obv['tmp'.$p] = 0;
	}
break;
	case 19:
	$obv['tmpspd']-= $obv['spd'] * 0.1;
break;
	case 20:
	setweather(3);
break;
	case 21:
	if($obvhurt <= 0){
		unset($rev['tmpstatus'][4]);
	}
	if($rev['tmpstatus'][5] >= $timestamp){
		$obvhurt *= 2;
	}
	if(!$rev['tmpstatus'][4]){
		$rev['tmpstatus'][4] = array($rev_skill['id'], $_CONFIG['battlewaittime']);
		runJS('waittime += '.$_CONFIG['battlewaittime'].' * 5;');
	}elseif($rev['tmpstatus'][4][1] <= $_CONFIG['battlewaittime'] && $rev['tmpstatus'][4][1] >= 1){
		$rev['tmpstatus'][4][1]--;
		runJS("setTimeout('skill($rev_skill[id])', $_CONFIG[battlewaittime]);");
	}else{
		unset($rev['tmpstatus'][4]);
	}
break;
	case 22:
	if($rev['level'] >= $obv['level'] && rand(1, 100) <= 30 + $rev['level'] - $obv['level']) $obv['status'] = 0;
break;
	case 23:
	$obvhurt = $obvhurt * rand(2, 5);
break;
	case 24:
	if($revhurt >= $rev['maxhp'] / 3) $obvhurt = $obvhurt * 2;
break;
	case 25:
	//先制攻击，不靠此处代码实现
break;
	case 26:
	$randtemp = rand(1, 10);
	if($randtemp == 1) $obv['status'] = 7;
	elseif($randtemp == 2){
		$revhurt = 0;
		$obv_undermsg.= '畏缩而没有听训练师的命令出招！';
	}
break;
	case 27:
break;
	case 28:
	if($obv_skill['ext'] == 36) $rev_skill['power'] = $rev_skill['power'] * 2;
break;
	case 29:
	if(rand(1, 10) == 1) $obv['tmpspd']-= $obv['spd'] / $obv['level'];
break;
	case 30:
	$rev['tmpdef']+= $rev['def'] / $obv['level'];
break;
	case 31:
	if(rand(1, 5) == 1){
		$revhurt = 0;
		$obv_undermsg.= $obv['name'].'畏缩而没有出招！';
		$obv_skill_succeed = false;
	}
break;
	case 32:
	$vitalrand = $vitalrand / 2;
break;
	case 33:
	if(rand(1, 2) == 1) $obv['sprtemp']-= $obv['spr'] / $obv['level'] * 2;
break;
	case 34:
	setweather(2);
break;
	case 35:
	$obv['tmpstatus'][7] <= $timestamp && $obvhurt *= 2;
	$obv['tmpstatus'][6] = $obv['tmpstatus'][11] = $timestamp + rand(1, 5) * $_CONFIG['battlewaittime'];
break;
	case 36:
	if($obv_skill['ext'] != 28) $revhurt = 0;
break;
	case 37:
	if(rand(1, 10) <= 3) $obv['tmpspr'] -= $obv['spr'] / $obv['level'] * 2;
break;
	case 38:
	tmpstatus_massedit('add', 15, array(1, 'MAX'), 'both');
break;
	case 39:
	if(rand(1, 5) == 1){
		$obv['tmpstatus'][2] = $timestamp + rand(1, 5) * $_CONFIG['battlewaittime'];
	}
break;
	case 40:
	if($obv['hp'] < $obv['maxhp'] / 2) $obvhurt = $obvhurt * 2;
break;
	case 41:
	$rev['tmpstatus'][12] = $timestamp + 10 * $_CONFIG['battlewaittime'];
break;
	case 42:
	if(rand(1, 10) == 1){
		$obv['tmpstatus'][2] = $timestamp + rand(1, 5) * $_CONFIG['battlewaittime'];
	}
break;
	case 43:
	if(rand(1, 10) == 1) $obv['tmpsdf']-= $obv['sdf'] / $obv['level'];
break;
	case 44:
	$obv['status'] = 4;
break;
	case 45:
	$rev['tmpatk']+= $rev['atk'] / $rev['level'];
break;
	case 46:
	$rev['tmpspd']+= $rev['spd'] / $rev['level'] * 2;
break;
	case 47:
	$db->query("DELETE FROM {$PKWpre}adventure WHERE id=$mid");
	$db->query("UPDATE {$PKWpre}mymon SET obvid=0,mapid=0 WHERE id=$mid AND owner='$discuz_user'");
	writelog('battle', "$rev[id]\t$obv[pokeid]\tAdventure\tObverse");
	showmsg('逃离成功！', 'pkw.php?index=map', 'undermsg');
break;
	case 48:
	$rev['tmpdef']+= $rev['def'] / $rev['level'] * 2;
break;
	case 49:
	tmpstatus_massedit('add', 17, $timestamp + 5 * $_CONFIG['battlewaittime'], $side);
break;
/*$ext[49]='5回合内己方全队受到特殊攻击伤害减少1/2,双打时改为减少1/3,交换后有效';*/
	case 50:
	tmpstatus_massedit('add', 16, $timestamp + 5 * $_CONFIG['battlewaittime'], $side);
/*$ext[50]='5回合内己方全体受到物理攻击伤害减少1/2,双打时改为1/3,交换后有效';*/
}
?>
