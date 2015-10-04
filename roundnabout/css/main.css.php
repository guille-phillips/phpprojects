<?php
	header('Content-Type: text/css');
	
	define('MENU_HEIGHT',56);
	define('FILTER_HEIGHT',34);
	define('LIST_WIDTH',650);
	
	$colours[0] = '#001fb7';
	$colours[1] = '#8f8f8f';
	
	$filter_top = (MENU_HEIGHT).'px';
	$body_top = (MENU_HEIGHT+FILTER_HEIGHT).'px';
	$menu_height = (MENU_HEIGHT).'px';
	$filter_height = (FILTER_HEIGHT).'px';
	$list_width = (LIST_WIDTH).'px';
	$menu_horiz_offset = '300px';
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
	left:<?=$list_width?>;
	top:<?=$body_top?>;				
	width: calc(100% - <?=$list_width?>);
	height: calc(100% - <?=$body_top?>);
	background-color: #CCC;
}


#menu {
	width:calc(100% - <?=$menu_horiz_offset?>);
	height:<?=$menu_height?>;
	margin:0;
	font-size:20px;
	font-weight:600;
	background-color: blue;
	padding-left:<?=$menu_horiz_offset?>;
	
	/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#1e5799+0,007eff+50,2989d8+100,7db9e8+100 */
	background: #1e5799; /* Old browsers */
	background: -moz-linear-gradient(left,  #1e5799 0%, #007eff 50%, #2989d8 100%, #7db9e8 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, right top, color-stop(0%,#1e5799), color-stop(50%,#007eff), color-stop(100%,#2989d8), color-stop(100%,#7db9e8)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(left,  #1e5799 0%,#007eff 50%,#2989d8 100%,#7db9e8 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(left,  #1e5799 0%,#007eff 50%,#2989d8 100%,#7db9e8 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(left,  #1e5799 0%,#007eff 50%,#2989d8 100%,#7db9e8 100%); /* IE10+ */
	background: linear-gradient(to right,  #1e5799 0%,#007eff 50%,#2989d8 100%,#7db9e8 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=1 ); /* IE6-9 */	
}

#menu > ul {
	color: white;
	margin:0;
}
#menu > ul > li {
	display:inline-block;
	/* border-right:1px solid #888; */
	padding:0px 5px 0px 5px;
	vertical-align:top;
	cursor:default;
	height:<?=$menu_height?>;
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
}
#filter > ul > li {
	display:inline-block;
	/* border-right:1px solid #888; */
	padding:5px 23px 1px 3px;
	vertical-align:top;
	cursor:default;
	height:<?=$filter_height?>;
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
	width:calc(<?=$list_width?> - 4px - 30px);
	height:calc(237px - 30px);
	border:2px solid white;
	padding:15px;
	cursor:pointer;
}

.place_list_item:hover {
	background-color:#ecf8fe;
	border:2px solid <?=$colours[0]?>;
}

.place_list_item > div {
	display:inline-block;
	vertical-align:top;
	/* border:1px solid red; */
	width:calc(650px - 30px - 209px - 14px);
	height:calc(207px - 2px);
}

.place_list_item > img {
	display:inline-block;
	vertical-align:top;
	width:calc(207px - 2px);
	height:calc(207px - 2px);
	border:1px solid #ccc;
	margin-right:10px;
}

h1 {
	color:<?=$colours[0]?>;
	margin-top:0;
	margin-bottom:4px;
	font-size:22px;
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
	color:<?=$colours[0]?>;
}

.website {
	/*border:1px solid red;*/
	color:<?=$colours[0]?>;
	text-decoration:none;
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
	background-size:45px;
	background-repeat:no-repeat;
	background-position:center bottom;
}

.opening_times {
	background-image:url('../resources/clock-icon.png');
}

.prices {
	background-image:url('../resources/pound-icon.png');
}

.comments {
	background-image:url('../resources/speech-icon.png');
}

.email {
	background-image:url('../resources/envelope-icon.png');
}

.rating {
	display:inline-block;
	vertical-align:top;	
	width:45px;
	height:45px;
	
	border-radius:10px;
	background-color:<?=$colours[0]?>;
	color:white;
	
	text-align:center;
	font-size:30px;
}

.description {
	border:1px solid black;
}


/* Map Markers */

.marker {
	position:absolute;
}

.marker-pin {
	position:relative;
	left:-21px;
	top:-52px;
	width:42px;
	height:60px;
	/*border:1px solid red;*/
	background-image:url('../resources/pin-144ppi.png');
	background-repeat:no-repeat;
	font-family:'Rooney Sans';
	font-size:18px;
	font-weight:bold;
	padding:7px 0px 0px 0px;
	text-align:center;
	color:white;
}

.marker-home {
	position:relative;
	left:-64px;
	top:-64px;
	width:127px;
	height:127px;
	background-image:url('../resources/home-marker.png');
	background-repeat:no-repeat;
}

.marker-bubble-left {
	position:relative;
	left:-51px;
	top:-240px;
	width:calc(443px - 30px);
	height:calc(193px - 30px);
	/*border:1px solid red;*/
	padding:15px;
	background-image:url('../resources/bubble-left.png');
	background-repeat:no-repeat;
	display:none;
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


/* Other */

#logo {
	background-image:url('../resources/logo.png');
	background-repeat:no-repeat;
	background-size: 130px;
	position:absolute;
	top:0px;
	left:0px;
	width:<?=$menu_horiz_offset?>;
	height:<?=$body_top?>;
	background-position: right top;
}