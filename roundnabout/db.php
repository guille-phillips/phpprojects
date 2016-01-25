<?php
	switch ($_SERVER['HTTP_HOST']) {
		case 'localhost:8080':
			$db = new mysqli('localhost', 'root', '', 'roundnabout'); // home
			break;
		case 'localhost':
			$db = new mysqli('localhost', 'root', 'almeria72', 'roundnabout'); // work
			break;
		default:
			$db = new mysqli('localhost', 'rnadb', 'almeria72', 'roundnabout'); // site
	}
	