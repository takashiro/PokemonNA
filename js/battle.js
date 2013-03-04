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
		$('#msg_under').html('技能使用中...');
		setTimeout('useSkill('+skill_id+');', 1000);
		waittime--;
		return;
	}
	waittime = 3;

	$.ajax({
		url:skillurl + '&mid=' + mid + '&kid=' + skill_id,
		type:'get',
		dataType:'json',
		success:function(data){
			$('#msg_under').html('');
			
			setHp('pos', data.pos_hp);
			setHp('neg', data.neg_hp);

			for(var i in data.msg){
				$('#msg_' + i).html(data.msg[i]);
			}
		},
		error:function(){
			$('#msg_under').html('技能施展失败，请重新施展');
		},
		complete:function(){
			skill_onrequest = false;
			$('#skillshow button').attr('disabled', false);
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

function viewBackpack(type){
	if($('#backpack').length == 0){
		var backpack = $('<div></div>');
		backpack.attr('id', 'backpack');
		backpack.attr('class', 'box popup');
		backpack.css({'width':170, 'height':280});

		var top = ($(window).height() - backpack.outerHeight()) / 2 + $(window).scrollTop();
		var left = ($(window).width() - backpack.outerWidth()) / 2 + $(window).scrollLeft();
		backpack.css({'top':top, 'left':left});

		var title = $('<h4></h4>');
		title.html('我的背包');

		var content = $('<div></div>');
		content.attr('class', 'content');
		content.css({'height':270, 'overflow-y':'auto'});

		backpack.append(title);
		backpack.append(content);

		$('body').append(backpack);
	}else{
		$('#backpack').slideUp();
	}

	$('#backpack .content').load('myware.php?ajax=1&mid='+mid+'&type='+type, function(){
		$(this).parent().slideDown();
	});
}

function setattrib(element, attrib, value, laytime){
	if(laytime <= 0) eval("$('"+element+"')."+attrib+"='"+value+"';");
	else setTimeout("setattrib('"+element+"', '"+attrib+"', '"+value+"', 0);", laytime);
}

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