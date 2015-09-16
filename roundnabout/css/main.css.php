<?php
	header('Content-Type: text/css');
	
	define('MENU_HEIGHT',40);
	define('FILTER_HEIGHT',40);
	define('LIST_WIDTH',400);
	
	$filter_top = (MENU_HEIGHT).'px';
	$body_top = (MENU_HEIGHT+FILTER_HEIGHT).'px';
	$menu_height = (MENU_HEIGHT).'px';
	$filter_height = (FILTER_HEIGHT).'px';
	$list_width = (LIST_WIDTH).'px';
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

#place_list {
	position:absolute;
	top:<?=$body_top?>;
	left:0px;
	width:<?=$list_width?>;
	height:calc(100% - <?=$body_top?>);
	overflow-y:scroll;
	overflow-x:hidden;
}

.place_list_item {
	width:90%;
	/*height:400px;*/
	background-color: white;
	border:1px solid black;
	margin-bottom: 15px;
	padding:3px;
	cursor:pointer;
}

#menu {
	width:100%;
	height:<?=$menu_height?>;
	margin:0;
	font-size:25px;
}

#menu > ul {
	background-color: blue;
	color: white;
	margin:0;
}
#menu > ul > li {
	display:inline-block;
	border-right:1px solid #888;
	padding:0px 5px 0px 5px;
	vertical-align:top;
	cursor:default;
	height:<?=$menu_height?>;
}

#filter {
	width:100%;
	height:<?=$filter_top?>;
	margin:0;
	font-size:25px;
}

#filter > ul {
	background-color: black;
	color: white;
	margin:0;
}
#filter > ul > li {
	display:inline-block;
	border-right:1px solid #888;
	padding:0px 5px 0px 5px;
	vertical-align:top;
	cursor:default;
	height:<?=$filter_height?>;
}
#filter > ul > li:after {
	content:'*';
}
#filter > ul > li > ul {
	position:absolute;
	top:<?=$body_height?>;
	left:0px;
	width:200px;
	height:200px;
	display:block;
}


.blur {
	-webkit-filter: blur(2px);
	-moz-filter: blur(2px);
	-o-filter: blur(2px);
	-ms-filter: blur(2px);			
}



.square {
	width:120px;
	height:120px;
	float:left;
	border:1px solid #ccc;
	margin-right:10px;
}

h1 {
	color:#001fb7;
	margin-top:0;
}

.category_item {
	display:inline-block;
	background-color:blue;
	color:white;
	padding:0px 3px 0px 3px;
	margin:3px;
}

.address {
	/*border:1px solid red;*/
}

.description {
	border:1px solid black;
}


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