<?php

class WildPokemon extends Pokemon{
	public function __construct($id){
		global $db;
		DBObject::__construct('adventure');
		DBObject::fetchAttributesFromDB('*', 'id='.$id);
	
		$this->attr('tmpstatus') && $this->attr('tmpstatus', unserialize($this->attr('tmpstatus')));
	}

	public function __destruct(){
		$this->attr('tmpstatus') && $this->attr('tmpstatus', serialize($this->attr('tmpstatus')));
		DBObject::__destruct();
	}
}

?>