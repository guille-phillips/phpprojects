<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" type="text/css" href="css/main.css.php">
		<script src="https://maps.googleapis.com/maps/api/js"></script>
		<script src="javascript/custom-google-map-marker.js"></script>
		<script src="javascript/jquery-2.1.4.min.js"></script>
		<script>
			var unixoffset = <?php echo time();?>;
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

			String.prototype.replaceBlock = function(search,list) {
				var pos = 0;
				var replaced = '';
				var search_start = '{'+search+'/}';
				var search_end = '{/'+search+'}';

				if (!isArray(list)) {
					list = [list];
				}
				
				while (pos!=-1) {
					if ((pos_start = this.indexOf(search_start,pos))>-1) {
						replaced += this.substr(pos,pos_start-pos);
						if ((pos_end = this.indexOf(search_end,pos_start))>-1) {
							var sub = this.substr(pos_start+search_start.length,pos_end-pos_start-search_start.length);
							for (var index in list) {
								replaced += sub.replace('{'+search+'}',list[index]);
							}
							pos = pos_end+search_end.length;
						} else {
							replaced += this.substr(pos_start,this.length-pos_start);
							pos = -1;
						}
					} else {
						replaced += this.substr(pos,this.length-pos);
						pos = -1;
					}
				}
				return replaced;
			}
			
			String.prototype.replaceTag = function(search,list) {
				var pos = 0;
				var replaced = '';
				var search_tag = '{'+search+'}';
				
				if (!isArray(list)) {
					list = [list];
				}
				
				while (pos!=-1) {
					if ((pos_next = this.indexOf(search_tag,pos))>-1) {
						replaced += this.substr(pos,pos_next-pos);
						replaced += list.join(', ');
						pos = pos_next+search_tag.length;
					} else {
						replaced += this.substr(pos,this.length-pos);
						pos = -1;
					}
				}
				return replaced;
			}

			var isArray = (function () {
				// Use compiler's own isArray when available
				if (Array.isArray) {
					return Array.isArray;
				} 
			 
				// Retain references to variables for performance
				// optimization
				var objectToStringFn = Object.prototype.toString, arrayToStringResult = objectToStringFn.call([]); 
			 
				return function (subject) {
					return objectToStringFn.call(subject) === arrayToStringResult;
				};
			}());
			
			function CreatePlaceListItem(place,marker_index) {
				var pl = document.getElementById('place_list').firstChild.innerHTML;
				
				pl = pl.replaceBlock('index',marker_index);
				pl = pl.replaceTag('index',marker_index);
					
				for (property in place) {
					pl = pl.replaceBlock(property,place[property]);
					pl = pl.replaceTag(property,place[property]);
				}
				
				return pl;
				
				// alert(pl);
				
				/*
				<div>
					<h1>{index}. {name}</h1>
					<div class="category_item">{category}</div><div class="category_item">Farm</div><div class="category_item">Animals</div><div class="category_item">Adventure</div><div class="category_item">Rides</div>
					<div class="address">{address}</div>
					<div class="telephone">{telephone}</div>
					Opening Times:<div class="opening_times">{opening_times}</div>
					Entry Rates:<div class="entry_rates">{entry_rates}</div>
				</div><img src="#">
				*/
				
				var html_array = [];

				html_array.push(Tag('h1',marker_index+'.'+place.name));

				if (place.category.join) {
					html_array.push( place.category.map(function(content){return Tag('div',content,{class:'category_item'});}).join('') );
				}

				if (place.address.join) {
					html_array.push( Tag('div',place.address.join(', '),{class:'address'}) );
				}

				if (place.telephone.join) {
					html_array.push( Tag('div',place.telephone.join(', '),{class:'telephone'}) );
				}
				html_array.push('Opening Times:');
				if (place.opening_times.join) {
					html_array.push( Tag('div',place.opening_times.join(', '),{class:'opening_times'}) );
				}
				html_array.push('Entry Rates:');
				if (place.entry_rates.join) {
					html_array.push( Tag('div',place.entry_rates.join(', '),{class:'entry_rates'}) );
				}
				
				html_array.push(Tag('img','',{class:'square'}));

				return html_array.join('');
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

            function Whole(number) {
            	return Math.floor(number+0.5);
            }

			function Pad(n, width, z) {
				z = z || '0';
				n = n + '';
				return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
			}

			function Tag(tag_name,content,attributes) {
				var html_attributes = [''];
				for (attribute in attributes) {
					html_attributes.push(attribute+'="'+attributes[attribute]+'"');
				}
				return '<'+tag_name+html_attributes.join(' ')+'>'+content+'</'+tag_name+'>';
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
				xmlhttp.open("GET","data.php?method="+method+"&id="+id+"&value="+value+"&date=<?php echo date("Y-m-d H:i:s");?>",false);
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
		</script>
		<style>

		</style>
	</head>
	<body>
		<div id="menu">
			<ul>
				<li id="menu-home">Home</li>
				<li id="menu-about-us">About Us</li>
				<li id="menu-upload-a-place">Upload a Place</li>
			</ul>
		</div>
		<div id="filter">
			<ul>
				<li id="filter-all">All</li>
				<li id="filter-free">Free</li>
				<li id="filter-paid">Paid</li>
				<li id="filter-indoor">Indoor</li>
				<li id="filter-outdoor">Outdoor</li>
				<li id="filter-animals-and-nature">Animals &amp; Nature</li>
				<li id="filter-water-fun">Water Fun</li>
				<li id="filter-rides">Rides</li>
				<li id="filter-transport">Transport</li>
				<li id="filter-more">More
					<ul>
						<li id="filter-play-centre">Play Centre</li>
						<li id="filter-history">History</li>
						<li id="filter-beaches">Beaches</li>
					</ul>
				</li>
			</ul>
		</div>
		<div id="logo">&nbsp;</div>
		<div id="map_box"></div>
		<div id="place_list"><div id="place_{id}" class="place_list_item" data-id="{id}">
				<div>
					<h1>{index}. {name}</h1>
					<div class="address">{address}</div>
					<div class="telephone">{telephone}</div>
					<div class="website">{website}</div>
					<div class="rating">{rating}</div>
					{category/}<div class="category_item">{category}</div>{/category}
					Opening Times:<div class="opening_times">{opening_times}</div>
					Entry Rates:<div class="entry_rates">{entry_rates}</div>
				</div><img src="#">
			</div>
		<div>
	</body>
</html>