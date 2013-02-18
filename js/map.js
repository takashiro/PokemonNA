
var G = new Array();
G['area_id'] = 1;
G['land_id'] = 0;
G['type'] = 'gym';
G['pokemon_id'] = 0;

var area_data = new Array();
var area_name = new Array('', 'kanto', 'johto', 'hoenn', 'shinnoh');

function setArea(area_id){
	G['area_id'] = area_id;

	var pic_path = imgpath + '/map/' + area_name[area_id] + '.jpg';
	var img = $('<img></img>');
	img.attr('src', pic_path);
	img.attr('usemap', '#' + G['type'] + '_' + G['area_id']);
	
	$('#map').html('');
	$('#map').append(img);

	$('#landmark').load('./data/map_' + area_id + '.htm', function(){
		$('#landtitle').html('');
		$('#landmark map:eq(0) area').each(function(){
			var li = $('<li></li>');
			var a = $('<a></a>');
			a.attr('href', $(this).attr('href'));
			a.html($(this).attr('alt'));
			li.append(a);
			$('#landtitle').append(li);

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

function setLand(land_id){
	G['land_id'] = land_id;
	redirect();
}

function setType(type){
	G['type'] = type;
	$('#map img').attr('usemap', '#' + G['type'] + '_' + G['area_id']);
}

function setPokemon(pokemon_id){
	G['pokemon_id'] = pokemon_id;
	redirect();
}

function setMap(type, land_id){
	setType(type);
	setLand(land_id);
}

function redirect(){
	if(G['land_id'] && G['pokemon_id']){
		location.href = G['type'] + '.php?gid=' + G['land_id'] + '&mid=' + G['pokemon_id'];
	}
}