<?php

class Mysql {

	var $version = '';		//Mysql版本
	var $querynum = 0;		//查询次数
	var $link = NULL;		//资源ID
	var $tpre = '';			//表前缀
	var $tablename = '';	//默认操作表

	var $db = array();

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $pconnect = 0, $halt = TRUE) {
		$func = empty($pconnect) ? 'mysql_connect' : 'mysql_pconnect';
		if(!$this->link = @$func($dbhost, $dbuser, $dbpw, 1)) {
			$halt && $this->halt('Can not connect to MySQL server');
		} else {
			if($this->version() > '4.1') {
				$charset = $GLOBALS['_G']['config']['charset'];
				$dbcharset = str_replace('-', '', $charset);
				$serverset = $dbcharset ? 'character_set_connection='.$dbcharset.', character_set_results='.$dbcharset.', character_set_client=binary' : '';
				$serverset .= $this->version() > '5.0.1' ? ((empty($serverset) ? '' : ',').'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $this->link);
			}
			$dbname && @mysql_select_db($dbname, $this->link);
		}

		$this->db = array(
			'host' => $dbhost,
			'user' => $dbuser,
			'pw' => $dbpw,
			'name' => $dbname,
			'pconnect' => $pconnect,
			'charset' => $dbcharset
		);
	}

	function select_db($dbname) {
		return mysql_select_db($dbname, $this->link);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}
	
	function fetch_all($sql, $filter = ''){
		$filter = ($filter && function_exists($filter)) ? $filter : '';
		$query = $this->query($sql);
		$result = array();
		if($filter){
			while($t = $this->fetch_array($query)){
				$result[] = $filter($t);
			}
		}else{
			while($t = $this->fetch_array($query)){
				$result[] = $t;
			}
		}
		return $result;
	}

	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	function query($sql, $type = '') {	
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($type, 0, 5) != 'RETRY') {
				$this->close();

				$this->connect($this->db['host'], $this->db['user'], $this->db['pw'], $this->db['name'], $this->db['pconnect'], true, $this->db['charset']);
				$this->query($sql, 'RETRY'.$type);
			} elseif($type != 'SILENT' && substr($type, 5) != 'SILENT') {
				echo 'MySQL Query Error : '.$sql.'<br />'.$this->error();
				exit();
			}
		}

		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		if(empty($this->version)) {
			$this->version = mysql_get_server_info($this->link);
		}
		return $this->version;
	}

	function close() {
		return mysql_close($this->link);
	}
	
	function halt($message) {
		//fwritelog('mysql', $message);
		if(!defined('IN_ADMINCP')){
			showmsg('数据查询发生错误，已自动报告给管理员。请等待错误处理。');
		}else{
			echo $message;
		}
	}
	
	function select_table($tablename){
		$this->tablename = $tablename;
	}
	
	function set_tablepre($tpre){
		$this->tpre = $tpre;
	}

	function SELECT($fields, $condition = '1'){
		is_array($fields) && $fields = '`'.implode('`,`', $fields).'`';
		return $this->query("SELECT $fields FROM `{$this->tpre}{$this->tablename}` WHERE $condition");
	}
	
	function FETCH($fields, $condition = '1'){
		is_array($fields) && $fields = '`'.implode('`,`', $fields).'`';
		return $this->fetch_first("SELECT $fields FROM `{$this->tpre}{$this->tablename}` WHERE $condition");
	}

	function MFETCH($fields, $condition = '1'){
		is_array($fields) && $fields = '`'.implode('`,`', $fields).'`';
		return $this->fetch_all("SELECT $fields FROM `{$this->tpre}{$this->tablename}` WHERE $condition");
	}
	
	function SRESULT($field, $condition = '1'){
		return $this->result_first("SELECT $field FROM `{$this->tpre}{$this->tablename}` WHERE $condition");
	}
	
	function UPDATE($node, $condition = '1', $priority = ''){
		$sql = array();
		foreach($node as $k => $v){
			$sql[] = "`$k`='$v'";
		}
		$sql = implode(',', $sql);
		$priority = '' ? '' : 'LOW_PRIORITY';
		return $this->query("UPDATE $priority `{$this->tpre}{$this->tablename}` SET $sql WHERE $condition");
	}
	
	function INSERT($node, $replace = false, $extra = ''){
		$action = $replace ? 'REPLACE' : 'INSERT';
		$fields = implode('`,`',array_keys($node));
		$values = implode('\',\'', $node);
		return $this->query("$action $extra INTO `{$this->tpre}{$this->tablename}` (`$fields`) VALUES ('$values')");
	}
	
	function INSERTS($nodes, $replace = false, $extra = ''){
		$action = $replace ? 'REPLACE' : 'INSERT';

		$nodes = array_values($nodes);
		
		$fields = array_keys($nodes[0]);

		$values = array();
		foreach($nodes as $n){
			$v = array();
			foreach($fields as $f){
				$v[] = $n[$f];
			}
			$values[] = '\''.implode('\',\'', $v).'\'';
		}

		$values = '('.implode('),(', $values).')';
		$fields = implode('`,`', $fields);
		return $this->query("$action $extra INTO `{$this->tpre}{$this->tablename}` (`$fields`) VALUES $values");
	}
		
	function DELETE($condition){
		if($condition == '1'){
			return $this->query("TRUNCATE `{$this->tpre}{$this->tablename}`");
		}else{
			return $this->query("DELETE FROM `{$this->tpre}{$this->tablename}` WHERE $condition");
		}
	}
}

?>