<?php

class Admin extends DBObject{	
	static private $perms = array('all' => 0x1, 'addproject' => 0x2, 'editproject' => 0x4, 'deleteproject' => 0x8, 'addprocess' => 0x10, 'deleteprocess' => 0x20);

	public function __construct($uid = 0){
		parent::__construct('administrator');
		if($uid){
			parent::fetchAttributesFromDB('uid='.$uid);
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}

	public function login($name, $pw = '', $method = ''){
		global $db, $tpre;
		if(!$pw){//Login by Cookie
			$v = $this->decodeCookie($name);
			
			if(!$v || !$v['uid'] || !$v['pwmd5']){
				$this->logout();
				return false;
			}
			
			parent::fetchAttributesFromDB("$v[uid] AND pwmd5='$v[pwmd5]'");
		}else{
			if(!$method){
				if(strpos('@', $name) !== false){
					$method = 'email';
				}elseif(!in_array($method, array('uid', 'username', 'email'))){
					$method = 'uid';
				}
			}else if(!in_array($method, array('uid','email','username'))){
				$method = 'username';
			}
			$pwmd5 = rmd5($pw);
			$this->v = $db->fetch_first("SELECT * FROM {$tpre}administrator WHERE `$method`='$name' AND pwmd5='$pwmd5'");
		}

		if(!$this->v){
			$this->logout();
			return false;
		}else{
			$this->logged = true;
			rsetcookie('rcadmininfo', $this->encodeCookie());
			$this->o = $this->v;
			
			if($pw){
				$this->v['lastlogin'] = $this->v['logintime'];
				$this->v['logintime'] = $GLOBALS['_G']['timestamp'];
				$this->v['ip'] = $this->ip();
			}
			
			return true;
		}
	}
	
	public function logout(){
		rsetcookie('rcadmininfo');
	}
	
	public function isSuperAdmin(){
		return ($this->v['permission'] & 0x1) == 0x1;
	}
	
	public function register(){
		global $db;
		
		if(!$this->v['username']){
			return -1;
		}
		
		$this->v['pwmd5'] = rmd5($this->v['password']);
		unset($this->v['password']);
		
		$db->select_table('administrator');
		$db->INSERT($this->v);
		
		return $db->insert_id();
	}
	
	public function changePassword($old, $new, $new2){
		if(!$this->v['uid']){
			return -3;
		}elseif(rmd5($old) != $this->v['pwmd5']){
			return -1;
		}elseif($new != $new2){
			return -2;
		}
		
		$this->v['pwmd5'] = rmd5($new);
		rsetcookie('rcadmininfo', $this->encodeCookie());

		return true;
	}
	
	public function hasPermission($permission){
		if(isset(self::$perms[$permission])){
			return $this->isSuperAdmin() || ($this->v['permission'] & self::$perms[$permission]) == self::$perms[$permission];
		}else{
			return false;
		}
	}
	
	public function setPermission($permission, $value = true){
		if(!is_array($permission)){
			if(isset(self::$perms[$permission])){
				if($value){
					$this->v['permission'] |= self::$perms[$permission];
				}else{
					$this->v['permission'] &= ~$value;
				}
				return true;
			}else{
				return false;
			}
		}else{
			$this->v['permission'] = $this->isSuperAdmin() ? 0x1 : 0x0;
			foreach(self::$perms as $p => $c){
				if($permission[$p]){
					$this->v['permission'] |= $c;
				}
			}
			return true;
		}
	}
	
	static public function getAdminList($offset, $limit){
		global $db;
		$db->select_table('administrator');
		return $db->MFETCH('*', "1 LIMIT $offset,$limit");
	}
	
	static public function getAdminNum(){
		global $db, $tpre;
		return $db->result_first("SELECT COUNT(*) FROM {$tpre}administrator WHERE 1");
	}
	
	static public function deleteAdmin($uid){
		global $db;
		
		if(!$uid = intval($uid)){
			return -1;
		}
		
		$db->select_table('administrator');
		$db->DELETE('uid='.$uid.' AND MOD(permission,2)!=1');
		return $db->affected_rows();
	}
}
?>