
var G = new Array();
G['region_id'] = getcookie('map_region_id');
G['region_id'] = G['region_id'] ? G['region_id'] : 1;
G['area_id'] = 0;
G['type'] = 'gym';
G['pokemon_id'] = 0;

var region_data = new Array();

function setRegion(region_id){
	G['region_id'] = region_id;
	setcookie('map_region_id', region_id);

	var pic_path = imgpath + '/map/' + region_name[region_id] + '.jpg';
	var img = $('<img></img>');
	img.attr('src', pic_path);
	img.attr('usemap', '#' + G['type'] + '_' + G['region_id']);
	
	$('#map').html('');
	$('#map').append(img);

	$('#landmark').load('./data/map_' + region_id + '.htm', function(){
		$('#areatitle').html('');
		$('#landmark map:eq(0) area').each(function(){
			var li = $('<li></li>');
			var a = $('<a></a>');
			a.attr('href', $(this).attr('href'));
			a.html($(this).attr('alt'));
			li.append(a);
			$('#areatitle').append(li);

			$(this).attr('title', $(this).attr('alt'));
		});

		$('#gymtitle').html('');
		$('#landmark map:eq(1) area').each(function(){
			var li = $('<li></li>');
			var a = $('<a></a>');
			a.attr('href', $(this).attr('href'));
			a.html($(this).attr('alt'));
			li.append(a);
			$('#gymtitle').append(li);

			$(this).attr('title', $(this).attr('alt'));
		});
	});
}

function setArea(area_id){
	G['area_id'] = area_id;
	redirect();
}

function setType(type){
	G['type'] = type;
	$('#map img').attr('usemap', '#' + G['type'] + '_' + G['region_id']);
}

function setPokemon(pokemon_id){
	G['pokemon_id'] = pokemon_id;
	redirect();
}

function setMap(type, area_id){
	setType(type);
	setArea(area_id);
}

function redirect(){
	if(G['area_id'] && G['pokemon_id']){
		location.href = G['type'] + '.php?gid=' + G['area_id'] + '&mid=' + G['pokemon_id'];
	}
}

setRegion(G['region_id']);