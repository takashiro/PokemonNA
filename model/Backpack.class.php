<?php

class Backpack{
	private $ownerid = 0;

	function __construct($ownerid){
		$this->ownerid = $ownerid;
	}

	function updateStorage($wareid, $nums){
		global $_CONFIG, $tpre, $db;
		$query = $db->query("SELECT id,nums FROM {$tpre}backpack WHERE ownerid={$this->ownerid} AND wareid=$wareid");
		$w = $db->fetch_array($query);
		if(!$w['nums']){
			$db->query("INSERT INTO {$tpre}backpack (ownerid, wareid, nums) VALUES ('{$this->ownerid}', '$wareid', '$nums')");
		}elseif($w['nums'] + $nums == 0){
			$db->query("DELETE FROM {$tpre}backpack WHERE id=$w[id]");
		}else{
			$db->query("UPDATE {$tpre}backpack SET nums=nums+$nums WHERE id=$w[id]");
		}
	}	
}

?>