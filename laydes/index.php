<!DOCTYPE html>
<html>
	<head>
		<title>Fixtures Wheel</title>
		<meta name="description" content="Fixture Wheel">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<script>
			var canvas;
			var userEventController;

			var EVENT_GRAB = 0;
			var EVENT_DRAG = 1;
			var EVENT_MOVE = 2;
			var EVENT_RELEASE = 3;
			var EVENT_HOVER = 4;
			var CAPTURE_EVENT = false;
			var PASS_ON_EVENT = true;
			var ORIENT_HORIZONTAL = 0;
			var ORIENT_VERTICAL = 1;
			var UNIT_PIXEL = 0;
			var UNIT_PERCENT = 1;
			var UNIT_EM = 2;

			var PI2 = 2*Math.PI;

			var mutex = false;

			var event_controller = undefined;
			var render_controller = undefined;

			window.requestAnimFrame = (function(callback) {
			  return (
			    window.requestAnimationFrame || 
			    window.webkitRequestAnimationFrame || 
			    window.mozRequestAnimationFrame || 
			    window.oRequestAnimationFrame || 
			    window.msRequestAnimationFrame ||
			    function(callback) {
			      window.setTimeout(callback, 30);
			    }
			  );
			})();

			function Initialise() {
				canvas = document.getElementById("canvas");
		  		ctx = canvas.getContext("2d");
				ctx.canvas.width  = window.innerWidth;
				ctx.canvas.height = window.innerHeight;

		  		event_controller = new EventController();
		  		event_controller.createEventListeners(canvas);

		  		render_controller = new RenderController();

		  		var test = new Line();

			}

			function RenderController() {
				this.lines = [];
				this.boxes = [];
				var self = this;

				this.RegisterLine = function(line) {
					self.lines.push(line);
				}

				this.RegisterBox = function(box) {
					self.boxes.push(box);
				}

				this.UnregisterLine = function(line) {

				}

				this.UnregisterBox = function(box) {

				}

				this.RenderAll = function() {
					self.RenderLines();
					self.RenderBoxes();
				}

				this.RenderLines = function() {
					for (var index in self.lines) {
						var line = self.lines[index];
					}
				}

				this.RenderBoxes = function() {
					for (var index in self.boxes) {
						var box = self.boxes[index];
					}
				}
			}

			function EventController () {
				this.RegisterDelegate = function (delegate) {
					this.delegates.push(delegate);
				};

				this.UnregisterDelegate = function (delegate) {
					// do
				};

				this.createEventListeners = function(canvas) {
					canvas.addEventListener('mousedown',
						function (event) {
							//document.getElementById('info2').innerHTML = 'mousedown';

							if (mutex) return;

							self.mouseState = EVENT_GRAB;
							var coords = self.getCoords(event);

							for (var i = 0; i<self.delegates.length; i++) {
								if (self.delegates[i](self.mouseState,coords,self.previousCoords)==CAPTURE_EVENT) break;
							}
							self.previousCoords = coords;
						}
					);

					canvas.addEventListener('mousemove',
						function (event) {
							//document.getElementById('info2').innerHTML = 'mousemove';
							if (mutex) return; // throw away event whilst drawing

							switch (self.mouseState) {
								case EVENT_GRAB:
								case EVENT_DRAG:
									self.mouseState = EVENT_DRAG;
									break;
								default:
									self.mouseState = EVENT_MOVE;
							}

							var coords = self.getCoords(event);
					
							for (var i = 0; i<self.delegates.length; i++) {
								if (self.delegates[i](self.mouseState,coords,self.previousCoords)==CAPTURE_EVENT) break;
							}

							self.previousCoords = coords;										
						}
					);
					
					
					canvas.addEventListener('mouseup',
						function (event) {
							//document.getElementById('info2').innerHTML = 'mouseup';
							
							self.mouseState = EVENT_RELEASE;
							var coords = self.getCoords(event);

							for (var i = 0; i<self.delegates.length; i++) {
								if (self.delegates[i](self.mouseState,coords,self.previousCoords)==CAPTURE_EVENT) break;
							}			
							self.previousCoords = coords;				
						}
					);

					canvas.addEventListener('mouseout',
						function (event) {
							//document.getElementById('info2').innerHTML = 'mouseout';
							
							self.mouseState = EVENT_RELEASE;
							var coords = self.getCoords(event);

							for (var i = 0; i<self.delegates.length; i++) {
								if (self.delegates[i](self.mouseState,coords,self.previousCoords)==CAPTURE_EVENT) break;
							}			
							self.previousCoords = coords;				
						}
					);

					canvas.addEventListener('touchstart',
						function (event) {
							//document.getElementById('info2').innerHTML = 'touchstart';

							if (event.changedTouches.length!=1) return; // ignore multi touch

							self.mouseState = EVENT_GRAB;
							var coords = self.getCoords(event);

							for (var i = 0; i<self.delegates.length; i++) {
								if (self.delegates[i](self.mouseState,coords,self.previousCoords)==CAPTURE_EVENT) break;
							}			
							self.previousCoords = coords;				
						}
					);

					canvas.addEventListener('touchmove',
						function (event) {
							//document.getElementById('info2').innerHTML = 'touchmove';
							if (event.changedTouches.length!=1) return;

							if (mutex) return; // throw away event whilst drawing

							if (self.mouseState == EVENT_GRAB || self.mouseState == EVENT_DRAG) {
								self.mouseState = EVENT_DRAG;
							} else {
								self.mouseState = EVENT_MOVE;
							}
							var coords = self.getCoords(event);

							for (var i = 0; i<self.delegates.length; i++) {
								if (self.delegates[i](self.mouseState,coords,self.previousCoords)==CAPTURE_EVENT) break;
							}			
							self.previousCoords = coords;				
						}
					);


					canvas.addEventListener('touchend',
						function (event) {
							//document.getElementById('info2').innerHTML = 'touchend';
							if (event.changedTouches.length!=1) return;

							self.mouseState = EVENT_RELEASE;
							var coords = self.getCoords(event);

							for (var i = 0; i<self.delegates.length; i++) {
								if (self.delegates[i](self.mouseState,coords,self.previousCoords)==CAPTURE_EVENT) break;
							}			
							self.previousCoords = coords;				
						}
					);

				};

				function Line(refs,value,units) {
					this.refs = refs;
					this.value = value;
					this.units = units;
					var self = this;

					this.Render = function() {
						mutex = true;

						ctx.beginPath();
						ctx.lineWidth = 1;
						ctx.strokeStyle = 'rgb(128,128,128)';
						ctx.moveTo(self.centre.x,self.centre.y);
						ctx.lineTo(self.outsideRadius*Math.cos(angle)+self.centre.x+2,self.outsideRadius*Math.sin(angle)+self.centre.y);
						ctx.stroke();

						mutex = false;
					}

					this.AbsoluteValue = function() {
						switch (self.units) {
							case UNITS_PIXELS:
								if (self.refs) {
									return self.refs[0].AbsoluteValue()+self.value;
								} else {
									return self.value;
								}
								break;
							case UNITS_PERCENTAGE:
								if (self.refs) {
									return self.refs[0].AbsoluteValue()+self.percentage*(self.refs[1].AbsoluteValue()-self.refs[0].AbsoluteValue())/100;
								} else {
									return self.value;
								}
								break;
						}
						return self.value;
					}

					
				}

				function Box() {
					this.Render = function() {
						mutex = true;
						mutex = false;
					}
				}
			}
		</script>
	</head>
	<body onload='Initialise()' onresize='Initialise()'>
		<canvas id="canvas"></canvas>
	</body>
</html>