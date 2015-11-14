<?php

	header('Content-Type: text/html; charset=utf-8');
	
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
	
	foreach (scandir('.') as $file) {
		if ($dot = strrpos($file,'.')) {
			if (strtoupper(substr($file,$dot+1))=='CSV') {
				ProcessFile($file);
			}
		}
	}
	
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
		
		echo $file."\n";
		
		$handle = fopen($file,'r');

		//$count = 2;
		while ($record=fgetcsv($handle)) {			
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
			
			$sql = <<<SQL
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

			if ($stmt = $db->prepare($sql)) {
				/* bind parameters for markers */
				$stmt->bind_param("sddssssssssdsssss",
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

				$stmt->execute();
				$stmt->close();

				echo 'New record inserted<br><br>';
			} else {
				
				echo htmlspecialchars($db->error);
			}			
			
			//$count--;
			//if ($count==0) break;
		}
	}
	
	
	