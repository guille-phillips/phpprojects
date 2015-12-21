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
	
	function ProcessFile($file) {
		global $db;
		
		echo $file."<br><br>";

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
					?
				)
SQL;

		if ($stmt_insert = $db->prepare($sql_insert)) {
			/* bind parameters for markers */
			$stmt_insert->bind_param("sddssssssssdsssss",
				$name,
				$latitude,
				$longitude,
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
				name = ?
				latitude = ?
				longitude = ?
				category = ?
				email = ?
				telephone = ?
				address = ?
				postcode = ?
				website = ?
				entry_rates = ?
				opening_times = ?
				rating = ?
				more_info = ?
				facilities = ?
				disabled_facilities = ?
				good_stuff = ?
				bad_stuff = ?
			WHERE
				id = ?
SQL;
		
		if ($stmt_update = $db->prepare($sql_update)) {
			/* bind parameters for markers */
			$stmt_update->bind_param("sddssssssssdsssssi",
				$name,
				$latitude,
				$longitude,
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
				$bad_stuff
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
		
		while ($record=fgetcsv($handle)) {
			if ($count==0) {
				$count++;
				continue;
			}
			
			$name = StripSpace($record[1]);	
			$latitude = StripSpace($record[2]);
			$longitude = StripSpace($record[3]);
			$email = Nullable(StripSpace($record[4]),true);
			$telephone = CleanUp($record[5]);
			$address = CleanUp($record[6]);
			$postcode = StripSpace($record[7]);
			$website = StripSpace($record[8]);
			$entry_rates = CleanUp($record[9]);
			$opening_times = Nullable(CleanUp($record[10]),true);
			$rating = Nullable(StripSpace($record[11]));
			$more_info = StripSpace($record[12]);
			$facilities = StripSpace($record[13]);
			$disabled_facilities = StripSpace($record[14]);
			$good_stuff = StripSpace($record[15]);
			$bad_stuff = StripSpace($record[16]);

			$categories = array();
			for ($index=17; $index<=40; $index++) {
				if (isset($record[$index]) && $record[$index]!='') {
					$categories[] = $record[$index];
				}
			}
			
			$category = CleanUp(implode(',',$categories));
			
			// echo "latlong:$latitude $longitude<br><br>";
			
			$latitude_search = (float) $latitude;
			$longitude_search = (float) $longitude;
			$stmt_search->execute();
			echo $stmt_insert->error;
			$match_id=0;
			$found_count=0;
			while ($stmt_search->fetch()) {
				if ($name_search==$name) {
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
			} else {
				if ($match_id != 0) {
					
				} else {
					echo "skipped: $name,$latitude,$longitude<br>";
				}
				$skipped++;
			}
			
			$count++;
		}
		echo $inserted.' records were inserted<br>';
		echo $updated.' records were updated<br>';
		echo $skipped.' records were skipped<br>';
	}
	
	if (isset($_POST['import'])) {
		
		var_dump($_FILES); echo '<br>';
		
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
			
			ProcessFile($_FILES['csv_file']['tmp_name']);
			
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
	