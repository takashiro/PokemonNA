<?php

class PokemonFight{
	private $trainer = array();
	private $mode = '1v1';
	private $weather = 0;

	public function setTrainers($trainers){
		$this->trainer = $trainers;
	}

	static public function AtbEffect($skill_atb, &$obv){
		if(is_object($obv)){
			return self::$AtbRestraint[$skill_atb][$obv->attr('atb1')] * self::$AtbRestraint[$skill_atb][$obv->attr('atb2')];
		}elseif(is_array($obv)){
			return self::$AtbRestraint[$skill_atb][$obv['atb1']] * self::$AtbRestraint[$skill_atb][$obv['atb2']];
		}
	}

	//属性克制表
	static public $AtbRestraint = array(
		array(),
		array(1,0.5,2,1,0.5,0.5,1,1,1,1,1,1,0.5,1,2,2,0.5,1),
		array(1,0.5,0.5,2,2,0.5,1,1,1,1,1,1,1,1,1,1,0.5,1),
		array(1,1,1,0.5,1,1,1,1,1,1,1,0.5,1,1,2,1,0.5,1),
		array(1,2,0.5,0.5,0.5,2,1,1,1,1,1,2,2,2,0.5,1,1,1),
		array(1,2,1,1,1,0.5,1,1,1,1,2,1,1,1,1,2,2,1),
		array(1,1,1,1,1,1,0.5,1,2,1,0.5,1,2,1,1,1,1,2),
		array(1,0.5,0.5,0.5,0.5,2,1,2,1,1,1,1,1,1,1,1,1,1),
		array(1,1,1,1,1,1,0,1,0.5,1,2,1,2,1,1,1,1,0.5),
		array(1,1,1,1,1,1,1,1,1,1,2,1,1,1,1,1,1,0),
		array(1,1,1,1,1,1,2,1,0.5,1,1,2,0.5,1,1,0.5,1,1),
		array(1,1,1,2,0.5,2,1,1,1,1,0.5,1,0.5,1,0,2,1,1),
		array(1,2,1,1,0.5,1,1,1,1,1,0.5,2,1,1,0.5,2,1,1),
		array(1,1,1,1,0.5,1,2,1,1,1,0.5,1,0.5,0.5,2,1,1,1),
		array(1,1,2,0,2,2,1,1,1,1,1,1,1,0.5,1,0.5,1,1),
		array(1,0.5,2,1,2,1,1,1,1,0.5,2,0.5,1,0.5,2,1,2,1),
		array(1,2,1,1,0.5,0.5,0.5,0.5,0.5,0.5,2,0.5,0.5,0,2,0.5,0.5,0.5),
		array(1,1,1,1,1,1,1,1,2,0,0,1,0.5,0.5,1,1,1,2),
	);

	//技能附加效果能够修改的字段
	static public $SkillExtAllowedKey = array('shape','status','hp','mp','tmpstatus','tmptrait','tmpatk','tmpdef','tmpstk','tmpsdf','tmpspd','tmpspr','equip','skill');
}

?>