<?php
	header('Content-Type: text/css');

	define('MENU_HEIGHT',103);
	define('FILTER_HEIGHT',34);
	define('LIST_WIDTH',580);

	$colours[0] = '#8b17e3'; // purple #8E48E3 old
	$colours[1] = '#8f8f8f'; // grey
	$colours[2] = '#0030b7'; // blue
	$colours[3] = '#ac35f4'; // light purple
	$colours[4] = '#F4EDFF'; // 

	$filter_top = (MENU_HEIGHT).'px';
	$body_top = (MENU_HEIGHT+FILTER_HEIGHT).'px';
	$menu_height = (MENU_HEIGHT).'px';
	$filter_height = (FILTER_HEIGHT).'px';
	$list_width = (LIST_WIDTH).'px';
	$menu_horiz_offset = '0px';
	$list_image_dimension = '164px';
	$list_hover = '#F4EDFF';
?>
@font-face {
	font-family: "Rooney Sans";
	src: url("../resources/fonts/Rooney Sans.woff") format('woff');
}

@font-face {
	font-family: "Rooney Sans";
	src: url("../resources/fonts/Rooney Sans Bold.woff") format('woff');
	font-weight: bold;

}

@font-face {
	font-family: "Rooney Sans";
	src: url("../resources/fonts/Rooney Sans Medium.woff") format('woff');
	font-weight: 600;
}

html {
	margin:0;
	height:96%;
	width:100%;
}

body {
	margin:0;
	height:100%;
	width:100%;
	font-family: "Rooney Sans";
}

#map_box {
	position:absolute;
	left: calc(<?=$list_width?> + 17px);
	top:<?=$body_top?>;
	width: calc(100% - <?=$list_width?> - 17px);
	height: calc(100% - <?=$body_top?>);
	background-color: #CCC;
}


#menu {
	width:calc(100% - <?=$menu_horiz_offset?>);
	height:<?=$menu_height?>;
	margin:0;
	font-size:15px;
	font-weight:0;
	background-color: <?=$colours[0]?>;
	padding-left:<?=$menu_horiz_offset?>;

	/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#4b1e7f+0,b87fff+51,8c36e3+100 */
	*background: #4b1e7f; /* Old browsers */
	*background: -moz-linear-gradient(left,  #4b1e7f 0%, #b87fff 51%, #8c36e3 100%); /* FF3.6-15 */
	*background: -webkit-linear-gradient(left,  #4b1e7f 0%,#b87fff 51%,#8c36e3 100%); /* Chrome10-25,Safari5.1-6 */
	*background: linear-gradient(to right,  #4b1e7f 0%,#b87fff 51%,#8c36e3 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
	*filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#4b1e7f', endColorstr='#8c36e3',GradientType=1 ); /* IE6-9 */
}

#menu > ul {
	color: white;
	margin:0;
	cursor:default;
	text-align:right;
}
#menu > ul > li {
	display:inline-block;
	padding:0px 5px 0px 5px;
	vertical-align:top;
	cursor:pointer;
	height:18px;
	border-top:4px solid <?=$colours[0]?>
}

#menu > ul > li:not(:first-child) {
	border-left:1px solid white;
}

/* Filter Menu */
.switch-on {
	background-image:url('../resources/check-on.png');
	background-repeat:no-repeat;
	background-position: right center;
	background-size: 20px;
}

.switch-off {
	background-image:url('../resources/check-off.png');
	background-repeat:no-repeat;
	background-position: right center;
	background-size: 20px;
}

.arrow {
	background-image:url('../resources/right-arrow.png');
	background-repeat:no-repeat;
	background-position: right 5px;
	background-size: 15px;
}

#filter {
	width:calc(100% - <?=$menu_horiz_offset?>);
	height:<?=$filter_height?>;
	margin:0;
	font-size:18px;
	font-weight:600;
	padding-left:<?=$menu_horiz_offset?>;
	background-color:black;
}

#filter > ul {
	background-color: black;
	color: white;
	margin:0;
	height:<?=$filter_height?>;
}
#filter > ul > li {
	display:inline-block;
	/* border-right:1px solid #888; */
	padding:5px 23px 1px 3px;
	vertical-align:top;
	cursor:pointer;
}

#filter > ul > li > ul {
	position:absolute;
	top:<?=$body_height?>;
	left:1100px;
	width:150px;
	height:calc(40ex + 10px);
	display:block;
	z-index:99;
	background-color:black;
	display:none;
	margin:0;
	padding:5px;
	list-style-type: none;
}


.blur {
	-webkit-filter: blur(2px);
	-moz-filter: blur(2px);
	-o-filter: blur(2px);
	-ms-filter: blur(2px);
}


/* Listing */
#place_list {
	position:absolute;
	top:<?=$body_top?>;
	left:0px;
	width:calc(<?=$list_width?> + 17px);
	height:calc(100% - <?=$body_top?>);
	overflow-y:scroll;
	overflow-x:hidden;
}

#place_list > div:first-child {
	display:none;
}

.place_list_item {
	position:relative;
	width:calc(<?=$list_width?> - 8px - 20px);
	height:calc(240px - 20px);
	border:4px solid <?=$colours[0]?>;
	padding:10px;
	cursor:pointer;
}

.place_list_item:hover {
	background-color:<?=$colours[4]?>;
	border:4px solid <?=$colours[3]?>;
}

.place_list_item > div:nth-child(1) {
	display:inline-block;
	vertical-align:top;
	width:calc(<?=$list_width?> - <?=$list_image_dimension?> - 20px - 8px);
	height:calc(170px - 2px);
}

