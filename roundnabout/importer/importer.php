<?php

	header('Content-Type: text/html; charset=utf-8');
	

	function Nullable($field,$is_text=false) {
		if ($field=='') {
			return null;
		} else {
			return $field;
		}
	}

	function StripSpace($field) {
		return trim(implode(' ',preg_split('/ {2,}/',$field)));
	}

	function CleanUp($field) {
		$delimiter = '/\v|\s*[,|\/\\\.]\s*/';
		$split_array = preg_split($delimiter, $field);
		$split_array = array_map(function($e){return StripSpace($e);},$split_array);
		return json_encode($split_array);
	}
	
	function ProcessFile($file,$region) {
		global $db;

		$sql_find = <<<SQL
			SELECT
				id,
				name
			FROM
				places
			WHERE
				ABS(latitude - ?) < 0.00001
				AND ABS(longitude - ?) < 0.00001
SQL;

		if ($stmt_search = $db->prepare($sql_find)) {
			/* bind parameters for markers */
			$stmt_search->bind_param("dd",
				$latitude_search,
				$longitude_search);
			$stmt_search->bind_result($search_id,$search_name);
		} else {
			echo htmlspecialchars($db->error);
		}	
		
		$sql_insert = <<<SQL
			INSERT INTO
				places
				(
					name,
					latitude,
					longitude,
					region,
					category,
					email,
					telephone,
					address,
					postcode,
					website,
					entry_rates,
					opening_times,
					rating,
					more_info,
					facilities,
					disabled_facilities,
					good_stuff,
					bad_stuff)
			VALUES
				(
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?,
					?
				)
SQL;

		if ($stmt_insert = $db->prepare($sql_insert)) {
			/* bind parameters for markers */
			$stmt_insert->bind_param("sddsssssssssdsssss",
				$name,
				$latitude,
				$longitude,
				$region,
				$category,
				$email,
				$telephone,
				$address,
				$postcode,
				$website,
				$entry_rates,
				$opening_times,
				$rating,
				$more_info,
				$facilities,
				$disabled_facilities,
				$good_stuff,
				$bad_stuff);
			// echo 'New record inserted<br><br>';
		} else {
			echo htmlspecialchars($db->error);
		}				

		$sql_update = <<<SQL
			UPDATE
				places
			SET
				name = ?,
				latitude = ?,
				longitude = ?,
				region = ?,
				category = ?,
				email = ?,
				telephone = ?,
				address = ?,
				postcode = ?,
				website = ?,
				entry_rates = ?,
				opening_times = ?,
				rating = ?,
				more_info = ?,
				facilities = ?,
				disabled_facilities = ?,
				good_stuff = ?,
				bad_stuff = ?
			WHERE
				id = ?
SQL;
		
		if ($stmt_update = $db->prepare($sql_update)) {
			$stmt_update->bind_param("sddsssssssssdsssssi",
				$name,
				$latitude,
				$longitude,
				$region,
				$category,
				$email,
				$telephone,
				$address,
				$postcode,
				$website,
				$entry_rates,
				$opening_times,
				$rating,
				$more_info,
				$facilities,
				$disabled_facilities,
				$good_stuff,
				$bad_stuff,
				$update_id);
			// echo 'New record inserted<br><br>';
		} else {
			echo htmlspecialchars($db->error);
		}	
		
		$handle = fopen($file,'r');

		$count = 0;
		$inserted = 0;
		$updated = 0;
		$skipped = 0;
		
		$header=fgetcsv($handle);
		foreach ($header as $index=>$field_name) {
			$slug = str_replace('.','',str_replace(' ','_',strtolower($field_name)));
			$var_name = "csv_$slug";
			$$var_name = $index;
		}
		// print_r(get_defined_vars());
		// exit;
		
		while ($record=fgetcsv($handle)) {
			if ($count==0) {
				$count++;
				continue;
			}
			
			$name = StripSpace($record[$csv_name]);	
			
			$latitude = StripSpace($record[$csv_latitude]);
			$longitude = StripSpace($record[$csv_longitude]);
			if ($name=='' || $latitude=='' || $longitude=='') {
				if ($name!='') {
					echo "skipped: $name,$latitude,$longitude<br>";
					$skipped++;
				}
				continue;
			}
			$email = Nullable(StripSpace($record[$csv_email]),true);
			$telephone = CleanUp($record[$csv_telephone]);
			$address = CleanUp($record[$csv_address]);
			$postcode = StripSpace($record[$csv_postcode]);
			$website = StripSpace($record[$csv_website]);
			$entry_rates = CleanUp($record[$csv_entry_rates]);
			$opening_times = Nullable(CleanUp($record[$csv_opening_times]),true);
			$rating = Nullable(StripSpace($record[$csv_rating]));
			$more_info = StripSpace($record[$csv_more_info]);
			$facilities = StripSpace($record[$csv_facilities]);
			$disabled_facilities = StripSpace($record[$csv_disabled_facilities]);
			$good_stuff = StripSpace($record[$csv_good_stuff]);
			$bad_stuff = StripSpace($record[$csv_bad_stuff]);

			$categories = array();
			for ($index=$csv_indoor; $index<=($csv_free+30); $index++) {
				if (isset($record[$index]) && $record[$index]!='') {
					$categories[] = $record[$index];
				}
			}
			
			$category = CleanUp(implode(',',$categories));
			
			// echo "latlong:$latitude $longitude<br><br>";
			
			$latitude_search = (float) $latitude;
			$longitude_search = (float) $longitude;
			$stmt_search->execute();
			
			$match_id=0;
			$found_count=0;
			while ($stmt_search->fetch()) {
				if ($search_name==$name) {
					if ($match_id==0) {
						$match_id=$search_id;
					} 
				}
				$found_count++;
			}
			
			if ($found_count==0) {
				$inserted++;
				$stmt_insert->execute();
				echo "inserted: $name,$latitude,$longitude<br>";
				echo $stmt_insert->error;
			} elseif ($found_count==1) {
				$updated++;
				if ($match_id!=0) {
					$update_id = $match_id;
					$stmt_update->execute();
				}
				echo "updated (location match): $update_id, $name, $latitude, $longitude<br>";
			} else {
				if ($match_id != 0) {
					$updated++;
					$update_id = $match_id;
					$stmt_update->execute();
					echo "updated (name match): $update_id, $name, $latitude, $longitude<br>";					
				} else {
					echo "skipped: $name,$latitude,$longitude<br>";
					$skipped++;
				}
			}
			
			$count++;
		}
		echo '<br><br>';
		echo $inserted.' records were inserted<br>';
		echo $updated.' records were updated<br>';
		echo $skipped.' records were skipped<br>';
	}
	
	if (isset($_POST['import'])) {
		
		// var_dump($_FILES); echo '<br>'; exit;
		
		if ($_FILES['csv_file']['error'] != 0) {
			echo 'There was a problem with the file. Please try again.<br><br>';
		} else {
			
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
			
			$regions = array(
				'channel islands',
				'cheshire',
				'cornwall',
				'cumbria',
				'devon',
				'dorset',
				'gloucestershire',
				'greater london',
				'highlands',
				'isle of man',
				'lancashire',
				'manchester',
				'merseyside',
				'somerset',
				'wiltshire'
			);
			
			$filename = $_FILES['csv_file']['name'];
			$file_region = '';
			foreach ($regions as $region) {
				// echo $region;
				if (stripos($filename,$region)!==false) {
					$file_region = $region;
					break;
				}
			}
			
			if ($file_region == '') {
				die('Region Not Recognised. Try again.');
			}
			echo "File: ".$filename;
			echo '<br>';
			echo "Region: ".ucwords($file_region);
			echo '<br>';
			echo '<br>';
			
			ProcessFile($_FILES['csv_file']['tmp_name'],$file_region);
			
			echo '<br><br>';
			
			// foreach (scandir('.') as $file) {
				// if ($dot = strrpos($file,'.')) {
					// if (strtoupper(substr($file,$dot+1))=='CSV') {
						// ProcessFile($file);
					// }
				// }
			// }
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<style>
			.field_name {
				width:100px;
				display:inline-block;
				vertical-align: top;
			}

			.field_value {
				display:inline-block;
				vertical-align: top;
			}
		</style>
	</head>
	<body>
		<form method="post" action="importer.php" onsubmit="SetImageForPost(); return Validate();" enctype="multipart/form-data">
			<div class="field_name">CSV File To Upload</div><div class="field_value"><input type="file" name="csv_file" accept=".csv"></div>
			<input type="submit" value="Submit" name="import">
		</form>
	</body>
</html>		
	