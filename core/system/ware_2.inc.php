<?php
if(!defined('IN_PKW')) exit('Access Denied');

$basic_time = 12 * 60;

$ustatus = $db->result_first("SELECT status FROM {$PKWpre}myprofile WHERE id=$_USER[id]");
if(empty($ustatus)){
	$ustatus = array();
}else{
	$ustatus = unserialize($ustatus);
}
if($ustatus[1] > $timestamp) $ustatus[1] += $basic_time;
else $ustatus[1] = $timestamp + $basic_time;

$ustatus = serialize($ustatus);
$db->query("UPDATE {$PKWpre}myprofile SET status='$ustatus' WHERE id=$_USER[id]");

showmsg('成功使用除虫器!', 'back');
?>