.place_list_item > div:nth-child(2) {
	position:relative;	
	display:inline-block;
	vertical-align:top;
	vertical-align:top;
	width:calc(<?=$list_image_dimension?> - 2px);
	height:calc(<?=$list_image_dimension?> - 2px);
	border:1px solid #ccc;
}

.rating {
	position:absolute;
	top:7px;
	right:7px;
	width:48px;
	height:43px;
	padding-top:5px;
	padding-left:2px;
	padding-right:2px;

	background-color:<?=$colours[0]?>;
	color:white;

	text-align:center;
	font-size:23px;
}

.edit {
	position:absolute;
	top:76px;
	right:0px;
	width:100%;
	height:31px;
	background-color:#888;
	border:1px solid white;
	color:white;
	text-align:center;
	font-size:24px;	
	font-weight:bold;
	    opacity: 0.7;
}

.move {
	position:absolute;
	top:126px;
	right:0px;
	width:100%;
	height:31px;
	background-color:#888;
	border:1px solid white;
	color:white;
	text-align:center;
	font-size:24px;	
	font-weight:bold;
	opacity: 0.7;
}

.place_list_item > div:nth-child(2) > img {
	border:1px solid #ccc;
	width:100%;
	height:100%;
}

.info_box {
	border-left:2px solid <?=$colours[0]?>;
	border-right:2px solid <?=$colours[0]?>;
	border-bottom:2px solid <?=$colours[0]?>;
	z-index:999;
	display:none;
	padding:0px 10px 3px 10px;
	background-color:<?=$colours[4]?>;
}

.place_list_item > div:nth-child(3) {

}
.place_list_item > div:nth-child(4) {

}
.place_list_item > div:nth-child(5) {

}
.place_list_item > div:nth-child(6) {

}
.place_list_item > div:nth-child(7) {

}
.place_list_item > div:nth-child(8) {

}
.place_list_item > div:nth-child(9) {

}

h1 {
	color:black;
	margin-top:-5px;
	margin-bottom:4px;
	font-size:22px;
	text-overflow:ellipsis;
	overflow:hidden;
	white-space:nowrap;
}

h1:hover {
	overflow:initial;
	background-color:<?=$list_hover;?>;
}

.category_item {
	display:inline-block;
	background-color:<?=$colours[1]?>;
	color:white;
	padding:0px 7px 0px 5px;
	margin-right:4px;
	margin-bottom:2px;
	font-size:13px;
}

.address {
	/*border:1px solid red;*/
	color:<?=$colours[1]?>;
}

.telephone {
	color:<?=$colours[2]?>;
}

.website {
	display:block;
	color:<?=$colours[2]?>;
	text-decoration:none;
	text-overflow:ellipsis;
	overflow:hidden;
	white-space:nowrap;
}

.website:hover {
	text-decoration:underline;
}
.website:visited {
	color:<?=$colours[0]?>;
	text-decoration:underline;
}

.icon {
	display:inline-block;
	vertical-align:top;
	width:45px;
	height:45px;
	*border:1px solid black;
	margin-right:20px;
	background-size:35px;
	background-repeat:no-repeat;
	background-position:left bottom;
}

.opening_times {
	background-image:url('../resources/openingtimes_icon.svg');
	background-repeat:no-repeat;
}

.entry_rates {
	background-image:url('../resources/entryrates-icon.svg');
	background-repeat:no-repeat;
}

.more_info {
	background-image:url('../resources/moreinfo-icon.svg');
	background-repeat:no-repeat;
}

.disabled {
	background-image:url('../resources/disabled-icon.svg');
	background-repeat:no-repeat;
}

.facilities {
	background-image:url('../resources/facilities-icon.svg');
	background-repeat:no-repeat;
}

.good_stuff {
	background-image:url('../resources/goodstuff-icon.svg');
	background-repeat:no-repeat;
}

.bad_stuff {
	background-image:url('../resources/badstuff-icon.svg');
	background-repeat:no-repeat;
}


.description {
	border:1px solid black;
}


/* Map Markers */

.marker {
	position:absolute;
/*border:1px solid red;*/
}

.marker-pin {
	position:relative;
	left:-21px;
	top:-52px;
	width:42px;
	height:60px;
	background-image:url('../resources/pin.png');
	background-repeat:no-repeat;
	font-family:'Rooney Sans';
	font-size:18px;
	font-weight:bold;
	padding:7px 0px 0px 0px;
	text-align:center;
	color:white;
/*border:1px solid blue;*/
}

.marker-home {
	position:relative;
	left:-61.5px;
	top:-61.5px;
	width:123px;
	height:123px;
	background-image:url('../resources/home-marker.png');
	background-repeat:no-repeat;
}

.marker-bubble-left {
	position:relative;
	left:-115px;
	top:-251px;
	width:calc(430px - 30px);
	height:calc(201px - 30px);
	padding:15px;
	background-image:url('../resources/bubble-left.png');
	background-repeat:no-repeat;
	display:none;
	z-index:999;
	*border:1px solid green;
}



/* Other */

#logo {
	background-image:url('../resources/logo.png');
	background-repeat:no-repeat;
	background-size: 192px;
	position:absolute;
	top:0px;
	left:0px;
	width:190px;
	height:calc(<?=$body_top?> + 11px);
	background-position: left -3px;
	z-index: 1;
}

#current_location {
	position:relative;
	top:20px;
	left:617px;
	width:auto;
	height:auto;
	max-width:210px;
	display:block;
	cursor:pointer;
}

#search_here {
	position:relative;
	top:-34px;
	left:964px;
	width:auto;
	height:auto;
	max-width:150px;
	display:block;
	cursor:pointer;
}