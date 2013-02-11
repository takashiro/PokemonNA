<?php
require_once './core/init.inc.php';

if(!submitcheck('buysubmit')){
	$type = intval($_GET['type']);
	$extra = !empty($type) ? "AND type=$type" : '';
	$limit = 10;
	$offset = ($page - 1) * $limit;
	$num = $db->result_first("SELECT COUNT(*) AS num FROM {$tpre}ware WHERE selltag=1 $extra");
	$warelist = array();
	$query = $db->query("SELECT * FROM {$tpre}ware WHERE selltag=1 $extra LIMIT $offset, $limit");
	while($ware = $db->fetch_array($query)){
		$warelist[] = $ware;
	}

	$multipage = multi($num, $limit, $page, 'shop.php?type='.$type);

}else{
	$buynums = intval($_POST['buynums']);
	if($buynums <= 0){
		showmsg('请输入正确的购买数量！', 'back');
	}

	$wareid = intval($_POST['wareid']);
	$ware = $db->fetch_first("SELECT id,price FROM {$tpre}ware WHERE id=$wareid AND selltag=1");
	if(!$ware){
		showmsg('您要买的东西不存在！', 'back');
	}else{
		if($ware['price'] * $buybums > $my['cash']){
			showmsg('你的钱不够了！', 'back');
		}else{
			$ware['price'] *= $buynums;
			$db->query("UPDATE {$tpre}trainer SET cash=cash-$ware[price] WHERE id=$_USER[id]");
			writelog('ware', "{$ware[id]}\t{$buynums}\tbuy");
			pkw_storage($ware['id'], $buynums, $_USER[id]);
		}
		showmsg('成功购买！', 'refresh');
	}
}

include view('shop');

?>
