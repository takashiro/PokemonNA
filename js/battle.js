var skill_ext25 = new Array(32,55,148,219,298,378,394,413);
var skill_onrequest = false;

function useSkill(skill_id){
	if(!skill_onrequest){
		if(in_array(skill_id, skill_ext25)){
			if(waittime >= 3){
				waittime -= 3;
			}else{
				waittime = 0;//先制攻击
			}
		}

		skill_onrequest = true;
		$('#skillshow button').attr('disabled', true);
	}

	if(waittime > 0){
		$('#undermsg').html('技能使用中...');
		setTimeout('useSkill('+skill_id+');', waittime * 1000);
		waittime = 0;
		return;
	}

	$.ajax({
		url:skillurl + '&mid=' + mid + '&kid=' + skill_id,
		type:'get',
		dataType:'json',
		success:function(data){
			$('#undermsg').html('');
			
			setHp('pos', data.pos_hp);
			setHp('neg', data.neg_hp);

			for(var i in data.msg){
				$('#msg_' + i).html(data.msg[i]);
			}
		},
		error:function(){
			$('#undermsg').html('技能施展失败，请重新施展');
		},
		complete:function(){
			skill_onrequest = false;
			$('#skillshow button').attr('disabled', false);
			waittime = 3;
		}
	});
}

function setHp(side, hp){
	var hp_bar = $('#' + side + '_hpbar');

	var percent = hp / parseInt($('#' + side + '_maxhp').html(), 10);
	var max_width = hp_bar.parent().width();
	var new_width = percent * max_width;
	hp_bar.animate({width : new_width + 'px'});

	$('#' + side + '_hp').html(hp);
}

/*function changeMon(){
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

*/