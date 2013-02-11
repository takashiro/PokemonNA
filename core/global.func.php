<?php

if(!defined('IN_PLUSQUIZ')) exit('Access Denied');

//显示一个消息，并跳转到$url_forward
function showmsg($message, $url_forward = ''){
	extract($GLOBALS, EXTR_SKIP);

	switch($url_forward){
		case 'back':
			$url_forward = 'javascript:history.back()';
		break;
		case 'refresh':
			$url_forward = $_SERVER['HTTP_REFERER'];
		break;
		case 'login':
			$url_forward = 'memcp.php?action=login';
		break;
		case 'confirm':
			if($_POST){
				$confirm_key = randomstr(8);
				rsetcookie('postdata_'.$confirm_key, serialize($_POST));
			}else{
				$confirm_key = '';
			}
			rsetcookie('http_referer', $_SERVER['HTTP_REFERER']);
		break;
	}

	if(!$_GET['ajax']){
		include view('show_message');
	}else{
		echo json_encode(array($message, $url_forward));
	}
	exit();
}

//设置一个cookie, $extexpiry是有效时间长度
function rsetcookie($varname, $value = '', $extexpiry = 0){
	global $_G;
	if(!$value){
		setcookie($varname, '', $_G['timestamp'] - 3600);
	}else{
		$extexpiry == -1 && $extexpiry = $_G['timestamp'] + 365 * 24 * 3600;
		setcookie($varname, $value, $extexpiry);
	}
}

function multi($totalnum, $limit, $curpage, $baseurl){
	$pagelimit=9;
	
	$pagenum = ceil($totalnum / $limit);
	$startpage = max($curpage - floor($pagelimit / 2),1);
	$endpage = min($curpage + floor($pagelimit / 2),$pagenum);
	$pre = strstr($baseurl, '?') ? '&' : '?';
	
	if($pagenum <= 1){
		return '';
	}
	
	$html = '<div id="multipage">';
	
	if($curpage>1) {
		$html.= '<a href="'.$baseurl.$pre.'page=1">首页</a>';
		$html.= '<a href="'.$baseurl.$pre.'page='.($curpage - 1).'">上一页</a>';
	}

	for($i = $startpage; $i <= $endpage; $i++){
		$html.= '<a href="'.$baseurl.$pre.'page='.$i.'"'.($curpage == $i ? ' class="current"' : '').'>'.$i.'</a>';
	}
	
	if($curpage<$pagenum) {
		$html.= '<a href="'.$baseurl.$pre.'page='.($curpage + 1).'">下一页</a>';
		$html.= '<a href="'.$baseurl.$pre.'page='.$pagenum.'">末页</a>';
	}
	
	$html.= '&nbsp;共'.$pagenum.'页&nbsp;转到第<input type="text" id="mul_pagenumber" />页&nbsp;';
	$html.= '<a href="javascript:changePage('."'".$baseurl.$pre.'page='."'".','.$pagenum.');">确定</a>';
	$html.= '</div>';
	
	return $html;
}


//将$data变量保存到缓存$file中
function writecache($file, $data){
	return file_put_contents(S_ROOT.'./data/cache/'.$file.'.php', '<?php return '.var_export($data, true).';?>');
}

//读取缓存$file中存储的变量
function readcache($file){
	if(file_exists(S_ROOT.'./data/cache/'.$file.'.php')){
		return include S_ROOT.'./data/cache/'.$file.'.php';
	}else{
		if(updatecache($file)){
			return readcache($file);
		}else{
			return NULL;
		}
	}
}

function updatecache($file){
	global $db;
	switch($file){
	case 'project_type':
		$db->select_table('projecttype');
		$types = array();
		$query = $db->SELECT('*');
		while($t = $db->fetch_array($query)){
			$types[$t['id']] = $t['name'];
		}
		writecache('project_type', $types);
	return true;
	}
	
	return false;
}

//找出$arr2与$arr1不同的地方，返回的数组表示是$arr2相对$arr1做出的改变，忽略新增加的元素
function array_diff_update($arr1, $arr2){
	$update = array();
	foreach($arr1 as $key => $val){
		if($val != $arr2[$key]){
			$update[$key] = $arr2[$key];
		}
	}
	return $update;
}


