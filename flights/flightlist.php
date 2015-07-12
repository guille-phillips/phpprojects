<!DOCTYPE html>
<html>
	<head>
		<script src="airports.js"></script>
		<script src="airlines.js"></script>
		<script>
			var unixoffset = <?php echo time();?>;

			var centre_lat = 51.147101948513985;
			var centre_long = -0.17337799072265625;

			var flights=[];
			var boxes = [];

			var update_count = 0;

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
					if (flight.distance<150) {
						strips.push(flight);
					}
				}

				strips.sort(function(a,b){return (a.distance<b.distance)?-1:1});

				document.getElementById('info').innerHTML = '';
				for (key in strips) {
					var strip = strips[key];

					if (!CheckAltitudeBand(parseInt(strip.altitude))) {continue};

					if (strip.dead) {
						strip.colour = '#808080';
					} else {
						if (strip.reg != '') {
							strip.colour = ColourOf(strip.reg);
						} else {
							strip.colour = ColourOf(strip.callsign);
						}
					}
					
					style = {style:'background-color:'+strip.colour};
					var div = document.createElement('div');
					div.className = 'strip';
					div.innerHTML =
						[strip.callsign+(strip.dead?'*':''),
						strip.distance.toFixed(1)+(['&#x25B2','&nbsp;','&#x25BC'][strip.approaching+1]),
						strip.model,
						strip.reg,
						strip.origin,
						strip.destination,
						strip.altitude.toFixed(0)+(['&#x25BC','&nbsp;','&#x25B2'][strip.descending+1])+(strip.landed?'L':''),
						strip.speed,
						strip.heading.pad(3)
						].map(function(content,index,arr){return (content.toString()==''?'&nbsp':content.toString()).tag('div',style);}).join('');

					document.getElementById('info').appendChild(div);

					/*
					var div = document.createElement('div');
					div.className = 'strip-extended';
					div.innerHTML =
						[strip.callsign_short,
						strip.origin_airport,
						strip.destination_airport,
						strip.icao,
						strip.airline,
						strip.callsign_name
						].map(function(content,index,arr){return (content.toString()==''?'&nbsp':content.toString()).tag('div',style);}).join('');

					document.getElementById('info').appendChild(div);
					*/
					
					//div.key = key;
					/*
					(function (thiskey,lat,lon) {
						div.onclick = function() {highlighted_flight=thiskey; SetCentre(lat,lon);};
					})(div.key,flights[div.key][LAT],flights[div.key][LON]);
					*/


				}
			}


			function GetFlights() {
				xmlhttp=new XMLHttpRequest();
				//xmlhttp.open("GET","getflight.php?lat="+centre_lat+"&long="+centre_long+"&zoom=10",true);
				xmlhttp.open("GET","http://arn.data.fr24.com/zones/fcgi/feed.js?bounds=55.9142084705325,47.74010429497699,-10.8984375,2.7685546875&faa=1&mlat=1&flarm=1&adsb=1&gnd=1&air=1&vehicles=0&estimated=1&maxage=900&gliders=0&stats=0",true);

				xmlhttp.send();

				xmlhttp.onreadystatechange=function(){
					if (xmlhttp.readyState==4 && xmlhttp.status==200){
						//alert(xmlhttp.responseText);
						var new_flights = JSON.parse(xmlhttp.responseText);

						if (new_flights != null) {
							for (key in flights) {
								if (typeof new_flights[key] == 'undefined') {
									// flight has disappeared
									flights[key].dead = true;
								} else {
									var new_flight = new flight(new_flights[key]);
									if (new_flight.unixtime > flights[key].unixtime) {
										var current_distance = flights[key].distance;
										var current_altitude = flights[key].altitude;
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
			
			function UpdateFlights() {
				for (var key in flights) {
					var flight = flights[key];
					var elapsed = Date.now()/1000-flight.unixtime;

					if (elapsed>120) {
						delete flights[key];
						continue;
					}
					
					if (flight.history) {} else {console.log(key);return;}

					var estimates = ExtractHistory(flight.history);
					flight.altitude_rate_est = estimates[0];
					flight.speed_rate_est = estimates[1];
					flight.heading_rate_est = estimates[2];

					var heading = flight.heading;
					var speed = flight.speed;
					var speed_rate = flight.speed_rate_est;
					var speed_est = speed+speed_rate*elapsed;

					var heading_rate_est = flight.heading_rate_est;
					var heading_est = heading+heading_rate_est*elapsed;
					flight.speed_est = speed_est;
					flight.heading_est = heading_est;

					var speed_x = Math.cos((90-heading_est)*2*Math.PI/360)*(speed_est)*1852/3600; // metres per second
					var speed_y = Math.sin((90-heading_est)*2*Math.PI/360)*(speed_est)*1852/3600; // metres per second

					flight.lon = flight.lon_orig+elapsed*360*speed_x/(40000000*Math.cos(2*Math.PI*flight.lat_orig/360));
					flight.lat = flight.lat_orig+elapsed*360*speed_y/40000000;
					var current_distance = flight.distance;
					flight.distance = DistanceBetween(initial_latlong,[flight.lat,flight.lon]);

					if (flight.distance < current_distance) {
						flight.approaching = -1;
					} else if (flight.distance > current_distance) {
						flight.approaching = 1;
					}
					
					flight.altitude = flight.altitude_orig+elapsed*flight.altitude_rate/60;
					if (flight.altitude < 0) {
						flight.altitude = elapsed*flight.altitude_rate/60;
						flight.speed -= (10)*elapsed;
						if (flight.speed<0) {
							flight.speed = 0;
						}
					}
				}
			}

			
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
				this.icao = '';
				this.airline = '';
				this.callsign_name = '';	
				
				var icao_found;
				if (icao_found = this.callsign.match(/^\w{3}(?=\d)/)) {
					this.icao = icao_found[0];
					if (typeof airlines[this.icao] != 'undefined') { 
						this.airline = airlines[this.icao][0];
						this.callsign_name = airlines[this.icao][1]					
					}
				}
				
				this.heading = parseInt(information[3]);
				this.altitude = parseInt(information[4]);
				this.speed = parseInt(information[5]);
				this.squawk = information[6];
				this.origin = information[11];
				this.destination = information[12];
				if (typeof airports[this.origin] != 'undefined') {
					this.origin_airport = airports[this.origin][0];
				} else {
					this.origin_airport = '';
				}
				if (typeof airports[this.destination] != 'undefined') {
					this.destination_airport = airports[this.destination][0];
				} else {
					this.destination_airport = '';
				}				
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
				this.dead = false;
				this.approaching = 0; // -1 approach 1 recede
				this.descending = 0;
				
				if (this.altitude_rate < 0) {
					this.descending = -1;
				} else if (this.altitude_rate > 0) {
					this.descending = 1;
				}				
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

			function initialize() {
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
				alert("Error getting location");
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


			String.prototype.tag = function(tag_name,attributes){
				var atts = '';
				for (var a in attributes) {
					atts += a+"='"+attributes[a]+"' ";
				}
				return "<"+tag_name+" "+atts+">"+this+"</"+tag_name+">";
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

			function RenderAll() {
				ListFlights();
				update_count++;
				if (update_count>20) {
					GetFlights();
					update_count = 0;
				}
			}

			function ColourOf(text) {
				var hash = 0;
				for (i=0;i<text.length;i++) {
					hash += text.charCodeAt(i)*5;
				}
				hash = hash%8;
				return ['#329462','#336687','#D39547','#D36947','#51AC7E','#4F7E9C','#F5BC73','#F59373'][hash];
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
		</script>
		<style>
			html {
				width:100%;
			}
			body {
				font-family: Sans-Serif;
				font-size:200%;
				width:100%;
			}

			#info {
				width:100%;
			}

			.strip-extended {
				width:100%;
				margin-bottom: 1px;
				cursor:pointer;
			}
			.strip-extended > div {
				background-color: #0000FF;
				color:white;
				display:inline-block;
				height:2ex;
				margin-right:3px;
				padding-left:5px;
				padding-top:7px;
				padding-bottom:3px;
				padding-right:5px;
				overflow:hidden;
			}

			.strip-extended > div:nth-of-type(1) {width:10%;border-bottom-left-radius: 6px;}
			.strip-extended > div:nth-of-type(2) {width:25%;}
			.strip-extended > div:nth-of-type(3) {width:25%;}
			.strip-extended > div:nth-of-type(4) {width:10%;}
			.strip-extended > div:nth-of-type(5) {width:6%;text-align: right;border-bottom-right-radius:6px;}

			.strip {
				width:100%;
				margin-bottom: 1px;
				cursor:pointer;
			}
			.strip > div {
				background-color: #0000FF;
				color:white;
				display:inline-block;
				height:2ex;
				margin-right:3px;
				padding-left:5px;
				padding-top:5px;
				padding-bottom:5px;
				padding-right:5px;
				overflow:hidden;
			}

			.strip > div:nth-of-type(1) {width:15%;border-top-left-radius:6px;border-bottom-left-radius: 6px;}
			.strip > div:nth-of-type(2) {width:8%;text-align: right;}
			.strip > div:nth-of-type(3) {width:10%;}
			.strip > div:nth-of-type(4) {width:15%;}
			.strip > div:nth-of-type(5) {width:7%;}
			.strip > div:nth-of-type(6) {width:7%;}
			.strip > div:nth-of-type(7) {width:10%;text-align: right;}
			.strip > div:nth-of-type(8) {width:6%;text-align: right;}
			.strip > div:nth-of-type(9) {width:6%;text-align: right;border-top-right-radius:6px;border-bottom-right-radius:6px;}

			.strip:hover {
				/*background-color: #0080FF;*/
			}

			#search_div {
				width:170px;
				height:25px;
			}

			#options_panel {
				margin-top:10px;
				height:5ex;
				padding-right:20px;
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