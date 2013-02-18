var G = new Array();
var G['type'] = getcookie('type');if(!G['type']) G['type'] = 'gym';
var G['landid'] = getcookie('landid');if(!G['landid']) G['landid'] = 1;
var G['mapid'] = 0;
var selectedmon = 0;
var mapimg = new Array('','kanto','johto','hoenn','shinnoh');
var monselectHTML = $('#monselect').html();

function changetype(type){
	G['type'] = type;
	setcookie('G['type']', G['type'], 3600*1000);
	map_src = imgpath+'/map/'+mapimg[G['landid']]+'.jpg';
	$('#map').html('<img src="'+map_src+'" usemap="#'+type+'_'+G['landid']+'" border=0 />');
	if(G['type'] == 'contest'){
		var map_object = $('#map>div:first-child');
		map_width = map_object.width();
		map_height = map_object.height();
		$('#map').html('<div style="width:'+map_width+';height:'+map_height+';background:url('+map_src+') no-repeat scroll center;"></div>');
		$('#map>div').html($('#contest_'+G['landid']).length > 0 ? $('#contest_'+G['landid']).html() : '');
	}
};
function onDownloadDone(data){
	$('#gymtitle').html('');
	$('#maptitle map:first-child area').each(function(){
		var a = $('<a></a>');
		a.html($(this).attr('alt'));
		a.attr('href', $(this).attr('href'));
		var li = $('<li></li>');
		li.append(a);
		$('#gymtitle').append(li);
	});

	$('#maptitle map:first-child area').each(function(){
		var a = $('<a></a>');
			a.html($(this).attr('alt'));
			a.attr('href', $(this).attr('href'));
			var li = $('<li></li>');
			li.append(a);
			$('#maptitle').append(li);
	});
}

function changemap(landid){
	alert(type);
	G['landid'] = landid;
	changetype(G['type']);
	setcookie('G['landid']', G['landid'], 3600*1000);
	$('#maptitle').load('./data/data_map_'+G['landid']+'.htm', onDownloadDone);
}

function setmap(type, id){
	G['type'] = type;
	G['mapid'] = id;
	if(G['type'] == 'land') index = 'adven';
	else if(G['type'] == 'gym') index = 'gym';
	if(selectedmon) location.href="pkw.php?index="+index+"&gid="+G['mapid']+"&revid="+selectedmon;
}
function setmon(mid){
	$('monselect').innerHTML = monselectHTML;
	$('mon_'+mid).style.filter = 'Alpha(opacity=100)';
	selectedmon = mid;
	if(G['type'] == 'land') index = 'adven';
	else if(G['type'] == 'gym') index = 'gym';
	if(G['mapid']) location.href="pkw.php?index="+index+"&gid="+G['mapid']+"&revid="+selectedmon;
}