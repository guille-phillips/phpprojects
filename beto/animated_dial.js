var position_bottom = false;
var target_angle = 55;
var canvas;
var context;
var scale=1;
var frame=0;

window.requestAnimFrame = (function(callback) {
	return (
		window.requestAnimationFrame || 
		window.webkitRequestAnimationFrame || 
		window.mozRequestAnimationFrame || 
		window.oRequestAnimationFrame || 
		window.msRequestAnimationFrame ||
		function(callback) {
			window.setTimeout(callback, 1000 / 60);
		}
	);
})();

function Animate(parameters) {
	canvas = document.getElementById('canvas');
	context = canvas.getContext("2d");
	context.translate(canvas.width/2,position_bottom?canvas.height:canvas.height/2);
	Render();
}

function Render() {
	context.beginPath();
	context.clearRect(-canvas.width/2,-canvas.height/2,canvas.width,canvas.height);
	context.closePath();

	RenderBetometer(frame++);

	if (frame<500) {
		window.requestAnimFrame(Render);
	}
}


function RenderBetometer(frame) {
	var CIRCLE = 0;
	var RECTANGLE = 2;
	var GRATICULE = 3;
	var ARROW = 4;
	var SHADOW_CIRCLE = 5;

	var centrepiece = 10;
	var inner = 90;
	var outer = inner+13;
	var rim = outer+4;
	var border = rim+5;
	var shadow = border+5;
	var graticule_thin = 1;
	var graticule_fat = 2;
	var needle_width = 4;
	var needle_length = 70;
	var needle_tail_length = 40;
	var needle_tip_length = 12;
	var needle_tip_width = 12;
	var graticule_expansion = 1.2;
	var frame_counter;
	
	if (frame_counter = document.getElementById('frame_counter')) {
		frame_counter.innerHTML=frame;
	}

	var needle_angle = Springy(frame);

	var originv = new Vector2d(0,0);

	var elements = [
		[RECTANGLE,'#D1514B',0,0,200,-50],
		[RECTANGLE,'#7FB4E0',0,0,-200,-50],
		[CIRCLE,'#004000',border],
		[CIRCLE,'#bbb',rim],
		[CIRCLE,'white',outer],
		[CIRCLE,'#7FB4E0',outer,251,307], // blue
		[CIRCLE,'#EEE163',outer,0,53], // yellow
		[CIRCLE,'#D1514B',outer,54,108], // red
		[CIRCLE,'white',inner],
		[CIRCLE,'black',centrepiece],
		[GRATICULE,'black',[inner+2,outer-2],graticule_thin,48,-11,11,graticule_expansion],
		[GRATICULE,'black',[inner,outer],graticule_fat,8,-2,2,graticule_expansion],					
		[ARROW,'black',needle_length,needle_tail_length,needle_width,needle_angle,needle_tip_length,needle_tip_width],
		[CIRCLE,'black',[inner+15,outer+15],0,360,true]
	];

	for (var index in elements) {
		var element = elements[index];
		switch (element[0]) {
			case CIRCLE:
				Circle(element[1],originv,element[2],element[3],element[4],element[5]);
				break;
			case RECTANGLE:
				Rectangle(element[1],new Vector2d(element[2],element[3]),new Vector2d(element[4],element[5]));
				break;
			case GRATICULE:
				Graticule(element[1],originv,element[2],element[3],element[4],element[5],element[6],element[7]);
				break;
			case ARROW:
				Arrow(element[1],originv,element[2],element[3],element[4],element[5],element[6],element[7]);
		}
		
	}
}

function Rectangle(colour,corner1,corner2) {
	context.beginPath();
	context.fillStyle = colour;
	context.fillRect(corner1.x,corner1.y,corner2.x-corner1.x,corner2.y-corner1.y);
	context.closePath();
}

