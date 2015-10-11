var marker_resource = 'resources/pin-144ppi.png';
var home_marker_resource = 'resources/home-marker.png';

var categories = [];

function CategoryController() {
	var self = this;
	var category_array;
	var has_all_category;
	
	this.categories = {
		"All": true
	};

	this.Include = function (category) {
		self.categories[category] = true;
		category_array = void 0;
		has_all_category = void 0;
	}

	this.Exclude = function (category) {
		console.log("Exclude("+category+")");
		delete self.categories[category];
		category_array = void 0;
		has_all_category = void 0;
	}
	
	this.Toggle = function(category) {
		if (self.categories[category]) {
			self.Exclude(category);
		} else {
			self.Include(category);
		}	
	}
	
	this.ExcludeEverything = function () {
		self.categories = {};
		category_array = void 0;
		has_all_category = void 0;
	}

	this.Categories = function() {
		if (category_array) {
			return category_array;
		}
		category_array = [];
		for (category in self.categories) {
			category_array.push(category);
		}
		return category_array;
	}
	
	this.HasAllCategory = function() {
		if (has_all_category) {
			return has_all_category;
		}
		has_all_category = category_controller.Categories().indexOf('All')!=-1;
		return has_all_category;
	}
}

function InteractionController() {
	var menu_mapping =
	{
		"filter-all":"All",
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

	function FindElementIdFromCategory(category) {
		var index = Object.keys(menu_mapping).map(function(e){return menu_mapping[e];}).indexOf(category);
		return Object.keys(menu_mapping)[index];
	}
	
	function DisplayCategories() {
		for (var filter_index in category_controller.Categories()){
			var element_id = FindElementIdFromCategory(category_controller.Categories()[filter_index]);
			$('#'+element_id).removeClass('switch-off');
			$('#'+element_id).addClass('switch-on');
		}		
	}
	
	function ToggleCategory(element,category) {
		$('#filter li').removeClass('switch-on');
		$('#filter li').addClass('switch-off');		
		
		if (category=='All') {
			category_controller.Toggle(category);

			if (category_controller.HasAllCategory()) {
				$('#filter-all').removeClass('switch-off');
				$('#filter-all').addClass('switch-on');				
			} else {
				DisplayCategories();
			}
		} else {
			if (!category_controller.HasAllCategory()) {
				category_controller.Toggle(category);

				DisplayCategories();
			} else {
				category_controller.ExcludeEverything();
				category_controller.Include(category);
				
				DisplayCategories();		
			}
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
	
	this.marker_state_controller = new MarkerStateController();

	this.AddMarker = function(place,id,name,lat,lon,resource,callback) {
		var pin_html = "<div class='marker-pin'>"+name+"</div>";
		var overlay = new CustomMarker(
			new google.maps.LatLng(lat, lon),
			map_controller.google_map,
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
			map_controller.google_map,
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
			map_controller.google_map,
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
		this.marker_state_controller.Reset();
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

function MarkerStateController(){
	var previous_id = undefined;
	var ignore_next_click_map = false;
	
	this.Reset = function() {
		previous_id = undefined;
		ignore_next_click_map = false;
	}
	this.Event = function(info) {
		switch (info.name) {
			case 'click_marker':
				ignore_next_click_map = true;
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
				if (ignore_next_click_map) {
					ignore_next_click_map = false;
					break;
				}
				ignore_next_click_map = false;
				if (previous_id !== undefined) {
					HideBubble(previous_id);
					previous_id = undefined;
				}
				break;
			case 'click_list':
				ignore_next_click_map = false;
				if (previous_id !== undefined) {
					HideBubble(previous_id);
				}
				ShowBubble(info.id);
				var place = place_controller.GetPlaceById(info.id);
				map_controller.SetCentre(place.latitude,place.longitude);
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
		places = ajax_controller.Message('GetPlaces',JSON.stringify({categories:categories,position:[map_controller.home_lat,map_controller.home_long]}) );
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
						marker_controller.marker_state_controller.Event({name:'click_marker',id:place_id});
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
						marker_controller.marker_state_controller.Event( {name:'click_list',id:place_id} );
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

function MapController(centre_lat,centre_long) {
	var self = this;
	this.google_map = undefined;
	
	this.home_lat = undefined;
	this.home_long = undefined;
	this.centre_lat = centre_lat;
	this.centre_long = centre_long;
	
	this.CentreChanged = function() {
		var centre = self.google_map.getCenter();

		this.centre_lat = centre.lat();
		this.centre_long = centre.lng();
	}

	this.SetCentre = function(lat,lon) {
		self.google_map.setCenter(new google.maps.LatLng(lat, lon));
	}

	this.ZoomChanged = function() {
		// new_zoom_google = map.getZoom();
	}
	
	this.Initialise = function () {
		var mapOptions = {
			center: new google.maps.LatLng(self.centre_lat, self.centre_long),
			zoom: 14,
			minZoom: 11,
			maxZoom: 18,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			streetViewControl: false,
			navigationControl: false,
			scaleControl: true
		}

		self.google_map = new google.maps.Map(document.getElementById('map_box'), mapOptions);	
		
		google.maps.event.addListener(self.google_map, 'zoom_changed', self.ZoomChanged);
		google.maps.event.addListener(self.google_map, 'center_changed', self.CentreChanged);
		google.maps.event.addListener(self.google_map, 'click',
			function() {
				marker_controller.marker_state_controller.Event(
					{name:'click_map'}
				) 
			} 
		);
		
		self.ZoomChanged();
	}


}

function ConfigurationController() {

	var self = this;

	var attempts = 0;
	var max_attempts = 4;

	this.Initialise = function () {
		map_controller.Initialise();
			
		var options = {
		  enableHighAccuracy: true,
		  timeout: 20000,
		  maximumAge: 10000
		};
		navigator.geolocation.getCurrentPosition(LocationSuccess, LocationError, options);

		$('#filter li').addClass('switch-off');
		$('#filter-all').removeClass('switch-off');
		$('#filter-all').addClass('switch-on');
		$('#filter-more').removeClass('switch-off');
		
		$('li').click(interaction_controller.MenuClick);
	}


	function LocationSuccess(location_info) {
		map_controller.home_lat = location_info.coords.latitude;
		map_controller.home_long = location_info.coords.longitude;
		map_controller.SetCentre(map_controller.home_lat, map_controller.home_long);

		marker_controller.AddHomeMarker(map_controller.home_lat, map_controller.home_long, HomeMarkerClicked);

		place_controller.Show(category_controller.Categories());
	};

	function LocationError(error) {
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
var map_controller = new MapController(51.5072,-0.1275);

google.maps.event.addDomListener(window, 'load', configuration_controller.Initialise);