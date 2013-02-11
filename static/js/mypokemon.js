function fw_rename(mid){
	var html = '<form action="pkw.php?index=mypokemon&action=rename&mid='+mid+'" method="post">';
	html+= '<input type="hidden" name="formhash" value="'+FORMHASH+'" />';
	html+= '<p>新名字：<input type="text" name="newname" maxlength=15 /></p>';
	html+= '<button type="submit" class="submit" name="renamesubmit">确定</button>';
	html+= '</form>';
	pnotice(html);
}

function fw_give(mid){
	var html = '<form action="pkw.php?index=mypokemon&action=give&mid='+mid+'" method="post">';
	html+= '<input type="hidden" id="formhash" name="formhash" value="'+FORMHASH+'" />';
	html+= '<p>对方用户名：<input type="text" id="receiver" name="receiver" maxlength=15 /></p>';
	html+= '<button type="submit" class="submit" id="givesubmit" name="givesubmit">确定</button>';
	html+= '</form>';
	pnotice(html);
}

function fw_throw(mid){
	msg = '<p>您确认丢弃吗？</p>';
	msg+= '<p>请输入您的登录密码：<input type="text" type="password" id="password" name="password" /></p>';
	msg+= '<button class="submit" onclick="location.href=\'pkw.php?index=mypokemon&action=throw&id='+mid+'\'">确认</button>&nbsp;';
	msg+= '<button class="submit" onclick="floatwin(\'close_confirm\')">取消</button>';
	pnotice(msg, '', '', 140);
}