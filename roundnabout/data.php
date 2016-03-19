<?php
	header('Content-Type: text/plain; charset=utf-8');
	ini_set('html_errors', false);

	require_once 'db.php';
	
	if($db->connect_errno > 0){
		Error('Unable to connect to database [' . $db->connect_error . ']');
	}

	include 'key.php';
	
	$master = false;
	if (isset($_COOKIE['master'])) {
		if (IsValidKey($_COOKIE['master'])){
			define('KEY_TIMEOUT',60*60); // 60 minutes
			$master = true;
		} else {
			define('KEY_TIMEOUT',5*60); // 60 minutes
		}
	} else {
		define('KEY_TIMEOUT',5*60); // 60 minutes
	}
	
	
	$db->set_charset('utf8');

	switch ($_GET['method']) {
		case 'Search':
			if (!isset($_COOKIE['session'])) {
				die("Your session has expired due to inactivity.\n\nPlease reload the page.1");
			} else {
				$key = $_COOKIE['session'];
				if (IsValidKey($key)) {
					RemoveKey($key);
					$key = InsertKey();
					setcookie('session',$key,time()+KEY_TIMEOUT,'/','',false,true);
				} else {
					setcookie('session',$key,time()-86400,'/','',false,true);
					die("Your session has expired due to inactivity.\n\nPlease reload the page.2");
				}
			}
			
			RemoveExpiredKeys();
			
			$value = json_decode($_GET['value']);
			$search = '%'.$value->search.'%';
			$search_results = array();

			$sql = <<<SQL
				SELECT
					id,
					name,
					shortname,
					latitude,
					longitude,
					placetype
				FROM
					placenames
				WHERE
					name LIKE ?
				ORDER BY
					name
SQL;

			if ($stmt = $db->prepare($sql)) {
				$stmt->bind_param("s", $search);
				$stmt->execute();
				$stmt->bind_result(
					$id,
					$name,
					$shortname,
					$latitude,
					$longitude,
					$placetype
				);
				
				while ($stmt->fetch()) {
					$search_results[] = array(0,$name,$latitude,$longitude);
				}
				$stmt->close();
			}	
			
			$sql = <<<SQL
				SELECT
					id,
					name,
					latitude,
					longitude
				FROM
					places
				WHERE
					name LIKE ?
				ORDER BY
					name
SQL;

			if ($stmt = $db->prepare($sql)) {
				$stmt->bind_param("s", $search);
				$stmt->execute();
				$stmt->bind_result(
					$id,
					$name,
					$latitude,
					$longitude
				);
				
				while ($stmt->fetch()) {
					$search_results[] = array($id,$name,$latitude,$longitude);
				}
				$stmt->close();
			}
		
			echo json_encode($search_results);
			
			break;
		case 'GetPlaces':
			if (!isset($_COOKIE['session'])) {
				die("Your session has expired due to inactivity.\n\nPlease reload the page.");
			} else {
				$key = $_COOKIE['session'];
				if (IsValidKey($key)) {
					RemoveKey($key);
					$key = InsertKey();
					setcookie('session',$key,time()+KEY_TIMEOUT,'/','',false,true);
				} else {
					setcookie('session',$key,time()-86400,'/','',false,true);
					die("Your session has expired due to inactivity.\n\nPlease reload the page.");
				}
			}
			
			RemoveExpiredKeys();
			
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
					$address_divvy = Divvy(array_merge(DecodeJSON($row['address'],true),array($row['postcode'])), 2);
					$address_split = array_map(function($el){return implode(', ',$el);},$address_divvy);
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
						'address1'=>$address_split[0],
						'address2'=>$address_split[1],
						'website'=>$row['website'],
						'entry_rates'=>DecodeJSONField($row['entry_rates']),
						'opening_times'=>DecodeJSONField($row['opening_times'],"<br>"),
						'rating'=>number_format((float) $row['rating'],1,'.',''),
						'more_info'=>(($row['more_info']!='')?$row['more_info']:'Sorry no info'),
						'disabled_facilities'=>(($row['disabled_facilities']!='')?$row['disabled_facilities']:'Sorry no info'),
						'facilities'=>(($row['facilities']!='')?$row['facilities']:'Sorry no info'),
						'good_stuff'=>(($row['good_stuff']!='')?$row['good_stuff']:'Sorry no info'),
						'bad_stuff'=>(($row['bad_stuff']!='')?$row['bad_stuff']:''),
						'image_url'=>$image_url
					);
				}
			}
			
			usort($rows,function($a,$b){return $a['distance']-$b['distance'];});
			
			// limit results for normal users
			if (!$master) {
				$count = 0;
				foreach ($rows as $index=>$row) {
					if ($row['distance']>=20) {
						unset($rows[$index]);
					}
					$count++;
				}
			}
			
			echo json_encode($rows);
			break;
	}

	function DecodeJSONField($field,$join=null) {
		if ($json = json_decode($field,true)) {
			if ($join) {
				return implode($join,$json);
			} else {
				return $json;
			}
		} else {
			return $field;
		}
	}
	function DecodeJSON($field,$remove_blank=false) {
		if ($json = json_decode($field,true)) {
			if (!$remove_blank) {
				return $json;
			} else {
				return array_filter($json,function($el){return $el!='';});
			}
		} else {
			return array();
		}
	}
	
	function Divvy($array_var, $number_of_sub_arrays) {
		$result = array();
		if (count($array_var)>0) {
			$mod = count($array_var)%$number_of_sub_arrays;
			if ($mod == 0) {
				$in_each = floor(count($array_var)/$number_of_sub_arrays);
			} else {
				$deficit = count($array_var)+($number_of_sub_arrays-$mod);
				$in_each = floor($deficit/$number_of_sub_arrays);
			}
			$not_finished = true;
			$index = 0;
			while ($not_finished) {
				$sub_result = array();
				for ($i=0; $i<$in_each; $i++) {
					if ($index<count($array_var)) {
						$sub_result[]=$array_var[$index];
					} else {
						$not_finished = false;
					}
					$index++;
				}
				if (count($sub_result)>0) {
					$result[] = $sub_result;
				}
			}
		}
		while (count($result)<$number_of_sub_arrays) {
			$result[]=array();
		}
		return $result;
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