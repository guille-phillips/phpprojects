<?php
	include 'key.php';
	if (isset($_GET['pin']) && $_GET['pin']=='3141') {
		$editable = true;
		define('KEY_TIMEOUT',60*60); // 60 minutes
		$key = InsertKey();
		setcookie('master',$key,time()+KEY_TIMEOUT,'/','',false,true);
	} else {
		$editable = false;
		define('KEY_TIMEOUT',5*60); // 5 minutes
		if (isset($_COOKIE['master'])) {
			setcookie('master',$_COOKIE['master'],time()-86400,'/','',false,true);
		}
	}

	include 'access.php';
	
	InsertAccess();
	
	if (isset($_COOKIE['session'])) {
		RemoveKey($_COOKIE['session']);
	}
	RemoveExpiredKeys();
	$key = InsertKey();
	setcookie('session',$key,time()+KEY_TIMEOUT,'/','',false,true);
?>
<!DOCTYPE html>
<html>
	<head>
		<script>
			map_min_zoom=<?=$editable?5:11;?>;
			place_limit=<?=$editable?-1:30;?>;
			map_type_control=<?=$editable?'true':'false';?>;
		</script>
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<script src="https://maps.googleapis.com/maps/api/js"></script>
		<script src="javascript/custom-google-map-marker.js"></script>
		<script src="javascript/jquery-2.1.4.min.js"></script>
		<script src="javascript/helper.js"></script>
		<script src="javascript/main.js"></script>
	</head>
	<body>
		<div id="menu">
			<ul>
				<li id="menu-home">About Us</li>
				<li id="menu-about-us">Claim a Business</li>
				<li id="menu-upload-a-place">Add a Business</li>
			</ul>
		</div>
		<div id="filter">
			<ul>
				<li>What day out do you fancy?&nbsp;&nbsp;&nbsp;&nbsp;</li>
				<li id="filter-all">All</li>
				<li id="filter-free">Free</li>
				<li id="filter-paid">Paid</li>
				<li id="filter-indoor">Indoor</li>
				<li id="filter-outdoor">Outdoor</li>
				<li id="filter-animals-and-nature">Animals &amp; Nature</li>
				<li id="filter-water-fun">Water Fun</li>
				<li id="filter-rides">Rides</li>
				<li id="filter-transport">Transport</li>
				<li id="filter-more" class="arrow">More
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
		<img id="current_location" src="resources/current-location.png" onclick="map_controller.GoHome();">
		<img id="search_here" src="resources/search-here.png" onclick="place_controller.ShowAtCurrentPosition();">
		<div id="place_list"><div id="place_{id}" class="place_list_item" data-id="{id}">
			<div>
				<h1>{index}. {name}</h1>
				<div class="address">{address} {postcode}</div>
				<div class="telephone">{telephone}{website/} | <a href="http://{website}" class="website" target="_blank">website</a>{/website}{email/} | <a href="mailto:{email}" class="email" target="_blank">email</a>{/email}</div>
				{category/}<div class="category_item">{category}</div>{/category}
				<br>
				<div><div id="opening_times_{id}" class="icon opening_times">&nbsp;</div><div id="entry_rates_{id}" class="icon entry_rates">&nbsp;</div><div id="more_info_{id}" class="icon more_info">&nbsp;</div><div id="facilities_{id}" class="icon facilities">&nbsp;</div><div id="disabled_{id}" class="icon disabled">&nbsp;</div><div id="good_stuff_{id}" class="icon good_stuff">&nbsp;</div><div id="bad_stuff_{id}" class="icon bad_stuff">&nbsp;</div></div>
			</div><div><img src="{image_url}">
				<div class="rating">{rating}</div>
				<?php if ($editable): ?>
				<div id="edit_{id}" class="edit">EDIT</div>
				<div id="move_{id}" class="move">MOVE</div>
				<?php endif; ?>
			</div>
			<div id="opening_times_info_{id}" class="info_box">{opening_times}</div>
			<div id="entry_rates_info_{id}" class="info_box">{entry_rates}</div>
			<div id="more_info_info_{id}" class="info_box"><p>{more_info}</p></div>
			<div id="facilities_info_{id}" class="info_box">{facilities}</div>
			<div id="disabled_info_{id}" class="info_box">{disabled_facilities}</div>
			<div id="good_stuff_info_{id}" class="info_box">{good_stuff}</div>
			<div id="bad_stuff_info_{id}" class="info_box">{bad_stuff}</div> 
		</div></div>
	</body>
</html>