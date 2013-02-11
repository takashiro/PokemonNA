<?php
require_once './core/init.inc.php';

$credit = 'extcredits'.$_CONFIG['bank_credit'];
$extcre = $extcredits[$_CONFIG['bank_credit']];
$extcredit = $GLOBALS[$credit];

$query = $db->query("SELECT p.pokeid,p.name,p.owner,p.level,p.shape,m.badge FROM {$tpre}pokemon p LEFT JOIN {$tpre}trainer m ON m.username=p.owner WHERE 1 ORDER BY m.badgenum DESC LIMIT 0,1");
$toptrainer['picid'] = $toptrainer['shape']?($toptrainer['pokeid'].'_'.$toptrainer['shape']):$toptrainer['pokeid'];
$toptrainer = $db->fetch_array($query);
$toptrainer['badgenums'] = count(explode(',', $toptrainer['badge']));
$query = $db->query("SELECT p.pokeid,p.name,p.owner,p.level,p.shape,m.ribbon FROM {$tpre}pokemon p LEFT JOIN {$tpre}trainer m ON m.username=p.owner WHERE 1 ORDER BY m.ribbonnum DESC LIMIT 0,1");
$topcoordinator['picid'] = $topcoordinator['shape']?($topcoordinator['pokeid'].'_'.$topcoordinator['shape']):$topcoordinator['pokeid'];
$topcoordinator = $db->fetch_array($query);
$topcoordinator['ribbonnums'] = count(explode(',', $topcoordinator['ribbon']));
$query = $db->query("SELECT pokeid,name,owner,level,shape FROM {$tpre}pokemon WHERE 1 ORDER BY `height`*5+`weight`*5+`godev`+`frd`*3 DESC LIMIT 0,1");
$topbreeder['picid'] = $topbreeder['shape']?($topbreeder['pokeid'].'_'.$topbreeder['shape']):$topbreeder['pokeid'];
$topbreeder = $db->fetch_array($query);

/*$query = $db->query("SELECT * FROM {$tpre}sessions WHERE invisible='0' AND username!='' ORDER BY lastactivity DESC");
eval('$actions = array_flip(array('.$_CONFIG['actionids'].'));');
$actionlang = array('center'=>'在精灵中心','institude'=>'在研究所','mypokemon'=>'查看自己的精灵','myware'=>'查看自己的背包','my'=>'使用训练师手表','shop'=>'在逛精灵商店','feeder'=>'在精灵饲育屋','fruit'=>'在树果屋','adven'=>'与野生精灵战斗','gym'=>'挑战道馆','contest'=>'参加华丽大赛');*/
$onlinelist = array();
/*while($user = $db->fetch_array($query)){
	$user['action'] = $actionlang[$actions[$user['action']]];
	$user['action'] = $user['action']?$user['action']:'在论坛专区';
	$onlinelist[] = $user;
}*/
$onlinenum = count($onlinelist);

list($hour, $minute) = explode(':', gmdate('H:i', $timestamp + 3600 * $timeoffset));
if($minute <= 15) $joy_time = $hour.':15';
elseif($minute > 15 && $minute <= 45) $joy_time = $hour.':45';
elseif($minute > 45) $joy_time = (($hour == 23)?0:$hour+1).':15';

include view('center');
?>
