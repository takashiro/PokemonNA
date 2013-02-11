<?php

require_once './core/init.inc.php';

if(submitcheck('registersubmit')){
	$selected = intval($_POST['selected']);
	if(!in_array($selected, explode(',', $_CONFIG['selectable_pokemon']))){
		showmsg('这个精灵不太适合新人训练家啊！', 'institude.php');
	}

	if($_G['user']->isLoggedIn()){
		showmsg('您已经有最初的口袋怪兽了！', 'back');
	}

	if(!$trainer_name = $_POST['trainer_name']){
		showmsg('请先填写您的名字。', 'back');
	}else if(!$trainer_email = $_POST['trainer_email']){
		showmsg('请先填写您的邮箱。', 'back');
	}else if(!$trainer_password = $_POST['trainer_password']){
		showmsg('请先填写您的登录密码。', 'back');
	}else if($trainer_password != $_POST['trainer_password2']){
		showmsg('您两次输入的密码不一致，请重新输入。', 'back');
	}

	$uid = User::Register($trainer_name, $trainer_email, $trainer_password);
	switch($uid){
		case -1:showmsg('您的名字长度必须在4到15之间。', 'back');
		case -2:showmsg('请填写有效的邮箱地址。', 'back');
		case -4:showmsg('您的姓名已被使用，请重新输入。', 'back');
		case -5:showmsg('您的邮箱已经注册，您是否需要找回密码？');
	}

	$_G['user']->login($trainer_email, $trainer_password);
	$_G['user']->attr('pokenum', 1);

	$mon = Pokemon::Generate($selected, 5);
	$namelang = !in_array($namelang, array('j','c','e')) ? 'j' : $namelang;
	$mon['name'] = $mon['name_'.$namelang];
	$mon['ownerid'] = $_G['user']->attr('id');
	$mon['owner'] = $_G['user']->attr('username');

	Pokemon::InsertIntoDB($mon);

	//writelog('institude', $selected);
	showmsg("成功领取{$mon[name]}！", 'mypokemon.php');

}elseif($_GET['action'] == 'join'){
	$query = $db->query("SELECT id FROM {$tpre}trainer WHERE username='$discuz_user'");
	if($db->fetch_array($query)) showmsg('对不起，你已经领取过了！', 'institude.php');
	$db->query("INSERT INTO {$tpre}trainer (id,username,battleon,pokenum) VALUES ('$_USER[id]','$discuz_user', 1, 1)");
	pkw_storage(198, 5, $_USER[id]);
	showmsg('成功获取宠物球！你可以开始在这里的冒险了！', 'institude.php');

}else{
	$condition = 'm.id IN ('.$_CONFIG['selectable_pokemon'].')';

	$pokelist = array();
	$query = $db->query("SELECT m.id,m.name_e,m.name_j,m.name_c,m.atb1,m.atb2,m.gender,m.height,m.weight,m.hp,m.atk,m.def,m.stk,m.sdf,m.spd,m.eggtype1,m.eggtype2,m.growthtype,m.eggstep,m.intro FROM {$tpre}pokemoninfo m WHERE $condition");
	while($pokemon = $db->fetch_array($query)){
		$pokemon['atb1'] = Pokemon::$atb[$pokemon['atb1']];
		$pokemon['atb2'] = $pokemon['atb2'] ? Pokemon::$atb[$pokemon['atb2']] : '';
		$pokemon['gender'] = (254 - $pokemon['gender']).'：'.$pokemon['gender'];
		$pokemon['eggtype1'] = Pokemon::$eggType[$pokemon['eggtype1']];
		$pokemon['eggtype2'] = $pokemon['eggtype2']?Pokemon::$eggType[$pokemon['eggtype2']]:0;
		$pokemon['growthtype'] = Pokemon::$growthType[$pokemon['growthtype']];
		$pokelist[] = $pokemon;
	}
}

include view('institude');
?>