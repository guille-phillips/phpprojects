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
						<li id="filter-activity-centre">Activity Centre</li>
						<li id="filter-adventure">Adventure</li>
						<li id="filter-bowling">Bowling</li>
						<li id="filter-educational">Educational</li>
						<li id="filter-farm">Farm</li>
						<li id="filter-go-karting">Go Karting</li>
						<li id="filter-historical">Historical</li>
						<li id="filter-leisure-centre">Leisure Centre</li>
						<li id="filter-museum">Museum</li>
						<li id="filter-nature">Nature</li>
						<li id="filter-park">Park</li>
						<li id="filter-play-centre">Play Centre</li>
						<li id="filter-playground">Playground</li>
						<li id="filter-skatepark">Skatepark</li>
						<li id="filter-softplay">Softplay</li>
						<li id="filter-theme-park">Theme Park</li>
					</ul>
				</li>
			</ul>
		</div>
		<div id="logo">&nbsp;</div>
		<div id="map_box"></div>
		<div id="place_list"><div id="place_{id}" class="place_list_item" data-id="{id}">
			<div>
				<h1>{index}. {name}</h1>
				<div class="address">{address} {postcode}</div>
				<div class="telephone">{telephone}</div>
				<a href="http://{website}" class="website" target="_blank">{website}</a>
				{category/}<div class="category_item">{category}</div>{/category}
				<br>
				<div id="opening_times_{id}" class="icon opening_times">&nbsp;</div><div id="entry_rates_{id}" class="icon entry_rates">&nbsp;</div><div id="comments_{id}" class="icon comments">&nbsp;</div><div id="disabled_{id}" class="icon disabled">&nbsp;</div><div id="email_{id}" class="icon email">&nbsp;</div>
			</div><div><img src="{image_url}"><div class="rating">{rating}</div></div>
			<div id="opening_times_info_{id}" class="info_box">{opening_times}</div>
			<div id="entry_rates_info_{id}" class="info_box">{entry_rates}</div>
			<div id="comments_info_{id}" class="info_box"><p>{more_info}</p><p>{facilities}</p><p>{good_stuff}</p><p>{bad_stuff}</p></div>
			<div id="disabled_info_{id}" class="info_box">{disabled_facilities}</div>
			<div id="email_info_{id}" class="info_box">{email}</div>
		</div></div>
	</body>
</html>