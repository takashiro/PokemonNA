<?php

define('IN_PLUSQUIZ', true);//用于防止模块文件被直接运行
define('S_ROOT', dirname(dirname(__FILE__)).'/');//网站根目录常量
define('S_VERSION', 'Lugia');
error_reporting(E_ERROR | E_WARNING | E_PARSE);//Debug
set_magic_quotes_runtime(0);//魔法括号
set_time_limit(0);

//类自动加载
function __autoload($classname){
	if(substr($classname, 0, 8) != 'PHPExcel'){
		require_once './model/'.$classname.'.class.php';
	}
}

//公用函数
require_once './core/global.func.php';

//初始化一个自定义的全局变量，用于存储用户信息，缓存信息等等
$_G = array();

//程序配置及关键信息
$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$_G['config'] = (include './data/config.inc.php') + (include './data/stconfig.inc.php');
$_G['config']['db'] = include './data/dbconfig.inc.php';
$_G['root_url'] = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api|archiver|wap)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/');//网站根路径，可用于邮件发送中的验证链接等等
$_G['style'] = &$_G['config']['style'];
empty($_G['style']) && $_G['style'] = 'default';

//数据库配置
$_G['db'] = new Mysql();
$db = &$_G['db'];
$tpre = $_G['config']['db']['tpre'];
$db->set_tablepre($tpre);
$db->connect($_G['config']['db']['server'], $_G['config']['db']['user'], $_G['config']['db']['pw'], $_G['config']['db']['name'], $_G['config']['db']['pconnect']);

//时间戳
if(PHP_VERSION > '5.1') {
	@date_default_timezone_set('Etc/GMT +'.intval($_G['config']['timezone']));
}

$_G['timestamp'] = time() + intval($_G['config']['timefix']);
$mtime = explode(' ', microtime());
$_G['starttime'] = $mtime[1] + $mtime[0];

if($_GET['confirm']){
	$_SERVER['HTTP_REFERER'] = $_COOKIE['http_referer'];
	rsetcookie('http_referer');
	if(!empty($_GET['confirm_key'])){
		$_POST = unserialize($_COOKIE['postdata_'.$_GET['confirm_key']]);
		rsetcookie('postdata_'.$_GET['confirm_key']);
	}
}
foreach(array('_POST', '_GET', '_COOKIE') as $request){
	${$request} = rhtmlspecialchars(raddslashes(${$request}));
}


//常用变量处理
$navtitle = '';
$page = max(1, intval($_GET['page']));

$_G['user'] = new User();
$_G['user']->login();
$_G['staticdir'] = './static/image';

//Alias
$_USER = $_G['user']->toArray();
$_CONFIG = &$_G['config'];
define('TIMESTAMP', $_G['timestamp']);

$staticdir = &$_G['staticdir'];
?>
