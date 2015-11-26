<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width">
		<script src="https://maps.googleapis.com/maps/api/js"></script>
		<script src="vector2d.js"></script>
		<script src="fixes.js"></script>
		<script src="fixes2.js"></script>
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

			var flights=[]; 
			var boxes = [];

			//var flights = {test1:['',51.147101948513985,-0.17337799072265625,135,1000,0,'',0,'','',0,'LHR','LGW','','','','B747'],test2:['',51.14,-0.18,000,1000,0,'',0,'','',0,'LHR','LGW','','','','B747']};
			//var boxes= {test1:[650,350,100,100,0,0],test2:[650,350,100,100,0,0]};

			var update_count = 0;

			var canvas;
			var context;

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
			var GRAPH_X = 20;
			var GRAPH_Y = 21;
			var HISTORY = 22;
			
			
			var highlighted_flight;

			var show_labels=true;
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

			function initialize() {
				var options = {
				  enableHighAccuracy: true,
				  timeout: 5000,
				  maximumAge: 0
				};
				navigator.geolocation.getCurrentPosition(location_success, location_error, options)


				var mapCanvas = document.getElementById('google_map');
				var flights_map = document.getElementById('flights_map');
				var info_panel = document.getElementById('info');

				canvas = flights_map;
				context = flights_map.getContext("2d");
				context.font="10px Arial";
				
				screen_width = window.innerWidth;
				screen_height = window.innerHeight;

				flights_map.width = window.innerWidth;
				flights_map.height = window.innerHeight;

				info_panel.style.height = window.innerHeight-2;

				window.onresize = function(event) {
					screen_width = window.innerWidth;
					screen_height = window.innerHeight;
					flights_map.width = window.innerWidth;
					flights_map.height = window.innerHeight;
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

				document.getElementById("option1").className = show_labels?'option_selected':'';
				document.getElementById("option2").className = show_alt100?'option_selected':'';
				document.getElementById("option3").className = show_alt1000?'option_selected':'';
				document.getElementById("option4").className = show_alt5000?'option_selected':'';
				document.getElementById("option5").className = show_alt10000?'option_selected':'';
				document.getElementById("option6").className = show_alt20000?'option_selected':'';
				document.getElementById("option7").className = show_alt40000?'option_selected':'';
				document.getElementById("option8").className = show_history?'option_selected':'';
				document.getElementById("option9").className = show_fixes?'option_selected':'';

				//DrawPlane(200,200,135);

				GetFlights();

				setInterval(function(){UpdateFlights();RenderAll();}, 500);
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

			function SetCentre(lat,lon) {
				map.setCenter(new google.maps.LatLng(lat, lon));		
			}

			function ZoomChanged() {
				new_zoom_google = map.getZoom();
				zoom_flights_x = zooms_x[new_zoom_google];
				zoom_flights_y = zooms_y[new_zoom_google];				
				for (key in boxes) {
					boxes[key][0] -= (screen_width/2);
					boxes[key][1] -= (screen_height/2);
					boxes[key][0] = boxes[key][0]*zoom_flights_x/zooms_x[zoom_google];
					boxes[key][1] = boxes[key][1]*zoom_flights_y/zooms_y[zoom_google];
					boxes[key][0] += (screen_width/2);
					boxes[key][1] += (screen_height/2);
				}
				zoom_google = new_zoom_google;
				//document.getElementById("info").innerHTML = zoom_google;
			}

			google.maps.event.addDomListener(window, 'load', initialize);


			function GetCanvasPosition(lat,lon) {
				return [canvas.width/2+(lon-centre_long)*zoom_flights_x,canvas.height/2-(lat-centre_lat)*zoom_flights_y];
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
				var letters = document.getElementById('search').value.toUpperCase().split('');
				if (letters.length == 0) {
					return;
				}
				var strips = [];
				for (key in flights) {
					if (flights[key][CALLSIGN]!=undefined) {
						var callsignx = flights[key][CALLSIGN].split('');
						var ok = true;
						var found;
						var found = -1;
						for (index in letters) {
							if ((found = callsignx.indexOf(letters[index],found+1))==-1) {
								ok = false;
								break;
							}
						}
						if (ok) {
							strips.push([key,flights[key][CALLSIGN]]);
						}
					}
				}

				strips.sort(function(a,b){return (a[1]<b[1])?-1:1});

				document.getElementById('info').innerHTML = '';
				for (key in strips) {
					var div = document.createElement('div');
					div.className = 'strip';
					div.innerHTML = strips[key][1];
					div.key = strips[key][0];
					(function (thiskey,lat,lon) {
						div.onclick = function() {highlighted_flight=thiskey; SetCentre(lat,lon);};
					})(div.key,flights[div.key][LAT],flights[div.key][LON]);
					document.getElementById('info').appendChild(div);
				}
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
					altitude.push([history[point][5]-unixoffset,history[point][2]]);
					speed.push([history[point][5]-unixoffset,history[point][3]]);
					
					this_heading = history[point][4];
					if (previous_heading != undefined) {
						if (this_heading>=(previous_heading+180)) {
							this_heading -= 360;
						} else if (this_heading<=(previous_heading-180)) {
							this_heading += 360;
						}
					}
					heading.push([history[point][5]-unixoffset,this_heading]);
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
					var interval_new = Date.now()/1000-flights[key][UNIXTIME];
					
					
					var estimates = ExtractHistory(flights[key][HISTORY]);
					flights[key][ALTITUDE_RATE_EST] = estimates[0];
					flights[key][SPEED_RATE_EST] = estimates[1];
					flights[key][HEADING_RATE_EST] = estimates[2];

					flights[key][ALTITUDE] = flights[key][ALTITUDE_ORIG]+interval_new*flights[key][ALTITUDE_RATE]/60;
					if (flights[key][ALTITUDE] < 0) {
						//flights[key][ALTITUDE] = interval_new*flights[key][ALTITUDE_RATE]/60;
						//flights[key][SPEED] -= (10)*interval_new;
						flights[key][ALTITUDE] = 0;
					}
					if (flights[key][SPEED]<0) {
						flights[key][SPEED] = 0;
					}				
					if (flights[key].dead) {
						flights[key].dead--;
						if (flights[key][LANDED]==1 || flights[key][ALTITUDE]==0) {
							flights[key][SPEED] = 0;
							flights[key][ALTITUDE] = 0;
						}
							
						if (flights[key].dead <= 0) {
							delete flights[key];
							delete boxes[key];
						}						
						
					}
					
					var heading = flights[key][HEADING];
					var speed = flights[key][SPEED];
					//var speed_rate = flights[key][SPEED_RATE];
					var speed_est = speed;//+speed_rate*interval_new;
					
					var heading_rate_est = flights[key][HEADING_RATE_EST];
					var heading_est = heading+heading_rate_est*interval_new;
					flights[key][SPEED_EST] = speed_est;
					flights[key][HEADING_EST] = heading_est;
					

					
					var speed_x = Math.cos((90-heading)*2*Math.PI/360)*(speed_est)*1852/3600; // metres per second
					var speed_y = Math.sin((90-heading)*2*Math.PI/360)*(speed_est)*1852/3600; // metres per second

					flights[key][LON] = flights[key][LON_ORIG]+interval_new*360*speed_x/(40000000*Math.cos(2*Math.PI*flights[key][LAT_ORIG]/360));
					flights[key][LAT] = flights[key][LAT_ORIG]+interval_new*360*speed_y/40000000;
					
					ConvertLatLonToXY(key);


				}
			}

			function DrawPlane(x,y,dir,alt) {
				dir = dir-90;
				var size = 5;
				var points = [];
				points[0] = new Vector2d(x,y);//.Add(new Vector2d(0,-alt/100));
				points[1] = VectorPolar(size,dir).Add(points[0]);
				points[2] = VectorPolar(size,dir+120).Add(points[0]);
				points[3] = VectorPolar(size,dir-120).Add(points[0]);

				context.fillStyle = '#000';
				context.beginPath();
				context.moveTo(points[0].x,points[0].y);
				context.lineTo(points[2].x,points[2].y);
				context.lineTo(points[1].x,points[1].y);
				context.lineTo(points[3].x,points[3].y);
				context.closePath();
				context.fill();
				
				/*
				context.strokeStyle = '#808080';
				context.beginPath();
				context.moveTo(x,y);
				context.lineTo(points[0].x,points[0].y);
				context.line
				context.closePath();
				context.stroke();
				*/
			}

			function DrawFix(x,y,name) {
				var size = 4;
				var points = [];
				points[0] = new Vector2d(x,y);
				points[1] = VectorPolar(size,0-90).Add(points[0]);
				points[2] = VectorPolar(size,120-90).Add(points[0]);
				points[3] = VectorPolar(size,240-90).Add(points[0]);
				
				context.fillStyle = '#080';
				context.beginPath();
				context.moveTo(points[1].x,points[1].y);
				context.lineTo(points[2].x,points[2].y);
				context.lineTo(points[3].x,points[3].y);
				context.closePath();
				context.fill();		

				context.fillStyle="#080";
				context.font = "8pt Arial";
				context.fillText(name,x+size+3,y);				
			}
			
			function RenderFixes() {
				if (!show_fixes) {
					return;
				}
				for (key in fixes) {
					var pos = GetCanvasPosition(fixes[key][1],fixes[key][2]);
					DrawFix(pos[0],pos[1],fixes[key][0]);
				}
			}
			
            function RenderFlights() {
                var spacing = 13;
                var padding_left = 8;
                var padding_top = 15;

				if (show_labels) {
					JiggleBoxes();
				}
				
                for (var key in flights) {
					var altitude = flights[key][ALTITUDE];
					if (!CheckAltitudeBand(altitude)) {
						continue;
					}
					
					ConvertLatLonToXY(key);					
					var graph_x = flights[key][GRAPH_X];
					var graph_y = flights[key][GRAPH_Y];
					
					if (graph_x<0 || graph_x>screen_width || graph_y<0 || graph_y>screen_height) {
						continue;
					}					
					
                    var lat = flights[key][LAT];
                    var lon = flights[key][LON];
                    var callsign_short = flights[key][CALLSIGN_SHORT];
                    var callsign = flights[key][CALLSIGN];
                    var heading = flights[key][HEADING];
                    var altitude = flights[key][ALTITUDE];
                    var speed = flights[key][SPEED];
                    var squawk = flights[key][SQUAWK];
                    var origin = flights[key][ORIGIN];
                    var destination = flights[key][DESTINATION];
                    var reg = flights[key][REG];
                    var hex = flights[key][HEX];
                    var model = flights[key][MODEL];
					var landed = flights[key][LANDED];

					var altitude_rate_est = flights[key][ALTITUDE_RATE_EST];
					var speed_rate_est = flights[key][SPEED_RATE_EST];
					var heading_rate_est = flights[key][HEADING_RATE_EST];
					var heading_est = flights[key][HEADING_EST];
					var unixtime = flights[key][UNIXTIME];
					var speed_est = flights[key][SPEED_EST];
					
                    DrawPlane(graph_x,graph_y,heading,altitude);
	
                    if (show_history) {
						context.strokeStyle = '#d0a0a0';
						context.beginPath();
						
						if (flights[key][HISTORY]) {
							var pos = GetCanvasPosition(flights[key][HISTORY][0][0],flights[key][HISTORY][0][1]);
							context.moveTo(pos[0],pos[1]);             	
	                    	for (var index in flights[key][HISTORY]) {
	                    		var pos = GetCanvasPosition(flights[key][HISTORY][index][0],flights[key][HISTORY][index][1]);
	                    		context.lineTo(pos[0],pos[1]);
	                    	}
	                    	context.lineTo(graph_x,graph_y);
	                    	context.stroke();
	                    }
                    }

					if (show_labels) {
						context.strokeStyle = '#808080';
						context.beginPath();
						context.moveTo(boxes[key][0], boxes[key][1]);
						context.lineTo(graph_x,graph_y);
						context.stroke();
	
						var box_left = boxes[key][0]-boxes[key][2]/2;
						var box_top = boxes[key][1]-boxes[key][3]/2;
						var box_right = boxes[key][0]+boxes[key][2]/2
						
						context.beginPath();
						context.rect(box_left,box_top,boxes[key][2],boxes[key][3]);

						if (key==highlighted_flight) {
							context.fillStyle = 'rgba(0,255,0,0.6)';
						} else if (flights[key].dead) {
							context.fillStyle = 'rgba(255,80,80,0.6)';
						} else {
							context.fillStyle = 'rgba(255,255,0,0.6)';
						}
						context.fill();
						
						context.fillStyle="#800000";
						context.font = "bold 8pt Arial";
						context.fillText(callsign,box_left+padding_left, box_top+padding_top);
						context.font = "8pt Arial";
						context.fillText(callsign_short, box_left+padding_left,box_top+spacing*1+padding_top);
						context.fillText(model+' '+reg, box_left+padding_left,box_top+spacing*2+padding_top);
						context.fillText(origin+'-'+destination, box_left+padding_left,box_top+spacing*3+padding_top);
						context.fillText(Whole(altitude)+((altitude>0 && landed==1)?'L':'')+'-'+Whole(speed)+'-'+Pad(Whole(heading),3), box_left+padding_left,box_top+spacing*4+padding_top);
						context.fillText(Whole(DistanceBetween(initial_latlong,[lat,lon])), box_right-padding_left*2.5,box_top+spacing*0+padding_top);
						//context.fillText(Whole(Date.now()/1000-unixtime), box_left+padding_left,box_top+spacing*5+padding_top);

/*
						index=0;
						for (subkey in flights[key]) {
							context.fillText(flights[key][subkey],box_left+padding_left,box_top+padding_top+spacing*index);
							index++;
						}
					*/	
					}
                }
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
			function JiggleBoxes() {
				var boxesnew = [];
				
				for (key1 in boxes) {
					boxesnew[key1]=[boxes[key1][0],boxes[key1][1],boxes[key1][2],boxes[key1][3]];				
					if (key1=='version' || key1=='full_count') {
						continue;
					}
							
					if (flights[key1][GRAPH_X]<0 || flights[key1][GRAPH_X]>screen_width || flights[key1][GRAPH_Y]<0 || flights[key1][GRAPH_Y]>screen_height) {
						continue;
					}
					
					if (!CheckAltitudeBand(flights[key1][ALTITUDE])) {
						continue;
					}
					


					var vdelta = new Vector2d(0,0);
					var divider =0;

					var vthisaircraft = new Vector2d(flights[key1][GRAPH_X],flights[key1][GRAPH_Y]);
					var vthistail = vthisaircraft.Add(VectorPolar(100,flights[key1][HEADING]+90));
					var vthisbox = new Vector2d(boxes[key1][0],boxes[key1][1]);		

					var vthisaircraftbox = vthistail.Sub(vthisbox);
					var vthisleadersize = vthisaircraftbox.Size();
				
					if (vthisleadersize>300) {
						vdelta = vthisaircraftbox.Sub(vthisaircraftbox.Resize(290));
						divider++;
					} else {
						var vdelta = vdelta.Add(vthisaircraftbox.Resize(vthisleadersize/10));
						divider++;						
					}

					if (vthisleadersize<250) {
						for (key2 in boxes) {
							if (key2=='version' || key2=='full_count') {
								continue;
							}
							if (key1!=key2) {
								if (flights[key2][GRAPH_X]<0 || flights[key2][GRAPH_X]>screen_width || flights[key2][GRAPH_Y]<0 || flights[key2][GRAPH_Y]>screen_height) {
									continue;
								}
								
								if (!CheckAltitudeBand(flights[key2][ALTITUDE])) {
									continue;
								}

								var vthatbox = new Vector2d(boxes[key2][0],boxes[key2][1]);
								var vthataircraft = new Vector2d(flights[key2][GRAPH_X],flights[key2][GRAPH_Y]);
							
								var vdiff = vthatbox.Sub(vthisbox);
								var dist = vdiff.Size();
								if (dist<110) {
									var vdelta = vdelta.Add(vdiff.Resize(-50/Math.exp(dist/100)));
									divider++;
								}
							

								var vdiff2 = vthisbox.Sub(vthataircraft);
								var dist2 = vdiff2.Size();
								
								if (dist2<110) {
									var vdelta = vdelta.Add(vdiff2.Resize(10/Math.exp(dist2/600)));
									divider++;
								}	
												
							}
						}
					}
					if (divider==0) {
						var vnew = vthisbox;
					} else {
						var vnew = vthisbox.Add(vdelta.Scale(1/divider));
					}
					
					boxesnew[key1] = [vnew.x,vnew.y,boxes[key1][2],boxes[key1][3]];						
				}
				
				boxes = boxesnew;

				
				for (key1 in boxes) {
					if (key1=='version' || key1=='full_count') {
						continue;
					}
							
					if (flights[key1][GRAPH_X]<0 || flights[key1][GRAPH_X]>screen_width || flights[key1][GRAPH_Y]<0 || flights[key1][GRAPH_Y]>screen_height) {
						continue;
					}
					
					if (!CheckAltitudeBand(flights[key1][ALTITUDE])) {
						continue;
					}

					var vthisaircraft = new Vector2d(flights[key1][GRAPH_X],flights[key1][GRAPH_Y]);
					var vthisbox = new Vector2d(boxes[key1][0],boxes[key1][1]);		

					for (key2 in boxes) {
						if (key2=='version' || key2=='full_count') {
							continue;
						}
						if (key1!=key2) {
							if (flights[key2][GRAPH_X]<0 || flights[key2][GRAPH_X]>screen_width || flights[key2][GRAPH_Y]<0 || flights[key2][GRAPH_Y]>screen_height) {
								continue;
							}
							
							if (!CheckAltitudeBand(flights[key2][ALTITUDE])) {
								continue;
							}

							var vthatbox = new Vector2d(boxes[key2][0],boxes[key2][1]);
							var vthataircraft = new Vector2d(flights[key2][GRAPH_X],flights[key2][GRAPH_Y]);
							
							var crosses = LinesCross(vthisbox,vthisaircraft,vthatbox,vthataircraft);
							if (crosses) {
								var temp = boxes[key1];
								boxes[key1] = boxes[key2];
								boxes[key2] = temp;
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
				canvas.width = canvas.width;

				RenderFixes();
				RenderFlights();
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
									var history = flights[key][HISTORY];
									flights[key] = new_flights[key];
									flights[key][LON_ORIG] = flights[key][LON];
									flights[key][LAT_ORIG] = flights[key][LAT];
									flights[key][ALTITUDE_ORIG] = flights[key][ALTITUDE];
									ConvertLatLonToXY(key);
									if (history) {
										if (flights[key][UNIXTIME] > history[history.length-1][5]) {
											history.push([flights[key][LAT],flights[key][LON],flights[key][ALTITUDE],flights[key][SPEED],flights[key][HEADING],flights[key][UNIXTIME]]);
										}
										flights[key][HISTORY] = history;
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
									flights[key] = new_flights[key];
									flights[key][LON_ORIG] = flights[key][LON];
									flights[key][LAT_ORIG] = flights[key][LAT];
									flights[key][ALTITUDE_ORIG] = flights[key][ALTITUDE];
									ConvertLatLonToXY(key);
									flights[key][HISTORY] = [[flights[key][LAT],flights[key][LON],flights[key][ALTITUDE],flights[key][SPEED],flights[key][HEADING],flights[key][UNIXTIME]]];
									var heading = flights[key][HEADING];
									var pos = GetCanvasPosition(flights[key][LAT],flights[key][LON]);
													
									var vdirection = new Vector2d(Math.cos((90-heading)*2*Math.PI/360),-Math.sin((90-heading)*2*Math.PI/360));
									vdirection = vdirection.Scale(-60);
									var vaircraft = new Vector2d(pos[0],pos[1]);
									var vbox = vaircraft.Add(vdirection);

									boxes[key] = [vbox.x,vbox.y,90,75];			
					
								}
							}
						}						
					}
				}

			}

			function Options(element) {
				var option = document.getElementById("option"+element.dataset.id)
				option.selected = (option.className!='');
				option.className = option.selected?'':'option_selected';
				option.selected = !option.selected;
				
				if (element.dataset.id==0) {
					if (option.selected) {
						for (var index = 1; index <= 9; index++) {
							document.getElementById("option"+index).style.display="block";
						}
					} else {
						for (var index = 1; index <= 9; index++) {
							document.getElementById("option"+index).style.display="none";
						}						
					}
				} else {
					switch (element.dataset.id) {
						case "1": // labels
							show_labels = option.selected;
							break;
						case "2": // 99 ft
							show_alt100 = option.selected;
							break;
						case "3": // 999 ft
							show_alt1000 = option.selected;
							break;
						case "4": // 4999 ft
							show_alt5000 = option.selected;
							break;
						case "5": // 9999 ft
							show_alt10000 = option.selected;
							break;
						case "6": // 19999 ft
							show_alt20000 = option.selected;
							break;
						case "7": // 39999 ft
							show_alt40000 = option.selected;
							break;
						case "8": // history
							show_history = option.selected;
							break;
						case "9": // fixes
							show_fixes = option.selected
							break;							
					}
				}
				
				//alert(element.dataset.id);
			}
		</script>
		<style>
			html {
				width:100%;
				height:100%;
				overflow:hidden;
			}
			
			body {
				width:100%;
				height:100%;
				margin:0;
				font-family:Arial,sans-serif;
				font-weight:bold;
				font-size:12px;
			}
			
			#google_map {
				width: 100%;
				height: 100%;
				position:absolute;
				left:0px;
				top:0px;
				margin:0;				
				background-color: #CCC;
			}
			
			#flights_map {
				width:100%;
				height:100%;
				position:absolute;
				left:0px;
				top:0px;
				display:block;
				pointer-events:none;
			}
			
			#hover_menu {
				position:absolute;
				top:50px;
				right:5px;
				width:87px;
			}
			
			#hover_menu > div {
				background-color:white;
				border:1px solid #888888;
				padding:3px 7px 2px 7px;
				cursor:default;
			}

			#hover_menu > div:not(:first-child) {
				display:none;
			}
			
			#hover_menu > div:hover {
				color:white;
				background-color:black;
			}
			
			.option_selected {
				color:white;
				background-color:blue !important;
			}
			

		
			#search_div {
				position:absolute;
				top:50px;
				right:100px;				
				width:87px;
			}
			#search_div > input {
				width:87px;
				margin-bottom:2px;
			}
			
			#info {
				height:300px;
			}

			.strip {
				background-color: #0000FF;
				color:white;
				width:87px;
				height:17px;
				margin-bottom: 2px;
				padding:3px;
				cursor:pointer;
			}
			
			.strip:hover {
				background-color: #0080FF;
			}			
		</style>		
	</head>
	<body>
		<div id="google_map"></div>
		<canvas id="flights_map"></canvas> 
		<div id="search_div">
			<input id="search" onchange="ListFlights();" value="" placeholder="search">
			<div id="info"></div>
		</div>
		<div id="hover_menu">
			<div id="option0" onclick="Options(this);" data-id="0">Options</div>
			<div id="option1" onclick="Options(this);" data-id="1">Labels</div>
			<div id="option2" onclick="Options(this);" data-id="2">99</div>
			<div id="option3" onclick="Options(this);" data-id="3">999</div>
			<div id="option4" onclick="Options(this);" data-id="4">4999</div>
			<div id="option5" onclick="Options(this);" data-id="5">9999</div>
			<div id="option6" onclick="Options(this);" data-id="6">19999</div>
			<div id="option7" onclick="Options(this);" data-id="7">39999</div>
			<div id="option8" onclick="Options(this);" data-id="8">History</div>
			<div id="option9" onclick="Options(this);" data-id="9">Fixes</div>
		</div>
		<div id="info"><div>
	</body>

</html>