<?php
if(!defined('IN_PKW')) exit('Access Denied');

//属性克制表
$_CONFIG['atb'][1]=array(1,0.5,2,1,0.5,0.5,1,1,1,1,1,1,0.5,1,2,2,0.5,1);
$_CONFIG['atb'][2]=array(1,0.5,0.5,2,2,0.5,1,1,1,1,1,1,1,1,1,1,0.5,1);
$_CONFIG['atb'][3]=array(1,1,1,0.5,1,1,1,1,1,1,1,0.5,1,1,2,1,0.5,1);
$_CONFIG['atb'][4]=array(1,2,0.5,0.5,0.5,2,1,1,1,1,1,2,2,2,0.5,1,1,1);
$_CONFIG['atb'][5]=array(1,2,1,1,1,0.5,1,1,1,1,2,1,1,1,1,2,2,1);
$_CONFIG['atb'][6]=array(1,1,1,1,1,1,0.5,1,2,1,0.5,1,2,1,1,1,1,2);
$_CONFIG['atb'][7]=array(1,0.5,0.5,0.5,0.5,2,1,2,1,1,1,1,1,1,1,1,1,1);
$_CONFIG['atb'][8]=array(1,1,1,1,1,1,0,1,0.5,1,2,1,2,1,1,1,1,0.5);
$_CONFIG['atb'][9]=array(1,1,1,1,1,1,1,1,1,1,2,1,1,1,1,1,1,0);
$_CONFIG['atb'][10]=array(1,1,1,1,1,1,2,1,0.5,1,1,2,0.5,1,1,0.5,1,1);
$_CONFIG['atb'][11]=array(1,1,1,2,0.5,2,1,1,1,1,0.5,1,0.5,1,0,2,1,1);
$_CONFIG['atb'][12]=array(1,2,1,1,0.5,1,1,1,1,1,0.5,2,1,1,0.5,2,1,1);
$_CONFIG['atb'][13]=array(1,1,1,1,0.5,1,2,1,1,1,0.5,1,0.5,0.5,2,1,1,1);
$_CONFIG['atb'][14]=array(1,1,2,0,2,2,1,1,1,1,1,1,1,0.5,1,0.5,1,1);
$_CONFIG['atb'][15]=array(1,0.5,2,1,2,1,1,1,1,0.5,2,0.5,1,0.5,2,1,2,1);
$_CONFIG['atb'][16]=array(1,2,1,1,0.5,0.5,0.5,0.5,0.5,0.5,2,0.5,0.5,0,2,0.5,0.5,0.5);
$_CONFIG['atb'][17]=array(1,1,1,1,1,1,1,1,2,0,0,1,0.5,0.5,1,1,1,2);

//天气附加时长
$_CONFIG['weatherextraexpiry'] = array(0, 103, 104, 106, 105, 155);

//前置技能附加效果表
$foreext = array(16,57,59,73);

//附加效果-直接异常状态或弱化
$direct_status_ext = array(6, 83);

//技能附加效果能够修改的字段
$skillext_allowed_key = array_flip(array('shape','status','hp','mp','tmpstatus','tmptrait','tmpatk','tmpdef','tmpstk','tmpsdf','tmpspd','tmpspr','equip','skill'));

/*
临时附加状态2 $rev['tmpstatus'][key] = expiry
1 -> 能力不被下降
2 -> 混乱
3 -> 反弹对方附加效果
4 -> 连续攻击5回合,首次威力30,其后回合命中则威力为上一回合的两倍,未命中则中止攻击,变圆后威力乘2,不换下场变圆2倍加成一直存在
     array($skill_id, $rest_times)
5 -> 变圆
6 -> 不可交换或逃走
7 -> 水下
8 -> 飞空
9 -> 地下
10 -> 漂浮，不受地面攻击
11 -> 每回合损失1/16体力
12 -> 每回合恢复1/16体力
13 -> 临时无法使用多个技能
      array($skill_ids = array(), $expiry)
14 -> 害怕，无法出招
15 -> 对$atb属性攻击伤害减半
	  array($atb, $expiry)
16 -> 对物理攻击伤害减半
17 -> 对特殊攻击伤害减半
18 -> 无视属性抵抗造成伤害
19 -> 不可防御
20 -> $value后产生eval($effect)效果
	  array($effect, $value)
*/
$clear_temp_point = ',tmpstatus=\'\',tmpatk=0,tmpdef=0,tmpstk=0,tmpsdf=0,tmpspd=0,tmpspr=0';

$rev_skill_succeed = $obv_skill_succeed = TRUE;

function runJS($code){
	$GLOBALS['extrajs'].= $code;
}

