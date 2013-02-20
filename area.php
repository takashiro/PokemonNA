<?php

require_once './core/init.inc.php';

$gid = $_USER['mapid'] ? $_USER['mapid'] : intval($_GET['gid']);
$mid = intval($_GET['mid']);
if(!$gid || !$mid){
	rheader('Location: map.php');
}

$pos = new Pokemon($mid);
if($pos->attr('ownerid') != $_G['user']->attr('id')){
	rheader('Location: map.php');
}

if($pos->attr('hp')<= 0 || $pos->attr('status') == 0 || $pos->attr('status') == 2){
	showmsg('您的精灵已经不能战斗了！', 'map.php');
}elseif($pos->attr('skill') == ''){
	showmsg('您的精灵未学会任何技能！不能战斗！', 'back');
}elseif($pos->attr('status') > 9){
	showmsg('此精灵还暂存在研究所！', 'back');
}

//需要初始化的变量
$msg = array();
if($_USER['status'][1] >= TIMESTAMP){
	$msg['under'].= '喷雾器剩余时间：'.($_USER['status'][1] - TIMESTAMP).'秒';
}

$_G['user']->attr('actiontime', TIMESTAMP);

$map = $db->fetch_first("SELECT a.*,r.name_c AS regionname FROM {$tpre}regionarea a LEFT JOIN {$tpre}region r ON r.id=a.regionid WHERE a.id=$gid");
$map['weather'] = (TIMESTAMP <= $_USER['weatherexpiry']) ? $_USER['weather'] : $map['weather'];

