<?php
if(!defined('IN_PKW')) exit('Access Denied');

function pkw_evolute($mon = array(), $action = ''){
	global $timestamp, $timeoffset, $db, $tablepre, $PKWpre, $_CONFIG;

	if(!is_array($mon) || !$mon) exit('Error: Argue of Evolution');
	if($mon['status'] == 0 || $mon['status'] == 2 || $mon['status'] >= 9) return false;

	$presenthour = intval(gmdate('H', $timestamp + 3600 * $timeoffset));
	$success = FALSE;
	$exsql = $success_msg = '';
	$orimonid = $mon['id'];
	
	$query = $db->query("SELECT e.*,m.name_c,m.atb1,m.atb2 FROM {$PKWpre}evolution e LEFT JOIN {$PKWpre}mon m ON m.id=e.evoluted WHERE e.original=$mon[pokeid] ORDER BY rand()");
	while(!$success && $evo = $db->fetch_array($query)){
		switch($evo['evotype']){
		case 1:if($mon['frd'] >= 200){
			$success_msg = '亲密度进化';
			$success = TRUE;
		}
		break;
		case 2:if($mon['frd'] >= 200 && ($presenthour >= 6 || $presenthour <= 18)){
			$success_msg = '白天亲密度进化';
			$success = TRUE;
		}
		break;
		case 3:if($mon['frd'] >= 200 && ($presenthour <= 6 || $presenthour >= 18)){
			$success_msg = '夜晚亲密度进化';
			$success = TRUE;
		}
		break;
		case 4:if($mon['level'] >= $evo['require']){
			$success_msg = "等级达到$evo[require]级";
			$success = TRUE;
		}
		break;
		case 5:if($action == 'give'){
			$success_msg = '通讯进化';
			$success = TRUE;
		}
		break;
		case 6:if($action == 'give' && $mon['equip'] == $evo['require']){
			$success_msg = '携带物品通讯进化';
			$exsql.= ',equip=0';
			$success = TRUE;
		}
		break;
		case 7:if($mon['equip'] == $evo['require']){
			$success_msg = "携带{$requireware}进化";
			$exsql.= ',equip=0';
			$success = TRUE;
		}
		break;
		case 8:if($mon['atk'] > $mon['def'] && $mon['level'] >= $evo['require']){
			$success_msg = "攻击大于防御/等级达到$evo[require]";
			$success = TRUE;
		}
		break;
		case 9:if($mon['atk'] == $mon['def'] && $mon['level'] >= $evo['require']){
			$success_msg = "攻击等于防御/等级达到$evo[require]";
			$success = TRUE;
		}
		break;
		case 10:if($mon['atk'] < $mon['def'] && $mon['level'] >= $evo['require']){
			$success_msg = "攻击小于防御/等级达到$evo[require]";
			$success = TRUE;
		}
		break;
		case 11:$judgement = $mon['id'] + $mon['nature'] + $mon['trait'] + $mon['gender'];
		if(floor($judgement / 2) == $judgement / 2 && $mon['level'] >= $evo['require']){
			$success_msg = "性格值尾数为偶数/等级达到$evo[require]";
			$success = TRUE;
		}
		break;
		case 12:$judgement = $mon['id'] + $mon['nature'];
		if(floor($judgement / 2) != $judgement / 2 && $mon['level'] >= $evo['require']){
			$success_msg = "性格值尾数为奇数/等级达到$evo[require]";
			$success = TRUE;
		}
		break;
		/*case 13:if($mon['level'] >= $evo['require']){
			$success_msg = "等级达到$evo[require]级";
			$success = TRUE;
		}
		break;
		case 14:if(1 == 2){
			$success_msg = "等级达到$evo[require]级，身上有空位";
			$success = TRUE;
		}*/
		break;
		case 15:if($mon['bty'] >= $evo['require']){
			$success_msg = "美丽度达到$evo[require]";
			$success = TRUE;
		}
		break;
		case 16:if($mon['gender'] == 1 && $mon['equip'] == $evo['require']){
			$success_msg = "雄性/使用$requireware";
			$success = TRUE;
		}
		break;
		case 17:if($mon['gender'] == 2 && $mon['equip'] == $evo['require']){
			$success_msg = "雌性/携带$requireware";
			$exsql.= ',equip=0';
			$success = TRUE;
		}
		break;
		case 18:if($mon['equip'] == $evo['require']){
			$exsql.= ',equip=0';
			$success_msg = "携带{$requireware}进化";
			$success = TRUE;
		}
		break;
		case 19:if($mon['equip'] == $evo['require'] && $presenthour <= 6 || $presenthour >= 18){
			$exsql.= ',equip=0';
			$success_msg = "夜晚携带$requireware";
			$success = TRUE;
		}
		break;
		case 20:if(in_array($evo['require'], $mon['skilllist'])){
			$success_msg = "学会{$requireskill}进化";
			$success = TRUE;
		}
		break;
		case 21:
			$query = $db->query("SELECT pokeid FROM {$PKWpre}mymon WHERE owner=$discuz_user");
			while($evo['require'] == $db->result($query, 0)){
				$success_msg = "队伍中有$evo[require]进化";
				$success = TRUE;
			}
		break;
		case 22:if($mon['gender'] == 1 && $mon['level'] >= $evo['require']){
			$success_msg = "雄性/等级达到$evo[require]";
			$success = TRUE;
		}
		break;
		case 23:if($mon['gender'] == 2 && $mon['level'] >= $evo['require']){
			$success_msg = "雌性/等级达到$evo[require]";
			$success = TRUE;
		}
		break;
		case 24:if($GLOBALS['index'] == 'adven' && $GLOBALS['mapid'] == $evo['require']){
			$success_msg = "殿元山脉地区升级";
			$success = TRUE;
		}
		break;
		/*case 25:
			$success_msg = "白岱森林升级";
		break;
		case 26:
			$success_msg = "殿元山脉飞雪地区升级";
		break;*/
		}
	}
	if($success){
		$newmon = pkw_generateMon($evo['evoluted'], $mon['level'], $gender = $mon['gender'], $mon['shape'], $mon['natureid'], 0, $mon['status']);
		$newmon['id'] = $mon['id'];
		$newmon['ori_pokeid'] = $evo['original'];
		return $newmon;
	}else return false;
}
?>