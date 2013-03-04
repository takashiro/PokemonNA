<?php

class DBObject{
	protected $table_name = '';

	protected $attr = array();
	protected $oattr = array();

	function __construct($table_name){
		$this->table_name = $table_name;
	}

	function __destruct(){
		global $db;

		$update = array();
		if($this->oattr){
			foreach($this->oattr as $key => $value){
				if($value != $this->attr[$key]){
					$update[$key] = $this->attr[$key];
				}
			}
		}

		if($update){
			$db->select_table($this->table_name);
			$db->UPDATE($update, 'id='.$this->attr('id'));
		}
	}

	function fetchAttributesFromDB($item, $condition){
		global $db;
		if(is_array($item)){
			$item = implode(',', $item);
		}
		if(is_array($condition)){
			$c = array();
			foreach($condition as $attr => $value){
				$c[] = '`'.$attr.'`=\''.$value.'\'';
			}
			$condition = implode(' AND ', $c);
		}

		$db->select_table($this->table_name);
		$this->attr = $this->oattr = $db->FETCH($item, $condition);
	}

	public function toArray(){
		return $this->attr;
	}

	public function attr($attr, $value = null){
		if($value === null){
			return $this->attr[$attr];
		}else if(array_key_exists($attr, $this->attr)){
			$this->attr[$attr] = $value;
		}
	}

	public function deleteFromDB(){
		global $db;
		$db->select_table($this->table_name);
		$db->DELETE('id='.$this->attr['id']);
		$this->attr = $this->oattr = array();
	}
}

?>