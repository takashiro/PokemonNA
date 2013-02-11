var skill_ext25 = new Array(32,55,148,219,298,378,394,413);
var skill_onrequest = false;

function creatrequest(){
	var request = false;
	if(window.XMLHttpRequest) {
		request = new XMLHttpRequest();
		if(request.overrideMimeType) {
			request.overrideMimeType('text/xml');
		}
	} else if(window.ActiveXObject) {
		var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
		for(var i=0; i<versions.length; i++) {
			try {
				request = new ActiveXObject(versions[i]);
				if(request) {
					return request;
				}
			} catch(e) {
				//alert(e.message);
			}
		}
	}
	return request;
}

function skill(skillname){
	if(!skill_onrequest){
		if(in_array(skillname, skill_ext25)) if(waittime >=3) waittime -= 3;else waittime = 0;//先制攻击
		skill_onrequest = true;
		skillbtn_ctrl('off');
	}
		

	if(waittime > 0){
		$('undermsg').innerHTML = '技能施展中...';
		setTimeout('skill('+skillname+')', waittime);
		return;
	}
	var url = skillurl+'&revid='+revid;
	var request=creatrequest();
	var data="kid="+skillname;
	request.open("Post", url, true);
	request.onreadystatechange = updatePage;
	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	request.send(data);
	function updatePage() {
		if (request.readyState == 1){
			$("undermsg").innerHTML = '技能施展中...';
		}
		if (request.readyState == 4){
			if (request.status == 200){
				response = request.responseText;
				var tt = response.split("\n");
				for( id in tt) eval(tt[id]);
			}else{
				$("undermsg").innerHTML = '技能施展失败，请重新施展';
			}
		}
	}
}

function skillbtn_ctrl(status){
	var skill_button = $('skillshow').getElementsByTagName('button');
	for(var i in skill_button){
		if(typeof skill_button[i] == 'object'){
			skill_button[i].disabled = (status == 'off') ? true : false;
		}
	}
}

function changeMon(){
	if(waittime > 0){
		$('undermsg').innerHTML = '替换中...剩余'+waittime+'秒';
		setTimeout('changeMon()', waittime);
		return;
	}
	floatwin('open_confirm', -1, 400, 180);
	$('floatwin_confirm').className = 'mainbox';
	$('floatwin_confirm_mask').className = '';
	$('floatwin_confirm_content').style.height = '80%';
	$('floatwin_confirm_content').style.overflow = 'auto';

	var url = 'pkw.php?index=view&action=ajax';
	var request=creatrequest();
	request.open("Get", url, true);
	request.onreadystatechange = updatePage;
	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	request.send('');
	function updatePage() {
		if (request.readyState == 4){
			if (request.status == 200){
				response = request.responseText;
				$('floatwin_confirm_content').innerHTML = '<ul id="monselect">'+response+'</ul>';
			}else{
				floatwin('close_confirm');
			}
		}
	}
}

function viewBackpack(type){
	if(waittime > 0){
		$('undermsg').innerHTML = '打开背包中，剩余'+waittime+'秒';
		setTimeout('viewBackpack('+type+')', waittime);
		return;
	}
	floatwin('open_confirm', -1, 400, 180);
	$('floatwin_confirm').className = 'mainbox';
	$('floatwin_confirm_mask').className = '';
	$('floatwin_confirm_content').style.overflow = 'auto';

	var url = 'pkw.php?index=myware&ajax=1&revid='+revid+'&type='+type;
	var request=creatrequest();
	request.open("Get", url, true);
	request.onreadystatechange = updatePage;
	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	request.send('');
	function updatePage() {
		if (request.readyState == 4){
			if (request.status == 200){
				response = request.responseText;
				$('floatwin_confirm_content').innerHTML = '<ul>'+response+'</ul>';
			}else{
				floatwin('close_confirm');
			}
		}
	}
}

function setattrib(element, attrib, value, laytime){
	if(laytime <= 0) eval("$('"+element+"')."+attrib+"='"+value+"';");
	else setTimeout("setattrib('"+element+"', '"+attrib+"', '"+value+"', 0);", laytime);
}

function barpercent(element, percent){
	var ori = parseInt($(element).style.width, 10);
	var fix = (percent > ori)?1:(percent < ori)?-1:0;
	setTimeout('changebar("'+element+'", '+fix+', '+percent+')', 10);
}

function changebar(element, fix, percent){
	var ori = parseInt($(element).style.width, 10);
	if(element.substr(3, 5) == 'hpbar'){
		if(ori <= 10) $(element).className = 'godev';
		else if(ori <= 45) $(element).className = 'exp';
		else $(element).className = 'hp';
	}
	if(ori != percent){
		$(element).style.width = (ori+fix)+"%";
		setTimeout('changebar("'+element+'", '+fix+', '+percent+')', 10);
	}
}

function reduceTime(){
	if(waittime > 0){
		waittime -= 1;
	}
}
setInterval('reduceTime()', 1000);

function startFloat(element){
	setInterval('floatElement(\"'+element+'\");', 100);
}
function floatElement(element){
	var rand_y = 55 + Math.floor(Math.random() * 5);
	setattrib(element, 'style.top', '-'+rand_y+'px');
}
function shockimg(element){
	setattrib(element, 'style.left', '-30px', 0);
	setattrib(element, 'style.left', '0px', 50);
	setattrib(element, 'style.left', '-30px', 100);
	setattrib(element, 'style.left', '0px', 150);
	setattrib(element, 'style.left', '-30px', 200);
	setattrib(element, 'style.left', '0px', 250);
}