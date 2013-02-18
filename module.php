<?php
require_once './core/init.inc.php';

$query = $db->query("SELECT * FROM {$tpre}trainer WHERE username='$discuz_user'");
if(!$PKW_user = $db->fetch_array($query)) showmsg('请到大木研究所领取精灵球！', 'institude.php');

if(submitcheck('banksubmit')){
	$result_msg = '';
	$credit = 'extcredits'.$_CONFIG['bank_credit'];
	$extcre = $extcredits[$_CONFIG['bank_credit']];
	$extcredit = $GLOBALS[$credit];
	$gaincash = $exchange * $_CONFIG['bank_c2c'];

	$deposit = intval($deposit);
	$draw = intval($draw);
	if($deposit && $draw) showmsg('你到底存款还是取款呀？', 'back');

	if($exchange = intval($exchange)){
		if($exchange > $GLOBALS[$credit]) showmsg("您的{$extcre[title]}不够！", 'pkw.php');
		updatecredits($_USER[id], array($_CONFIG['bank_credit']=>-$exchange));
		$db->query("UPDATE {$tpre}trainer SET cash=cash+$gaincash WHERE username='$discuz_user'",'UNBUFFERED');
		$result_msg.= "成功转换{$exchange}{$extcre[unit]}{$extcre[title]}为{$gaincash}个宠物币！";
	}
	if($deposit){
		$cash = $PKW_user['cash'];
		if($deposit > $cash) showmsg('您没有那么多现金！', 'pkw.php');
		$db->query("UPDATE {$tpre}trainer SET cash=cash-$deposit,deposit=deposit+$deposit WHERE username='$discuz_user'", 'UNBUFFERED');
		$result_msg.= "成功存入{$deposit}现金！";
	}
	if($draw){
		$deposit = $PKW_user['deposit'];
		if($draw > $deposit) showmsg('您没有那么多存款！', 'pkw.php');
		$db->query("UPDATE {$tpre}trainer SET cash=cash+$draw,deposit=deposit-$draw WHERE username='$discuz_user'", 'UNBUFFERED');
		$result_msg.= "成功取出{$draw}存款！";
	}
	if($result_msg){
		showmsg($result_msg, 'refresh');
	}else{
		showmsg('你输入的是什么啊！别玩我了！', 'pkw.php');
	}

}elseif(submitcheck('rescuesubmit')){
	$id = intval($pokemonid);
	$cash = $PKW_user['cash'];
	if($_CONFIG['rescue_fee'] > $cash) showmsg('您没有那么多现金！', 'pkw.php');
	$db->query("UPDATE {$tpre}pokemon SET status=2 WHERE id=$id AND owner='$discuz_user' AND status<9");
	if($db->affected_rows <= 0){
		$mon = $db->fetch_first("SELECT status,owner FROM {$tpre}pokemon WHERE id=$id");
		$mon['owner'] != $discuz_user && showmsg('该精灵不属于您！', 'back');
		$mon['status'] == 9 && showmsg('该精灵尚未孵化！', 'back');
		$mon['status'] >= 10 && showmsg('该精灵还在暂存在研究所中！', 'back');
	}
	$db->query("UPDATE {$tpre}trainer SET cash=cash-$_CONFIG[rescue_fee] WHERE username='$discuz_user'");
	showmsg('您的宠物已经送往病房中！', 'pkw.php');

}elseif(submitcheck('reportsubmit')){
	
}else{
	exit('Error: Submitcheck');
}
?>
