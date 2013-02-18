var pkw_leftid = Array('system', 'ware', 'log', 'gym', 'adven', 'member', 'contest');
var pkw_leftmenu = Array('系统核心设置', '商品管理', '操作记录', '道馆赛', '冒险大陆', '用户管理', '华丽大赛');
ul = document.createElement('ul');
ul.id = "pkw_menu";
top.document.getElementById('leftmenu').appendChild('ul');
for(i = 0;i<=pkw_leftmenu.length;i++){
	top.document.getElementById('leftmenu').innerHTML+= '<li><a href="admincp.php?action=plugins&operation=config&identifier=pokemon&mod='+pkw_leftid[i]+'" hidefocus="true" target="main">'+pkw_leftmenu[i]+'</a></li>';
}