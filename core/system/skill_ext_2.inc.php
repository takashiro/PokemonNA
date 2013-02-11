<?php
if(!defined('IN_PKW')) exit('Access Denied');

switch($skillext){
	case 51:
	$rev['tmpsdf']+= $rev['sdf'] / $rev['level'] * 2;
break;
	case 52:
	$obv['tmpspr']-= $obv['spr'] * 0.1;
break;
	case 53:
	$rev['hp']+= $obvhurt / 2;
break;
	case 54:
	$obv['hp']-= rand(0, $obv['hp']) / 2;
break;
	case 55:
	$rev['hp'] = $rev['maxhp'];
	$rev['status'] = 4;
break;
	case 56:
	if($obv['atb1'] != 8 && $obv['atb2'] != 8) $obvhurt = $revhurt * 2;	
break;
	case 57:
	$code = '$rev[\'tmpstatus\'][18] = $rev[\'tmpstatus\'][19] = $timestamp + $_CONFIG[\'battlewaittime\'];';
	$rev['tmpstatus'][20] = array($code, $timestamp + 2 * $_CONFIG['battlewaittime']);
break;
	case 58:
	$obv_equip = $obv['equip'];
	$obv['equip'] = $rev['equip'];
	$rev['equip'] = $obv_equip;
	unset($obv_equip);
break;
	case 59:
	$rev['tmptrait'] = $obv['trait'];
break;
	case 60:
	if(in_array($obv_skill['ext'] , $direct_status_ext)){
		skillext_effect($obv_skill['ext'], $side);
		$rev['status'] = 1;
		$rev['tmpatk'] = $rev['tmpdef'] = $rev['tmpstk'] = $rev['tmpsdf'] = $rev['tmpspd'] = $rev['tmpspr'] = 0;
	}
	//先制并反弹对手的直接异常状态技与直接弱化技
break;
	case 61:
	$obv['tmptrait'] = $rev['trait'];
	$rev['tmptrait'] = $obv['trait'];
break;
	case 62:
	$obv['tmpstatus'][13] = array($rev['skill'], $timestamp + 5 * $_CONFIG['battlewaitime']);
	if($obv['username']){
		$tmpstatus = $obv['tmpstatus'][13];
		$db->query("UPDATE {$PKWpre}mymon SET tmpstatus='$tmpstatus' WHERE owner='$obv[username]' AND status < 10 AND status!=0 AND status!=9");
	} 
	//己方拥有的技巧敌全队不可使用
break;
	case 63:
	if(rand(1, 2) == 1) $obv['tmpdef']-= $obv['sdf'] / $obv['level'];
break;
	case 64:
	if(rand(1, 2) == 1) $obv['tmpdef']-= $obv['stk'] / $obv['level'];
break;
	case 65:
	$rev['tmpdef']+= $rev['def'] / $rev['level'];
	$rev['tmpsdf']+= $rev['sdf'] / $rev['level'];
break;
	case 66:
	if(rand(1, 10) == 1){
		$revhurt = 0;
		$obv_undermsg.= '畏缩而没有听训练师的命令出招！';
	}
break;
	case 67:
	$rev['tmpstk']+= $rev['stk'] / $rev['level'];
	$rev['tmpsdf']+= $rev['sdf'] / $rev['level'];
break;
	case 68:
	//飞行系与飘浮特性以及电磁飘浮状态可以被地面系攻击所伤害,飞空和飞跃不可使用,已飞空PM重返地面
break;
	case 69:
	//对手回避率复原且超能系攻击技巧能攻击邪恶系
break;
	case 70:
	//己方丧失战斗力,己方上场精灵回复体力和异常状态
break;
	case 71:
	//自身异常状态转移至目标单体
break;
	case 72:
	//5回合内敌二体不可使用回复技巧
break;
	case 73:
	$dist = $rev['atk'] - $rev['def'];
	$rev['tmpatk'] -= $dist;
	$rev['tmpdef'] += $dist;
break;
	case 74:
	//和对手交换攻击和特殊攻击力的能力等级
break;
	case 75:
	//和对手交换防御和特殊防御力的能力等级
break;
	case 76:
	//与对手互换能力等级
break;
	case 77:
	if(rand(1, 5) == 1) $obv['tmpstatus'][14] = $timestamp + $_CONFIG['battlewaittime'];
break;
	case 78:
	//后制,5回合内若双方技能优先度相同则改为速度慢者先攻,全场有效
break;
	case 79:
	//自身失去战斗力,己方上场精灵体力,异常状态与PP全回复
break;
	case 80:
	if(rand(1, 10) == 1) $obv['status'] = 5;
break;
	case 81:
	$obv['status'] = 5;
break;
	case 82:
	//30%敌麻痹,晴天命中50%,雨天100%命中率,能击中飞空/飞跃的PM
break;
	case 83:
	$obv['status'] = 5;
break;
	case 84:
	if(rand(1, 10) <= 3) $obv['status'] = 5;
break;
	case 85:
	//己方单体提升特殊防御力,下回合己方电系伤害加倍
break;
	case 86:
	//自身损失等量于1/3伤害的体力,10%敌麻痹
break;
	case 87:
	//必定命中
break;
	case 88:
	//5回合内己方不会被地面系技能攻击
break;
	case 89:
	//10%对手麻痹或畏缩
break;
	case 90:
	//70%提升自身特殊攻击力
break;
	case 91:
	//战斗后得到金钱
break;
	case 92:
	//当回合蓄力,下回合攻击
break;
	case 93:
	//提升自身两级攻击力
break;
	case 94:
	//强制对方交换精灵,强制结束野外战斗,成立条件:	n*（攻方lv+对方lv）/256+1>=对方lv/4,n为0~255随机数
break;
	case 95:
	//30%敌畏缩,攻击变小的对手则威力加倍
break;
	case 96:
	//30%敌畏缩
break;
	case 97:
	//命中则自身损失等量于1/4伤害的体力
break;
	case 98:
	//随机攻击敌方单体,攻击完后混乱
break;
	case 99:
	//自身损失等量于1/3伤害的体力
break;
	case 100:
	//敌方二体降低防御力
}
?>