function atbeffect($skillatb, $obvatb1, $obvatb2){
	return $GLOBALS['_PKW']['atb'][$skillatb][$obvatb1] * $GLOBALS['_PKW']['atb'][$skillatb][$obvatb2];
}

function posit($num){
	return ($num >= 0)?$num:0;
}

function skill_effect($side){
	global $timestamp;
	$side = ($side == 'rev')?'rev':'obv';
	$obside = ($side == 'rev')?'obv':'rev';
	if($GLOBALS[$side.'_skill']['type'] < 3){
		if($GLOBALS[$side]['status'] == 5 && rand(1, 5) <= 2){
			$GLOBALS[$side.'_skill']['spr'] = 0;
			$GLOBALS[$side.'_topmsg'] = $GLOBALS[$side]['name'].'麻痹了，无法攻击！';
		}
		if($GLOBALS[$side]['mp'] > 0 && $GLOBALS[$side.'_skill']['power'] && $GLOBALS[$side.'_skill']['spr'] >= rand(1, 105)){//技能攻击
			$atbefct = atbeffect($GLOBALS[$side.'_skill']['atb'], $GLOBALS[$obside]['atb1'], $GLOBALS[$obside]['atb2']);
			//其他因素修正(道具加成)在此添加
			if($GLOBALS[$side.'_skill']['atb'] == $GLOBALS[$side]['atb1'] || $GLOBALS[$side.'_skill']['atb'] == $GLOBALS[$side]['atb2'])//同属性修正
				$GLOBALS[$side.'_skill']['power'] = $GLOBALS[$side.'_skill']['power'] * 1.5;
			$GLOBALS[$obside.'hurt'] = max(1, ($GLOBALS[$side.'_skill']['atb'] <= 8)?floor((($GLOBALS[$side]['level'] * 0.4 + 2) * $GLOBALS[$side.'_skill']['power'] * $GLOBALS[$side]['stk'] / $GLOBALS[$obside]['sdf'] / 50 + 2) * $atbefct * rand(85, 100) / 100):floor((($GLOBALS[$side]['level'] * 0.4 + 2) * $GLOBALS[$side.'_skill']['power'] * $GLOBALS[$side]['atk'] / $GLOBALS[$obside]['def'] / 50 + 2) * $atbefct * rand(85, 100) / 100));
	
		}else{
			$GLOBALS[$obside.'hurt'] = 0;
			if($GLOBALS[$side.'_skill']['power']) $GLOBALS[$side.'_topmsg'] = $GLOBALS[$side]['name'].'没有击中！';
		}
	}
}

function skillext_effect($skillext, $side){
	global $map, $db, $timestamp, $PKWpre, $_CONFIG;
	$rev_skill_succeed = $obv_skill_succeed = TRUE;
	if($side == 'rev'){
		$rev = $GLOBALS['rev'];$obv = $GLOBALS['obv'];
		$revhurt = $GLOBALS['revhurt'];$obvhurt = $GLOBALS['obvhurt'];
		$rev_skill = $GLOBALS['rev_skill'];$obv_skill = $GLOBALS['obv_skill'];
	}elseif($side == 'obv'){
		$rev = $GLOBALS['obv'];$obv = $GLOBALS['rev'];
		$revhurt = $GLOBALS['obvhurt'];$obvhurt = $GLOBALS['revhurt'];
		$rev_skill = $GLOBALS['obv_skill'];$obv_skill = $GLOBALS['rev_skill'];
	}
	$revsql = $obvsql = $battlesql = '';
	$vitalrand = 10;
	$datafile = ceil($skillext / 50);
	if($datafile) include S_ROOT.'system/skill_ext_'.$datafile.'.inc.php';

	if(rand(1, $vitalrand) == 1 && $obvhurt != 0){//击中要害
		$obvhurt = $obvhurt * 2;
		$GLOBALS[$side.'_topmsg'].= '击中要害！';
	}

	if($side == 'rev'){
		$GLOBALS['rev'] = $rev;$GLOBALS['obv'] = $obv;
		$GLOBALS['revhurt'] = $revhurt;$GLOBALS['obvhurt'] = $obvhurt;
		$GLOBALS['rev_skill'] = $rev_skill;$GLOBALS['obv_skill'] = $obv_skill;
		$GLOBALS['revsql'].= $revsql;$GLOBALS['obvsql'].= $obvsql;
		$GLOBALS['rev_skill_succeed'] = $rev_skill_succeed;
		$GLOBALS['obv_skill_succeed'] = $obv_skill_succeed;
	}elseif($side == 'obv'){
		$GLOBALS['rev'] = $obv;$GLOBALS['obv'] = $rev;
		$GLOBALS['revhurt'] = $obvhurt;$GLOBALS['obvhurt'] = $revhurt;
		$GLOBALS['obv_skill'] = $rev_skill;$GLOBALS['rev_skill'] = $obv_skill;
		$GLOBALS['revsql'].= $obvsql;$GLOBALS['obvsql'].= $revsql;
		$GLOBALS['rev_skill_succeed'] = $obv_skill_succeed;
		$GLOBALS['obv_skill_succeed'] = $rev_skill_succeed;
	}
	$GLOBALS['battlesql'].= $battlesql;
}