//生成野生精灵
if(!$_USER['negid']){
	if(!$map['normalmonids']){
		showmsg('这里没有野生精灵！','back');
	}
	
	$wild_pokemons = explode(',', $map['normalmonids']);
	if($map['raremonids'] && rand(1, 100) == 1){
		$wild_pokemons = $wild_pokemons + explode(',', $map['raremonids']);
	}

	$hour_now = intval(gmdate('H', TIMESTAMP + 3600 * $_CONFIG['timezone']));
	if($hour_now >= 6 && $hour_now <= 18){
		if($map['daymonids']){
			$wild_pokemons = $wild_pokemons + explode(',', $map['daymonids']);
		}
	}else{
		if($map['nightmonids']){
			$wild_pokemons = $wild_pokemons + explode(',', $map['nightmonids']);
		}
	}

	$negid = $wild_pokemons[array_rand($wild_pokemons)];

	$neg = array();
	if($_USER['ustatus'][1] >= TIMESTAMP){
		if($map['maxlevel'] < $pos['level']){
			showmsg('没有遇到野生精灵！','refresh');
		}else{
			$neg['level'] = rand($rev['level'], $map['maxlevel']);
			$msg['under'].= '喷雾器剩余时间：'.($rev['ustatus'][1] - TIMESTAMP).'秒';
		}
	}else{
		$neg['level'] = rand($map['minlevel'], $map['maxlevel']);
	}
	$neg = Pokemon::Generate($negid, $neg['level']);

	$neg_skill = array();
	$query = $db->query("SELECT skillid FROM {$tpre}pokemonskill WHERE pokeid=$negid AND type=1 AND reqlevel<=$neg[level] ORDER BY reqlevel LIMIT 7");
	while($k = $db->fetch_array($query)){
		$neg_skill[] = $k['skillid'];
	}
	$neg['skill'] = implode(',', array_unique($neg_skill));

	$itemrand = rand(1, 20);
	$temp = $db->query("SELECT item1,item2 FROM {$tpre}pokemoninfo WHERE id=$negid");
	if($temp['item2'] && $itemrand == 1){
		$neg['equip'] = $temp['item2'];
	}elseif($temp['item1'] && $itemrand >= 10){
		$neg['equip'] = $item['item1'];
	}else{
		$neg['equip'] = 0;
	}
	unset($temp);

	$db->select_table('adventure');

	$db->query("INSERT INTO {$tpre}adventure (id,pokeid,shape,name,atb1,atb2,gender,trait,natureid,godev,level,status,hp,maxhp,mp,maxmp,height,weight,atk,def,stk,sdf,spd,iv_hp,iv_atk,iv_def,iv_stk,iv_sdf,iv_spd,equip,skill)
	VALUES ($_USER[id],$neg[id],$neg[shape],'$neg[name_c]',$neg[atb1],$neg[atb2],$neg[gender],$neg[trait],$neg[natureid],$neg[godev],$neg[level],$neg[status],$neg[hp],$neg[hp],$neg[mp],$neg[mp],$neg[height],$neg[weight],$neg[atk],$neg[def],$neg[stk],$neg[sdf],$neg[spd],$neg[iv_hp],$neg[iv_atk],$neg[iv_def],$neg[iv_stk],$neg[iv_sdf],$neg[iv_spd],$neg[equip],'$neg[skill]')");
	$_G['user']->attr('negid', $negid);
	$_G['user']->attr('mapid', $gid);
}

//取出野生精灵数据
$neg = new WildPokemon($_USER['id']);
if(!$neg->attr('id')){
	$_G['user']->attr('negid', 0);
	rheader('Location: map.php');
	exit;
}

if(!$kid || !$action){
	$skillname = array();//技能名称显示
	$selected_skill_ids = $pos->attr('skill');
	$query = $db->query("SELECT id,name_c FROM {$tpre}skill WHERE id IN ($selected_skill_ids)");
	while($k = $db->fetch_array($query)){
		$skillname[$k['id']] = $k['name_c'];
	}
}

$action = &$_GET['action'];
if($action == 'fight'){
	$pos_kid = intval($_GET['kid']);
	$pos_kid = $pos->hasSkill($pos_kid) ? $pos_kid : 131;
	$neg_kid = $neg->getRandomSkill();
	$neg_kid = $neg_kid ? $neg_kid : 98;

	$query = $db->query("SELECT id,spr,name_c,power,atb,ext,cmp FROM {$tpre}skill WHERE id=$pos_kid OR id=$neg_kid");
	while($k = $db->fetch_array($query)){
		$k['id'] == $pos_kid && $pos_skill = $k;
		$k['id'] == $neg_kid && $neg_skill = $k;
	}
	unset($k);

	//动作时限&ext25-先制攻击
	/*if(($pos_skill['ext'] != 25 && TIMESTAMP - $_G['user']->attr('actiontime') < $_CONFIG['battlewaittime']) || TIMESTAMP - $_G['user']->attr('actiontime') < 2){
		exit('alert(\'请勿非法操作！出招超前！\');');
	}*/

	$msg['pos_top'].= $pos->attr('name').'的'.$pos_skill['name_c'].'！';
	$msg['neg_top'].= $neg->attr('name').'的'.$neg_skill['name_c'].'！';

	//技能前置特殊效果
	//if(in_array($rev_skill['ext'], $foreext)){skillext_effect($rev_skill['ext'], 'rev');$rev_skill_back = FALSE;}
	//if(in_array($obv_skill['ext'], $foreext)){skillext_effect($obv_skill['ext'], 'obv');$obv_skill_back = FALSE;}

	//技能伤害
	if($pos->getSpd() > $neg->getSpd() || ($pos->getSpd() == $neg->getSpd() && rand(1, 2) == 1)){
		$neg_damage = $pos->useSkill($pos_skill, $neg);
		$pos_damage = $neg->useSkill($neg_skill, $pos);
	}else{
		$pos_damage = $neg->useSkill($neg_skill, $pos);
		$neg_damage = $pos->useSkill($pos_skill, $neg);
	}

	//技能后置特殊效果
	//$rev_skill_back && skillext_effect($rev_skill['ext'], 'rev');
	//$obv_skill_back && skillext_effect($obv_skill['ext'], 'obv');

	//其他效果
	//other_effect('rev');
	//other_effect('obv');

	//重新计算双方数值
	//caculate_point('rev');
	//caculate_point('obv');

	if($neg->attr('status') == 0){
		$negspecies = $db->fetch_first("SELECT baseexp,ep FROM {$tpre}pokemoninfo WHERE id=".$neg->attr('pokeid'));

		$gainep = explode(',', $negspecies['ep']);
		foreach($gainep as $ep){
			list($point, $value) = explode('+', $ep);
			$pos->attr($point, $pos->attr($point) + $value);
		}
		$exp = floor($negspecies['baseexp'] / 7 * $neg->attr('level'));
		$pos->gainExp($exp);

		$db->query("DELETE FROM {$tpre}adventure WHERE id=$_USER[id]");
		//writelog('adven', "$rev[id]\t$obv[pokeid]\tReverse");
		$endbattle = TRUE;

	}elseif($pos->attr('status') == 0){
		//writelog('adven', "$rev[id]\t$obv[pokeid]\tObverse");
		$endbattle = TRUE;
	}

	if($endbattle){
		$db->query("DELETE FROM {$tpre}adventure WHERE id=$_USER[id]");
		$_G['user']->attr('negid', 0);
		$_G['user']->attr('mapid', 0);
		$pos->clearTempAttributes();
	}

	$return_data = array(
		'pos_hp' => $pos->attr('hp'),
		'neg_hp' => $neg->attr('hp'),
		'msg' => $msg,
	);
	exit(json_encode($return_data));

}elseif($action == 'escape'){
	$neg->deleteFromDB();
	$_G['user']->attr('negid', 0);
	$_G['user']->attr('mapid', 0);
	//writelog('adven', "$rev[id]\t$obv[pokeid]\tObverse");
	showmsg('逃离成功！', 'map.php');

}elseif($action == 'catch' && $ballid = intval($_GET['ballid'])){
	$backpack = new Backpack($_USER['id']);
	$backpack->updateStorage($ballid, -1);

	$negspecies = $db->fetch_first("SELECT catchrate,growthtype FROM {$tpre}pokemoninfo WHERE id=".$neg->attr('pokeid'));

	$catchrate = $negspecies['catchrate'];
	switch($ballid){
		case 199:$catchrate *= 1.5;break;
		case 200:$catchrate *= 2;break;
		case 201:$catchrate *= ($neg->attr('atb1') == Pokemon::Water || $neg->attr('atb1') == Pokemon::Bug || $neg->attr('atb2') == Pokemon::Water || $neg->attr('atb2') == Pokemon::Bug) ? 3 : 1;break;
		case 202:$catchrate *= ($neg->attr('atb1') == Pokemon::Water || $neg->attr('atb2') == Pokemon::Water) ? 3.5 : 1;break;
		case 203:$catchrate *= ($neg->attr('level') <= 29) ? (rand(11, 39) / 10) : 1;break;
		case 204:$catchrate *= ($map['type'] == 6) ? 4 : 1;break;
		case 205:$catchrate *= ($neg->attr('hp') / $neg->attr('maxhp')) * 3;break;
		case 207:$catchrate *= (1 - $pos->attr('hp') / $pos->attr('maxhp')) * 3;break;
		default:$catchrate = 0;
	}
	$catchrate = ($catchrate >= 256) ? 255 : $catchrate;
	switch($obv['status']){
		case 3:$fix_effect = 5;
		case 4:$fix_effect = 10;
		case 5:$fix_effect = 5;
		case 6:$fix_effect = 5;
		case 7:$fix_effect = 10;
		default:$fix_effect = 0;
	}
	$catchrate = ((1 - $obv['hp'] / $obv['maxhp'] * 2 / 3) * $catchrate + $fix_effect + 1) / 2.56;

	if(rand(1, 100) <= $catchrate){
		switch($ballid){
			case 208:$neg->attr('frd', $neg->attr('frd') + 10);break;
			case 209:$neg->attr('hp', $neg->attr('maxhp'));break;
		}

		$N = $neg->toArray();
		$neg->deleteFromDB();

		$N['exp'] = Pokemon::LevelToExp($N['level'], $N['growthtype']);

		if($_G['user']->attr('pokenum') >= 6){
			$N['status'] += 10;
		}

		$_G['user']->attr('pokenum', $_G['user']->attr('pokenum') + 1);
		$_G['user']->attr('negid', 0);
		$_G['user']->attr('mapid', 0);

		$N['ownerid'] = $_G['user']->attr('id');
		$N['owner'] = $_G['user']->attr('username');
		$N['regdate'] = TIMESTAMP;

		$db->select_table('pokemon');
		$db->INSERT($N);

		showmsg('捕捉成功！', 'pkw.php?index=mypokemon');

	}else{
		showmsg('捕捉失败！', 'back');
	}
}

$P = $pos->toReadable();
$N = $neg->toReadable();

include view('area');

?>