<?php
	$response = file_get_contents('https://www.google.co.uk');

	$time = date('H:i:s');
	$title = $response==''?"DOWN @ $time":"UP @ $time";
	$text = $response==''?"Internet is DOWN @ $time":"Internet is UP @ $time";
	$color = $response==''?"background-color:red":"background-color:green";
	$icon_colour = $response==''?'red':'green';
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="refresh" content="10">
		<title><?=$title;?></title>

		<link rel="apple-touch-icon" sizes="57x57" href="./apple-touch-icon-57x57-<?=$icon_colour?>.png">
		<link rel="apple-touch-icon" sizes="60x60" href="./apple-touch-icon-60x60-<?=$icon_colour?>.png">
		<link rel="icon" type="image/png" href="./favicon-32x32-<?=$icon_colour?>.png" sizes="32x32">
		<link rel="icon" type="image/png" href="./favicon-16x16-<?=$icon_colour?>.png" sizes="16x16">
		<link rel="manifest" href="./manifest.json">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="theme-color" content="#ffffff">		
	</head>
	<body style="<?=$color;?>">
		<?=$text?>
	</body>
</html>