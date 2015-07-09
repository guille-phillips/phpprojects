<!DOCTYPE html>
<html>
	<head>
		<!--<script src="https://maps.googleapis.com/maps/api/js"></script>-->
		<!--<script src="vector2d.js"></script>-->
		<!--<script src="fixes.js"></script>-->
		<!--<script src="fixes2.js"></script>-->
		<script>
			var unixoffset = <?php echo time();?>;

			var centre_lat = 70; // 51.147101948513985; //51.1513;
			var centre_long = -0.17337799072265625; //-0.1866;

			var flights=[]; 
			var boxes = [];

			var update_count = 0;

			function history_datum(flight) {
				this.lat = flight.lat;
				this.lon = flight.lon;
				this.altitude = flight.altitude;
				this.speed = flight.speed;
				this.heading = flight.heading;
				this.unixtime = flight.unixtime;
			}
			
			function flight(information) {
				this.lat = parseFloat(information[1]);
				this.lon = parseFloat(information[2]);
				this.callsign_short = information[13];
				this.landed = information[14];
				this.callsign = information[16];
				this.heading = parseInt(information[3]);
				this.altitude = parseInt(information[4]);
				this.speed = parseInt(information[5]);
				this.squawk = information[6];
				this.origin = information[11];
				this.destination = information[12];
				this.reg = information[9];
				this.hex = information[0];
				this.model = information[8];
				this.altitude_rate = parseFloat(information[15]);
				this.unixtime = parseInt(information[10]);
				
				this.heading_est = this.heading;
				this.altitude_est = this.altitude;
				this.speed_est = this.speed;
				this.lon_orig = this.lon;
				this.lat_orig = this.lat;
				this.heading_rate_est = 0;
				this.speed_rate_est = 0;
				this.altitude_rate_est = this.altitude_rate;
				this.altitude_orig = this.altitude;
				this.distance = DistanceBetween(initial_latlong,[this.lat,this.lon]);;
				this.history = [new history_datum(this)];		
				this.dead = undefined;
			}
			
			var LAT = 1;
			var LON = 2;
			var CALLSIGN_SHORT = 13;
			var LANDED = 14;
			var CALLSIGN = 16;
			var HEADING = 3;
			var ALTITUDE = 4;
			var SPEED = 5;
			var SQUAWK = 6;
			var ORIGIN = 11;
			var DESTINATION = 12;
			var REG = 9;
			var HEX = 0;
			var MODEL = 8;
			var ALTITUDE_RATE = 15;
			var UNIXTIME = 10;
			var HEADING_EST = 23;
			var ALTITUDE_EST = 24;
			var SPEED_EST = 25;
			var LON_ORIG = 26;
			var LAT_ORIG = 27;
			var HEADING_RATE_EST = 29;
			var SPEED_RATE_EST = 30;
			var ALTITUDE_RATE_EST = 31;
			var ALTITUDE_ORIG = 28;
			var DISTANCE = 29;
			var HISTORY = 22;
			
			
			var highlighted_flight;

			var show_labels=false;
			var show_history=false;
			var show_fixes=true;
			var show_alt100=true;
			var show_alt1000=true;
			var show_alt5000=true;
			var show_alt10000=true;
			var show_alt20000=true;
			var show_alt40000=true;
			
			var screen_width;
			var screen_height;
			
			var initial_latlong;

			

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
			
			function initialize() {
				console.log("initialize");
				var options = {
				  enableHighAccuracy: true,
				  timeout: 5000,
				  maximumAge: 0
				};
				navigator.geolocation.getCurrentPosition(location_success, location_error, options);
				var info_panel = document.getElementById('info');
			}

			function location_success(pos) {
				var crd = pos.coords;
				initial_latlong = [crd.latitude, crd.longitude];
				GetFlights();
				setInterval(function(){UpdateFlights();RenderAll();}, 500);				
			}

			function location_error(err) {		
				// do nothing
			}

			function CentreChanged() {
				var origin = GetCanvasPosition(centre_lat,centre_long);

				var centre = map.getCenter();
		
				var offset = GetCanvasPosition(centre.lat(),centre.lng());

				for (key in boxes) {
					boxes[key][0] -= (offset[0]-origin[0]);
					boxes[key][1] -= (offset[1]-origin[1]);
				}

				centre_lat=centre.lat();
				centre_long=centre.lng();
			}

			function CheckAltitudeBand(altitude) {
				if (altitude>=20000) {
					return show_alt40000;
				} else if (altitude>=10000) {
					return show_alt20000;
				} else if (altitude>=5000) {
					return show_alt10000;
				} else if (altitude>=1000) {
					return show_alt5000;
				} else if (altitude>=100) {
					return show_alt1000;
				} else {
					return show_alt100;
				}
			}
			
			function ListFlights() {
				document.getElementById('info').innerHTML = '';
				/*
				var letters = document.getElementById('search').value.toUpperCase().split('');
				if (letters.length == 0) {
					return;
				}
				*/
				var strips = [];
				
				/*
				for (key in flights) {
					if (flights[key][CALLSIGN]!=undefined) {
						var callsignx = flights[key][CALLSIGN].split('');
						var ok = true;
						for (index in letters) {
							if (callsignx.indexOf(letters[index])==-1) {
								ok = false;
								break;
							}
						}
						if (ok) {
							strips.push([key,flights[key]);
						}
					}
				}
				*/
				
				for (key in flights) {
					var flight = flights[key];
					if (flight.distance<40) {
						strips.push(flight);
					}
				}
				
				strips.sort(function(a,b){return (a.distance<b.distance)?-1:1});

				document.getElementById('info').innerHTML = '';
				for (key in strips) {
					var strip = strips[key];
					
					var div = document.createElement('div');
					div.className = 'strip';
					div.innerHTML = 
						[strip.callsign,
						strip.distance.toFixed(1),
						strip.model,
						strip.reg,
						strip.origin+'-'+strip.destination,
						strip.altitude.toFixed(0),
						strip.speed,
						strip.heading
						].join("/");
					div.key = key;
					/*
					(function (thiskey,lat,lon) {
						div.onclick = function() {highlighted_flight=thiskey; SetCentre(lat,lon);};
					})(div.key,flights[div.key][LAT],flights[div.key][LON]);
					*/
					document.getElementById('info').appendChild(div);

				}
			}
			

			String.prototype.tag = function(tag_name){
				return "<"+tag_name+">"+this+"</"+tag_name+">";
			}
			
            Number.prototype.whole = function () {
            	return Math.floor(this+0.5);
            }

			Number.prototype.pad = function Pad(width, z) {
			  z = z || '0';
			  n = this + '';
			  return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
			}

			Number.prototype.toRad = function() {
			   return this * Math.PI / 180;
			}

			
			function ExtractHistory(history) {				
				var point = history.length-1;
				var count = 10;
				var altitude = [];
				var speed = [];
				var heading = [];
				var previous_heading;
				var this_heading;
				
				while (count>0 && point>=0) {
					altitude.push([history[point].unixtime-unixoffset,history[point].altitude]);
					speed.push([history[point].unixtime-unixoffset,history[point].speed]);
					
					this_heading = history[point].heading;
					if (previous_heading != undefined) {
						if (this_heading>=(previous_heading+180)) {
							this_heading -= 360;
						} else if (this_heading<=(previous_heading-180)) {
							this_heading += 360;
						}
					}
					heading.push([history[point].unixtime-unixoffset,this_heading]);
					previous_heading = this_heading;
					point--;
					count--;
				}
				
				current_altitude_rate = Regression(altitude,Date.now()/1000-unixoffset)[1];
				current_speed_rate = Regression(speed,Date.now()/1000-unixoffset)[1];
				current_heading_rate = Regression(heading,Date.now()/1000-unixoffset)[1];
				//if (current_heading<0) current_heading+=360;
				//if (current_heading>=360) current_heading-=360;
				
				return [current_altitude_rate,current_speed_rate,current_heading_rate];
			}
			
			function UpdateFlights() {
				for (var key in flights) {
					var flight = flights[key];
					var interval_new = Date.now()/1000-flight.unixtime;
					
					if (flight.history) {} else {console.log(key);return;}

					var estimates = ExtractHistory(flight.history);
					flight.altitude_rate_est = estimates[0];
					flight.speed_rate_est = estimates[1];
					flight.heading_rate_est = estimates[2];
					
					var heading = flight.heading;
					var speed = flight.speed;
					var speed_rate = flight.speed_rate_est;
					var speed_est = speed+speed_rate*interval_new;
					
					var heading_rate_est = flight.heading_rate_est;
					var heading_est = heading+heading_rate_est*interval_new;
					flight.speed_est = speed_est;
					flight.heading_est = heading_est;
					
					var speed_x = Math.cos((90-heading_est)*2*Math.PI/360)*(speed_est)*1852/3600; // metres per second
					var speed_y = Math.sin((90-heading_est)*2*Math.PI/360)*(speed_est)*1852/3600; // metres per second

					flight.lon = flight.lon_orig+interval_new*360*speed_x/(40000000*Math.cos(2*Math.PI*flight.lat_orig/360));
					flight.lat = flight.lat_orig+interval_new*360*speed_y/40000000;
					flight.distance = DistanceBetween(initial_latlong,[flight.lat,flight.lon]);

					flight.altitude = flight.altitude_orig+interval_new*flight.altitude_rate/60;
					if (flight.altitude < 0) {
						flight.altitude = interval_new*flight.altitude_rate/60;
						flight.speed -= (10)*interval_new;
						if (flight.speed<0) {
							flight.speed = 0;
						}
					}
				
					if (flights[key].dead) {
						if (flights[key][ALTITUDE]<100) {
							flights[key].dead--;
							if (flights[key].dead <= 0) {
								delete flights[key];
								delete boxes[key];
							}
						}
					}
				}
			}

			function LinesCross(v11,v12,v21,v22) {
				var v1 = v12.Sub(v11);
				var v121 = v21.Sub(v11);
				var v122 = v22.Sub(v11);

				var x121 = v1.Cross(v121);
				var x122 = v1.Cross(v122);
				
				if ((x121>0 && x122>0) || (x121<0 && x122<0)) {
					return false;
				}

				var v2 = v22.Sub(v21);
				var v211 = v11.Sub(v21);
				var v212 = v12.Sub(v21);
				
				var x211 = v2.Cross(v211);
				var x212 = v2.Cross(v212);
				
				if ((x211>0 && x212>0) || (x211<0 && x212<0)) {
					return false;
				}
				
				return true;
			}
			
			function RenderAll() {
				ListFlights();
				update_count++;
				if (update_count>5) {
					GetFlights();
					update_count = 0;
				}
			}

			function Regression(history, variable) {
				var sumx = 0;
				var sumy = 0;
				var sumxy = 0;
				var sumx2 = 0;
				var n = history.length;
				
				if (n==1) return [history[0][1],0,0];
				
				for (i in history) {
					sumx += history[i][0];
					sumy += history[i][1];
					sumx2 += history[i][0]*history[i][0];
					sumxy += history[i][0]*history[i][1];
				}
				
				var denom = (n*sumx2-sumx*sumx)
				if (denom == 0) return [history[0][1],0,0];
				var slope = (n*sumxy-sumx*sumy)/denom;
				var intercept = (sumy-slope*sumx)/n;
				
				return [intercept+slope*variable,slope,intercept];
			}
			
			function GetFlights() {
				xmlhttp=new XMLHttpRequest();
				//xmlhttp.open("GET","getflight.php?lat="+centre_lat+"&long="+centre_long+"&zoom=10",true);
				xmlhttp.open("GET","http://arn.data.fr24.com/zones/fcgi/feed.js?bounds=55.9142084705325,47.74010429497699,-10.8984375,2.7685546875&faa=1&mlat=1&flarm=1&adsb=1&gnd=1&air=1&vehicles=1&estimated=1&maxage=900&gliders=1&stats=1",true);

				xmlhttp.send();
				
				xmlhttp.onreadystatechange=function(){
					if (xmlhttp.readyState==4 && xmlhttp.status==200){
						//alert(xmlhttp.responseText);
						var new_flights = JSON.parse(xmlhttp.responseText);

						if (new_flights != null) {
							for (key in flights) {
								if (typeof new_flights[key] == 'undefined') {
									if (flights[key].dead) {
									} else {
										flights[key].dead = 240;
									}
								} else {
									var new_flight = new flight(new_flights[key]);
									if (new_flight.unixtime > flights[key].unixtime) {									
										var history = flights[key].history;
										flights[key] = new_flight;
										flights[key].history = flights[key].history.concat(history);
									}
								}
							}
							for (key in new_flights) {
								if (typeof flights[key] == 'undefined') {
									switch (key) {
										case 'full_count':
										case 'version':
										case 'stats':
											continue;
									}
									flights[key] = new flight(new_flights[key]);
								}
							}
						}						
					}
				}

			}
		</script>
		<style>
			body {
				font-family: Sans-Serif;
			}

			#info {
				width:100%;
			}

			.strip {
				background-color: #0000FF;
				color:white;
				width:100%;
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
			}
		</style>		
	</head>
	<body>
		<div id="search_div">
			<input id="search" onchange="ListFlights();" value="">
		</div>
		<div id="options_panel">
			<label id="history"><input type="checkbox" onclick="show_history=this.checked;">History</label>
			<label id="alt100"><input type="checkbox" onclick="show_alt100=this.checked;" checked="checked">0-99</label>
			<label id="alt1000"><input type="checkbox" onclick="show_alt1000=this.checked;" checked="checked">100-999</label>
			<label id="alt5000"><input type="checkbox" onclick="show_alt5000=this.checked;" checked="checked">1000-4999</label>
			<label id="alt10000"><input type="checkbox" onclick="show_alt10000=this.checked;" checked="checked">5000-9999</label>
			<label id="alt20000"><input type="checkbox" onclick="show_alt20000=this.checked;" checked="checked">10000-19999</label>
			<label id="alt40000"><input type="checkbox" onclick="show_alt40000=this.checked;" checked="checked">20000-40000</label>
		</div>
		<div id="info"><div>
	</body>

</html>
<script>
	initialize();
</script>