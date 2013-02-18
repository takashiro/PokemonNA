<?php

require_once './core/init.inc.php';

$action = &$_GET['action'];

if(!$_G['user']->isLoggedIn()){
	showmsg('请先登录本站。', 'memcp.php?action=login');
}

if(!$action){
	$pokelist = array();
	$allskill = $allequip = $multipage = '';
	$extrac = 'AND status<=9';
	if($box){
		$limit = 6;
		$page = max(1, intval($page));
		$offset = ($page - 1) * $limit;
		$extrac = 'AND status > 9';

		$extrac.= ' LIMIT '.$offset.','.$limit;
		$totalnum = $db->result_first("SELECT COUNT(*) FROM {$tpre}pokemon WHERE ownerid=$_USER[id] AND status > 9");
		$multipage = multi($totalnum, $limit, $page, 'mypokemon.php?box=1');
	}
	$query = $db->query("SELECT m.*,s.growthtype FROM {$tpre}pokemon m LEFT JOIN {$tpre}pokemoninfo s ON s.id=m.pokeid WHERE ownerid=$_USER[id] $extrac");
	while($mon = $db->fetch_array($query)){
		$box && $mon['status'] -= 10;
		$mon['nature_c'] = PokemonNature::$value[$mon['natureid']]['name_c'];
		$mon['trait_c'] = PokemonTrait::$value[$mon['trait']]['name_c'];
		$mon['trait_i'] = PokemonTrait::$value[$mon['trait']]['intro'];
		$pokelist[] = Pokemon::ToReadable($mon);

		if(!empty($mon['skill'])) $allskill.= ','.$mon['skill'];
		if(!empty($mon['equip'])) $allequip.= ','.$mon['equip'];
	}
	unset($_CONFIG['trait'], $_CONFIG['nature']);
	$pokelist || showmsg('您还没有任何口袋怪兽'.($box ? '在研究所' : '随身携带着').'！', $box ? 'back' : 'institude.php');

	$allskill = substr($allskill, 1);
	if(!empty($allskill)){
		$skillname = array();
		$query = $db->query("SELECT id,name_c,atb FROM {$tpre}skill WHERE id IN ($allskill)");
		while($k = $db->fetch_array($query)){
			$skillname[$k['id']] = $k['name_c'];
			$skillatb[$k['id']] = Pokemon::$Atb[$k['atb']];
		}
	}

	$allequip = substr($allequip, 1);
	if(!empty($allequip)){
		$warename = array();
		$query = $db->query("SELECT id,name_c FROM {$tpre}ware WHERE id IN ($allequip)");
		while($k = $db->fetch_array($query)){
			$warename[$k['id']] = $k['name_c'];
		}
	}
	$warename[0] = '没有';

}elseif($action == 'evolute'){
	$pokemon = new Pokemon($_GET['mid']);
	if($pokemon->evolute()){
		showmsg('进化'.$pokemon->attr('name').'成功！','mypokemon.php');
	}else{
		showmsg('条件不足！进化失败！', 'back');
	}

}elseif($action=='rename'){
	if($mon['status'] >= 9 || !$mon['status']) exit();
	if($newname = $_POST['newname']){
		$mid = intval($_POST['mid']);
		if(!empty($newname)) $db->query("UPDATE {$tpre}pokemon SET name='$newname' WHERE id=$mid");
		showmsg('成功改名为'.$newname.'！', 'mypokemon.php');
	}

}elseif($action=='give'){
	if($username = raddslashes($receiver)){
		$extsql = '';
		$rec = $db->fetch_first("SELECT id,pokenum,username FROM {$tpre}trainer WHERE username='$username'");
		
		if($rec['id'] == $_USER[id]) showmsg('送给自己的话就没有必要了。', 'back');
		elseif(!$rec['id']) showmsg('对方未领取精灵球！', 'back');
		elseif($rec['pokenum'] >= 6) $extsql = ',status=status+10';
		else{
			$id = intval($id);
			$db->query("UPDATE {$tpre}pokemon SET owner='$username' $extsql WHERE id=$mid");
			$db->query("UPDATE {$tpre}trainer SET status=status+1 WHERE username='$rec[id]'");
			$db->query("UPDATE {$tpre}trainer SET status=status-1 WHERE id=$_USER[id]");
			writelog('pokemon', "$mid\tPresent\t$username");
			$subject = "{$discuz_userss}给您传送了一只精灵";
			$message = '[url=mypokemon.php][查看详情][/url] ';
			if($mon['equip'] != 23){
				require_once S_ROOT.'system/evolute.func.php';
				$mon = pkw_evolute($mon, 'give');
				if($mon){
					$db->query("UPDATE {$tpre}pokemon SET pokeid=$mon[pokeid],atk=$mon[atk],def=$mon[def],hp=$mon[hp],stk=$mon[stk],sdf=$mon[sdf],spd=$mon[spd],maxhp=$mon[hp],mp=$mon[mp],maxmp=$mon[mp],atb1=$mon[atb1],atb2=$mon[atb2],height=$mon[height],weight=$mon[weight],trait=$mon[trait],exp=$mon[exp] WHERE id=$mon[id]");
					writelog('pokemon', "$mon[ori_pokeid]\tEvolution\t$mon[pokeid]");
				}
				$message.= '精灵在传送过程中进化了。';
			}
			sendpm($rec['id'], $subject, $message);
			showmsg("成功赠送给{$username}了！", 'mypokemon.php');
		}
	}

}elseif($action=='throw'){
	if($id = intval($id)){
		if(!empty($password)){
			require_once DISCUZ_ROOT.'uc_client/client.php';
			$uid = uc_user_login($_USER[id], $password, TRUE, FALSE);
		}
		if($uid <= 0 || empty($password)){
			showmsg('密码错误！', 'back');
		}
		$mon_status = $db->result_first("SELECT status FROM {$tpre}pokemon WHERE id=$id AND ownerid=$_USER[id]");
		if($mon_status > 0){
			$db->query("DELETE FROM {$tpre}pokemon WHERE id=$id");
			$db->query("DELETE FROM {$tpre}pokemonext WHERE id=$id");
			$mon_status > 9 && $db->query("UPDATE {$tpre}trainer SET pokenum=pokenum-1 WHERE id=$_USER[id]");
			showmsg('你好残忍啊！！！', 'mypokemon.php');
		}else{
			exit('Illegal Operation');
		}
	}else{
		exit('Illegal Operation');
	}
}elseif(substr($action, -3) == 'box'){
	$id = intval($_GET['id']);
	$mon = $db->fetch_first("SELECT name,status FROM {$tpre}pokemon WHERE id=$id AND ownerid=$_USER[id]");
	!$mon && showmsg('No_such_pokemon Error:1', 'back');

	$my_pokenum = &$_USER['pokenum'];
	if($action == 'intobox'){
		$mon['status'] > 9 && showmsg('No_such_pokemon Error:2', 'back');
		$my_pokenum <= 1 && showmsg('至少要留1只精灵在身边哦！', 'back');

		$db->query("UPDATE {$tpre}pokemon SET status=status+10 WHERE id=$id");
		$db->query("UPDATE {$tpre}trainer SET pokenum=pokenum-1 WHERE id=$_USER[id]");
		showmsg($mon['name'].'成功进入箱子并传送到研究所。', 'mypokemon.php?box=1');
	}else{
		$mon['status'] < 10 && showmsg('No_such_pokemon Error:3', 'back');
		$my_pokenum >= 6 && showmsg('最多携带6只精灵在身边！', 'back');

		$db->query("UPDATE {$tpre}pokemon SET status=status-10 WHERE id=$id");
		$db->query("UPDATE {$tpre}trainer SET pokenum=pokenum+1 WHERE id=$_USER[id]");
		showmsg($mon['name'].'成功传回！', 'mypokemon.php');
	}
}else{
	exit('Error: action');
}

include view('mypokemon');
?>
