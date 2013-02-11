<?php

class Pokemon{
	private $attr = array();

	function __construct($id){
		global $db;
		$db->select_table('pokemon');
		$this->attr = $db->FETCH('*', 'id='.$id);
	}

	function __destruct(){

	}

	function attr($key, $value = null){
		if($value == null){
			return $this->attr[$key];
		}else if(isset($this->attr[$key])){
			$this->attr[$key] = $value;
		}
	}

	function acquireSkill($skillid){

	}

	function removeSkill($skillid){

	}

	function getSkills(){

	}

	function getSkillIds(){
		return $this->attr['skill'];
	}

	function toArray(){
		return $this->attr;
	}

	function toReadable($mon = NULL){
		if($mon == NULL){
			$mon = $this->attr;
		}

		$mon['regdate_c'] = gmdate('Y-m-d H:i:s', $mon['regdate'] + 3600 * $GLOBALS['timeoffset']);
		if($mon['status'] == 9){
			foreach(array_keys($mon) as $k)
				$mon[$k] = ($k == 'regdate_c')?$mon['regdate_c']:(is_numeric($mon[$k])?0:'未知');
			$mon['exp_pct'] = $mon['spr'] = 0;
			$mon['name'] = $mon['status_c'] = '蛋';
			$mon['picid'] = 'egg';
		}else{
			if($mon['status'] != 2 && $mon['hp'] == 0) $mon['status'] = 0;
			$mon['picid'] = $mon['shape']?($mon['pokeid'].'_'.$mon['shape']):$mon['pokeid'];
			if($mon['hp'] > $mon['maxhp']) $mon['hp'] = $mon['maxhp'];
			if($mon['mp'] > $mon['maxmp']) $mon['mp'] = $mon['maxmp'];
			$mon['atb1_c'] = Pokemon::$atb[$mon['atb1']];
			$mon['atb2_c'] =  $mon['atb2'] ? Pokemon::$atb[$mon['atb2']] : '';
			$mon['gender_c'] = Pokemon::$gender[$mon['gender']];
			if($mon['maxhp'] != 0) $mon['hp_pct'] = intval(100 * $mon['hp'] / $mon['maxhp']);
			if($mon['maxmp'] != 0) $mon['mp_pct'] = intval(100 * $mon['mp'] / $mon['maxmp']);
			if(isset($mon['growthtype'])){
				$levelbase = Pokemon::LevelToExp($mon['level'], $mon['growthtype']);
				$levelfloor = Pokemon::LevelToExp($mon['level']+1, $mon['growthtype']) - $levelbase;
				$mon['exp_pct'] = intval (100 * ($mon['exp'] - $levelbase) / $levelfloor);
			}
			$mon['frd_pct'] = floor(100 * $mon['frd'] / 255);
			$mon['status_c'] = Pokemon::$status[$mon['status']];
			if($mon['skill']){
				$mon['skilllist'] = array_unique(explode(',', $mon['skill']));
			}
			if($mon['equip']) $mon['equiplist'] = explode(',', $mon['equip']);
			$mon['trait'] = $mon['tmptrait']?$mon['tmptrait']:$mon['trait'];
			$mon['atk'] += $mon['tmpatk'];
			$mon['def'] += $mon['tmpdef'];
			$mon['stk'] += $mon['tmpstk'];
			$mon['sdf'] += $mon['tmpsdf'];
			$mon['spd'] += $mon['tmpspd'];
			$mon['spr'] = floor(($mon['atk'] + $mon['hp'] + $mon['speed'] + $mon['mqp']) / 10) + $mon['tmpspr'];
			$mon['height_c'] = $mon['height'].' m';
			$mon['weight_c'] = $mon['weight'].' kg';
			$mon['growthtype_c'] = Pokemon::$growthType[$mon['growthtype']];
		}
		return $mon;
	}