function other_effect($side){
	global $timestamp;

	if($side == 'rev'){
		$rev = $GLOBALS['rev'];$obv = $GLOBALS['obv'];
		$side = 'rev';$obside = 'obv';
	}else{
		$rev = $GLOBALS['obv'];$obv = $GLOBALS['rev'];
		$side = 'obv';$obside = 'rev';
	}

	$flee = $rev['spd'] - $obv['spd'];//回避计算
	if($flee >= rand(1, $obv['spd'])){
		$GLOBALS[$side.'hurt'] = 0;
		$GLOBALS[$side.'_undermsg'] = $rev['name'].'回避成功！';
	}

	switch($rev['status']){//非正常状态附加影响
	case 3:
		$GLOBALS[$side.'hurt'] += ceil($rev['maxhp'] / 16);
		$GLOBALS[$side.'_topmsg'].= '因为中毒失去1/16 HP！';
	break;
	case 4:
		if(rand(1, 8) != 1){
			$GLOBALS[$obside.'hurt'] = 0;
			$GLOBALS[$side.'_topmsg'] = $rev['name'].'睡着了！无法攻击！';
			$GLOBALS[$side.'_skill_succeed'] = FALSE;
		}else{
			$rev['status'] = 1;
			$GLOBALS[$side.'_topmsg'] = $rev['name'].'醒了！'.$GLOBALS[$side.'_topmsg'];
		}
	break;
	case 6:
		$GLOBALS[$side.'hurt']+= ceil($rev['maxhp'] / 16);
		$GLOBALS[$side.'_topmsg'].= '因为烧伤失去1/16 HP！';
	break;
	case 7:
		if(rand(1, 8) != 1){
			$GLOBALS[$obside.'hurt'] = 0;
			$GLOBALS[$side.'_topmsg'] = $rev['name'].'冻住了！无法攻击！';
			$GLOBALS[$side.'_skill_succeed'] = FALSE;
		}else{
			$rev['status'] = 1;
			$GLOBALS[$side.'sql'].= ',status=1';
			$GLOBALS[$side.'_topmsg'] = $rev['name'].'解冻了！'.$GLOBALS[$side.'_topmsg'];
		}
	break;
	}

	if($rev['tmpstatus'][2] >= $timestamp){
		if(rand(1, 4) == 1){
			$GLOBALS[$side.'hurt']+= $GLOBALS[$obside.'hurt'];
			$GLOBALS[$obside.'hurt'] = 0;
			$GLOBALS[$side.'_topmsg'].= $rev['name'].'混乱了！击中了自己！';
			$GLOBALS[$side.'_skill_succeed'] = FALSE;
		}
	}
	if($rev['tmpstatus'][11] >= $timestamp){
		$GLOBALS[$side.'hurt'] += floor($rev['maxhp'] / 16);
		$GLOBALS[$side.'_topmsg'].= '附加效果影响，损失部分体力！';
	}
	if($rev['tmpstatus'][12] >= $timestamp){
		$GLOBALS[$side.'hp'] += floor($rev['maxhp'] / 16);
		$GLOBALS[$side.'_topmsg'].= '附加效果影响，恢复部分体力！';
	}

	if(is_array($rev['tmpstatus'])) foreach($rev['tmpstatus'] as $k => $v){
		if($k != 20){
			if(is_numeric($v) && $v != 'MAX' && $v < $timestamp) unset($rev['tmpstatus'][$k]);
			elseif(is_array($v) && $v[1] != 'MAX' && !$v[1] < $timestamp) unset($rev['tmpstatus'][$k]);
		}
	}

	if($side == 'rev'){
		$rev = $GLOBALS['rev'];$obv = $GLOBALS['obv'];
	}else{
		$rev = $GLOBALS['obv'];$obv = $GLOBALS['rev'];
	}
}

function caculate_point($side){
	$side = ($side == 'rev')?'rev':'obv';
	$obside = ($side == 'rev')?'obv':'rev';
	if($GLOBALS[$obside.'hurt'] > 0){//HP计算
		$GLOBALS[$obside]['hp'] = posit($GLOBALS[$obside]['hp'] - $GLOBALS[$obside.'hurt']);
		$GLOBALS[$obside.'sql'].= ',hp='.$GLOBALS[$obside]['hp'];
	}
	if($GLOBALS[$side]['mp'] > 0){
		$revcmp = ceil(25 / $GLOBALS[$side.'_skill']['cmp']);//MP计算
		$GLOBALS[$side.'sql'].= ',mp=mp-'.$revcmp;
	}else $GLOBALS[$side.'_undermsg'] = '气力不足！无法使用技能！';
}