//递归加上转义符号，可以处理数组
function raddslashes($str){
	if(is_array($str)){
		foreach($str as $key => $val){
			$str[$key] = raddslashes($val);
		}
	}else{
		$str = addslashes($str);
	}
	return $str;
}

//递归转义HTML，可以处理数组
function rhtmlspecialchars($str){
	if(is_array($str)){
		foreach($str as $key => $val){
			$str[$key] = rhtmlspecialchars($val);
		}
	}else{
		$str = htmlspecialchars($str);
	}
	return $str;
}

function view($tpl){
	$htmpath = S_ROOT.'./view/'.$GLOBALS['_G']['style'].'/'.$tpl.'.htm';
	if(!file_exists($htmpath)){
		$htmpath = S_ROOT.'./view/default/'.$tpl.'.htm';
	}
	$tplpath = S_ROOT.'./data/tpl/'.$GLOBALS['_G']['style'].'_'.$tpl.'.tpl.php';
	if(!file_exists($tplpath) || filemtime($htmpath) > filemtime($tplpath)){
		file_put_contents($tplpath, Tpl::parse_template($htmpath));
	}
	return $tplpath;
}

function rgmdate($dateline, $format = 'Y-m-d H:i:s'){
	return gmdate($format, $dateline + $GLOBALS['_G']['setting']['timezone'] * 3600);
}

function randomstr($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' ? mt_srand((double) microtime() * 1000000) : mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

function rfilesize($size){
	if($size < 1024){
		return $size.'B';
	}elseif($size < 1024 * 1024){
		return (floor($size / 1024 * 100) / 100).'KB';
	}else{
		return (floor($size / 1024 / 1024 * 100) / 100).'MB';
	}
}

function rheader($string, $replace = true, $http_response_code = 0) {
	$string = str_replace(array("\r", "\n"), array('', ''), $string);
	if(empty($http_response_code) || PHP_VERSION < '4.3' ) {
		@header($string, $replace);
	} else {
		@header($string, $replace, $http_response_code);
	}
	if(preg_match('/^\s*location:/is', $string)) {
		exit();
	}
}

function riconv($in, $out, $str){
	if(is_array($str)){
		foreach($str as $k => $v){
			$str[$k] = riconv($in, $out, $v);
		}
		return $str;
	}else{
		return iconv($in, $out, $str);
	}
}

function rmd5($str){
	return md5($str.$GLOBALS['_G']['setting']['halt']);
}

function writelog($logfile, $data){
	global $_G;
	$logfile = S_ROOT.'./data/log/'.gmdate('Ymd', $_G['timestamp']).'_'.$logfile.'.log.php';
	$content = file_exists($logfile) ? file($logfile) : array('<?php exit;?>');

	if(is_array($data)){
		foreach($data as $k => $v){
			$data[$k] = $_G['admin']->v['uid']."\t".$_G['admin']->v['username']."\t".$_G['admin']->v['loginip']."\t".$_G['timestamp']."\t".$v;
		}
	}else{
		$data = $_G['admin']->v['uid']."\t".$_G['admin']->v['username']."\t".$_G['admin']->v['loginip']."\t".$_G['timestamp']."\t".$data;
	}

	if(substr($content[0], 0, 13) != '<?php exit;?>'){
		$prefix = "<?php exit;?>\r\n";
	}else{
		$prefix = '';
		if(is_array($data)){
			$content = array_merge($content, $data);
		}else{
			$content[] = $data;
		}
	}
	$content = implode("\r\n", $content);

	$fp = fopen($logfile, 'wb');
	fwrite($fp, $prefix.$content);
	fclose($fp);
}

function submitcheck($var){
	if(isset($_POST[$var]) && ($_SERVER['REQUEST_METHOD'] == 'POST' && (empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))){
		return true;
	}else{
		return false;
	}
}

function randnum($number){
	return $number + rand(-ceil($number * 0.2), ceil($number * 0.2));
}

?>
