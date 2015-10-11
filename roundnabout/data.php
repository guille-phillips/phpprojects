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

			$search_categories = $value->categories;
			if (count($search_categories)>0) {
				$search_values = array_fill(0,count($search_categories),false);
				$filter = array_combine($search_categories,$search_values);
			} else {
				$filter = array();
			}
			
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
				$image_url='#';
				foreach ($image_extensions as $image_extension) {
					if (file_exists('images/'.$slug.'.'.$image_extension)) {
						$image_url = 'images/'.$slug.'.'.$image_extension;
					}
				}

				$categories_json = DecodeJSONField($row['category']);

				if (count($search_categories)>0) {
					$search_values = array_fill(0,count($search_categories),false);
					$filter = array_combine($search_categories,$search_values);
				} else {
					$filter = array();
				}
			
				$ok_to_add = false;
				foreach ($search_categories as $search_category) {
					if (in_array($search_category,$categories_json)) {
						$filter[$search_category] = true;
					}
				}

				$ok_to_add = CategoryExpression($filter);
				
				if ($ok_to_add) {
					$rows[(int) $row['id']]=array(
						'id'=>(int) $row['id'],
						'name'=>$row['name'],
						'latitude'=>$latitude,
						'longitude'=>$longitude,
						'distance'=>DistanceBetween(array($latitude,$longitude),array($centre_latitude,$centre_longitude)),
						'category'=>$categories_json,
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
			}

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

	function CategoryExpression($filter) {
		if (isset($filter['All'])) {
			return true;
		} else {
			$has_other_category = false;
			foreach ($filter as $category=>$has_category) {
				switch ($category) {
					case 'All':
					case 'Paid':
					case 'Free':
					case 'Indoor':
					case 'Outdoor':
						break;
					default:
						if ($has_category) {
							$has_other_category = true;
							break;
						}
				}
			}
// echo $has_other_category?'y':'n';
			$paid = isset($filter['Paid'])?$filter['Paid']:true;
			$free = isset($filter['Free'])?$filter['Free']:true;
			$indoor = isset($filter['Indoor'])?$filter['Indoor']:true;
			$outdoor = isset($filter['Outdoor'])?$filter['Outdoor']:true;
return $has_other_category;
			if (!$has_other_category) {
				return $paid && $free && $indoor && $outdoor;
			} else {
				return $paid && $free && $indoor && $outdoor && $has_other_category;
			}
		}
		return false;
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