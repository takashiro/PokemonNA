<?php

define('S_ROOT', dirname(dirname(__FILE__)).'/');//网站根目录常量
error_reporting(E_ERROR | E_WARNING | E_PARSE);//Debug

if(PHP_VERSION < '5.3'){
	set_magic_quotes_runtime(0);//魔法括号
}

function __autoload($classname){
	require_once S_ROOT.'./model/'.$classname.'.class.php';
}

function writeconfig($config, $value){
	file_put_contents(S_ROOT.'./data/'.$config.'.inc.php', '<?php return '.var_export($value, true).';?>');
}

require_once S_ROOT.'./core/global.func.php';

$_G = array();
@$_G['setting'] = array_merge(@include S_ROOT.'./data/config.inc.php', @include S_ROOT.'./data/stconfig.inc.php');
@$_G['setting']['db'] = include S_ROOT.'./data/dbconfig.inc.php';
$_G['style'] = 'admin';

if(file_exists(S_ROOT.'./data/install.lock')){
	rheader('Content-Type:text/html; charset=utf-8');
	exit('已经安装PokemonNA，要重新安装请删除./data/install.lock文件。');
}

if($_POST){
	$config = array(
		'timezone' => intval($_POST['config']['timezone']),
		'timefix' => 0,
		'cookiepre' => randomstr(3).'_',
		'charset' => 'utf-8',
	);
	writeconfig('config', $config);
	writeconfig('config_bak_'.randomstr(3), $config);
	
	$stconfig = array(
		'salt' => randomstr(32),
	);
	writeconfig('stconfig', $stconfig);
	writeconfig('stconfig_bak_'.randomstr(3), $stconfig);
	
	$dbconfig = array(
		'type' => $_POST['db']['type'],
		'charset' => 'utf8',
		'server' => $_POST['db']['server'],
		'user' => $_POST['db']['user'],
		'tpre' => $_POST['db']['tpre'],
		'pw' => $_POST['db']['pw'],
		'name' => $_POST['db']['name'],
		'pconnect' => intval($_POST['db']['pconnect']),
	);
	writeconfig('dbconfig', $dbconfig);
	writeconfig('dbconfig_bak_'.randomstr(3), $dbconfig);
	
	$db = new Mysql();
	$db->connect($dbconfig['server'], $dbconfig['user'], $dbconfig['pw'], '', $dbconfig['pconnect']);
	$db->set_tablepre($dbconfig['tpre']);

	$databases = $db->fetch_all('SHOW DATABASES');
	$database_exists = false;
	foreach($databases as $d){
		if($d['Database'] == $dbconfig['name']){
			$database_exists = true;
			break;
		}
	}
	if(!$database_exists){
		showmsg('您指定的数据库'.$dbconfig['name'].'不存在。');
	}
	$db->select_db($dbconfig['name']);

	$line = file('pokemon.sql');
	$line_max = count($line);
	$in_sql = false;
	$sql = '';
	for($i = 0; $i < $line_max; $i++){
		if((!$in_sql && substr_compare($line[$i], '--', 0, 2) == 0)){
			continue;
		}

		$line[$i] = trim($line[$i]);
		if(empty($line[$i])){
			continue;
		}

		$in_sql = true;
		$sql.= $line[$i];
		
		if(substr($line[$i], -1) == ';'){
			$in_sql = false;
			$sql = preg_replace('/`ark\_(.*?)`/is', "`{$dbconfig[tpre]}\\1`", $sql);
			$db->query($sql);
			$sql = '';
		}
	}

	$admin = new Admin();
	$admin->v['username'] = $_POST['admin']['username'];
	$admin->v['password'] = $_POST['admin']['password'];
	$admin->v['permission'] = 0x1;
	$admin->register();

	//安装标记
	touch(S_ROOT.'./data/install.lock');
	
	showmsg('安装成功！请手动删除网站根目录下install目录，防止重复安装以及其他可能出现的问题。', '../index.php');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Pokemon NA 安装程序</title>
<link href="../image/admin/common.css" rel="stylesheet" type="text/css" />
</head>

<body<?php if(defined('CURSCRIPT')) echo ' class="'.CURSCRIPT.'"';?>">

<div class="container">

	<div class="nav">
		<div class="left"></div>
		<ul id="navlist" class="middle" style="width:960px;color:white;">
        	Installing Pocket Monster Nostagia & Anticipation...
		</ul>
		<div class="right"></div>
		<script type="text/javascript">
		var index = true;
		$('#navlist a').each(function(){
			if(this.href == location.href){
				$(this).addClass('current');
				index = false;
			}
		});
		</script>
	</div>
	
	<div class="content">
		<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
		<h1>数据库配置</h1>
		<table>
			<tr><th><label>数据库类型：</label></th><td><input type="text" id="db[type]" name="db[type]" value="<?php echo $_G['setting']['db']['type']?>" /></td></tr>
			<tr><th><label>数据库字符集：</label></th><td><input type="text" id="db[charset]" name="db[charset]" value="<?php echo $_G['setting']['db']['charset']?>" /></td></tr>
			<tr><th><label>数据库服务器地址：</label></th><td><input type="text" id="db[server]" name="db[server]" value="<?php echo $_G['setting']['db']['server']?>" /></td></tr>
			<tr><th><label>数据库账号：</label></th><td><input type="text" id="db[user]" name="db[user]" value="<?php echo $_G['setting']['db']['user']?>" /></td></tr>
			<tr><th><label>数据库密码：</label></th><td><input type="text" id="db[pw]" name="db[pw]" value="<?php echo $_G['setting']['db']['pw']?>" /></td></tr>
			<tr><th><label>数据库表前缀：</label></th><td><input type="text" id="db[tpre]" name="db[tpre]" value="<?php echo $_G['setting']['db']['tpre']?>" /></td></tr>
			<tr><th><label>数据库名：</label></th><td><input type="text" id="db[name]" name="db[name]" value="<?php echo $_G['setting']['db']['name']?>" /></td></tr>
			<tr><th><label>是否持续链接：</label></th><td><input type="radio" id="db[pconnect]" name="db[pconnect]" value="0" checked="checked" />否 <input type="radio" id="db[pconnect]" name="db[pconnect]" value="0" />是</td></tr>
		</table>
		
		<h1>系统设置</h1>
		<table>
			<tr><th><label>时区设置：</label></th><td><input type="text" id="config[timezone]" name="config[timezone]" value="<?php echo $_G['setting']['timezone']?>" /></td></tr>
			<tr><th><label>初始管理员账号：</label></th><td><input type="text" id="admin[username]" name="admin[username]" /></td></tr>
			<tr><th><label>初始管理员密码：</label></th><td><input type="text" id="admin[password]" name="admin[password]" /></td></tr>
		</table>
		
		<button type="submit">开始安装</button>
		
		</form>
	</div>

	<div class="footer">
		<div class="mark"></div>
		<div class="split"></div>
		<div class="copyright">
			<p><a href="http://pokemon.3-a.net" target="_blank">Pokemon NA</a>, Powered By <a href="http://inu.3-a.net/?1" target="_blank">Satoshi Takashiro</a></p>
		</div>
	</div>

</div>

</body>
</html>