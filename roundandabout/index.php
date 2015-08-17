<!DOCTYPE html>
<html>
	<head>
		<script src="https://maps.googleapis.com/maps/api/js"></script>
		<script>
			var unixoffset = <?php echo time();?>;
			var map;
			var centre_lat = 70; // 51.147101948513985; //51.1513;
			var centre_long = -0.17337799072265625; //-0.1866;
			var zoom_flights_x;
			var zoom_flights_y;
			var zoom_google = 12;

			var zooms_x = [1,   1,   2.75, 5.5, 11,   22.734375, 45.46875, 90.9375, 181.875, 363.75, 727.5, 1455, 2910,  5820, 11650, 23300,  46560, 93120,  186240, 372480, 744960];
			var zooms_y = [1.6, 1.6, 4.4,  8.8, 17.6, 36.375,    72.75,    145.5,   291,     582,    1164,  2328, 4656,  9300, 18600, 37200,  74400, 148800, 297600, 595200, 1190400];


			var canvas;
			var context;

			
			var screen_width;
			var screen_height;
			
			var initial_latlong;

			
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


				var mapCanvas = document.getElementById('google_map');
				var info_panel = document.getElementById('info');

				screen_width = window.innerWidth-200;
				screen_height = window.innerHeight;
					
				mapCanvas.style.width = window.innerWidth-200+'px';
				mapCanvas.style.height = window.innerHeight+'px';

				info_panel.style.height = window.innerHeight-2;

				window.onresize = function(event) {
					screen_width = window.innerWidth-200;
					screen_height = window.innerHeight;
					mapCanvas.style.width = window.innerWidth-200+'px';
					mapCanvas.style.height = window.innerHeight+'px';
					info_panel.style.height = window.innerHeight-2;								
				};

				var mapOptions = {
					center: new google.maps.LatLng(centre_lat, centre_long),
					zoom: zoom_google,
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					streetViewControl: false,
					navigationControl: false
				}
				map = new google.maps.Map(mapCanvas, mapOptions);

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


			function AddMarker(lat,lon,name,callback) {
				var mapCanvas = document.getElementById('google_map');
				
				var marker = new google.maps.Marker({
					position: {lat:lat, lng:lon},
					map: map,
					title: name
				});	
				marker.addListener('click', function() {callback(name)});				
			}
			
			function List() {
				//document.getElementById('info').innerHTML = '';
			}

			
			function Render() {
				AddMarker(51,0,'test1',MarkerClicked);
				AddMarker(51,0.1,'test2',MarkerClicked);
			}
			
			function MarkerClicked(param) {
				alert('marker clicked:'+param);
			}

			function ConvertLatLonToXY(key) {
				var lat = flights[key][LAT];
				var lon = flights[key][LON];
				var pos = GetCanvasPosition(lat,lon);
				flights[key][GRAPH_X] = pos[0];
				flights[key][GRAPH_Y] = pos[1];			
			}
			
            function Whole(number) {
            	return Math.floor(number+0.5);
            }

			function Pad(n, width, z) {
			  z = z || '0';
			  n = n + '';
			  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
			}
			
			function RenderAll() {
				Render();
			}

		</script>
		<style>
			#google_map {
				width: 1024px;
				height: 768px;
				background-color: #CCC;

				position:absolute;
				left:0px;
				top:0px;				
			}

			#info {
				position:absolute;
				right:11px;
				top:250px;
				/*width:200px;*/
			}

			.strip {
				background-color: #0000FF;
				color:white;
				width:160px;
				height:20px;
				margin-bottom: 4px;
				padding:3px;
				cursor:pointer;
			}
			
			.strip:hover {
				background-color: #0080FF;
			}

			#search_div {
				width:170px;
				height:25px;
				position:absolute;
				top:0px;
				right:5px;
			}

			label {
				display:block;
				position:absolute;
			}
		</style>		
	</head>
	<body>
		<div id="google_map"></div>
		<div id="search_div">
			<input id="search" onchange="ListFlights();" value="">
		</div>
		<div id="info"><div>
	</body>

</html>