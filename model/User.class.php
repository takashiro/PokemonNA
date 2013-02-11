<?php

if(!defined('IN_PLUSQUIZ')) exit('Access Denied');

class User{
	protected $attr = array(
		'id' => 0,
	);	//用户信息
	//protected $o = array();	//原始用户信息，用于判断信息是否被修改和更新数据库

	public function login($email = '', $password = ''){
		global $db, $tpre;

		if(!$email){
			if($_COOKIE['rcuserinfo']){
				$this->attr = $this->decodeCookie($_COOKIE['rcuserinfo']);
				return $this->isLoggedIn();
			}
		}else{
			$pwmd5 = rmd5($password);
			$this->attr = $db->fetch_first("SELECT * FROM {$tpre}trainer WHERE email='$email' AND pwmd5='$pwmd5'");

			if($this->attr){
				rsetcookie('rcuserinfo', $this->encodeCookie());
				return true;
			}else{
				return false;
			}
		}

		return false;
	}
	
	public function logout(){
		rsetcookie('rcuserinfo');
	}
	
	public function isLoggedIn(){
		return $this->attr['id'] != 0;
	}

	public function toArray(){
		return $this->attr;
	}

	public function attr($attr, $value = null){
		if($value == null){
			return $this->attr[$attr];
		}else if(array_key_exists($attr, $this->attr)){
			$this->attr[$attr] = $value;
		}
	}

	static public function ip(){
		$onlineip = '0.0.0.0';
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		return $onlineip;
	}

	public static function Register($username, $email, $password){
		global $db, $tpre;

		$len = strlen($username);
		if($len < 4 || $len > 15){
			return -1;
		}

		if(!User::IsEmail($email)){
			return -2;
		}

		if(empty($password)){
			return -3;
		}

		$dup = $db->result_first("SELECT id FROM {$tpre}trainer WHERE username='$username'");
		if($dup > 0){
			return -4;
		}

		$dup = $db->result_first("SELECT id FROM {$tpre}trainer WHERE email='$email'");
		if($dup > 0){
			return -5;
		}

		$user = array(
			'username' => $username,
			'email' => $email,
			'pwmd5' => rmd5($password),
		);

		$db->select_table('trainer');
		$db->INSERT($user);
		return $db->insert_id();
	}
	
	protected function decodeCookie($auth){
		return unserialize($this->authcode($auth, 'DECODE', $GLOBALS['_CONFIG']['halt']));
	}
	
	protected function encodeCookie(){
		return $this->authcode(serialize($this->attr), 'ENCODE', $GLOBALS['_CONFIG']['halt']);
	}
	
	protected function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
	
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
	
		$result = '';
		$box = range(0, 255);
	
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
	
		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
	
		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
	
		if($operation == 'DECODE') {
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}

	static function IsEmail($email) {
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}
}
?>