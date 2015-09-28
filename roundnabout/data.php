<?php
	header('Content-Type: text/plain; charset=utf-8');
	ini_set('html_errors', false);
	
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
	
	if($db->connect_errno > 0){
		Error('Unable to connect to database [' . $db->connect_error . ']');
	}

	$db->set_charset('utf8');

	switch ($_GET['method']) {
		case 'GetPlaces':
		
			$value = json_decode($_GET['value']);

			$categories = $value->categories;
			$centre_latitude = $value->position[0];
			$centre_longitude = $value->position[1];

			$sql = <<<SQL
				SELECT
					*
				FROM
					places
SQL;
			if (!$list = $db->query($sql)) {
				Error('There was an error running the query [' . $db->error . ']');
			};

			$image_extensions = array('jpg','png','gif');
			
			$rows = array();
			while($row = $list->fetch_assoc()){
				$latitude = (float) $row['latitude'];
				$longitude = (float) $row['longitude'];

				$slug = strtolower(str_replace(' ','-',$row['name']));
				
				$image_extension = 'jpg';
				// $image_url='#';
				// foreach ($image_extensions as $image_extension) {
					// if (file_exists('images/'.$slug.'.'.$image_extension)) {
						$image_url = 'images/'.$slug.'.'.$image_extension;
					// }
				// }
				
				$rows[(int) $row['id']]=array(
					'id'=>(int) $row['id'],
					'name'=>$row['name'],
					'latitude'=>$latitude,
					'longitude'=>$longitude,
					'distance'=>DistanceBetween(array($latitude,$longitude),array($centre_latitude,$centre_longitude)),
					'category'=>DecodeJSONField($row['category']),
					'email'=>$row['email'],
					'telephone'=>DecodeJSONField($row['telephone']),
					'address'=>DecodeJSONField($row['address']),
					'postcode'=>$row['postcode'],
					'website'=>$row['website'],
					'entry_rates'=>DecodeJSONField($row['entry_rates']),
					'opening_times'=>DecodeJSONField($row['opening_times']),
					'rating'=>(int) $row['rating'],
					'more_info'=>$row['more_info'],
					'facilities'=>$row['facilities'],
					'good_stuff'=>$row['good_stuff'],
					'bad_stuff'=>$row['bad_stuff'],
					'image_url'=>$image_url
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

	function DistanceBetween($latlong1,$latlong2) {
		$lat2 = $latlong2[0];
		$lon2 = $latlong2[1];
		$lat1 = $latlong1[0];
		$lon1 = $latlong1[1];

		$R = 3959; // miles 

		$x1 = $lat2-$lat1;
		$dLat = 2*pi()*$x1/360;  
		$x2 = $lon2-$lon1;
		$dLon = 2*pi()*$x2/360;  
		$a = sin($dLat/2) * sin($dLat/2) + 
						cos(2*pi()*$lat1/360) * cos(2*pi()*$lat2/360) * 
						sin($dLon/2) * sin($dLon/2);  
		$c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
		$d = $R * $c; 
		
		return $d;
	}	
?>