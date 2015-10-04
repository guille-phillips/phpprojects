var map;
var centre_lat = 70; // 51.147101948513985; //51.1513;
var centre_long = -0.17337799072265625; //-0.1866;
var zoom_google = 14;


// var screen_width;
// var screen_height;

var initial_latlong;

var marker_resource = 'resources/pin-144ppi.png';
var home_marker_resource = 'resources/home-marker.png';

var categories = [];

var marker_state = new MarkerState();


function CategoryController() {
	var self = this;
	this.categories = {
		"Free": true,
		"Paid": true,
		"Indoor": true,
		"Outdoor": true,
		"Animals": true,
		"Water Fun": true,
		"Rides": true,
		"Transport": true,
		"Activity Centre": true,
		"Adventure": true,
		"Bowling": true,
		"Educational": true,
		"Farm": true,
		"Go Karting": true,
		"Historical": true,
		"Leisure Centre": true,
		"Museum": true,		
		"Nature": true,
		"Park": true,
		"Play Centre": true,
		"Playground": true,
		"Skatepark": true,
		"Softplay": true,
		"Theme Park": true
	};
	
	this.Include = function (category) {
		self.categories[category] = true;
	}
	
	this.Exclude = function (category) {
		delete self.categories[category];
	}
	
	this.Categories = function() {
		var category_array = [];
		for (category in self.categories) {
			category_array.push(category);
		}
		return category_array;
	}
}

function InteractionController() {
	var menu_mapping =
	{
		"filter-free":"Free",
		"filter-paid":"Paid",
		"filter-indoor":"Indoor",
		"filter-outdoor":"Outdoor",
		"filter-animals-and-nature":"Animals",
		"filter-water-fun":"Water Fun",
		"filter-rides":"Rides",
		"filter-transport":"Transport",
		"filter-activity-centre":"Activity Centre",
		"filter-adventure":"Adventure",
		"filter-bowling":"Bowling",
		"filter-educational":"Educational",
		"filter-farm":"Farm",
		"filter-go-karting":"Go Karting",
		"filter-historical":"Historical",
		"filter-leisure-centre":"Leisure Centre",
		"filter-museum":"Museum",		
		"filter-nature":"Nature",
		"filter-park":"Park",
		"filter-play-centre":"Play Centre",
		"filter-playground":"Playground",
		"filter-skatepark":"Skatepark",
		"filter-softplay":"Softplay",
		"filter-theme-park":"Theme Park"			
	};

	function ToggleCategory(element,category) {
		$(element).toggleClass('switch-on');
		$(element).toggleClass('switch-off');
		if ($(element).hasClass('switch-on')) {
			category_controller.Include(category);
		} else {
			category_controller.Exclude(category);
		}
		
		place_controller.Show(category_controller.Categories());
	}
	
	this.MenuClick = function() {
		if (menu_mapping[this.id]) {
			ToggleCategory(this,menu_mapping[this.id]);
		} else {
			switch (this.id) {
				case 'menu-home':
					break;
				case 'menu-about-us':
					break;
				case 'menu-upload-a-place':
					break;
				case 'filter-all':
					ToggleCategory(this,'all');
					break;
				case 'filter-more':
					$('#filter > ul > li > ul').toggle();
					break;
			}	
		}
	}
}

function HomeMarkerClicked() {
	alert('HomeMarkerClicked');
}


