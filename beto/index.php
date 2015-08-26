<?php

	var_dump($_GET);

	$split = explode('/', $_GET['param']);

	var_dump($split);

	$controller = strtolower($split[0]);

	switch ($controller) {
		case 'api':
			$method = $split[1];
			$client_key = $split[2];
			//include 'api.php';
			break;
		case 'view':
			break;
	}

	if ($_GET) {

	}