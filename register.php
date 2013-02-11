<?php

require_once './core/init.inc.php';

if(submitcheck('registersubmit')){
	if($_POST['password2'] != $_POST['password2']){
		showmsg('您两次输入的密码不一致。请重新输入。', 'back');
	}

	User::register($_POST['account'], $_POST['password']);
	showmsg('注册成功！', 'center.php');

}else{
	include view('register');
}

?>