	function evolute($action = ''){
		global $timestamp, $timeoffset, $db, $tpre, $_CONFIG;

		$mon = &$this->attr;
		if($mon['status'] == 0 || $mon['status'] == 2 || $mon['status'] >= 9) return false;

		$presenthour = intval(gmdate('H', $timestamp + 3600 * $timeoffset));
		$success = FALSE;
		$exsql = $success_msg = '';
		$orimonid = $mon['id'];
		
		$query = $db->query("SELECT e.*,m.name_c,m.atb1,m.atb2 FROM {$tpre}evolution e LEFT JOIN {$tpre}mon m ON m.id=e.evoluted WHERE e.original=$mon[pokeid] ORDER BY rand()");
		while(!$success && $evo = $db->fetch_array($query)){
			switch($evo['evotype']){
			case 1:if($mon['frd'] >= 200){
				//$success_msg = '亲密度进化';
				$success = TRUE;
			}
			break;
			case 2:if($mon['frd'] >= 200 && ($presenthour >= 6 || $presenthour <= 18)){
				//$success_msg = '白天亲密度进化';
				$success = TRUE;
			}
			break;
			case 3:if($mon['frd'] >= 200 && ($presenthour <= 6 || $presenthour >= 18)){
				//$success_msg = '夜晚亲密度进化';
				$success = TRUE;
			}
			break;
			case 4:if($mon['level'] >= $evo['require']){
				//$success_msg = "等级达到$evo[require]级";
				$success = TRUE;
			}
			break;
			case 5:if($action == 'give'){
				//$success_msg = '通讯进化';
				$success = TRUE;
			}
			break;
			case 6:if($action == 'give' && $mon['equip'] == $evo['require']){
				//$success_msg = '携带物品通讯进化';
				$exsql.= ',equip=0';
				$success = TRUE;
			}
			break;
			case 7:if($mon['equip'] == $evo['require']){
				//$success_msg = "携带{$requireware}进化";
				$exsql.= ',equip=0';
				$success = TRUE;
			}
			break;
			case 8:if($mon['atk'] > $mon['def'] && $mon['level'] >= $evo['require']){
				//$success_msg = "攻击大于防御/等级达到$evo[require]";
				$success = TRUE;
			}
			break;
			case 9:if($mon['atk'] == $mon['def'] && $mon['level'] >= $evo['require']){
				//$success_msg = "攻击等于防御/等级达到$evo[require]";
				$success = TRUE;
			}
			break;
			case 10:if($mon['atk'] < $mon['def'] && $mon['level'] >= $evo['require']){
				//$success_msg = "攻击小于防御/等级达到$evo[require]";
				$success = TRUE;
			}
			break;
			case 11:$judgement = $mon['id'] + $mon['nature'] + $mon['trait'] + $mon['gender'];
			if(floor($judgement / 2) == $judgement / 2 && $mon['level'] >= $evo['require']){
				//$success_msg = "性格值尾数为偶数/等级达到$evo[require]";
				$success = TRUE;
			}
			break;
			case 12:$judgement = $mon['id'] + $mon['nature'];
			if(floor($judgement / 2) != $judgement / 2 && $mon['level'] >= $evo['require']){
				//$success_msg = "性格值尾数为奇数/等级达到$evo[require]";
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
				//$success_msg = "美丽度达到$evo[require]";
				$success = TRUE;
			}
			break;
			case 16:if($mon['gender'] == 1 && $mon['equip'] == $evo['require']){
				//$success_msg = "雄性/使用$requireware";
				$success = TRUE;
			}
			break;
			case 17:if($mon['gender'] == 2 && $mon['equip'] == $evo['require']){
				//$success_msg = "雌性/携带$requireware";
				$exsql.= ',equip=0';
				$success = TRUE;
			}
			break;
			case 18:if($mon['equip'] == $evo['require']){
				$exsql.= ',equip=0';
				//$success_msg = "携带{$requireware}进化";
				$success = TRUE;
			}
			break;
			case 19:if($mon['equip'] == $evo['require'] && $presenthour <= 6 || $presenthour >= 18){
				$exsql.= ',equip=0';
				//$success_msg = "夜晚携带$requireware";
				$success = TRUE;
			}
			break;
			case 20:if(in_array($evo['require'], $mon['skilllist'])){
				//$success_msg = "学会{$requireskill}进化";
				$success = TRUE;
			}
			break;
			case 21:
				$query = $db->query("SELECT pokeid FROM {$tpre}mymon WHERE owner=$discuz_user");
				while($evo['require'] == $db->result($query, 0)){
					//$success_msg = "队伍中有$evo[require]进化";
					$success = TRUE;
				}
			break;
			case 22:if($mon['gender'] == 1 && $mon['level'] >= $evo['require']){
				//$success_msg = "雄性/等级达到$evo[require]";
				$success = TRUE;
			}
			break;
			case 23:if($mon['gender'] == 2 && $mon['level'] >= $evo['require']){
				//$success_msg = "雌性/等级达到$evo[require]";
				$success = TRUE;
			}
			break;
			case 24:if($GLOBALS['index'] == 'adven' && $GLOBALS['mapid'] == $evo['require']){
				//$success_msg = "殿元山脉地区升级";
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

	function useSkill($skillid, $target = null){

	}

	static function LevelToExp($level, $growth_type){
		switch($growth_type){
			case 0:$exp = pow($level, 3);break;
			case 1:
				if($level >= 0 && $level <= 50)
					$exp = pow($level, 3) * (100 - $level) / 50;
				elseif($level >= 51 && $level <= 68)
					$exp = pow($level, 3) * (150 - $level) / 100;
				elseif($level >= 69 && $level >= 98){
					$lvbase = $level % 3;
					$fixp = ($lvbase == 1)?0.008:(($lvbase == 2)?0.007:0);
					$exp = pow($level, 3) * (1.274 - 1 / 50 * floor($level / 3)) - $fixp * $lvbase;
				}elseif($lv >= 99)
					$exp = pow($level, 3) * (160 - $level) / 100;
			break;
			case 2:
				if($level >= 0 && $level <= 15)
					$exp = pow($level, 3) * (24 + floor(($level + 1) / 3)) / 50;
				elseif($level >= 16 && $level <= 35)
					$exp = pow($level, 3) * (14 + $level) / 50;
				elseif($level >= 36)
					$exp = pow($level, 3) * (32 + floor($level / 2)) / 50;
			break;
			case 3:$exp = 1.2 * pow($level, 3) - 15 * pow($level, 2) + 100 * $level - 140;break;
			case 4:$exp = 0.8 * pow($level, 3);break;
			case 5:$exp = 1.25 * pow($level, 3);break;
			default:break;
		}
		return floor($exp);
	}

	static function ExpToLevel($orilevel, $exp, $growth_type){
		$levelup = 0;
		while(Pokemon::LevelToExp(($orilevel + $levelup), $growth_type) < $exp) $levelup++;
		return $orilevel + $levelup - 1;
	}

	function pkw_exp_incre($orilevel, $exp, $monid, $pokeid, $natureid){
		global $tpre, $db;
		$selectfields = 's.growthtype';
		foreach(array('hp','atk','def','stk','sdf','spd') as $p) $selectfields.= ',s.'.$p.',i.iv_'.$p.',i.ep_'.$p;
		$mon = $db->fetch_first("SELECT $selectfields FROM {$tpre}mymonext i LEFT JOIN {$tpre}mon s ON s.id=$pokeid WHERE i.id=$monid");
		$mon['level'] = pkw_exp2lv($orilevel, $exp, $mon['growthtype']);
		$mon['hp'] = ceil(($mon['hp']*2 + $mon['iv_hp'] + ($mon['ep_hp'] / 4)) * $mon['level'] / 100 + 10 + $mon['level']);

		$naturefix = PokemonNature::$value[$natureid];
		foreach(array('atk','def','stk','sdf','spd') as $pi => $p){
			$mon[$p] = ceil((($mon[$p] * 2 + $mon['iv_'.$p] + ( $mon['ep_'.$p] / 4)) * $mon['level'] / 100 + 5) * $naturefix[$pi]);
		}
		$mon_frdup += ($mon['level'] - $orilevel) * 5;

		return ",level=$mon[level],exp=$exp,frd=frd+$mon_frdup,mp=maxmp,hp=$mon[hp],atk=$mon[atk],def=$mon[def],stk=$mon[stk],sdf=$mon[sdf],spd=$mon[spd]";
	}

	static function Generate($pokeid, $level, $gender = 0, $shape = 0, $natureid = 0, $trait = 0, $status = 1){
		global $tpre, $db;

		$query = $db->query("SELECT * FROM {$tpre}pokemoninfo WHERE id=$pokeid");
		if($mon = $db->fetch_array($query)){
			$mon['pokeid'] = $mon['id'];
			$level = intval(($level <= 0)?1:$level);
			if($gender == 0){
				$gender = ($mon['gender'] == 255) ? 3 :((rand(1, 254) >= $mon['gender']) ? 1 : 2);
			}else{
				$gender = ($mon['gender'] == 255) ? 3 : $gender;
			}
			$mon['gender'] = $gender;
			$mon['name'] = $mon['name_j'];
		
			$mon['godev'] = rand(0, 100);
			$mon['frd'] = randnum($mon['frd']);
			$mon['frd'] = ($frd < 70) ? 70 : $mon['frd'];
			$mon['natureid'] = ($natureid == 0)?rand(1, 25):$natureid;
			$naturefix = PokemonNature::$value[$mon['natureid']];

			$mon['iv_hp'] = rand(1, 31);
			$mon['hp'] = ceil(($mon['hp']*2 + $mon['iv_hp'] + (0/4)) * $level / 100 + 10 + $level);
			$mon['mp'] = rand(10, 100);
		
			foreach(array('atk','def','stk','sdf','spd') as $pi => $p){
				$mon['iv_'.$p] = rand(1, 31);
				$mon['ep_'.$p] = 0;
				$mon[$p] = ceil((($mon[$p] * 2 + $mon['iv_'.$p] + (0/4)) * $level / 100 + 5) * $naturefix[$pi]);
			}
		
			$mon['regdate'] = TIMESTAMP;
			if($trait == 0)
				$mon['trait'] = (rand(1,2)==1 && $mon['trait2'])?$mon['trait2']:$mon['trait1'];
			else
				$mon['trait'] = $trait;
			$mon['height'] = randnum($mon['height'] * 10) / 10;
			$mon['weight'] = randnum($mon['weight'] * 10) / 10;
		
			$mon['level'] = $level;
			$mon['status'] = intval($status);
			$mon['exp'] = Pokemon::LevelToExp($level, $mon['growthtype']);
			if($shape == 0)	$mon['shape'] = $mon['shapenum'] ? rand(1, $mon['shapenum']) : 0;
			else $mon['shape'] = ($shape <= $mon['shapenum']) ? $shape : 0;
		}else $mon = FALSE;

		unset($mon['shapenum']);

		$mon['maxhp'] = $mon['hp'];
		$mon['maxmp'] = $mon['mp'];

		return $mon;
	}

	static public function InsertIntoDB($mon){
		global $db;

		foreach($mon as $key => $value){
			if(!in_array($key, Pokemon::$individualAttributes)){
				unset($mon[$key]);
			}
		}

		$db->select_table('pokemon');
		$db->INSERT($mon);
	}

	static function EvolutionCondition($e){
		global $db, $mon, $tpre;
		switch($e['evotype']){
		case 1:
			$e['require_c'] = "亲密度进化";
		break;
		case 2:
			$e['require_c'] = "白天亲密度进化";
		break;
		case 3:
			$e['require_c'] = "夜晚亲密度进化";
		break;
		case 4:
			$e['require_c'] = "等级达到$e[require]级";
		break;
		case 5:
			$e['require_c'] = "通讯进化";
		break;
		case 6:
			$query = $db->query("SELECT name_c FROM {$tpre}ware WHERE id=$e[require]");
			$requireware = $db->result($query, 0);
			$e['require_c'] = "携带{$requireware}通讯进化";
		break;
		case 7:
			$query = $db->query("SELECT name_c FROM {$tpre}ware WHERE id=$e[require]");
			$requireware = $db->result($query, 0);
			$e['require_c'] = "携带{$requireware}进化";
		break;
		case 8:
			$e['require_c'] = "攻击大于防御/等级达到$e[require]";
		break;
		case 9:
			$e['require_c'] = "攻击等于防御/等级达到$e[require]";
		break;
		case 10:
			$e['require_c'] = "攻击小于防御/等级达到$e[require]";
		break;
		case 11:
			$e['require_c'] = "性格值尾数为偶数/等级达到$e[require]";
		break;
		case 12:
			$e['require_c'] = "性格值尾数为奇数/等级达到$e[require]";
		break;
		case 13:
			$e['require_c'] = "等级达到$e[require]级";
		break;
		case 14:
			$e['require_c'] = "等级达到$e[require]级，身上有空位";
		break;
		case 15:
			$e['require_c'] = "美丽度达到$e[require]";
		break;
		case 16:
			$query = $db->query("SELECT name_c FROM {$tpre}ware WHERE id=$e[require]");
			$requireware = $db->result($query, 0);
			$e['require_c'] = "雄性/使用$requireware";
		break;
		case 17:
			$query = $db->query("SELECT name_c FROM {$tpre}ware WHERE id=$e[require]");
			$requireware = $db->result($query, 0);
			$e['require_c'] = "雌性/携带$requireware";
		break;
		case 18:
			$query = $db->query("SELECT name_c FROM {$tpre}ware WHERE id=$e[require]");
			$requireware = $db->result($query, 0);
			$e['require_c'] = "携带{$requireware}进化";
		break;
		case 19:
			$query = $db->query("SELECT name_c FROM {$tpre}ware WHERE id=$e[require]");
			$requireware = $db->result($query, 0);
			$e['require_c'] = "夜晚携带$requireware";
		break;
		case 20:
			$query = $db->query("SELECT name_c FROM {$tpre}skill WHERE id=$e[require]");
			$requireskill = $db->result($query, 0);
			$e['require_c'] = "学会{$requireskill}进化";
		break;
		case 21:
			$query = $db->query("SELECT name_c FROM {$tpre}mon WHERE id=$e[require]");
			$requiremon = $db->result($query, 0);
			$e['require_c'] = "队伍中有{$requiremon}进化";
		break;
		case 22:
			$e['require_c'] = "雄性/等级达到$e[require]";
		break;
		case 23:
			$e['require_c'] = "雌性/等级达到$e[require]";
		break;
		case 24:
			$e['require_c'] = "殿元山脉地区升级";
		break;
		case 25:
			$e['require_c'] = "白岱森林升级";
		break;
		case 26:
			$e['require_c'] = "殿元山脉飞雪地区升级";
		break;
		default:break;
		}
		return $e;
	}

	static private $individualAttributes = array('pokeid','shape','ownerid','owner','name','regdate','atb1','atb2','level','exp','status','tmpstatus','gender','natureid','trait','tmptrait','height','weight','godev','frd','hp','maxhp','mp','maxmp','atk','tmpatk','def','tmpdef','stk','tmpstk','sdf','tmpsdf','spd','tmpspd','tmpspr','col','bty','cut','smt','tgh','equip','skill','ep_hp','ep_atk','ep_def','ep_stk','ep_sdf','ep_spd','iv_hp','iv_atk','iv_def','iv_stk','iv_sdf','iv_spd');
	
	static public $attrName = array('godev'=>'善恶值','status'=>'状态','mqp'=>'智慧','bty'=>'魅力','level'=>'等级','frd'=>'友好度','hp'=>'体力','maxhp'=>'体力上限','mp'=>'气力','maxmp'=>'气力上限','atk'=>'攻击','atktemp'=>'临时攻击','def'=>'防御','deftemp'=>'临时防御','atk'=>'特攻','atktemp'=>'临时特攻','def'=>'特防','deftemp'=>'临时特防','spd'=>'速度','spdtemp'=>'临时速度','spr'=>'速度','sprtemp'=>'临时速度');
	static public $atb = array('所有','火','水','电','草','冰','超','龙','恶','普','格','飞','虫','毒','地','岩','钢','鬼','???');
	static public $eggType = array('所有', '???','怪兽','水中1','水中2','水中3','虫','飞行','陆上','妖精','植物','矿物','人形','不定形','百变怪','龙','未发现');
	static public $growthType = array('较快','不定','波动','较慢','快','慢');
	static public $gender = array('任意','<font color=#3399FF>雄性</font>','<font color=#FF3366>雌性</font>','无');
	static public $status = array('<font color=gray>不能战斗</font>','正常','<font color=pink>救治中……</font>','<font color=purple>中毒</font>','<font color=lightblue>睡眠</font>','<font color=brown>麻痹</font>','<font color=red>烧伤</font>','<font color=blue>冰冻</font>','','蛋');
	
	static public function nature(){
		return PokemonNature::$value;
	}

	static public function trait(){
		return PokemonTrait::$value;
	}
}

?>