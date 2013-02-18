<?php
define('S_ROOT', DISCUZ_ROOT.'Pokemon/');
define('IN_PKW', TRUE);

function pkw_template($file){
	return template('pkw_'.$file, 0, 'Pokemon/templates');
}

function pkw_exp_incre($orilevel, $exp, $monid, $pokeid, $natureid){
	global $tpre, $db;
	$selectfields = 's.growthtype';
	foreach(array('hp','atk','def','stk','sdf','spd') as $p) $selectfields.= ',s.'.$p.',i.iv_'.$p.',i.ep_'.$p;
	$mon = $db->fetch_first("SELECT $selectfields FROM {$PKWpre}pokemonext i LEFT JOIN {$PKWpre}mon s ON s.id=$pokeid WHERE i.id=$monid");
	$mon['level'] = pkw_exp2lv($orilevel, $exp, $mon['growthtype']);
	$mon['hp'] = ceil(($mon['hp']*2 + $mon['iv_hp'] + ($mon['ep_hp'] / 4)) * $mon['level'] / 100 + 10 + $mon['level']);

	$naturefix = PokemonNature::$value[$natureid];
	foreach(array('atk','def','stk','sdf','spd') as $pi => $p){
		$mon[$p] = ceil((($mon[$p] * 2 + $mon['iv_'.$p] + ( $mon['ep_'.$p] / 4)) * $mon['level'] / 100 + 5) * $naturefix[$pi]);
	}
	$mon_frdup += ($mon['level'] - $orilevel) * 5;

	return ",level=$mon[level],exp=$exp,frd=frd+$mon_frdup,mp=maxmp,hp=$mon[hp],atk=$mon[atk],def=$mon[def],stk=$mon[stk],sdf=$mon[sdf],spd=$mon[spd]";
}

function pkw_lv2exp($level, $growth_type){
	switch($growth_type){
		case 0:$exp = pow($level, 3);break;
		case 1:
			if($level >= 0 && $level <= 50)
				$exp = pow($level, 3) * (100 - $level) / 50;
			elseif($level >= 51 && $level <= 68)
				$exp = pow($level, 3) * (150 - $level) / 100;
			elseif($level >= 69 && $level >= 98){
				$lvbase = $level % 3;
				$fixp = ($lvbase == 1)?0.008:(($lvbase == 2)?0.007:0);
				$exp = pow($level, 3) * (1.274 - 1 / 50 * floor($level / 3)) - $fixp * $lvbase;
			}elseif($lv >= 99)
				$exp = pow($level, 3) * (160 - $level) / 100;
		break;
		case 2:
			if($level >= 0 && $level <= 15)
				$exp = pow($level, 3) * (24 + floor(($level + 1) / 3)) / 50;
			elseif($level >= 16 && $level <= 35)
				$exp = pow($level, 3) * (14 + $level) / 50;
			elseif($level >= 36)
				$exp = pow($level, 3) * (32 + floor($level / 2)) / 50;
		break;
		case 3:$exp = 1.2 * pow($level, 3) - 15 * pow($level, 2) + 100 * $level - 140;break;
		case 4:$exp = 0.8 * pow($level, 3);break;
		case 5:$exp = 1.25 * pow($level, 3);break;
		default:break;
	}
	return floor($exp);
}

function pkw_exp2lv($orilevel, $exp, $growth_type){
	$levelup = 0;
	while(pkw_lv2exp(($orilevel + $levelup), $growth_type) < $exp) $levelup++;
	return $orilevel + $levelup - 1;
}

function randnum($number){
	return $number + rand(-ceil($number * 0.2), ceil($number * 0.2));
}

function pkw_generateMon($pokeid, $level, $gender = 0, $shape = 0, $natureid = 0, $trait = 0, $status = 1){
	global $PKWpre, $db, $timestamp;

	$query = $db->query("SELECT * FROM {$PKWpre}mon WHERE id=$pokeid");
	if($mon = $db->fetch_array($query)){
		$mon['pokeid'] = $mon['id'];
		$level = intval(($level <= 0)?1:$level);
		if($gender == 0){
			$gender = ($mon['gender'] == 255)?3:((rand(1, 254) >= $mon['gender'])?1:2);
		}else{
			$gender = ($mon['gender'] == 255)?3:$gender;
		}
		$mon['gender'] = $gender;
		$mon['name'] = $mon['name_j'];
	
		$mon['godev'] = rand(0, 100);
		$mon['frd'] = randnum($mon['frd']);
		$mon['frd'] = ($frd < 70)?70:$mon['frd'];
		$mon['natureid'] = ($natureid == 0)?rand(1, 25):$natureid;
		$naturefix = getnature($mon['natureid']);

		$mon['iv_hp'] = rand(1, 31);
		$mon['hp'] = ceil(($mon['hp']*2 + $mon['iv_hp'] + (0/4)) * $level / 100 + 10 + $level);
		$mon['mp'] = rand(10, 100);
	
		foreach(array('atk','def','stk','sdf','spd') as $pi => $p){
			eval("\$mon['iv_{$p}'] = rand(1, 31);");
			eval('$mon[\''.$p.'\'] = ceil((('.$mon[$p].'*2 + $mon[\'iv_'.$p.'\'] + (0/4)) * $level / 100 + 5) * $naturefix['.$pi.']);');
		}
	
		$mon['regdate'] = $timestamp;
		if($trait == 0)
			$mon['trait'] = (rand(1,2)==1 && $mon['trait2'])?$mon['trait2']:$mon['trait1'];
		else
			$mon['trait'] = $trait;
		$mon['height'] = randnum($mon['height'] * 10) / 10;
		$mon['weight'] = randnum($mon['weight'] * 10) / 10;
	
		$mon['level'] = $level;
		$mon['status'] = intval($status);
		$mon['exp'] = pkw_lv2exp($level, $mon['growthtype']);
		if($shape == 0)	$mon['shape'] = $mon['shapenum'] ? rand(1, $mon['shapenum']) : 0;
		else $mon['shape'] = ($shape <= $mon['shapenum']) ? $shape : 0;
	}else $mon = FALSE;

	return $mon;
}

