<?php header('Content-Type: application/javascript');?>function CustomMarker(latlng, map, args) {
	this.latlng = latlng;
	this.args = args;
	this.setMap(map);
}

CustomMarker.prototype = new google.maps.OverlayView();

CustomMarker.prototype.draw = function() {
console.log("CustomMarker.prototype::draw");
	var self = this;

	var div = this.div;

	if (!div) {

		div = this.div = document.createElement('div');

		if (self.args.className) {div.className = self.args.className};
		if (self.args.html) {div.innerHTML = self.args.html};

		if (typeof(self.args.marker_id) !== 'undefined') {
			div.dataset.marker_id = self.args.marker_id;
		}

		if (self.args.click_event) {
			google.maps.event.addDomListener(div, "click", function() {self.args.click_event(this);});
		}

		var panes = this.getPanes();
		panes.overlayImage.appendChild(div);
	}

	var point = this.getProjection().fromLatLngToDivPixel(this.latlng);

	if (point) {
		div.style.left = (point.x) + 'px';
		div.style.top = (point.y) + 'px';
	}
};

CustomMarker.prototype.remove = function() {
	// alert('removing');
console.log("CustomMarker.prototype::remove");
	if (this.div) {
		this.div.parentNode.removeChild(this.div);
		delete this.div;
		this.div = null;
	}
};

CustomMarker.prototype.getPosition = function() {
console.log("CustomMarker.prototype::getPosition");	
	return this.latlng;
};