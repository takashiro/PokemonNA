<?php

require_once './core/init.inc.php';

switch($_GET['action']){
	case 'login':
		if($_G['user']->isLoggedIn()){
			showmsg('您已经成功登录。', 'mypokemon.php');
		}

		if(submitcheck('loginsubmit')){
			if($_G['user']->login($_POST['email'], $_POST['password'])){
				showmsg('成功登录！', 'mypokemon.php');
			}else{
				showmsg('密码错误！', 'back');
			}
		}else{
			include view('memcp_login');
		}
	break;

	case 'logout':
		$_G['user']->logout();
		showmsg('您已经成功退出。', 'center.php');
	break;

	case 'edit':default:
	include view('memcp_edit');
}

?>