<?php
include './include/common.inc.php';
@include './evolution_data.php';
$page = intval($page);
$limit = 12;
$offset = ($page - 1) * $limit;
$query = $db->query("SELECT COUNT(*) FROM pkw_evolution WHERE 1");
$nums = $db->result($query, 0);
$multipage = multi($nums, $limit, $page, 'pokemon_evolution.php');

$evolist = array();
$extrac = $update?'':"LIMIT $offset,$limit";
$query = $db->query("SELECT * FROM pkw_evolution WHERE 1 $extrac");
while($evo = $db->fetch_array($query)){
	switch($evo['evotype']){
	case 1:
		$timearray = array('', '白天/', '晚上/');
		$evo['require'] = $timearray[$evo['require']].'亲密';
	break;
	case 2:
		$evo['require'] = '等级达到'.$evo['require'];
	break;
	case 3:
		if($update && $evo['require']) $requireware.= ",$evo[require]";
		if($evo['require']) $evo['require'] = '装备'.$ware[$evo['require']];else $evo['require'] = '通信';
	break;
	case 4:
		if($update) $requireware.= ",$evo[require]";
		$evo['require'] = '接触物品'.$ware[$evo['require']];
	break;
	case 5:
		if($evo['require'] == 4)
			$evo['require'] = "等级达到$evo[require]且需要善恶值大于50";
		elseif($evo['require'] == 5)
			$evo['require'] = "等级达到$evo[require]且需要善恶值小于50";
	break;
	case 6:
		$evo['require'] = '完全随机进化';
	break;
	case 7:
		if($update) $requireskill.= ",$evo[require]";
		$evo['require'] = '学会'.$skill[$evo['require']];
	break;
	case 8:
		$evo['require'] = "等级达到$evo[require]/雄性";
	break;
	case 9:
		$evo['require'] = "等级达到$evo[require]/雌性";
	break;
	case 10:
		if($update) $requireware.= ",$evo[require]";
		$evo['require'] = '需要物品'.$ware[$evo['require']].'/雄性';
	break;
	case 11:
		if($update) $requireware.= ",$evo[require]";
		$evo['require'] = '需要物品'.$ware[$evo['require']].'/雌性';
	break;
	case 12:
		$evo['require'] = "魅力达到$evo[require]";
	break;
	case 13:
		if($update) $requireware.= ",$evo[require]";
		$evo['require'] = '晚上/需要物品'.$ware[$evo['require']];
	break;
	case 14:
		if($update) $requiremon.= ",$evo[require]";
		$evo['require'] = '需要其它宠物'.$pokemon[$evo['require']];
	break;
	case 15:
	break;
	case 16:
		$evo['require'] = "等级$evo[require]/攻击大于防御";
	break;
	case 17:
		$evo['require'] = "等级$evo[require]/攻击等于防御";
	break;
	case 18:
		$evo['require'] = "等级$evo[require]/攻击小于防御";
	break;
	default:;break;
	}
	$evolist[] = $evo;
}
if($update){
	$requireware = substr($requireware, 1);
	$requireskill = substr($requireskill, 1);
	$requiremon = substr($requiremon, 1);
	
	$text = '';
	$query = $db->query("SELECT id,name_c FROM pkw_mon WHERE id IN ($requiremon)");
	while($m = $db->fetch_array($query)){
		$text.= "\$pokemon[$m[id]] = '$m[name_c]';";
	}
	$query = $db->query("SELECT id,name FROM pkw_ware WHERE id IN ($requireware)");
	while($m = $db->fetch_array($query)){
		$text.= "\$ware[$m[id]] = '$m[name]';";
	}
	$query = $db->query("SELECT id,name_c FROM pkw_skill WHERE id IN ($requireskill)");
	while($m = $db->fetch_array($query)){
		$text.= "\$skill[$m[id]] = '$m[name_c]';";
	}
	eval($text);
	@$fp = fopen('./evolution_data.php', 'w');
	@fwrite($fp, "<?php $text ?>");
	@fclose($fp);
	showmsg('更新完毕', 'pokemon_evolution.php');
}
$mon = array();
$query = $db->query("SELECT id,name_c FROM pkw_mon WHERE 1");
while($p = $db->fetch_array($query)){
	$mon[$p['id']] = array('name_c'=>$p['name_c'],'name_e'=>$p['name_e'],'name_j'=>$p['name_j']);
}
include template('header');
?>
<div id="nav"></div>
<div class="mainbox"><h1>POKEMON 进化表</h1>
<table>
  <thead>
    <td>原始编号</td>
    <td>原始图片</td>
    <td>进化条件</td>
    <td>进化编号</td>
    <td>进化图片</td>
  </thead>
<? foreach($evolist as $evo){ ?>
  <tr>
    <td><?=$evo['original']?></td>
    <td><img src=file:///F:/Pokemon/images/pokemon_thumb/<?=$evo['original']?>.gif  alt="<?=$mon[$evo['original']]['name_c']?>"/></td>
    <td><?=$evo['require']?></td>
    <td><?=$evo['evoluted']?></td>
    <td><img src=file:///F:/Pokemon/images/pokemon_thumb/<?=$evo['evoluted']?>.gif  alt="<?=$mon[$evo['evoluted']]['name_c']?>"/></td>
  </tr>
<? }?>
</table>
</div>
<div class="pages_btns"><?=$multipage?></div>
<? include template('footer');?>
