
var G_type = getcookie('G_type');if(!G_type) G_type = 'gym';
var G_landid = getcookie('G_landid');if(!G_landid) G_landid = 1;
var G_mapid = 0;
var selectedmon = 0;
var mapimg = new Array('','kanto','johto','hoenn','shinnoh');
var monselectHTML = $('#monselect').html();

function changetype(type){
	G_type = type;
	setcookie('G_type', G_type, 3600*1000);
	map_src = imgpath+'/map/'+mapimg[G_landid]+'.jpg';
	$('#map').html('<img src="'+map_src+'" usemap="#'+type+'_'+G_landid+'" border=0 />');
	if(G_type == 'contest'){
		var map_object = $('#map>div:first-child');
		map_width = map_object.width();
		map_height = map_object.height();
		$('#map').html('<div style="width:'+map_width+';height:'+map_height+';background:url('+map_src+') no-repeat scroll center;"></div>');
		$('#map>div').html($('#contest_'+G_landid).length > 0 ? $('#contest_'+G_landid).html() : '');
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
	G_landid = landid;
	changetype(G_type);
	setcookie('G_landid', G_landid, 3600*1000);
	$('#maptitle').load('./data/data_map_'+G_landid+'.htm', onDownloadDone);
}
changemap(G_landid);

function setmap(type, id){
	G_type = type;
	G_mapid = id;
	if(G_type == 'land') index = 'adven';
	else if(G_type == 'gym') index = 'gym';
	if(selectedmon) location.href="pkw.php?index="+index+"&gid="+G_mapid+"&revid="+selectedmon;
}
function setmon(mid){
	$('monselect').innerHTML = monselectHTML;
	$('mon_'+mid).style.filter = 'Alpha(opacity=100)';
	selectedmon = mid;
	if(G_type == 'land') index = 'adven';
	else if(G_type == 'gym') index = 'gym';
	if(G_mapid) location.href="pkw.php?index="+index+"&gid="+G_mapid+"&revid="+selectedmon;
}