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

		$naturefix = Pokemon::$nature[$natureid];
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
			$naturefix = Pokemon::$nature[$mon['natureid']];

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
	static public $nature = array(
		array(),
		array('name_j'=>'照れ屋','name_c'=>'害羞','name_e'=>'Bashful',1,1,1,1,1),
		array('name_j'=>'素直','name_c'=>'坦率','name_e'=>'Docile',1,1,1,1,1),
		array('name_j'=>'頑張り屋','name_c'=>'实干','name_e'=>'Hardy',1,1,1,1,1),
		array('name_j'=>'真面目','name_c'=>'认真','name_e'=>'Serious',1,1,1,1,1),
		array('name_j'=>'気紛れ','name_c'=>'浮躁','name_e'=>'Quirky',1,1,1,1,1),
		array('name_j'=>'図太い','name_c'=>'大胆','name_e'=>'Bold',0.9,1.1,1,1,1),
		array('name_j'=>'控えめ','name_c'=>'保守','name_e'=>'Modest',0.9,1,1.1,1,1),
		array('name_j'=>'穏やか','name_c'=>'沉着','name_e'=>'Calm',0.9,1,1,1.1,1),
		array('name_j'=>'臆病','name_c'=>'胆小','name_e'=>'Timid',0.9,1,1,1,1.1),
		array('name_j'=>'寂しがり','name_c'=>'孤僻','name_e'=>'Lonely',1.1,0.9,1,1,1),
		array('name_j'=>'おっとり','name_c'=>'稳重','name_e'=>'Mild',1,0.9,1.1,1,1),
		array('name_j'=>'おとなしい','name_c'=>'温顺','name_e'=>'Gentle',1,0.9,1,1.1,1),
		array('name_j'=>'せっかち','name_c'=>'急噪','name_e'=>'Hasty',1,0.9,1,1,1.1),
		array('name_j'=>'意地っ張り','name_c'=>'固执','name_e'=>'Adamant',1.1,1,0.9,1,1),
		array('name_j'=>'わんぱく','name_c'=>'淘气','name_e'=>'Impish',1,1.1,0.9,1,1),
		array('name_j'=>'慎重','name_c'=>'慎重','name_e'=>'Careful',1,1,0.9,1.1,1),
		array('name_j'=>'陽気','name_c'=>'开朗','name_e'=>'Jolly',1,1,0.9,1,1.1),
		array('name_j'=>'やんちゃ','name_c'=>'调皮','name_e'=>'Naughty',1.1,1,1,0.9,1),
		array('name_j'=>'能天気','name_c'=>'无虑','name_e'=>'Lax',1,1.1,1,0.9,1),
		array('name_j'=>'うっかりや','name_c'=>'马虎','name_e'=>'Rash',1,1,1.1,0.9,1),
		array('name_j'=>'無邪気','name_c'=>'天真','name_e'=>'Naive',1,1,1,0.9,1.1),
		array('name_j'=>'勇敢','name_c'=>'勇敢','name_e'=>'Brave',1.1,1,1,1,0.9),
		array('name_j'=>'呑気','name_c'=>'悠闲','name_e'=>'Relaxed',1,1.1,1,1,0.9),
		array('name_j'=>'冷静','name_c'=>'冷静','name_e'=>'Quiet',1,1,1.1,1,0.9),
		array('name_j'=>'生意気','name_c'=>'狂妄','name_e'=>'Sassy',1,1,1,1.1,0.9),
	);

	static public $trait = array(
		array(),
		array('name_c'=>'寒冰身体','name_j'=>'アイスボディ','name_e'=>'Ice Body','intro'=>'风雪时，自己每回合回复HP，回复量为自己最大HP的1/16'),
		array('name_c'=>'恶臭','name_j'=>'あくしゅう','name_e'=>'Stench','intro'=>'排在队伍首位则遇到野生精灵机率为1/2'),
		array('name_c'=>'厚脂肪','name_j'=>'あついしぼう','name_e'=>'Thick Fat','intro'=>'自己被火、冰系招式攻击时受到的伤害减半'),
		array('name_c'=>'后退','name_j'=>'あとだし','name_e'=>'Stall','intro'=>'自己使用不带优先度的招式时优先度-8'),
		array('name_c'=>'接雨盆','name_j'=>'あめうけざら','name_e'=>'Rain Dish','intro'=>'雨天时，自己每回合回复HP，回复量为自己最大HP的1/16'),
		array('name_c'=>'求雨','name_j'=>'あめふらし','name_e'=>'Drizzle','intro'=>'上场时将天气变为雨天(效果持续直至被更换天气)'),
		array('name_c'=>'沙地狱','name_j'=>'ありじごく','name_e'=>'Arena Trap','intro'=>'在场上时禁止对方逃跑和交换。对飞行系和浮游特性精灵无效，对接力バトンタッチ/蜻蜓回转とんぼがえり/吹飞ふきとばし/吼叫ほえる无效<>排头时遇到野生精灵几率*2'),
		array('name_c'=>'威吓','name_j'=>'いかく','name_e'=>'Intimidate','intro'=>'上场时使对方物理攻击下降一个等级<>排头时，50%机率不会遇到(自己Lv-5)等级或以下野生精灵'),
		array('name_c'=>'怒冲经穴','name_j'=>'いかりのつぼ','name_e'=>'Anger Point','intro'=>'自己被命中要害时，自己物理攻击等级提升至最大(+6)'),
		array('name_c'=>'石头脑袋','name_j'=>'いしあたま','name_e'=>'Rock Head','intro'=>'自己不会受到反弹类招式（突进とっしん/舍身撞すてみタックル/炽炎推进フレアドライブ/树桩锤ウッドハンマー/高压电击ボルテッカー/战鸟无惧ブレイブバード/地狱车じごくぐるま/诸刃头锤もろはのずつき）对自己的伤害反弹'),
		array('name_c'=>'有色眼镜','name_j'=>'いろめがね','name_e'=>'Tinted Lens','intro'=>'对目标攻击时，使用属性效果不佳的招式，其威力得到2倍补正'),
		array('name_c'=>'湿润身体','name_j'=>'うるおいボディ','name_e'=>'Hydration','intro'=>'下雨时，自己自动回复异常状态（毒，剧毒，麻痹，烧伤，睡眠，冰冻）'),
		array('name_c'=>'天气锁','name_j'=>'エアロック','name_e'=>'Air Lock','intro'=>'在场上时使天气影响消失'),
		array('name_c'=>'洞悉心灵','name_j'=>'おみとおし','name_e'=>'Frisk','intro'=>'上场时，察觉对手一员装备的道具'),
		array('name_c'=>'怪力钳','name_j'=>'かいりきバサミ','name_e'=>'Hyper Cutter','intro'=>'自己不会被别人下降物理攻击'),
		array('name_c'=>'踩影子','name_j'=>'かげふみ','name_e'=>'Shadow Tag','intro'=>'在场上时禁止对方逃跑和交换。对接力バトンタッチ/蜻蜓回转とんぼがえり/吹飞ふきとばし/吼叫ほえる无效，双方皆为此特性时无效'),
		array('name_c'=>'加速','name_j'=>'かそく','name_e'=>'Speed Boost','intro'=>'每回合结束时自己速度等级上升一阶段(被换上场不算)'),
		array('name_c'=>'破格','name_j'=>'かたやぶり','name_e'=>'Mold Breaker','intro'=>'自己的攻击不受对手防御型特性影响'),
		array('name_c'=>'甲虫装甲','name_j'=>'カブトアーマー','name_e'=>'Battle Armor','intro'=>'自己不会被命中要害'),
		array('name_c'=>'走钢线','name_j'=>'かるわざ','name_e'=>'Unburden','intro'=>'自己失去持有道具时，速度得到2倍的补正（换人或再次持有道具则失效）'),
		array('name_c'=>'坚硬','name_j'=>'がんじょう','name_e'=>'Sturdy','intro'=>'一击必杀技(尖角钻つのドリル/断头台ハサミギロチン/地裂じわれ/绝对零度ぜったいれいど)攻击自己无效'),
		array('name_c'=>'干燥肌肤','name_j'=>'かんそうはだ','name_e'=>'Dry Skin','intro'=>'晴天时，自己每回合受伤害，伤害量为自己最大HP的1/8；下雨时，自己每回合回复HP，回复量为自己最大HP的1/8；火系招式攻击自己时，自己受到的伤害增加25%；水系招式攻击自己时无效同时回复自己HP，回复量为自己最大HP的1/4'),
		array('name_c'=>'预知危险','name_j'=>'きけんよち','name_e'=>'Anticipation','intro'=>'上场时，能察觉到对手一员一招针对自己弱点的招式，或者一击必杀技和自爆技巧'),
		array('name_c'=>'气魄','name_j'=>'きもったま','name_e'=>'Scrappy','intro'=>'自己可以使用正常/格斗技能击中鬼系，鬼系技能击中普通系，属性伤害修正为一般'),
		array('name_c'=>'吸盘','name_j'=>'きゅうばん','name_e'=>'Suction Cups','intro'=>'自己不会被 吹飞/吼叫 强制交换<>排头时，较易用鱼杆钓到野生精灵'),
		array('name_c'=>'幸运','name_j'=>'きょううん','name_e'=>'Super Luck','intro'=>'自己击中对象要害的概率等级+1补正'),
		array('name_c'=>'贪吃','name_j'=>'くいしんぼう','name_e'=>'Gluttony','intro'=>'自己持有的果实都会提前到自己HP低于1/2时发动'),
		array('name_c'=>'净体','name_j'=>'クリアボディ','name_e'=>'Clear Body','intro'=>'自己不会被别人下降能力等级'),
		array('name_c'=>'激流','name_j'=>'げきりゅう','name_e'=>'Torrent','intro'=>'自己HP1/3以下时水系招数威力得到1.5倍补正'),
		array('name_c'=>'上进','name_j'=>'こんじょう','name_e'=>'Guts','intro'=>'自己异常状态下物理攻击得到1.5倍补正（毒，剧毒，麻痹，烧伤，睡眠，冰冻）（烧伤状态不减物攻）'),
		array('name_c'=>'鲨鱼皮','name_j'=>'さめはだ','name_e'=>'Rough Skin','intro'=>'每次被近身招式命中后，攻击者受伤害，伤害量为攻击者最大HP的1/8'),
		array('name_c'=>'太阳力量','name_j'=>'サンパワー','name_e'=>'Solar Power','intro'=>'天晴时，自己特攻得到1.5倍补正，但每回合受伤害，伤害量为自己最大HP的1/8'),
		array('name_c'=>'贝壳装甲','name_j'=>'シェルアーマー','name_e'=>'Shell Armor','intro'=>'自己不会被命中要害'),
		array('name_c'=>'自然恢复','name_j'=>'しぜんかいふく','name_e'=>'Natural Cure','intro'=>'战斗中被交换下场则自己全异常解除'),
		array('name_c'=>'湿气','name_j'=>'しめりけ','name_e'=>'Damp','intro'=>'在场上时禁止场上全体精灵使用自爆和大自爆'),
		array('name_c'=>'柔软','name_j'=>'じゅうなん','name_e'=>'Limber','intro'=>'自己不会进入麻痹状态'),
		array('name_c'=>'磁性','name_j'=>'じりょく','name_e'=>'Magnet Pull','intro'=>'在场上时禁止对方钢系精灵逃跑和交换。对接力バトンタッチ/蜻蜓回转とんぼがえり/吹飞ふきとばし/吼叫ほえる无效<>排头时，如果某地有钢系精灵，则50%机率遇到'),
		array('name_c'=>'白烟','name_j'=>'しろいけむり','name_e'=>'White Smoke','intro'=>'自己的能力等级不会被他人下降<>排头时，遇到野生精灵机率1/2'),
		array('name_c'=>'同步率','name_j'=>'シンクロ','name_e'=>'Synchronize','intro'=>'自己受到对方招式影响而中毒、麻痹、烧伤时，攻击者同样会传染上<>排头时，50%机率遇到相同性格的野生精灵'),
		array('name_c'=>'深绿','name_j'=>'しんりょく','name_e'=>'Overgrow','intro'=>'自己HP1/3以下时草系招数威力得到1.5倍补正'),
		array('name_c'=>'连锁技','name_j'=>'スキルリンク','name_e'=>'Skill Link','intro'=>'使用原在一回合能作出2~5次连续攻击的招式时命中，则必定使出5次攻击'),
		array('name_c'=>'舍身','name_j'=>'すてみ','name_e'=>'Reckless','intro'=>'使用自残类招式威力得到1.2倍补正（突进とっしん/舍身撞すてみタックル/炽炎推进フレアドライブ/树桩锤ウッドハンマー/高压电击ボルテッカー/战鸟无惧ブレイブバード/地狱车じごくぐるま/诸刃头锤もろはのずつき/飞踢とびげり/飞膝撞とびひざげり）'),
		array('name_c'=>'狙击手','name_j'=>'スナイパー','name_e'=>'Sniper','intro'=>'自己击中对手要害时给予对手的最终伤害补正不再是2倍，而是4倍'),
		array('name_c'=>'湿滑','name_j'=>'すいすい','name_e'=>'Swift Swim','intro'=>'雨天时自己速度得到2倍补正'),
		array('name_c'=>'扬沙','name_j'=>'すなおこし','name_e'=>'Sand Stream','intro'=>'上场时把天气变为沙尘暴(效果持续直至被更换天气)'),
		array('name_c'=>'沙隐术','name_j'=>'すながくれ','name_e'=>'Sand Veil','intro'=>'沙尘天气时，不会受到风沙伤害且对手对自己的命中率得到0.8倍补正<>排头时，在228道路遇到野生精灵机率*1/2'),
		array('name_c'=>'缓慢启动','name_j'=>'スロースタート','name_e'=>'Slow Start','intro'=>'出场或拥有此特性后的5回合内，自己的物攻、特攻和速度减半'),
		array('name_c'=>'明锐目光','name_j'=>'するどいめ','name_e'=>'Keen Eye','intro'=>'自己不会被别人下降命中率<>排头时，50%机率不会遇到(自己Lv-5)等级或以下野生精灵'),
		array('name_c'=>'精神力','name_j'=>'せいしんりょく','name_e'=>'Inner Focus','intro'=>'自己不会害怕'),
		array('name_c'=>'静电','name_j'=>'せいでんき','name_e'=>'Static','intro'=>'对自己使用近身攻击命中后攻击者有30%几率被麻痹<>排头时，如果某地有电系精灵，则50%机率会遇到'),
		array('name_c'=>'耐热','name_j'=>'たいねつ','name_e'=>'Heat Proof','intro'=>'自己被火系招式攻击时伤害减半－且受烧伤状态下每回合受到的伤害为1/16而不是1/8'),
		array('name_c'=>'下载','name_j'=>'ダウンロード','name_e'=>'Download','intro'=>'出场时，如果对手防御<特防，则自己提升攻击一个等级；防御>=特防，则自己提升特攻一个等级；防御和特防比较包括且仅包括其能力变化等级'),
		array('name_c'=>'蜕皮','name_j'=>'だっぴ','name_e'=>'Shed Skin','intro'=>'每回合结束时，有30%几率治愈自己的异常状态（毒，剧毒，麻痹，烧伤，睡眠，冰冻）'),
		array('name_c'=>'单纯','name_j'=>'たんじゅん','name_e'=>'Simple','intro'=>'自己的能力等级提升或下降，其最终效果2倍化，但上限仍然为6阶段的效果'),
		array('name_c'=>'千鸟足','name_j'=>'ちどりあし','name_e'=>'Tangled Feet','intro'=>'自己混乱时，对手对自己的命中率得到0.8倍补正'),
		array('name_c'=>'强有力','name_j'=>'ちからもち','name_e'=>'Huge Power','intro'=>'自己物理攻击得到2倍补正'),
		array('name_c'=>'蓄电','name_j'=>'ちくでん','name_e'=>'Volt Absorb','intro'=>'雷系招式攻击自己时无效同时回复自己HP，回复量为自己最大HP的1/4'),
		array('name_c'=>'蓄水','name_j'=>'ちょすい','name_e'=>'Water Absorb','intro'=>'水系招式攻击自己时无效同时回复自己HP，回复量为自己最大HP的1/4'),
		array('name_c'=>'适应力','name_j'=>'てきおうりょく','name_e'=>'Adaptibility','intro'=>'使用与自己属性相同招式时，威力补正不再是1.5倍，而是2倍'),
		array('name_c'=>'技师','name_j'=>'テクニシャン','name_e'=>'Technician','intro'=>'自己使用基础威力60及以下招式时威力得到1.5倍补正'),
		array('name_c'=>'铁拳','name_j'=>'てつのこぶし','name_e'=>'Iron Fist','intro'=>'自己使用拳击招式时威力得到1.2倍补正（升天拳スカイアッパー/百万吨拳メガトンパンチ/连续拳れんぞくパンチ/火焰拳ほのおのパンチ/雷电拳かみなりパンチ/冷冻拳れいとうパンチ/影子拳シャドーパンチ/音速拳マッハパンチ/子弹拳パレットパンチ/彗星拳コメットパンチ/爆裂拳ばくれつパンチ）'),
		array('name_c'=>'电引擎','name_j'=>'でんきエンジン','name_e'=>'Motor Drive','intro'=>'雷系招式攻击自己时无效同时自己速度等级提升一阶'),
		array('name_c'=>'气象台','name_j'=>'てんきや','name_e'=>'Forecast','intro'=>'自己随天气变换属性和相貌。通常-普通系，晴天-火系，雨天-水系，冰雹-冰系。只对351号天气小子ポワルン有效'),
		array('name_c'=>'天然','name_j'=>'てんねん','name_e'=>'Unaware','intro'=>'在场时，对手提升或下降的能力等级不能发挥效果'),
		array('name_c'=>'天之恩惠','name_j'=>'てんのめぐみ','name_e'=>'Serene Grace','intro'=>'自己使用技能附加效果的几率得到2倍补正(道具附加的效果几率不会增加)'),
		array('name_c'=>'斗争心','name_j'=>'とうそうしん','name_e'=>'Revalry','intro'=>'面对同性对手，自己物攻得到1.2倍补正，面对异性对手，自己物攻得到0.8倍补正。对手无性别无效'),
		array('name_c'=>'毒针','name_j'=>'どくのトゲ','name_e'=>'Poison Point','intro'=>'每次被近身招式命中后，攻击者有30%几率中毒'),
		array('name_c'=>'变装','name_j'=>'トレース','name_e'=>'Trace','intro'=>'上场时自己复制对方一员的特性'),
		array('name_c'=>'迟钝','name_j'=>'どんかん','name_e'=>'Oblivious','intro'=>'自己不会被进入迷惑状态'),
		array('name_c'=>'恶梦','name_j'=>'ナイトメア','name_e'=>'Bad Dreams','intro'=>'对手睡眠时每回合受伤害，伤害量为其最大HP的1/8'),
		array('name_c'=>'偷懒','name_j'=>'なまけ','name_e'=>'Truant','intro'=>'自己每隔一回合休息，不会攻击'),
		array('name_c'=>'逃跑能手','name_j'=>'にげあし','name_e'=>'Run Away','intro'=>'遇到野生精灵时候自己的可逃跑率为100%，无视防逃跑技能和特性'),
		array('name_c'=>'粘稠','name_j'=>'ねんちゃく','name_e'=>'Stick Hold','intro'=>'自己的装备品不会被夺走<>排头时较易钓到野生精灵'),
		array('name_c'=>'不要防守','name_j'=>'ノーガード','name_e'=>'No Guard','intro'=>'自己和攻击自己的精灵招式必中<>排头时遇到野生精灵机率*2'),
		array('name_c'=>'无天气','name_j'=>'ノーてんき','name_e'=>'Cloud Nine','intro'=>'在场上时使天气影响消失'),
		array('name_c'=>'普通皮肤','name_j'=>'ノーマルスキン','name_e'=>'Normalize','intro'=>'自己所有招式的属性都变为普通系'),
		array('name_c'=>'坚岩','name_j'=>'ハードロック','name_e'=>'Solid Rock','intro'=>'自己的弱点被两倍或以上的属性相克攻击时，所受伤害*0.75'),
		array('name_c'=>'发光','name_j'=>'はっこう','name_e'=>'Illuminate','intro'=>'排在队伍首位则遇到野生精灵机率为2倍'),
		array('name_c'=>'早足','name_j'=>'はやあし','name_e'=>'Quick Feet','intro'=>'自己异常状态时速度得到1.5倍补正（毒，剧毒，麻痹，烧伤，睡眠，冰冻）（麻痹不减速度）'),
		array('name_c'=>'早起','name_j'=>'はやおき','name_e'=>'Early Bird','intro'=>'自己从睡眠状态恢复所需回合数减半'),
		array('name_c'=>'紧张','name_j'=>'はりきり','name_e'=>'Hustle','intro'=>'自己物理攻击得到1.5倍补正，但是命中率得到0.8倍补正<>排头时，不会遇到该区最低Lv野生精灵，50%机会遇到该区最高Lv野生精灵'),
		array('name_c'=>'放晴','name_j'=>'ひでり','name_e'=>'Drought','intro'=>'上场时把天气变为晴天(效果持续直至被更换天气)'),
		array('name_c'=>'避雷针','name_j'=>'ひらいしん','name_e'=>'Lightningrod','intro'=>'双打时的电系技能都会引到自己身上来'),
		array('name_c'=>'过滤器','name_j'=>'フィルター','name_e'=>'Filter','intro'=>'自己的弱点被两倍或以上的属性相克攻击时，所受伤害*0.75'),
		array('name_c'=>'不用武器','name_j'=>'ぶきよう','name_e'=>'Klutz','intro'=>'自己持有的一切道具不会发挥效果'),
		array('name_c'=>'复眼','name_j'=>'ふくがん','name_e'=>'Compoundeyes','intro'=>'自己命中率得到1.3倍补正（对一击必杀技无效）<>排头时，增加遇到带有道具的野生精灵机会率'),
		array('name_c'=>'不屈之心','name_j'=>'ふくつのこころ','name_e'=>'Steadfast','intro'=>'自己害怕时，自己速度提升一个等级'),
		array('name_c'=>'神奇鳞片','name_j'=>'ふしぎなうろこ','name_e'=>'Marvel Scale','intro'=>'异常状态时自己物理防御得到1.5倍补正（毒，剧毒，麻痹，烧伤，睡眠，冰冻）'),
		array('name_c'=>'神奇护符','name_j'=>'ふしぎなまもり','name_e'=>'Wonder Guard','intro'=>'除了被两倍或以上的属性攻击及间接攻击外，其他攻击一律无效。此特性不能被交换'),
		array('name_c'=>'失眠','name_j'=>'ふみん','name_e'=>'Insomnia','intro'=>'自己不会进入睡眠状态'),
		array('name_c'=>'浮游','name_j'=>'ふゆう','name_e'=>'Levitate','intro'=>'自己不会被地面系攻击招式伤害，也不受地菱、毒菱和沙地狱的影响'),
		array('name_c'=>'正电','name_j'=>'プラス','name_e'=>'Plus','intro'=>'队伍中存在负电特性的精灵时自己的特殊攻击得到1.5倍补正'),
		array('name_c'=>'花朵礼物','name_j'=>'フラワーギフト','name_e'=>'Flower Gift','intro'=>'天晴时自己在场，自己形态改变，我方场上的精灵物攻及特防得到1.5倍补正'),
		array('name_c'=>'压力','name_j'=>'プレッシャー','name_e'=>'Pressure','intro'=>'对方对自己使用任何技能都需要消耗2点的PP<>排头时，不会遇到该区最低Lv野生精灵，50%机会遇到该区最高Lv野生精灵'),
		array('name_c'=>'毒液','name_j'=>'ヘドロえき','name_e'=>'Liquid Ooze','intro'=>'对自己使用HP吸取技能(吸血きゅうけつ/吸取すいとる/百万威力吸取メガドレイン/亿万威力吸取ギガドレイン/吸取拳ドレインパンチ/寄生种子やどりぎのタネ)反而会损失HP(对食梦ゆめくい不发动此特性)'),
		array('name_c'=>'变色','name_j'=>'へんしょく','name_e'=>'Color Change','intro'=>'自己即时转变成刚受到伤害的攻击招式的属性'),
		array('name_c'=>'毒疗','name_j'=>'ポイズンヒール','name_e'=>'Poison Heal','intro'=>'战斗时中毒，自己不会因中毒减去HP，反而每回合回复1/8HP'),
		array('name_c'=>'隔音','name_j'=>'ぼうおん','name_e'=>'Soundproof','intro'=>'自己不会受到声音系技能(呼噜いびき/治疗铃铛いやしのすず/噪音いやなおと/唱歌うたう/金属音きんぞくおん/草笛くさぶえ/吵闹さわぐ/超音波ちょうおんぱ/鸣叫なきごえ/高音ハイパーボイス/吼叫ほえる/灭亡歌ほろびのうた/虫鸣むしのさざめき/喋喋不休おしゃべり)的影响'),
		array('name_c'=>'孢子','name_j'=>'ほうし','name_e'=>'Effect Spore','intro'=>'每次被近身招式命中后，攻击者有30%几率中毒，麻痹，或者睡眠'),
		array('name_c'=>'火焰身躯','name_j'=>'ほのおのからだ','name_e'=>'Flame Body','intro'=>'每次被近身招式命中后，攻击者有30%几率被烧伤<>在队时，孵蛋所需步数减少'),
		array('name_c'=>'负电','name_j'=>'マイナス','name_e'=>'Minus','intro'=>'队伍中存在正电特性的精灵时自己的特殊攻击得到1.5倍补正'),
		array('name_c'=>'我行我素','name_j'=>'マイペース','name_e'=>'Own Tempo','intro'=>'自己不会陷入混乱状态'),
		array('name_c'=>'火焰盔甲','name_j'=>'マグマのよろい','name_e'=>'Magma Armor','intro'=>'自己不会陷入冰冻状态<>在队时，孵蛋所需步数减少'),
		array('name_c'=>'魔法守护','name_j'=>'マジックガード','name_e'=>'Magic Guard','intro'=>'除了受攻击招式的伤害外，其他间接形式（天气、道具、特性、伤害反弹、束缚、除混乱外的异常状态）对自己一律不造成伤害'),
		array('name_c'=>'多重属性','name_j'=>'マルチタイプ','name_e'=>'Multitype','intro'=>'自己随装备不同属性的プレート而改变属性及形态'),
		array('name_c'=>'水之掩护','name_j'=>'みずのベール','name_e'=>'Water Veil','intro'=>'自己不会进入烧伤状态'),
		array('name_c'=>'蜂蜜','name_j'=>'みつあつめ','name_e'=>'Honey Gather','intro'=>'不带道具时，战斗后有30%机率自己生成一个あまいミツ装备在身上'),
		array('name_c'=>'虫族警报','name_j'=>'むしのしらせ','name_e'=>'Swarm','intro'=>'自己HP1/3以下时虫系招数威力得到1.5倍补正'),
		array('name_c'=>'媚惑身躯','name_j'=>'メロメロボディ','name_e'=>'Cute Charm','intro'=>'异性对自己近身攻击有30%几率被迷惑<>排头时，50%机率遇到异性野生精灵'),
		array('name_c'=>'免疫','name_j'=>'めんえき','name_e'=>'Immunity','intro'=>'自己不会陷入毒，剧毒状态'),
		array('name_c'=>'烈火','name_j'=>'もうか','name_e'=>'Blaze','intro'=>'自己HP1/3以下时火系招数威力得到1.5倍补正'),
		array('name_c'=>'拾物','name_j'=>'ものひろい','name_e'=>'Pickup','intro'=>'不带道具时，战斗后自己有10%几率会生成一个物品装备在身上(具体概率见下表)'),
		array('name_c'=>'引火','name_j'=>'もらいび','name_e'=>'Flash Fire','intro'=>'火系招式攻击自己时无效同时会使自己的火系招数威力得到1.5倍补正(效果维持直至替换)'),
		array('name_c'=>'活跃','name_j'=>'やるき','name_e'=>'Vital Spirit','intro'=>'自己不会进入睡眠状态<>排头时，不会遇到该区最低Lv野生精灵，50%机会遇到该区最高Lv野生精灵'),
		array('name_c'=>'引爆','name_j'=>'ゆうばく','name_e'=>'Aftermatch','intro'=>'被近身招式击倒时，攻击者受伤害，伤害量为其最大HP的1/4'),
		array('name_c'=>'雪遁','name_j'=>'ゆきがくれ','name_e'=>'Snow Cloak','intro'=>'风雪时，不受冰雹伤害且对手对自己的命中率得到0.8倍补正'),
		array('name_c'=>'暴雪','name_j'=>'ゆきふらし','name_e'=>'Snow Warning','intro'=>'出场时，天气变成风雪(效果持续直至被更换天气)'),
		array('name_c'=>'叶绿素','name_j'=>'ようりょくそ','name_e'=>'Chlorophyll','intro'=>'晴天时自己速度得到2倍补正'),
		array('name_c'=>'瑜珈力','name_j'=>'ヨガパワー','name_e'=>'Pure Power','intro'=>'自己物理攻击得到2倍补正'),
		array('name_c'=>'预知梦','name_j'=>'よちむ','name_e'=>'Forewarn','intro'=>'出场时，预知对手一员的招式中威力最高的招式'),
		array('name_c'=>'吸水','name_j'=>'よびみず','name_e'=>'Storm Drain','intro'=>'双打时，场上的精灵使用单体水系招式，对象一定是自己'),
		array('name_c'=>'叶子守护','name_j'=>'リーフガード','name_e'=>'Leaf Guard','intro'=>'天晴时，自己不会因别人而得到异常状态（天晴前的异常状态不会回复）（毒，剧毒，麻痹，烧伤，睡眠，冰冻）'),
		array('name_c'=>'鳞粉','name_j'=>'りんぷん','name_e'=>'Shield Dust','intro'=>'自己不会受到攻击技能附加效果的影响。对于缠绕效果和失去道具效果无效'),
	);
}

?>