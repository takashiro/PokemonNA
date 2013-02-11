<?php

require_once './core/init.inc.php';

$mid = intval($_GET['mid']);

if(!$mid){
	showmsg('非法操作。');
}

$pokemon = new Pokemon($mid);
$pm = $pokemon->toReadable();

$learn = intval($_GET['learn']);
$forget = intval($_GET['forget']);
if(!$learn && !$forget){
	$atb = intval($atb);
	$condition = '';
	$sql = '';

	$limit = 12;
	$page = intval($page);
	$offset = ($page - 1) * $limit;

	include S_ROOT.'data/data_skillbtyext.php';
	include S_ROOT.'data/data_skillext.php';
	if($atb > 0){
		$selected = '*';
		$condition = "WHERE atb=$atb";
	}elseif($atb == -1){
		$selected = '*';
		$condition = "WHERE id IN ($pm[skill])";
	}else{
		$selected = 's.id,s.name_j,s.name_c,s.atb,s.power,s.bty,s.type,s.spr,s.cmp,s.ext,s.btyext,m.reqlevel';
		$condition = "s LEFT JOIN {$tpre}pokemonskill m ON m.pokeid=$pm[pokeid] AND m.type=1 WHERE s.id=m.skillid AND s.id".(!empty($pm['skill'])?" NOT IN ($pm[skill])":'');
	}
	$skilllist = array();
	$query = $db->query("SELECT $selected FROM {$tpre}skill $condition LIMIT $offset,$limit");
	while($skill = $db->fetch_array($query)){
		if($skill['id'] == 185 && $skill['reqlevel'] < $pm['level']) continue;
		$skill['atb'] = "<img src=$staticdir/atb/$skill[atb].gif />";
		$skill['aft'] = $_CONFIG['aftarray'][$skill['aft']];
		$skilllist[] = $skill;
	};
	$skillnum = $db->result_first("SELECT COUNT(*) FROM {$tpre}skill $condition");
	$multipage = multi($skillnum, $limit, $page, "train.php?mid=$mid&atb=$atb");
	unset(Pokemon::$atb[0]);

}elseif($learn){
	if(count($pm['skilllist']) >= 7)
		showmsg('对不起，您的宠物已学习满7项技能！', 'javascript:history.back()');
	if($pm['skill'] && in_array($learn, $pm['skilllist'])) showmsg('这项技能你已经学习过了！', 'javascript:history.back()');
	if($pm['mp']<=0 || $pm['hp']<=0 || $pm['frd']<70) showmsg('您的体力、气力、亲密度其中一项已经不足！', 'javascript:history.back()');
	$query = $db->query("SELECT k.*,m.reqlevel FROM {$tpre}pokemonskill m LEFT JOIN {$tpre}skill k ON k.id=m.skillid WHERE m.skillid=$learn AND m.pokeid=$pm[pokeid] AND m.type=1");
	if($skill = $db->fetch_array($query)){
		$cmp = floor($pm['maxmp'] / $skill['cmp'] * rand(1, 5) / 3);
		$chp = floor($cmp * rand(1, 5) / 3);
		$sql = '';
		if(rand(1, 40) == 1) $sql.= ',frd=frd+'.rand(-6,5);
		if(rand(1, 7) == 1) $sql.= ',exp=exp+'.rand(1,$pm['level']*4);
		if($skill['reqlevel'] > $pm['level']) $resultmsg = "失败了！$pm[name]等级不够！";
		else{
			if($pm['skill']!='') $newskill = ','.$skill['id']; else $newskill = $skill['id'];
			$sql.= ",skill='{$pm[skill]}{$newskill}'";
			$resultmsg = '学习成功！';
		}
		$db->query("UPDATE {$tpre}pokemon SET mp=mp-$cmp,hp=hp-$chp $sql WHERE id=$mid");
		showmsg($resultmsg, 'back');
	}else showmsg('Illegal Operation', 'back');

}elseif($forget){
	if(empty($pm['skill'])) showmsg('还有技能可忘吗？！', 'back');
	elseif(!in_array($forget, $pm['skilllist'])) showmsg('这个技能没学会呢！', 'back');

	$pm['skilllist'] = array_diff($pm['skilllist'], array($forget));
	$pm['skill'] = implode(',', $pm['skilllist']);
	$db->query("UPDATE {$tpre}pokemon SET skill='$pm[skill]' WHERE id=$mid AND owner='$discuz_user'");
	showmsg('成功忘记技能！', 'back');
}

include view('train');
?>
