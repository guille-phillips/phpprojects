<?php
	header('Content-Type: text/plain; charset=utf-8');
	ini_set('html_errors', false);
	
	//$db = new mysqli('localhost', 'rnadb', 'almeria72', 'roundnabout'); // site
	$db = new mysqli('localhost', 'root', 'almeria72', 'roundnabout'); // work
	//$db = new mysqli('localhost', 'root', '', 'roundnabout'); // home

	if($db->connect_errno > 0){
		Error('Unable to connect to database [' . $db->connect_error . ']');
	}

	$db->set_charset('utf8');

	switch ($_GET['method']) {
		case 'GetPlaces':
			$categories = json_decode($_GET['value']);

			$sql = <<<SQL
				SELECT
					*
				FROM
					places
SQL;
			if (!$list = $db->query($sql)) {
				Error('There was an error running the query [' . $db->error . ']');
			};

			$rows = array();
			while($row = $list->fetch_assoc()){
				$rows[(int) $row['id']]=array(
					'id'=>(int) $row['id'],
					'name'=>$row['name'],
					'latitude'=>(float) $row['latitude'],
					'longitude'=>(float) $row['longitude'],
					'category'=>DecodeJSONField($row['category']),
					'email'=>$row['email'],
					'telephone'=>DecodeJSONField($row['telephone']),
					'address'=>DecodeJSONField($row['address']),
					'postcode'=>$row['postcode'],
					'entry_rates'=>DecodeJSONField($row['entry_rates']),
					'opening_times'=>DecodeJSONField($row['opening_times']),
					'rating'=>(int) $row['rating'],
					'more_info'=>$row['more_info'],
					'facilities'=>$row['facilities'],
					'good_stuff'=>$row['good_stuff'],
					'bad_stuff'=>$row['bad_stuff']
				);
			}

			// print_r($rows);
			echo json_encode($rows);
			break;
	}

	function DecodeJSONField($field) {
		if ($json = json_decode($field)) {
			return $json;
		} else {
			return $field;
		}
	}
	
	function Error($description) {
		die(json_encode(array('error' => $description)));
	}	
?>