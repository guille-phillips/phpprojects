<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" type="text/css" href="css/main.css.php">
		<script src="https://maps.googleapis.com/maps/api/js"></script>
		<script src="javascript/custom-google-map-marker.js"></script>
		<script src="javascript/jquery-2.1.4.min.js"></script>
		<script src="javascript/helper.js"></script>
		<script src="javascript/main.js"></script>
	</head>
	<body>
		<div id="menu">
			<ul>
				<li id="menu-home">Home</li>
				<li id="menu-about-us">About Us</li>
				<li id="menu-upload-a-place">Upload a Place</li>
			</ul>
		</div>
		<div id="filter">
			<ul>
				<li id="filter-all">All</li>
				<li id="filter-free">Free</li>
				<li id="filter-paid">Paid</li>
				<li id="filter-indoor">Indoor</li>
				<li id="filter-outdoor">Outdoor</li>
				<li id="filter-animals-and-nature">Animals &amp; Nature</li>
				<li id="filter-water-fun">Water Fun</li>
				<li id="filter-rides">Rides</li>
				<li id="filter-transport">Transport</li>
				<li id="filter-more">More
					<ul>
						<li id="filter-play-centre">Play Centre</li>
						<li id="filter-history">History</li>
						<li id="filter-beaches">Beaches</li>
					</ul>
				</li>
			</ul>
		</div>
		<div id="logo">&nbsp;</div>
		<div id="map_box"></div>
		<div id="place_list"><div id="place_{id}" class="place_list_item" data-id="{id}">
			<div>
				<h1>{index}. {name}</h1>
				<div class="address">{address}</div>
				<div class="telephone">{telephone}</div>
				<a href="http://{website}" class="website">{website}</a>
				<br>
				{category/}<div class="category_item">{category}</div>{/category}
				<br>
				<div class="icon opening_times">&nbsp;</div><div class="icon prices">&nbsp;</div><div class="icon comments">&nbsp;</div><div class="icon email">&nbsp;</div><div class="rating">{rating}</div>
			</div><img src="{image_url}">
		</div><div>
	</body>
</html>