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
			
			var previous_marker_id = undefined;

			var categories = [123,125];

			function MarkerState(){
				var previous_id = undefined;
				this.Event = function(info) {
alert(JSON.stringify(info));

					switch (info.name) {
						case 'click_marker':
							if (previous_id === undefined) {
								ShowBubble(info.id);
								previous_id = info.id;
							} else if (info.id === previous_id) {
								HideBubble(info.id);
								previous_id = undefined;
							} else {
								ShowBubble(info.id);
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
					}
				}

				var ShowBubble = function(id) {
					document.getElementById("bubble"+id).style.display="none";
				}

				var HideBubble = function(id) {
					document.getElementById("bubble"+id).style.display="none";

				}
			}

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
				//google.maps.event.addListener(map, 'click', function(event){document.getElementById("info").innerHTML = event.latLng;});
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
				map.addListener('click', function(){alert('click');});
			}

			function ZoomChanged() {
				new_zoom_google = map.getZoom();
				//zoom_flights_x = zooms_x[new_zoom_google];
				//zoom_flights_y = zooms_y[new_zoom_google];				

				//zoom_google = new_zoom_google;
				//document.getElementById("info").innerHTML = zoom_google;
			}

			google.maps.event.addDomListener(window, 'load', Initialize);


			function Render(categories) {
				places = Ajax('GetPlaces',JSON.stringify({categories:categories,position:[centre_lat,centre_long]}) );
				if (places.error) {
					alert(places.error);
					return;
				}

				// Sort by distance 
				var places = Object.keys(places).map(function(k) { return places[k] });
				places.sort(function(a,b){return a.distance-b.distance;});

				var place_list = document.getElementById('place_list');
				
				var marker_index = 1;
				for (var index in places) {
					var place = places[index];

					AddMarker(place,place.id,marker_index,place.latitude,place.longitude,marker_resource,function(){marker_state.Event({name:'click_marker',id:place.id});});
					
					// Place List Item
					var div = document.createElement('div');
					div.id = 'place_' + place.id;
					div.dataset.id = place.id;
					div.className = 'place_list_item';
					div.innerHTML = CreatePlaceListItem(place,marker_index);
					div.addEventListener("click", function(){
						SetCentre(places[this.dataset.id].latitude,places[this.dataset.id].longitude);
						if (previous_marker_id!==undefined) {
							document.getElementById("bubble"+previous_marker_id).style.display="none";
						}
						document.getElementById("bubble"+this.dataset.id).style.display="inherit";
						previous_marker_id = this.dataset.id;
						
					});
					place_list.appendChild(div);

					marker_index++;
				}
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

			function CreatePlaceListItem(place,marker_index) {
				var html_array = [];
				html_array.push(Tag('div',marker_index,{class:'place_list_marker_index'}));	
				
				html_array.push(Tag('h1',place.name+':'+place.distance));				
				

				html_array.push(Tag('img','',{class:'square'}));

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
				
				return html_array.join('');
			}

			function MarkerClicked(marker) {
				//alert('marker clicked:'+marker.dataset.marker_id);
				if (previous_marker_id!==undefined) {
					document.getElementById("bubble"+previous_marker_id).style.display="none";
					if (previous_marker_id !== marker.dataset.marker_id) {
						document.getElementById("bubble"+marker.dataset.marker_id).style.display="inherit";
						document.getElementById("place_"+marker.dataset.marker_id).scrollIntoView();
					} else {
						previous_marker_id = undefined;
					}
				} else {
					document.getElementById("bubble"+marker.dataset.marker_id).style.display="inherit";
				}
				
				previous_marker_id = marker.dataset.marker_id;
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

			function AddMarker(place, id,name,lat,lon,resource,callback) {
				// console.log('AddMarker:'+id);
				
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
			
			/*
			function List() {
				//document.getElementById('info').innerHTML = '';
			}

			
			function OverlayOn() {
				document.getElementById('map_box').className='blur';
				document.getElementById('overlay').className='overlay-on';
			}
			
			function OverlayOff() {
				//alert('overlayoff');
				document.getElementById('map_box').className='';
				document.getElementById('overlay').className='overlay-off';
			}
			*/

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
		<div id="place_list"><div>
	</body>
</html>