function showmsg($message, $url='', $elementid=''){
	global $navtitle, $bbname, $seotitle, $charset;
	$waittime = 3;
	switch($url){
		case 'back':$url_forward = 'javascript:history.back()';break;
		case 'refresh':$url_forward = 'javascript:location.reload()';$waittime = 5;break;
		case 'referrer':$url_forward = dreferrer();break;
		default:$url_forward = $url;
	}
	if($GLOBALS['inajax']){
		$message = '<script type="text/javascript">pnotice("'.$message.'");';
		$url == 'refresh' && $message.= "setTimeout('location.reload()', $waittime * 1000);";
		$message.= '</script>';
		showmessage($message);
		exit();
	}
	if(!$elementid){
		include pkw_template('showmessage');
		exit();
	}else{
		$exit = "\$('$elementid').innerHTML = '$message'";
		$url_forward && $exit.= "<script type=\"text/javascript\">setTimeout(\'location.href=\"$url_forward\";\', $waittime * 1000);</script>';";
		header("Cache-Control: no-cache, must-revalidate");
		header('Content-type: text/plain;charset=GBK');
		exit($exit);
	}
}

function pkw_reload(){
	exit('<script type="text/javascript">location.reload();</script><noscript>请按F5</noscript>');
}

function option($name, $array, $pokeid=0){
	$html = '<select name="'.$name.'">';
	foreach($array as $k => $v){
		$html.= '<option value="'.$k.'" '.(($pokeid == $k)?'selected="selected"':'').'>'.$v.'</option>';
	}
	$html.= '</select>';
	return $html;
}

function getnature($natureid){
	include S_ROOT.'data/data_nature.php';
	$return = $_CONFIG['nature'][$natureid];
	unset($_CONFIG['nature']);
	return $return;
}

function writelog($file, $log) {
	global $timestamp, $_DCACHE, $_USER[id], $discuz_user, $onlineip;
	$yearmonth = gmdate('Ym', $timestamp + $_DCACHE['settings']['timeoffset'] * 3600);
	$logdir = S_ROOT.'logs/';
	$logfile = $logdir.$yearmonth.'_'.$file.'.log.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);

		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		$log = is_array($log) ? $log : array($log);
		foreach($log as $tmp) {
			fwrite($fp, "<?PHP exit;?>\t{$_USER[id]}\t{$discuz_user}\t{$onlineip}\t{$timestamp}\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
		}
		fclose($fp);
	}
}

function pkw_array_diff($arr1, $arr2){
	$arr = array();
	foreach($arr1 as $k => $v){
		if(isset($arr2[$k]) && $v != $arr2[$k]){
			$arr[$k] = $arr2[$k];
		}
	}
	return $arr;
}

require_once S_ROOT.'data/cache_settings.php';

$_CONFIG['aftarray'] = array('所有','选择','敌二体','自身','不定','己方全场','自身以外','全场','敌随机','队友');
$_CONFIG['evostatus'] = array('所有', '初级形态', '中等形态', '最终形态','神兽');
$_CONFIG['weather'] = array('正常','晴天','下雨','寒冷','沙暴','大雾');
$_CONFIG['btytype'] = array('所有','出色','美丽','可爱','聪明','坚强');
$_CONFIG['land'] = array('所有','关东','城都','芳缘','新奥');
$_CONFIG['maptype'] = array('所有','道路','建筑','城市','森林','草地','山区','水面','水底','雪地');
$_CONFIG['institude_pagelimit'] = 12;
$PKWpre = 'pkw_';

$imgdir = empty($imgdir) ? $_DCOOKIE['pkw_localpath'] : $imgdir;
if(empty($imgdir)){
	$_USER[id] && $imgdir = $db->result_first("SELECT localpath FROM {$PKWpre}myprofile WHERE id=$_USER[id]");
	$imgdir = $imgdir ? $imgdir : $_CONFIG['imgpath'];
	dsetcookie('pkw_localpath', $imgdir, $cookietime, 1, true);
}
?>