function Circle(colour,originv,radius,start,end,shadow) {
	var inner;

	if (typeof shadow === 'undefined') shadow = false;
	if (typeof start === 'undefined') start = 0;
	if (typeof end === 'undefined') end = 360;
	if (radius.constructor === Array) {inner=radius[0]; radius=radius[1];}

	if (shadow) {
		context.shadowBlur=15;
		context.shadowColor='rgba(0x00,0x80,0x80,0.1)';
		context.shadowOffsetX = -500;
		offset = new Vector2d(500,0);
	} else {
		offset = new Vector2d(0,0);
	}

	if (typeof inner === 'undefined') {
		context.beginPath();
		context.arc(originv.Add(offset).x,originv.Add(offset).y,radius,Rad(start),Rad(end),false);
		context.lineTo(originv.x,originv.y);
		context.closePath();
		context.fillStyle = colour;
		context.fill();
	} else {					
		context.lineWidth = radius-inner;
		context.strokeStyle = colour;
		context.beginPath();
		context.arc(originv.Add(offset).x,originv.Add(offset).y,(inner+radius)/2,Rad(start),Rad(end),false);
		context.closePath();
		context.stroke();
	}
	if (shadow) {
		context.shadowBlur=0;
		context.shadowOffsetX=0;
	}
}

function Graticule(colour,originv,radius,width,divisions,start,end,expansion) {
	var inner_radius;
	if (radius.constructor === Array) {inner_radius=radius[0]; radius=radius[1];}
	if (typeof expansion === 'undefined') expansion = 1;

	context.lineWidth = width;
	context.strokeStyle = colour;
	context.beginPath();

	for (var division = start; division <= end; division++) {
		var gratv = VectorPolar(1,expansion*(division*2*Math.PI/divisions)-Math.PI/2);
		
		var inner_radiusv = gratv.Scale(inner_radius).Add(originv);

		var radiusv = gratv.Scale(radius).Add(originv);

		if (typeof inner_radius === 'undefined') {
			context.moveTo(originv.x,originv.y);
		} else {						
			context.moveTo(inner_radiusv.x,inner_radiusv.y);
		}
		context.lineTo(radiusv.x,radiusv.y);
	}
	context.stroke();
	context.closePath();
}

function Arrow(colour,originv,radius,radius_tail,width,angle,tip_length,tip_width) {
	var base = VectorPolar(-radius_tail,Rad(angle)).Add(originv);
	var tip = VectorPolar(radius-tip_length,Rad(angle)).Add(originv);

	context.beginPath();
	context.shadowBlur=10;
	context.shadowColor='#888';
	context.moveTo(base.x,base.y);
	context.lineTo(tip.x,tip.y);
	context.lineWidth = width;
	context.strokeStyle = colour;
	context.stroke();
	context.closePath();

	Triangle(colour,tip,angle,tip_length,tip_width);
	context.shadowBlur=0;
}

function Triangle(colour,originv,angle,height,width) {
	var left = VectorPolar(width/2,Rad(angle-90)).Add(originv);
	var right = VectorPolar(width/2,Rad(angle+90)).Add(originv);
	var tip = VectorPolar(height,Rad(angle)).Add(originv);

	context.beginPath();
	context.moveTo(left.x,left.y);
	context.lineTo(tip.x,tip.y);
	context.lineTo(right.x,right.y);
	context.closePath();
	context.fillStyle = colour;
	context.fill();
}

function Springy(value) {
	if (value<400) {
		value = target_angle+(-100*Math.exp(-value/40)+0)*Math.cos((value/1.5)*value*Math.PI/360);
		return value
	} else {
		return target_angle;
	}
}

function Vector2d(x, y) {
    this.x = x;
    this.y = y;

    this.Add = function(vector) {
        return new Vector2d(this.x+vector.x, this.y+vector.y);
    };
    this.Sub = function(vector) {
        return new Vector2d(this.x-vector.x, this.y-vector.y);
    };               
    this.Dot = function(vector) {
        return new Vector2d(this.x*vector.x+this.y*vector.y);
    };
    this.Cross = function(vector) {
        return this.x*vector.y-this.y*vector.x;
    };
    this.Size = function() {
        return Math.sqrt(this.x*this.x+this.y*this.y)
    };
    this.Scale = function(size) {
        return new Vector2d(this.x*size, this.y*size);
    };
    this.Unit = function() {
        return this.Scale(1/this.Size());
    };
    this.Resize = function(size) {
        return this.Scale(size/this.Size());
    }
    this.Rotate90 = function() {
        return new Vector2d(-this.y, this.x);
    };               
    this.Rotate = function(angle) {
        return new Vector2d(this.x*Math.cos(angle)-this.y*Math.sin(angle), this.x*Math.sin(angle)+this.y*Math.cos(angle));
    };
}
function VectorPolar(size,radians) {
	return new Vector2d(size*Math.cos(radians),size*Math.sin(radians));
}

function Rad(degrees) {
	return (degrees-90)*2*Math.PI/360;
}