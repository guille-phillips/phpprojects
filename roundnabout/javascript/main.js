<?php 
	header('Content-Type: application/javascript');
	header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
	header("Pragma: no-cache"); // HTTP 1.0.
	header("Expires: 0"); // Proxies.

	function CreateKey() {
		return sha1(fisherYatesShuffle(date("Ymdhi").'£*Fnd98s',3141));
	}

	function fisherYatesShuffle($str, $seed){
		@mt_srand($seed);
		$items = str_split($str);
		for ($i = count($items) - 1; $i > 0; $i--){
			$j = @mt_rand(0, $i);
			$tmp = $items[$i];
			$items[$i] = $items[$j];
			$items[$j] = $tmp;
		}
		return implode('',$items);
	}
?>
var categories = [];

function CategoryController() {
	var self = this;
	var category_array;
	var has_all_category;

	this.categories = {
		"All": true
	};

	this.Include = function (category) {
//console.log("CategoryController::Include");
		self.categories[category] = true;
		category_array = void 0;
		has_all_category = void 0;
	}

	this.Exclude = function (category) {
//console.log("CategoryController::Exclude");
		delete self.categories[category];
		category_array = void 0;
		has_all_category = void 0;
	}

	this.Toggle = function(category) {
//console.log("CategoryController::Toggle");
		if (self.categories[category]) {
			self.Exclude(category);
		} else {
			self.Include(category);
		}
	}

	this.ExcludeEverything = function () {
//console.log("CategoryController::ExcludeEverything");
		self.categories = {};
		category_array = void 0;
		has_all_category = void 0;
	}

	this.Categories = function() {
//console.log("CategoryController::Categories");
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
//console.log("CategoryController::HasAllCategory");
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
//console.log("InteractionController::FindElementIdFromCategory");
		var index = Object.keys(menu_mapping).map(function(e){return menu_mapping[e];}).indexOf(category);
		return Object.keys(menu_mapping)[index];
	}

	function DisplayCategories() {
//console.log("InteractionController::DisplayCategories");
		for (var filter_index in category_controller.Categories()){
			var element_id = FindElementIdFromCategory(category_controller.Categories()[filter_index]);
			$('#'+element_id).removeClass('switch-off');
			$('#'+element_id).addClass('switch-on');
		}
	}

	function ToggleCategory(element,category) {
//console.log("InteractionController::ToggleCategory");
		$('#filter li').removeClass('switch-on');
		$('#filter li:not(:first-child)').addClass('switch-off');

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

		marker_controller.marker_state_controller.Event({name:'click_category'});

		place_controller.Show(map_controller.home_lat,map_controller.home_long);
		marker_controller.AddHomeMarker(map_controller.home_lat, map_controller.home_long, map_controller.GoHome);
	}

	this.MenuClick = function() {
//console.log("InteractionController::MenuClick");
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

function MarkerController() {
	var overlays = [];

	this.marker_state_controller = new MarkerStateController();

	this.AddMarker = function(place,id,name,lat,lon,callback) {
//console.log("MarkerController::AddMarker()");
		var overlay;
		
		var pin_html = "<div class='marker-pin'>"+name+"</div>";
		overlay = new CustomMarker(
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

		var bubble_html = "<div id='bubble"+id+"' class='marker-bubble-centre'>"+info_html+"</div>";
		overlay = new CustomMarker(
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
//console.log("MarkerController::AddHomeMarker");
		var marker_html = "<div class='marker-home'></div>";
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
//console.log("MarkerController::RemoveAll");
		for (index in overlays) {
			overlays[index].remove();
			delete overlays[index];
		}
		overlays = [];
		this.marker_state_controller.Reset();
		map_controller.Initialise();
		// alert('RemoveAll finished');
	}

	function CreateInfoBox(place) {
//console.log("MarkerController::CreateInfoBox");
		var html_array = [];

		var left_box_html_array = [];
		left_box_html_array.push(Tag('h1',place.name));

		if (place.category.join) {
			left_box_html_array.push(place.category.map(function(content){return Tag('div',content,{class:'category_item'});}).join('') );
		}

		if (place.address.join) {
			left_box_html_array.push( Tag('div',place.address.join(', '),{class:'address'}) );
		}

		html_array.push(Tag('div',left_box_html_array.join(''),{class:'bubble_left_box'}));
		html_array.push(Tag('img','',{src:place.image_url,class:'square'}));
		
		return html_array.join('');
	}

	this.Move = function(place_id) {
		map_controller.StartMove(place_id);
	}
}

function MarkerStateController(){
	var previous_id = undefined;
	var ignore_next_click_map = false;

	this.SelectedPlaceId = function() {
		//alert(previous_id);
		return previous_id;
	}
	
	this.Reset = function() {
//console.log("MarkerStateController::Reset");
		previous_id = undefined;
		ignore_next_click_map = false;
	}
	this.Event = function(info) {
//console.log("MarkerStateController::Event");
		switch (info.name) {
			case 'click_marker':
				ignore_next_click_map = true;
				if (previous_id === undefined) {
					document.getElementById("place_"+info.id).scrollIntoView();
					previous_id = info.id;
					var place = place_controller.GetPlaceById(info.id);
					var adjust = map_controller.AdjustToStayOnMap(place.latitude,place.longitude);
					adjust_lat_lon = adjust[0];
					map_controller.SetCentre(adjust_lat_lon[0],adjust_lat_lon[1]);
					ShowBubble(info.id,'marker-bubble-'+adjust[1]);
					
				} else if (info.id === previous_id) {
					HideBubble(info.id);
					previous_id = undefined;
				} else {
					document.getElementById("place_"+info.id).scrollIntoView();
					HideBubble(previous_id);
					var place = place_controller.GetPlaceById(info.id);
					var adjust = map_controller.AdjustToStayOnMap(place.latitude,place.longitude);
					adjust_lat_lon = adjust[0];
					map_controller.SetCentre(adjust_lat_lon[0],adjust_lat_lon[1]);						
					ShowBubble(info.id,'marker-bubble-'+adjust[1]);
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
				var place = place_controller.GetPlaceById(info.id);
				map_controller.SetCentre(place.latitude,place.longitude);
				adjust = map_controller.AdjustToStayOnMap(place.latitude,place.longitude);
				adjust_lat_lon = adjust[0];
				map_controller.SetCentre(adjust_lat_lon[0],adjust_lat_lon[1]);
				ShowBubble(info.id,'marker-bubble-'+adjust[1]);
				
				previous_id = info.id;
				break;
			case 'click_category':
				if (previous_id !== undefined) {
					HideBubble(previous_id);
					previous_id = undefined;
				}
				break;
		}
	}

	var ShowBubble = function(id,position_class) {
//console.log("MarkerStateController::ShowBubble");
		var bubble = document.getElementById("bubble"+id);
		if (position_class) {
			bubble.className = position_class;
		}
		bubble.style.display="inherit";
	}

	var HideBubble = function(id) {
//console.log("MarkerStateController::HideBubble");
		document.getElementById("bubble"+id).style.display="none";
	}
}

function AjaxController() {
	var async_callback;
	var xmlhttp;
	if (window.XMLHttpRequest) {
		xmlhttp=new XMLHttpRequest();
	} else {
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			if (async_callback != undefined) {
				try {
					//alert(xmlhttp.responseText);
					var response = JSON.parse(xmlhttp.responseText);
				} catch (err) {
					alert(xmlhttp.responseText);
					return;
				}
				async_callback(response);
			}
		}
	};

	this.Message = function (method,value,id) {
//console.log("AjaxController::Message");
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
	
	this.MessageAsync = function (callback, method, value, id) {
		async_callback = callback;
		xmlhttp.open("GET","data.php?method="+method+"&id="+id+"&value="+value+"&date="+Date.now(),true);
		xmlhttp.send();	
	}
}

function PlacesController() {
	var self = this;
	var overlays = [];
	
	var places = [];

	this.ShowAtCurrentPosition = function () {
		self.Show(map_controller.centre_lat,map_controller.centre_long);
		marker_controller.AddHomeMarker(map_controller.home_lat, map_controller.home_long, map_controller.GoHome);
	}
	
	this.Cluster = function () {
		var indeces_lat = places.map(function(o,k,n){var pos=map_controller.GetMapCartesian(o.latitude,o.longitude); o.x=pos[0]; o.y=pos[1]; return k;});
		var indeces_long = places.map(function(o,k,n){return k;});
		
		indeces_lat.sort(function(a,b){return places[a].latitude-places[b].latitude;});
		indeces_long.sort(function(a,b){return places[a].longitude-places[b].longitude;});
		
		var tolerance = 100 // pixels
		var tolerance2 = tolerance*tolerance;
		var check = 0;
		var next = 1;
		var group = 0;
		var max = indeces_lat.length-1;
		
		while (next<=max) {
			var dx = places[next].x-places[check].x;
			if (dx > tolerance) {
				check = next;
				next++;
				group++;
			} else {
				dy = places[next].y-places[check].y;
				dist = dx*dx+dy*dy;
				if (dist > tolerance2) {
					next++;
				} else {
					//alert(check);
					places[check].group = group;
					places[next].group = group;
					check = next;
					next++;
				}
			}
		}
		
		//alert(JSON.stringify(indeces_lat));
	}
	
	this.Show = function (this_lat,this_long) {
//console.log("PlacesController::Show");
		var categories = category_controller.Categories();
		
		places = ajax_controller.Message('GetPlaces',JSON.stringify({categories:categories,position:[this_lat,this_long]}) );
		if (places.error) {
			alert(places.error);
			return;
		}

		places = Object.keys(places).map(function(k) { return places[k] });
		
		//self.Cluster();
		
		marker_controller.RemoveAll();
		list_controller.RemoveAll();

		var place_list = document.getElementById('place_list');

		var marker_index = 1;

		if (typeof(mobile) !== 'undefined') {
			$('#place_list').width(mobile_list_width = places.length*580);
		}
		
		for (var index in places) {
			// if (place_limit!=-1 && index>=place_limit) continue;
			var place = places[index];

			marker_controller.AddMarker(place,place.id,marker_index+(place.group?String.fromCharCode(place.group+65):''),place.latitude,place.longitude,
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
			
			document.getElementById("opening_times_"+place.id).addEventListener("click",
				function(place_id){
					return function() {
						place_controller.ShowInfo({category:"opening_times",id:place_id});
					};
				}(place.id)
			);
			document.getElementById("opening_times_"+place.id).addEventListener("mouseleave",
				function(place_id) {
					return function(){
						place_controller.HideAllInfo({id:place_id});
					};
				}(place.id)			
			);
			document.getElementById("entry_rates_"+place.id).addEventListener("click",
				function(place_id){
					return function() {
						place_controller.ShowInfo({category:"entry_rates",id:place_id});
					};
				}(place.id)
			);
			document.getElementById("entry_rates_"+place.id).addEventListener("mouseleave",
				function(place_id) {
					return function(){
						place_controller.HideAllInfo({id:place_id});
					};
				}(place.id)			
			);			
			document.getElementById("more_info_"+place.id).addEventListener("click",
				function(place_id){
					return function() {
						place_controller.ShowInfo({category:"more_info",id:place_id});
					};
				}(place.id)
			);
			document.getElementById("more_info_"+place.id).addEventListener("mouseleave",
				function(place_id) {
					return function(){
						place_controller.HideAllInfo({id:place_id});
					};
				}(place.id)			
			);				
			document.getElementById("facilities_"+place.id).addEventListener("click",
				function(place_id){
					return function() {
						place_controller.ShowInfo({category:"facilities",id:place_id});
					};
				}(place.id)
			);		
			document.getElementById("facilities_"+place.id).addEventListener("mouseleave",
				function(place_id) {
					return function(){
						place_controller.HideAllInfo({id:place_id});
					};
				}(place.id)			
			);					
			document.getElementById("disabled_"+place.id).addEventListener("click",
				function(place_id){
					return function() {
						place_controller.ShowInfo({category:"disabled",id:place_id});
					};
				}(place.id)
			);
			document.getElementById("disabled_"+place.id).addEventListener("mouseleave",
				function(place_id) {
					return function(){
						place_controller.HideAllInfo({id:place_id});
					};
				}(place.id)			
			);					
			document.getElementById("good_stuff_"+place.id).addEventListener("click",
				function(place_id){
					return function() {
						place_controller.ShowInfo({category:"good_stuff",id:place_id});
					};
				}(place.id)
			);
			document.getElementById("good_stuff_"+place.id).addEventListener("mouseleave",
				function(place_id) {
					return function(){
						place_controller.HideAllInfo({id:place_id});
					};
				}(place.id)			
			);					
			document.getElementById("bad_stuff_"+place.id).addEventListener("click",
				function(place_id){
					return function() {
						place_controller.ShowInfo({category:"bad_stuff",id:place_id});
					};
				}(place.id)
			);	
			document.getElementById("bad_stuff_"+place.id).addEventListener("mouseleave",
				function(place_id) {
					return function(){
						place_controller.HideAllInfo({id:place_id});
					};
				}(place.id)			
			);						
			
			var edit_element = document.getElementById("edit_"+place.id);
			if (edit_element) {
				edit_element.addEventListener("click",
					function(place_id){
						return function() {
							place_controller.ShowEditPage(place_id);
						};
					}(place.id)
				);
			}
			
			var move_element = document.getElementById("move_"+place.id)
			if (move_element) {
				move_element.addEventListener("click",
					function(place_id){
						return function() {
							marker_controller.Move(place_id);
						};
					}(place.id)
				);
			}
			
			marker_index++;
		}
	}

	this.GetPlaceById = function (id) {
//console.log("PlacesController::GetPlaceById");
		var place_id = places.map(function(e){return e.id;}).indexOf(id);
		return places[place_id];
	}
	
	this.ShowInfo = function (info) {
		switch (info.category) {
			case "opening_times":
				var info_element = document.getElementById("opening_times_info_"+info.id);
				info_element.style.display="block";
				info_element.style.position="absolute";
				info_element.style.top=(info_element.parentNode.offsetHeight-2)+"px";
				info_element.style.left="-1px";
				break;
			case "entry_rates":
				var info_element = document.getElementById("entry_rates_info_"+info.id);
				info_element.style.display="block";
				info_element.style.position="absolute";
				info_element.style.top=(info_element.parentNode.offsetHeight-2)+"px";
				info_element.style.left="-1px";				
				break;
			case "more_info":
				var info_element = document.getElementById("more_info_info_"+info.id);
				info_element.style.display="block";
				info_element.style.position="absolute";
				info_element.style.top=(info_element.parentNode.offsetHeight-2)+"px";
				info_element.style.left="-1px";
				break;
			case "facilities":
				var info_element = document.getElementById("facilities_info_"+info.id);
				info_element.style.display="block";
				info_element.style.position="absolute";
				info_element.style.top=(info_element.parentNode.offsetHeight-2)+"px";
				info_element.style.left="-1px";
				break;				
			case "disabled":
				var info_element = document.getElementById("disabled_info_"+info.id);
				info_element.style.display="block";
				info_element.style.position="absolute";
				info_element.style.top=(info_element.parentNode.offsetHeight-2)+"px";
				info_element.style.left="-1px";
				break;				
			case "good_stuff":
				var info_element = document.getElementById("good_stuff_info_"+info.id);
				info_element.style.display="block";
				info_element.style.position="absolute";
				info_element.style.top=(info_element.parentNode.offsetHeight-2)+"px";
				info_element.style.left="-1px";
				break;	
			case "bad_stuff":
				var info_element = document.getElementById("bad_stuff_info_"+info.id);
				info_element.style.display="block";
				info_element.style.position="absolute";
				info_element.style.top=(info_element.parentNode.offsetHeight-2)+"px";
				info_element.style.left="-1px";
				break;					
		}
	}
	
	this.HideAllInfo = function (info) {
//console.log("HideAllInfo");
		document.getElementById("opening_times_info_"+info.id).style.display = "none";
		document.getElementById("entry_rates_info_"+info.id).style.display = "none";
		document.getElementById("more_info_info_"+info.id).style.display = "none";
		document.getElementById("facilities_info_"+info.id).style.display = "none";
		document.getElementById("disabled_info_"+info.id).style.display = "none";
		document.getElementById("good_stuff_info_"+info.id).style.display = "none";
		document.getElementById("bad_stuff_info_"+info.id).style.display = "none";
	}
	
	this.ShowEditPage = function (place_id) {
		window.open('entry.php?id='+place_id,'_blank');
	}
}

function ListController() {
	this.CreatePlaceListItem = function(place,marker_index) {
//console.log("ListController::CreatePlaceListItem");
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
//console.log("ListController::RemoveAll");
		$('#place_list > div').not(':first').remove();
	}
}

function MapController(centre_lat,centre_long) {
	var self = this;
	this.map_box = undefined;
	this.google_map = undefined;

	this.home_lat = undefined;
	this.home_long = undefined;
	this.centre_lat = centre_lat;
	this.centre_long = centre_long;
	this.zoom = 14;
	this.zooms_x = [1,   1,   2.75, 5.5, 11,   22.734375, 45.46875, 90.9375, 181.875, 363.75, 727.5, 1455, 2910,  5820, 11650, 23300,  46560, 93120,  186240, 372480, 744960];
	this.zooms_y = [1.6, 1.6, 4.4,  8.8, 17.6, 36.375,    72.75,    145.5,   291,     582,    1164,  2328, 4656,  9300, 18600, 37200,  74400, 148800, 297600, 595200, 1190400];
	
	var moving = false;
	var move_place_id = undefined;
	
	this.GetMapCartesian = function(lat,lon) {
		return [self.map_box.offsetWidth/2+(lon-self.centre_long)*self.zooms_x[self.zoom],self.map_box.offsetHeight/2-(lat-self.centre_lat)*self.zooms_y[self.zoom]];
	}
	
	this.GetMapLatLon = function(x,y) {
		return [(y-self.map_box.offsetHeight/2)/self.zooms_y[self.zoom]+self.centre_lat,(x-self.map_box.offsetWidth/2)/self.zooms_x[self.zoom]+self.centre_long];
	}
	
	this.AdjustToStayOnMap = function(lat,lon) {
		var position = 'centre';
		
		var cartesian = self.GetMapCartesian(lat,lon);
		shift = [0,0];
		if (cartesian[1]<340) { // shift down
			shift[1] = 340-cartesian[1];
		}
		
		if (cartesian[1]>(self.map_box.offsetHeight-20)) { // shift up
			shift[1] = (self.map_box.offsetHeight-20)-cartesian[1];
		}
		
		if (cartesian[0]<210) { // shift right
			shift[0] = cartesian[0]-130;
			position = 'left';
		}
		
		if (cartesian[0]>(self.map_box.offsetWidth-210)) { // shift left
			shift[0] = cartesian[0]-(self.map_box.offsetWidth-130);
			position = 'right';
		}
		
		var new_lat_lon = self.GetMapLatLon(self.map_box.offsetWidth/2+shift[0],self.map_box.offsetHeight/2+shift[1]);
		return [new_lat_lon,position];
	}
	
	
	this.StartMove = function(place_id) {
		moving = true;
		move_place_id = place_id
		self.google_map.setOptions({draggableCursor:'crosshair'});
	}
	
	this.EndMove = function(latlong) {
		if (moving) {
			////console.log(latlong);
			moving = false;
			self.google_map.setOptions({draggableCursor:'grab'});
			window.open('entry.php?id='+move_place_id+'&lat='+latlong[0]+'&long='+latlong[1],'_blank');
			return true;
		}
		return false;
	}
	
	this.CentreChanged = function() {
//console.log("MapController::CentreChanged");
		var centre = self.google_map.getCenter();

		self.centre_lat = centre.lat();
		self.centre_long = centre.lng();
	}

	this.SetCentre = function(lat,lon) {
//console.log("MapController::SetCentre");
		self.google_map.setCenter(new google.maps.LatLng(lat, lon));
	}

	this.GoHome = function() {
		self.SetCentre(self.home_lat,self.home_long);
		
	}
	
	this.ZoomChanged = function() {
//console.log("MapController::ZoomChanged");
		self.zoom = self.google_map.getZoom();
	}

	this.Initialise = function () {
//console.log("MapController::Initialise");	
				
		tony = [{"featureType": "landscape","elementType": "all","stylers": [{"hue": "#ffbb00"},{"saturation": "27"},{"lightness": 37.6},{"gamma": 1}]},{"featureType": "poi","elementType": "all","stylers": [{"hue": "#00ff6a"},{"saturation": "-2"},{"lightness": "11"},{"gamma": "0.95"},{"visibility": "on"}]},{"featureType": "poi","elementType": "labels","stylers": [{"visibility": "off"}]},{"featureType": "road.highway","elementType": "all","stylers": [{"hue": "#ffc200"},{"saturation": -61.8},{"lightness": 45.6},{"gamma": 1},{"visibility": "simplified"}]},{"featureType": "road.arterial","elementType": "all","stylers": [{"hue": "#ff0300"},{"saturation": -100},{"lightness": 51.2},{"gamma": 1}]},{"featureType": "road.local","elementType": "all","stylers": [{"hue": "#ff0300"},{"saturation": -100},{"lightness": 52},{"gamma": 1}]},{"featureType": "water","elementType": "all","stylers": [{"hue": "#0078ff"},{"saturation": "-3"},{"lightness": "34"},{"gamma": 1}]}];

		var mapOptions = {
			center: new google.maps.LatLng(self.centre_lat, self.centre_long),
			zoom: self.zoom,
			minZoom: map_min_zoom,
			maxZoom: 18,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			mapTypeControl: map_type_control,
			streetViewControl: false,
			navigationControl: false,
			scaleControl: true,
			styles: tony
		}
		self.map_box = document.getElementById('map_box');
		self.google_map = new google.maps.Map(self.map_box, mapOptions);

		google.maps.event.addListener(self.google_map, 'zoom_changed', self.ZoomChanged);
		google.maps.event.addListener(self.google_map, 'center_changed', self.CentreChanged);
		google.maps.event.addListener(self.google_map, 'click',
			function(event) {
				if (map_controller.EndMove([event.latLng.lat(),event.latLng.lng()]) ) return;
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
//console.log("ConfigurationController::Initialise");
		map_controller.Initialise();

		var options = {
		  enableHighAccuracy: true,
		  timeout: 20000,
		  maximumAge: 10000
		};
		navigator.geolocation.getCurrentPosition(LocationSuccess, LocationError, options);

		$('#filter li:not(:first-child)').addClass('switch-off');
		$('#filter-all').removeClass('switch-off');
		$('#filter-all').addClass('switch-on');
		$('#filter-more').removeClass('switch-off');

		$('li').click(interaction_controller.MenuClick);
		
		search_controller = new SearchController();
	}


	function LocationSuccess(location_info) {
//console.log("ConfigurationController::LocationSuccess");
		map_controller.home_lat = location_info.coords.latitude;
		map_controller.home_long = location_info.coords.longitude;
		map_controller.SetCentre(map_controller.home_lat, map_controller.home_long);
		place_controller.Show(map_controller.home_lat,map_controller.home_long);
		marker_controller.AddHomeMarker(map_controller.home_lat, map_controller.home_long, map_controller.GoHome);
	};

	function LocationError(error) {
//console.log("ConfigurationController::LocationError");
		if (attempts < max_attempts) {
			self.Initialise();
			attempts++;
		} else {
			alert("Unable to get your location.\nPlease try and reload page.");
		}
	};

}

function SearchController() {
	var self = this;
	var ok_to_request = true;
	
	$('#search').keyup(function() {self.Search();});
	$('#search').click(function() {self.Search();});
	
	this.Search = function() {
		var search_for = $('#search').val();
		if (search_for.length >= 3 && ok_to_request) {
			ok_to_request = false;
			search_ajax_controller.MessageAsync(self.PopulateSearchResult,'Search',JSON.stringify({search:search_for}) ); // need callback as this is too slow for synchronous
		}
	};
	
	this.PopulateSearchResult = function(found) {
		ok_to_request = true;
		found = Object.keys(found).map(function(k) { return found[k] });
		var search_results_div = document.getElementById('search_results');
		search_results_div.innerHTML = '';
		for (i in found) {
			var place = found[i];
				// alert(place);
			var div = document.createElement('div');
			div.id = 'found_' + place[0];
			div.dataset.id = place[0];
			div.className = 'found_list_item';
			div.innerHTML = place[1];				
			
			div.addEventListener("click",
				function(my_place) {
					return function(){
						$('#search_results').hide();
						map_controller.SetCentre(my_place[2],my_place[3]);
						place_controller.ShowAtCurrentPosition();
						marker_controller.marker_state_controller.Event( {name:'click_list',id:my_place[0]} );
					};
				}(place)
			);
		
			search_results_div.appendChild(div);				
		}
		
		$('#search_results').show();
	};
}

var configuration_controller = new ConfigurationController();
var place_controller = new PlacesController();
var marker_controller = new MarkerController();
var list_controller = new ListController();
var interaction_controller = new InteractionController();
var category_controller = new CategoryController();
var map_controller = new MapController(51.5072,-0.1275);
var ajax_controller = new AjaxController();
var search_ajax_controller = new AjaxController();
var search_controller;

google.maps.event.addDomListener(window, 'load', configuration_controller.Initialise);