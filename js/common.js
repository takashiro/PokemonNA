function shockwave(src, width, height, flashvar){
	if(typeof flashvar == 'string' && flashvar){
		src += '?' + flashvar;
	}
	return '<embed src="' + src + '" width="' + width + '" height="' + height + '" wmode="transparent" allowScriptAccess="always"></embed>';
}

function showmsg(){
	var msg = arguments[0];
	var width = arguments[1] ? arguments[1] : 400;
	var height = arguments[2] ? arguments[2] : 200;

	var win = $('<div></div>');
	win.attr('class', 'box popup');
	win.css({'width':width, 'height':height});
	
	var title = $('<h2></h2>');
	title.html('系统提示');
	
	var content = $('<div></div>');
	content.attr('class', 'content');
	content.html(msg);

	win.append(title);
	win.append(content);

	$('body').append(win);

	popupelement(win);
}

function popupelement(element){
	var top = ($(window).height() - element.outerHeight()) / 2 + $(window).scrollTop();
	var left = ($(window).width() - element.outerWidth()) / 2 + $(window).scrollLeft();
	element.css({'top':top, 'left':left});
	element.animate({'opacity':'show','top':'-=80px'}, 1000);
}

function getcookie(Name){
	var search = Name + "=";
	if(document.cookie.length > 0){
		offset = document.cookie.indexOf(search);
		if(offset != -1){
			offset += search.length;
			end = document.cookie.indexOf(";", offset);
			if(end == -1){
				end = document.cookie.length;
			}
			return unescape(document.cookie.substring(offset, end));
		}
	}

	return "";
}

function setcookie(name, value){
	var argv = setcookie.arguments;
	var argc = setcookie.arguments.length;
	var expires = (argc > 2) ? argv[2] : null;
	if(expires != null){
		var LargeExpDate = new Date ();
		LargeExpDate.setTime(LargeExpDate.getTime() + (expires*1000*3600*24));        
	}
	document.cookie = name + "=" + escape (value)+((expires == null) ? "" : ("; expires=" +LargeExpDate.toGMTString()));
}

$(function(){
	$('.menu').mouseenter(function(){
		var menu_id = '#' + this.id + '_menu';
		if($(menu_id).is(":visible") == false){
			$('.popup').slideUp();
			$(menu_id).slideDown();
		}
	});

	$('.menu').mouseleave(function(){
		var menu_id = '#' + this.id + '_menu';
		$(menu_id).fadeOut();
	});
});