var map;
var centre_lat = 70; // 51.147101948513985; //51.1513;
var centre_long = -0.17337799072265625; //-0.1866;
var zoom_flights_x;
var zoom_flights_y;
var zoom_google = 14;

var canvas;
var context;


var screen_width;
var screen_height;

var initial_latlong;

var places = [];

var marker_resource = 'resources/pin-144ppi.png';
var home_marker_resource = 'resources/home-marker.png';

var categories = [];

var marker_state = new MarkerState();

function LocationSuccess(pos) {
	var crd = pos.coords;
	initial_latlong = [crd.latitude, crd.longitude];
	SetCentre(crd.latitude,crd.longitude);
	AddHomeMarker(crd.latitude,crd.longitude,HomeMarkerClicked);

	Render([]);
};

function LocationError(err) {
	alert("location error");
};

function HomeMarkerClicked() {
	alert('HomeMarkerClicked');
}

function Initialize() {
	var options = {
	  enableHighAccuracy: true,
	  timeout: 5000,
	  maximumAge: 0
	};
	navigator.geolocation.getCurrentPosition(LocationSuccess, LocationError, options)


	//var map_box = document.getElementById('map_box');
	var place_list = document.getElementById('place_list');

	screen_width = window.clientWidth-200;
	screen_height = window.clientHeight;

	//map_box.style.width = window.clientWidth-200+'px';
	//map_box.style.height = window.clientHeight+'px';

	//place_list.style.height = (window.clientHeight-2)+'px';

	window.onresize = function(event) {
		screen_width = window.clientWidth-200;
		screen_height = window.clientHeight;
		//map_box.style.width = window.clientWidth-200+'px';
		//map_box.style.height = window.clientHeight+'px';
		//place_list.style.height = (window.clientHeight-2)+'px';
	};

	var mapOptions = {
		center: new google.maps.LatLng(centre_lat, centre_long),
		zoom: zoom_google,
		minZoom: 11, 
		maxZoom: 18, 
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false,
		navigationControl: false,
		scaleControl: true
	}
	map = new google.maps.Map(map_box, mapOptions);

	google.maps.event.addListener(map, 'zoom_changed', ZoomChanged);
	google.maps.event.addListener(map, 'center_changed', CentreChanged);

	ZoomChanged();

	$('li').click(MenuClick);
	$('#filter li').addClass('switch-off');

}

function MenuClick() {
	switch (this.id) {
		case 'menu-home':
			break;
		case 'menu-about-us':
			break;
		case 'menu-upload-a-place':
			break;
		case 'filter-all':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			if ($(this).hasClass('switch-on')) {
				$('#').removeClass();

			} else {

			}

			break;
		case 'filter-free':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
		case 'filter-paid':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
		case 'filter-indoor':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
		case 'filter-outdoor':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
		case 'filter-animals-and-nature':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
		case 'filter-water-fun':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			$('#').removeClass();
			break;
		case 'filter-rides':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			$('#').removeClass();
			break;
		case 'filter-transport':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
		case 'filter-more':
			$('#filter > ul > li > ul').toggle();
			break;
		case 'filter-play-centre':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
		case 'filter-history':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
		case 'filter-beaches':
			$(this).toggleClass('switch-on');
			$(this).toggleClass('switch-off');
			break;
	}

}

function CentreChanged() {
	var centre = map.getCenter();

	centre_lat=centre.lat();
	centre_long=centre.lng();
}

function SetCentre(lat,lon) {
	map.setCenter(new google.maps.LatLng(lat, lon));
}

function ZoomChanged() {
	new_zoom_google = map.getZoom();
}

google.maps.event.addDomListener(window, 'load', Initialize);


function Render(categories) {
	places = Ajax('GetPlaces',JSON.stringify({categories:categories,position:[centre_lat,centre_long]}) );
	if (places.error) {
		alert(places.error);
		return;
	}

	// Sort by distance 
	var temp_places = Object.keys(places).map(function(k) { return places[k] });
	temp_places.sort(function(a,b){return a.distance-b.distance;});
	places = temp_places;
	
	var place_list = document.getElementById('place_list');

	var marker_index = 1;
	for (var index in places) {
		var place = places[index];

		AddMarker(place,place.id,marker_index,place.latitude,place.longitude,marker_resource,
			function(place_id){
				return function(){
					marker_state.Event({name:'click_marker',id:place_id}); 
				};
			}(place.id) 
		);

		// Place List Item
		var div = document.createElement('div');
		div.id = 'place_' + place.id;
		div.dataset.id = place.id;
		div.className = 'place_list_item';
		div.innerHTML = CreatePlaceListItem(place,marker_index);

		div.addEventListener("click", 
			function(place_id) {
				return function(){
					marker_state.Event( {name:'click_list',id:place_id} );
				};
			}(place.id)
		);

		place_list.appendChild(div);

		marker_index++;
	}
}


