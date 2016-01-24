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
			if (!CheckKeyValid($_GET['session'])) {
				echo "Your session has expired due to inactivity.\n\nPlease reload the page.";
				exit;
			}

			$value = json_decode($_GET['value']);
			$category_controller = new CategoryController($value->categories);
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
			}

			$image_extensions = array('jpg','png','gif');

			$rows = array();
			while($row = $list->fetch_assoc()){
				$latitude = (float) $row['latitude'];
				$longitude = (float) $row['longitude'];

				$slug = strtolower(str_replace(' ','-',$row['name']));

				$image_extension = 'jpg';
				$image_url='images/no-image.png';
				foreach ($image_extensions as $image_extension) {
					if (file_exists('images/'.$slug.'.'.$image_extension)) {
						$image_url = 'images/'.$slug.'.'.$image_extension;
					}
				}

				$categories_json = DecodeJSONField($row['category']);
				
				if ($category_controller->Accept($categories_json)) {
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
						'rating'=>number_format((float) $row['rating'],1,'.',''),
						'more_info'=>(($row['more_info']!='')?$row['facilities']:'Sorry no info'),
						'disabled_facilities'=>(($row['disabled_facilities']!='')?$row['facilities']:'Sorry no info'),
						'facilities'=>(($row['facilities']!='')?$row['facilities']:'Sorry no info'),
						'good_stuff'=>(($row['good_stuff']!='')?$row['facilities']:'Sorry no info'),
						'bad_stuff'=>(($row['bad_stuff']!='')?$row['facilities']:'Sorry no info'),
						'image_url'=>$image_url
					);
				}
			}

			echo json_encode($rows);
			break;
	}

	function CheckKeyValid($key) {
		for ($minute=0; $minute<5; $minute++){
			$dt = date('Ymdhi',strtotime("-$minute minutes",time()));
			$session = sha1(fisherYatesShuffle($dt.'£*Fnd98s',3141));
			if ($key == $session) {
				return true;
			}
		}
		return false;
	}
	
	function fisherYatesShuffle($str, $seed){
		@mt_srand($seed);
		$items = str_split($str);
		for ($i = count($items) - 1; $i > 0; $i--){
			$j = @mt_rand(0, $i);
			$tmp = $items[$i];
			$items[$i] = $items[$j];
			$items[$j] = $tmp;
		}
		return implode('',$items);
	}	

	function DecodeJSONField($field) {
		if ($json = json_decode($field)) {
			return $json;
		} else {
			return $field;
		}
	}
	
	class CategoryController {
		private $filter = array();
		private $has_all = false;
		private $has_paid = false;
		private $has_free = false;
		private $has_indoor = false;
		private $has_outdoor = false;
		private $has_other = false;
		
		public function __construct($filter) {
			$this->filter = $filter;
			$this->has_all = in_array('All',$filter);
			$this->has_paid = in_array('Paid',$filter);
			$this->has_free = in_array('Free',$filter);
			$this->has_indoor = in_array('Indoor',$filter);
			$this->has_outdoor = in_array('Outdoor',$filter);
			
			foreach ($filter as $filter_category) {
				switch ($filter_category) {
					case 'All':
					case 'Paid':
					case 'Free':
					case 'Indoor':
					case 'Outdoor':
						break;
					default:
						$this->has_other = true;
						break 2;
				}
			}
		}
		
		public function Accept($categories) {
			// return true;
			if ($this->has_all) {
				return true;
			}
			if (!is_array($categories)) {
				return false;
			}
			$paid = in_array('Paid',$categories);
			$free = in_array('Free',$categories);
			$indoor = in_array('Indoor',$categories);
			$outdoor = in_array('Outdoor',$categories);
			$other = false;
			foreach ($categories as $category) {
				switch ($category) {
					case 'Paid':
					case 'Free':
					case 'Indoor':
					case 'Outdoor':
						break;
					default:
						$other = in_array($category,$this->filter);
						if ($other) {
							break 2;
						}
				}			
			}
			
			$ok1 = true;
			if ($this->has_paid && $this->has_free) {
				$ok1 = $paid || $free;
			} elseif ($this->has_paid) {
				$ok1 = $paid;
			} elseif ($this->has_free) {
				$ok1 = $free;
			}
			
			$ok2 = true;
			if ($this->has_indoor && $this->has_outdoor) {
				$ok2 = $indoor || $outdoor;
			} elseif ($this->has_indoor) {
				$ok2 = $indoor;
			} elseif ($this->has_outdoor) {
				$ok2 = $outdoor;
			}
			
			$ok3 = true;
			if ($this->has_other) {
				$ok3 = $other;
			}
			return $ok1 && $ok2 && $ok3;
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