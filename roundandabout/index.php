<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width">
		<script src="https://maps.googleapis.com/maps/api/js"></script>
		<script>
			var unixoffset = <?php echo time();?>;
			var map;
			var centre_lat = 70; // 51.147101948513985; //51.1513;
			var centre_long = -0.17337799072265625; //-0.1866;
			var zoom_flights_x;
			var zoom_flights_y;
			var zoom_google = 14;

			var zooms_x = [1,   1,   2.75, 5.5, 11,   22.734375, 45.46875, 90.9375, 181.875, 363.75, 727.5, 1455, 2910,  5820, 11650, 23300,  46560, 93120,  186240, 372480, 744960];
			var zooms_y = [1.6, 1.6, 4.4,  8.8, 17.6, 36.375,    72.75,    145.5,   291,     582,    1164,  2328, 4656,  9300, 18600, 37200,  74400, 148800, 297600, 595200, 1190400];


			var canvas;
			var context;

			
			var screen_width;
			var screen_height;
			
			var initial_latlong;

			var places = [];
			
			Number.prototype.toRad = function() {
			   return this * Math.PI / 180;
			}

			function DistanceBetween(latlong1,latlong2) {
				var lat2 = latlong2[0];
				var lon2 = latlong2[1];
				var lat1 = latlong1[0];
				var lon1 = latlong1[1];

				var R = 3959; // miles 
				//has a problem with the .toRad() method below.
				var x1 = lat2-lat1;
				var dLat = x1.toRad();  
				var x2 = lon2-lon1;
				var dLon = x2.toRad();  
				var a = Math.sin(dLat/2) * Math.sin(dLat/2) + 
								Math.cos(lat1.toRad()) * Math.cos(lat2.toRad()) * 
								Math.sin(dLon/2) * Math.sin(dLon/2);  
				var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
				var d = R * c; 
				
				return d;
			}
			
			
			function location_success(pos) {
				var crd = pos.coords;
				initial_latlong = [crd.latitude, crd.longitude];
				SetCentre(crd.latitude,crd.longitude);
			};

			function location_error(err) {		  
			};

			function Initialize() {
				var options = {
				  enableHighAccuracy: true,
				  timeout: 5000,
				  maximumAge: 0
				};
				navigator.geolocation.getCurrentPosition(location_success, location_error, options)


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

				Render();
				//setInterval(function(){UpdateFlights();RenderAll();}, 500);
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
				//zoom_flights_x = zooms_x[new_zoom_google];
				//zoom_flights_y = zooms_y[new_zoom_google];				

				//zoom_google = new_zoom_google;
				//document.getElementById("info").innerHTML = zoom_google;
			}

			google.maps.event.addDomListener(window, 'load', Initialize);


			function Render() {
				//AddMarker(51,0,'test1',MarkerClicked);
				//AddMarker(51,0.1,'test2',MarkerClicked);
				
				places = Ajax('GetPlaces');
				if (places.error) {
					alert(places.error);
					return;
				}
				
				var place_list = document.getElementById('place_list');
				
				for (var index in places) {
					var place = places[index];
					AddMarker(place.id,place.name,place.latitude,place.longitude,MarkerClicked);
					
					// Place List Item
					var div = document.createElement('div');
					div.id = 'place_' + place.id;
					div.dataset.id = place.id;
					div.className = 'place_list_item';
					div.innerHTML = CreateInfoBox(place);
					div.addEventListener("click", function(){
						SetCentre(places[this.dataset.id].latitude,places[this.dataset.id].longitude);
					});
					place_list.appendChild(div);
				}
			}
			
			function CreateInfoBox(place) {
				var html_array = [];
				html_array.push(Tag('h1',place.name));
				if (place.category.join) {
					html_array.push( place.category.map(function(content){return Tag('div',content,{class:'category_item'});}).join('') );
				}
				
				if (place.address.join) {
					html_array.push( Tag('div',place.address.join(', '),{class:'address'}) );
				}
				
				if (place.telephone.join) {
					html_array.push( Tag('div',place.telephone.join(', '),{class:'address'}) );
				}
				html_array.push('Opening Times:');
				if (place.opening_times.join) {
					html_array.push( Tag('div',place.opening_times.join(', '),{class:'address'}) );
				}
				html_array.push('Entry Rates:');
				if (place.entry_rates.join) {
					html_array.push( Tag('div',place.entry_rates.join(', '),{class:'address'}) );
				}				
				
				return html_array.join('');
			}

			function MarkerClicked(marker) {
				//alert('marker clicked:'+marker.id);
				OverlayOn();
				document.getElementById('place_'+marker.id).scrollIntoView();
				SetCentre(places[marker.id].latitude,places[marker.id].longitude);
			}
			
			function AddMarker(id,name,lat,lon,callback) {
				var map_box = document.getElementById('google_map');
				
				var marker = new google.maps.Marker({
					position: {lat:lat, lng:lon},
					map: map,
					title: name,
					icon: 'resources/Green-Pin.png'
				});
				marker.id = id;
				marker.addListener('click', function() {callback(this)});				
			}
			
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
			
			/*
			function ConvertLatLonToXY(key) {
				var lat = flights[key][LAT];
				var lon = flights[key][LON];
				var pos = GetCanvasPosition(lat,lon);
				flights[key][GRAPH_X] = pos[0];
				flights[key][GRAPH_Y] = pos[1];			
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
				Render();
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
			html {
				height:96%;
			}
			body {
				height:100%;
			}
			
			#map_box {
				position:absolute;
				left:0px;
				top:0px;				
				width: 100%;
				height: 100%;
				background-color: #CCC;
			}
			
			#overlay {
				position:fixed;
				left:0px;
				top:0px;
				height:100%;
				width:100%;
				background-color:rgba(0, 0, 0, 0.7);
			}
			
			#place_list {
				width:80%;
				height:100%;
				margin:auto;
				overflow-y:scroll;
				overflow-x:hidden;
			}
			
			.place_list_item {
				width:90%;
				/*height:400px;*/
				background-color: white;
				border:1px solid black;
				margin-bottom: 15px;
				padding:3px;
				cursor:pointer;
			}
			
			.blur {
				-webkit-filter: blur(2px);
				-moz-filter: blur(2px);
				-o-filter: blur(2px);
				-ms-filter: blur(2px);			
			}
			
			.overlay-off {
				display:none;
			}
			.overlay-on {
				display:default;
			}
			
			.h1 {
			}
			
			.category_item {
				display:inline-block;
				background-color:blue;
				color:white;
				padding:0px 3px 0px 3px;
				margin:3px;
			}
			
			.address {
				border:1px solid black;
			}
			
			.description {
				border:1px solid black;
			}
		</style>
	</head>
	<body>
		<div id="map_box"></div>
		<div id="overlay" onclick="OverlayOff();" class="overlay-off">
			<div id="place_list"><div>
		</div>
	</body>

</html>