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

function VectorPolar(dist,deg) {
	return new Vector2d(dist*Math.cos(2*Math.PI*deg/360),dist*Math.sin(2*Math.PI*deg/360));
}