function MarkerController() {
	var overlays = [];

	this.AddMarker = function(place,id,name,lat,lon,resource,callback) {
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

		overlays.push(overlay);
		
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

		overlays.push(overlay);
	}

	this.AddHomeMarker = function(lat,lon,callback) {
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


	this.RemoveAll = function() {
		for (index in overlays) {
			overlays[index].remove();
		}
		overlays = [];
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
				var place = place_controller.GetPlaceById(info.id);
				SetCentre(place.latitude,place.longitude);
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


function AjaxController() {
	var xmlhttp;
	if (window.XMLHttpRequest) {
		xmlhttp=new XMLHttpRequest();
	} else {
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	this.Message = function (method,value,id) {
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
}

function PlacesController() {
	var overlays = [];
	var ajax_controller = new AjaxController();
	var places = [];
	
	this.Show = function (categories) {
		places = ajax_controller.Message('GetPlaces',JSON.stringify({categories:categories,position:[centre_lat,centre_long]}) );
		if (places.error) {
			alert(places.error);
			return;
		}

		// Sort by distance 
		var temp_places = Object.keys(places).map(function(k) { return places[k] });
		temp_places.sort(function(a,b){return a.distance-b.distance;});
		places = temp_places;

		marker_controller.RemoveAll();
		list_controller.RemoveAll();
				
		var place_list = document.getElementById('place_list');
		
		var marker_index = 1;
		for (var index in places) {
			var place = places[index];

			marker_controller.AddMarker(place,place.id,marker_index,place.latitude,place.longitude,marker_resource,
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
			div.innerHTML = list_controller.CreatePlaceListItem(place,marker_index);

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

	this.GetPlaceById = function (id) {
		var place_id = places.map(function(e){return e.id;}).indexOf(id);
		return places[place_id];
	}
		
	// function RenderAll() {
		// Render([]);
	// }
	
}

function ListController() {
	this.CreatePlaceListItem = function(place,marker_index) {
		var pl = document.getElementById('place_list').firstChild.innerHTML;
		
		pl = pl.replaceBlock('index',marker_index);
		pl = pl.replaceTag('index',marker_index);
			
		for (property in place) {
			pl = pl.replaceBlock(property,place[property]);
			pl = pl.replaceTag(property,place[property]);
		}
		
		return pl;
	}
	
	this.RemoveAll = function() {
		$('#place_list > div').not(':first').remove();
	}
}


function ConfigurationController() {

	var self = this;

	var attempts = 0;
	var max_attempts = 4;
	
	this.Initialise = function () {
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
		
		this.CentreChanged = function() {
			var centre = map.getCenter();

			centre_lat=centre.lat();
			centre_long=centre.lng();
		}

		this.SetCentre = function(lat,lon) {
			map.setCenter(new google.maps.LatLng(lat, lon));
		}

		this.ZoomChanged = function() {
			new_zoom_google = map.getZoom();
		}		
		var options = {
		  enableHighAccuracy: true,
		  timeout: 20000,
		  maximumAge: 10000
		};
		navigator.geolocation.getCurrentPosition(LocationSuccess, LocationError, options);


		//var map_box = document.getElementById('map_box');
		var place_list = document.getElementById('place_list');

		// screen_width = window.clientWidth-200;
		// screen_height = window.clientHeight;

		//map_box.style.width = window.clientWidth-200+'px';
		//map_box.style.height = window.clientHeight+'px';

		//place_list.style.height = (window.clientHeight-2)+'px';

		// window.onresize = function(event) {
			// screen_width = window.clientWidth-200;
			// screen_height = window.clientHeight;
			// //map_box.style.width = window.clientWidth-200+'px';
			// //map_box.style.height = window.clientHeight+'px';
			// //place_list.style.height = (window.clientHeight-2)+'px';
		// };

	
		google.maps.event.addListener(map, 'zoom_changed', this.ZoomChanged);
		google.maps.event.addListener(map, 'center_changed', this.CentreChanged);

		this.ZoomChanged();
		
		$('#filter li').addClass('switch-on');
		$('li').click(interaction_controller.MenuClick);
	}
	
	
	function LocationSuccess(pos) {
		var crd = pos.coords;
		initial_latlong = [crd.latitude, crd.longitude];
		SetCentre(crd.latitude,crd.longitude);
		
		marker_controller.AddHomeMarker(crd.latitude,crd.longitude,HomeMarkerClicked);

		place_controller.Show(category_controller.Categories());
	};

	function LocationError(error) {
		console.log(error.message);
		if (attempts < max_attempts) {
			self.Initialise();
			attempts++;
		} else {
			alert("Unable to get your location.\nPlease try and reload page.");
		}
	};
	
}

var configuration_controller = new ConfigurationController();
var place_controller = new PlacesController();
var marker_controller = new MarkerController();
var list_controller = new ListController();
var interaction_controller = new InteractionController();
var category_controller = new CategoryController();

google.maps.event.addDomListener(window, 'load', configuration_controller.Initialise);