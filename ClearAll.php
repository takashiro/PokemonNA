<?php
if(!defined('IN_PKW') || $adminid != 1) exit('Access Denied');

$tpl = dir(S_ROOT.'logs');
while($entry = $tpl->read()) {
	if(preg_match("/\.log\.php$/", $entry)) {
		@unlink(S_ROOT.'logs/'.$entry);
	}
}
$tpl->close();

$db->query("TRUNCATE {$tpre}mymon");
$db->query("TRUNCATE {$tpre}mymonext");
$db->query("TRUNCATE {$tpre}adventure");
$db->query("TRUNCATE {$tpre}trainer");
?>