<?php

require_once './core/init.inc.php';

$pokelist = array();
$atb = intval($_GET['atb']);
$condition = 1;
if($atb) $condition.= " AND (m.atb1=$atb OR m.atb2=$atb)";
$limit = 15;
$offset = ($page - 1) * $limit;
$num = $db->result_first("SELECT count(*) as num FROM {$tpre}pokemoninfo m WHERE $condition");
$multipage = multi($num, $limit, $page, "pokedex.php?atb=$atb");
$condition.= " LIMIT $offset, $limit";

$query = $db->query("SELECT m.id,m.name_e,m.name_j,m.name_c,m.atb1,m.atb2,m.gender,m.height,m.weight,m.hp,m.atk,m.def,m.stk,m.sdf,m.spd,m.eggtype1,m.eggtype2,m.growthtype,m.eggstep,m.intro FROM {$tpre}pokemoninfo m WHERE $condition");
while($pokemon = $db->fetch_array($query)){
	$pokemon['atb1'] = Pokemon::$Atb[$pokemon['atb1']];
	$pokemon['atb2'] = $pokemon['atb2'] ? Pokemon::$Atb[$pokemon['atb2']] : '';
	$pokemon['gender'] = (254 - $pokemon['gender']).'：'.$pokemon['gender'];
	$pokemon['eggtype1'] = Pokemon::$EggType[$pokemon['eggtype1']];
	$pokemon['eggtype2'] = $pokemon['eggtype2']?Pokemon::$EggType[$pokemon['eggtype2']]:0;
	$pokemon['growthtype'] = Pokemon::$GrowthType[$pokemon['growthtype']];
	$pokelist[] = $pokemon;
}

include view('pokedex');

?>