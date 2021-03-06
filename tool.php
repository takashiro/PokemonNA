﻿<?php

require_once './core/init.inc.php';

$item = !$_GET['item'] ? 'calc' : $_GET['item'];

if($item == 'calc'){
	$monlist = array();
	$query = $db->query("SELECT id,name_c FROM {$tpre}pokemoninfo");
	while($mon = $db->fetch_array($query)){
		$monlist[] = $mon;
	}

}elseif($item == 'fruit'){
	$hardarray = array('','特软','很软','软','硬','很硬','特硬');

	$page = intval($page);
	$limit = 12;
	$offset = ($page - 1) * $limit;
	$query = $db->query("SELECT COUNT(*) AS num FROM {$tpre}ware WHERE type=5");
	$num = $db->result($query, 0);
	$multipage = multi($num, $limit, $page, 'tool.php?item=fruit');

	$fruitlist = array();
	$query = $db->query("SELECT w.id,w.name_c,w.name_j,f.* FROM {$tpre}ware w LEFT JOIN {$tpre}fruit f ON f.id=w.id WHERE w.type=5 LIMIT $offset, $limit");
	while($f = $db->fetch_array($query)){
		$f['powertype'] = Pokemon::$Atb[$f['powertype']];
		$f['name_c'] = $f['name_c']?$f['name_c']:$f['name_j'];
		$f['hard'] = $hardarray[$f['hard']];
		$fruitlist[] = $f;
	}
}elseif($item == 'cpanel' && $_G['user']->isLoggedIn()){
	if(submitcheck('cpanelupdate')){
		$_G['user']->attr('localpath', $_POST['localpath']);
		$_G['user']->attr('battleone', intval($_POST['battleon']));

		showmsg('成功修改设置！', 'javascript:history.back()');
	}else{
		$my = $db->fetch_first("SELECT localpath,battleon FROM {$tpre}trainer WHERE id=$_USER[id]");
		$monlist = array();
		$query = $db->query("SELECT id,name FROM {$tpre}pokemon WHERE ownerid='$_USER[id]' AND status!=9");
		while($m = $db->fetch_array($query)) $monlist[] = $m;
	}
}elseif($item == 'evolution'){
	$condition = '';
	if($original = intval($original)) $condition = " WHERE original=$original ";
	elseif($evoluted = intval($evoluted)) $condition = " WHERE evoluted=$evoluted ";
	else $condition = '';


	$limit = 4;
	$query = $db->query("SELECT COUNT(*) FROM {$tpre}evolution $condition");
	$nums = $db->result($query, 0);
	$page = intval($page);
	$offset = ($page - 1) * $limit;
	$multipage = multi($nums, $limit, $page, 'tool.php?item=evolution');

	$elist = array();
	$query = $db->query("SELECT * FROM {$tpre}evolution $condition ORDER BY `original`,`evoluted` LIMIT $offset, $limit");
	while($e = $db->fetch_array($query)){
		$e = Pokemon::EvolutionCondition($e);
		$elist[] = $e;
	}
}elseif($item == 'trait'){
	$_CONFIG['trait'] = PokemonTrait::$value;
	unset($_CONFIG['trait'][0]);
}

include view('tool');
?>
