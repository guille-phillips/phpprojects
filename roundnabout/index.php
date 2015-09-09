<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<script src="https://maps.googleapis.com/maps/api/js"></script>
		<script src="javascript/custom-google-map-marker.js"></script>
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
			
			var marker_resource = 'resources/pin-144ppi.png';
			var home_marker_resource = 'resources/home-marker.png';
			
			var previous_marker_id = undefined;

			function LocationSuccess(pos) {
				var crd = pos.coords;
				initial_latlong = [crd.latitude, crd.longitude];
				SetCentre(crd.latitude,crd.longitude);
				//AddMarker('home','You are here',crd.latitude,crd.longitude,home_marker_resource,HomeMarkerClicked)
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
				places = Ajax('GetPlaces');
				if (places.error) {
					alert(places.error);
					return;
				}
				
				var place_list = document.getElementById('place_list');
				
				var marker_index = 1;
				for (var index in places) {
					var place = places[index];

					AddMarker(place, place.id,marker_index,place.latitude,place.longitude,marker_resource,MarkerClicked);
					
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
					} else {
						previous_marker_id = undefined;
					}
				} else {
					document.getElementById("bubble"+marker.dataset.marker_id).style.display="inherit";
				}
				
				previous_marker_id = marker.dataset.marker_id;
			}
			
			function AddMarker(place, id,name,lat,lon,resource,callback) {
				console.log('AddMarker:'+id);
				
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
			
			/*
			function ConvertLatLonToXY(key) {
				var lat = flights[key][LAT];
				var lon = flights[key][LON];
				var pos = GetCanvasPosition(lat,lon);
				flights[key][GRAPH_X] = pos[0];
				flights[key][GRAPH_Y] = pos[1];			
			}
			*/
									
		</script>
		<style>
			
		</style>
	</head>
	<body>
		<div id="map_box"></div>
		<div id="overlay" onclick="OverlayOff();" class="overlay-off">
			<div id="place_list"><div>
		</div>
	</body>

</html>