function setweather($weatherid){
	global $index, $timestamp;
	$expiry = $timestamp + (($GLOBALS['_PKW']['weatherextraexpiry'][$weatherid] == $GLOBALS['rev']['equip'])?16:10);
	$GLOBALS['battlesql'].= ',weather='.$weatherid.',weatherexpiry='.$expiry;
}

function hidden_power($mon = array()) {
	$power = array();
	$power[1] = ($mon['iv_hp']%4 == 2 || $mon['iv_hp']%4 == 3)?1:0;
	$power[2] = ($mon['iv_atk']%4 == 2 || $mon['iv_atk']%4 == 3)?2:0;
	$power[3] = ($mon['iv_def']%4 == 2 || $mon['iv_def']%4 == 3)?4:0;
	$power[4] = ($mon['iv_spd']%4 == 2 || $mon['iv_spd']%4 == 3)?8:0;
	$power[5] = ($mon['iv_stk']%4 == 2 || $mon['iv_stk']%4 == 3)?16:0;
	$power[6] = ($mon['iv_sdf']%4 == 2 || $mon['iv_sdf']%4 == 3)?32:0;
	return floor(($power[1] + $power[2] + $power[3] + $power[4] + $power[5] + $power[6])*40/63 + 30);
}

function hidden_type($mon = array()) {
	$type = array();
	$type[1] = ($mon['iv_hp']%2 == 1)?1:0;
	$type[2] = ($mon['iv_atk']%2 == 1)?2:0;
	$type[3] = ($mon['iv_def']%2 == 1)?4:0;
	$type[4] = ($mon['iv_spd']%2 == 1)?8:0;
	$type[5] = ($mon['iv_stk']%2 == 1)?16:0;
	$type[6] = ($mon['iv_sdf']%2 == 1)?32:0;
	return floor(($type[1] + $type[2] + $type[3] + $type[4] + $type[5] + $type[6])*15/63);
}

function tmpstatus_massedit($statusid = 0, $value, $side = 'both'){
	global $timestamp, $PKWpre;
	if(!$statusid || !in_array($side, array('both','rev','obv'))) return;

	$extrac = ' WHERE '.($side != 'both' ? 'ownerid='.$GLOBALS[$side]['ownerid'] : 'ownerid IN ('.$GLOBALS['rev']['ownerid'].','.$GLOBALS['obv']['ownerid'].')').' AND status < 9 AND status > 0 AND status!=2';

	$pokelist = array();
	$all_tmpstatus_empty = TRUE;
	$query = $db->query("SELECT id,tmpstatus FROM {$PKWpre}pokemon WHERE $extrac");
	while($m = $db->fetch_array($query)){
		if($m['id'] != $GLOBALS['rev']['id'] && $m['id'] != $GLOBALS['obv']['id']){
			if($all_tmpstatus_empty && $m['tmpstatus']) $all_tmpstatus_empty = FALSE;
			$pokelist[] = $m;
		}
	}
	if($all_tmpstatus_empty && $value){
		$tmpstatus = array();
		$tmpstatus[$statusid] = $value;
		$tmpstatus = serialize($tmpstatus);
		$db->query("UPDATE {$PKWpre}pokemon SET tmpstatus='$tmpstatus' WHERE $extrac");
	}elseif(!$all_tmpstatus_empty && $value){
		foreach($pokelist as $m){
			$m['tmpstatus'] = unserialize($m['tmpstatus']);
			$m['tmpstatus'][$statusid] = $value;
			$m['tmpstatus'] = serialize($m['tmpstatus']);
			$db->query("UPDATE {$PKWpre}pokemon SET tmpstatus='$m[tmpstatus]' WHERE id=$m[id]");
		}
	}elseif(!$all_tmpstatus_empty && !$value){
		foreach($pokelist as $m){
			$m['tmpstatus'] = unserialize($m['tmpstatus']);
			unset($m['tmpstatus'][$statusid]);
			$m['tmpstatus'] = serialize($m['tmpstatus']);
			$db->query("UPDATE {$PKWpre}pokemon SET tmpstatus='$m[tmpstatus]' WHERE id=$m[id]");
		}
	}

	if($side == 'both'){
		$GLOBALS['rev']['tmpstatus'][$statusid] = $GLOBALS['obv']['tmpstatus'][$statusid] = $value;
	}else{
		$GLOBALS[$side]['tmpstatus'][$statusid] = $value;
	}
}
?>