function MarkerState(){
	var previous_id = undefined;
	this.Event = function(info) {
		console.log(new Date().getTime());
		switch (info.name) {
			case 'click_marker':
				if (previous_id === undefined) {
					ShowBubble(info.id);
					document.getElementById("place_"+info.id).scrollIntoView();
					previous_id = info.id;
				} else if (info.id === previous_id) {
					HideBubble(info.id);
					previous_id = undefined;
				} else {
					ShowBubble(info.id);
					document.getElementById("place_"+info.id).scrollIntoView();
					HideBubble(previous_id);
					previous_id = info.id;
				}

				break;
			case 'click_map':
				if (previous_id !== undefined) {
					HideBubble(previous_id);
					previous_id = undefined;
				}
				break;
			case 'click_list':
				if (previous_id !== undefined) {
					HideBubble(previous_id);
				}
				ShowBubble(info.id);
				var place_id = places.map(function(e){return e.id;}).indexOf(info.id);
				SetCentre(places[place_id].latitude,places[place_id].longitude);
				previous_id = info.id;
		}
	}

	var ShowBubble = function(id) {
		document.getElementById("bubble"+id).style.display="inherit";
	}

	var HideBubble = function(id) {
		document.getElementById("bubble"+id).style.display="none";
	}
}

function CreatePlaceListItem(place,marker_index) {
	var pl = document.getElementById('place_list').firstChild.innerHTML;
	
	pl = pl.replaceBlock('index',marker_index);
	pl = pl.replaceTag('index',marker_index);
		
	for (property in place) {
		pl = pl.replaceBlock(property,place[property]);
		pl = pl.replaceTag(property,place[property]);
	}
	
	return pl;
}

function AddHomeMarker(lat,lon,callback) {
	var marker_html = "<div class='marker-home'>"+name+"</div>";
	var overlay = new CustomMarker(
		new google.maps.LatLng(lat, lon), 
		map,
		{marker_id: 'home',
		className: 'marker',
		html: marker_html,
		click_event: callback
		}
	);
}

function AddMarker(place,id,name,lat,lon,resource,callback) {
	var map_box = document.getElementById('google_map');

	var pin_html = "<div class='marker-pin'>"+name+"</div>";
	var overlay = new CustomMarker(
		new google.maps.LatLng(lat, lon), 
		map,
		{marker_id: id,
		className: 'marker',
		html: pin_html,
		click_event: callback
		}
	);

	var info_html = CreateInfoBox(place);

	var bubble_html = "<div id='bubble"+id+"' class='marker-bubble-left'>"+info_html+"</div>";
	var overlay = new CustomMarker(
		new google.maps.LatLng(lat, lon), 
		map,
		{marker_id: id,
		className: 'marker',
		html: bubble_html,
		click_event: callback
		}
	);

}

function CreateInfoBox(place) {
	var html_array = [];

	html_array.push(Tag('img','',{class:'square'}));

	html_array.push(Tag('h1',place.name));

	if (place.category.join) {
		html_array.push( place.category.map(function(content){return Tag('div',content,{class:'category_item'});}).join('') );
	}

	if (place.address.join) {
		html_array.push( Tag('div',place.address.join(', '),{class:'address'}) );
	}

	return html_array.join('');
}

function RenderAll() {
	Render([]);
}

var xmlhttp;
if (window.XMLHttpRequest) {
	xmlhttp=new XMLHttpRequest();
} else {
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
}

function Ajax(method,value,id) {
	xmlhttp.open("GET","data.php?method="+method+"&id="+id+"&value="+value+"&date="+Date.now(),false);
	xmlhttp.send();
	try {
		//alert(xmlhttp.responseText);
		var response = JSON.parse(xmlhttp.responseText);
	} catch (err) {
		alert(xmlhttp.responseText);
		return;
	}
	return response;
}