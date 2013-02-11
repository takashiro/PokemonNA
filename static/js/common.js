function shockwave(src, width, height, flashvar){
	if(typeof flashvar == 'string' && flashvar){
		src += '?' + flashvar;
	}
	return '<embed src="' + src + '" width="' + width + '" height="' + height + '" wmode="transparent" allowScriptAccess="always"></embed>';
}

function pnotice(msg, script, width, height) {
	var script = script ? script : '';
	var width = width ? width : 400;
	var height = height ? height : 110;
	floatwin('open_confirm', -1, width, height);
	$('floatwin_confirm').className = 'mainbox';
	$('floatwin_confirm_mask').className = '';
	$('floatwin_confirm_content').style.padding = '15px';
	$('floatwin_confirm_content').innerHTML = msg + (script ? '<br /><button onclick="' + script + ';floatwin(\'close_confirm\')">È·¶¨</button>' : '');
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