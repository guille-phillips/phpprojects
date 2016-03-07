<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width">
		<link rel="stylesheet" type="text/css" href="css/main.css.php">
		<script src="javascript/jquery-2.1.4.min.js"></script>
		<style>
			* {
				margin:0;
				padding:0;
			}
			html {
				width:100%;
				height:98%;
			}
			body {
				width:100%;
				height:100%;		
				background-image:url('./resources/holding-page.png');
				background-size: 100% 100%;
				background-repeat: no-repeat;
				background-position: center center;
				background-color: #8E48E3;
				overflow-y:hidden;
			}
			video {
				width:100%;
				position:relative;
				top:50%;
				transform: translateY(-50%);
			}
		</style>
	</head>
	<body>
		<video autoplay loop>
		  <source src="./resources/holding.webm" type='video/webm;codecs="vp8, vorbis"'/>
		  <source src="./resources/holding.mp4" type='video/mp4;codecs="avc1.42E01E, mp4a.40.2"'/>
		  <source src="./resources/holding.ogg" type='video/ogg;codecs="theora, vorbis"'/>				  
		</video>
	</body>
</html>	