<!DOCTYPE html>
<html>
	<head>
		<style>
			input, textarea {
				/*position:absolute;*/
				left:200px;
			}
			textarea {
				width:500px;
			}
			input {
				width:300px;
			}

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
		<script src="jquery-2.1.4.min.js"></script>
		<script>
			$(document).ready(
				function () {
					$('input,textarea').focus(function(){$('#focus').val(this.name);} );
				}
			);

			function isNumeric(n) {
			  return !isNaN(parseFloat(n)) && isFinite(n);
			}

			function ValidateNumeric(field_name,field_value) {
				if (!isNumeric(field_value)) {
					alert(field_name+' must be a number and not blank');
					return false;
				}
				return true;
			}

			function ValidateBlank(field_name,field_value) {
				if (field_value.trim() == '') {
					alert(field_name+" cannot be blank");
					return false;
				}
				return true;
			}

			function Validate() {
				if (submit_button=='entry') {
					if (!ValidateBlank('name',$('#name').val())) {return false;}
					if (!ValidateNumeric('latitude',$('#latitude').val())) {return false;}
					if (!ValidateNumeric('longitude',$('#longitude').val())) {return false;}

					return true;
				} else {
					return true;
				}
			}
		</script>
	</head>
	<body>
		<?php

			function Nullable($field,$is_text=false) {
				if ($field=='') {
					return 'NULL';
				} else {
					if ($is_text) {
						return "'$field'";
					} else {
						return $field;
					}
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

			//print_r($_POST);
			if (isset($_POST['entry'])) {
				$telephone = CleanUp($_POST['telephone']);
				$address = CleanUp($_POST['address']);
				$entry_rates = CleanUp($_POST['entry_rates']);
				$opening_times = Nullable(CleanUp($_POST['opening_times']),true);
				$name = StripSpace($_POST['name']);
				$latitude = StripSpace($_POST['latitude']);
				$longitude = StripSpace($_POST['longitude']);
				$category = StripSpace($_POST['category']);
				$email = Nullable(StripSpace($_POST['email']),true);
				$postcode = StripSpace($_POST['postcode']);
				$rating = Nullable(StripSpace($_POST['rating']));

				$more_info = StripSpace($_POST['more_info']);
				$facilities = StripSpace($_POST['facilities']);
				$good_stuff = StripSpace($_POST['good_stuff']);
				$bad_stuff = StripSpace($_POST['bad_stuff']);

				$db = new mysqli('localhost', 'rnadb', 'almeria72', 'roundnabout');
				//$db = new mysqli('localhost', 'root', 'almeria72', 'roundnabout');
				//$db = new mysqli('localhost', 'root', '', 'roundnabout');

				if($db->connect_errno > 0){
					die('Unable to connect to database [' . $db->connect_error . ']');
				}

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
                        	entry_rates,
                        	opening_times,
                        	rating,
                        	more_info,
                        	facilities,
                        	good_stuff,
                        	bad_stuff)
                    VALUES
                        (
							'$name',
							$latitude,
							$longitude,
							'$category',
							$email,
							'$telephone',
							'$address',
							'$postcode',
							'$entry_rates',
							$opening_times,
							$rating,
							'$more_info',
							'$facilities',
							'$good_stuff',
							'$bad_stuff'
                        )
SQL;

                if (!$db->query($sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                }
                echo 'New record inserted<br><br>';
			} elseif (isset($_POST['search'])) {

			}
		?>
		<form method="post" action="entry.php" onsubmit="return Validate();"> 
			<div class="field_name">Name</div><div class="field_value"><input id="name" type="text" name="name"></div><br><br>
			<div class="field_name">Latitude</div><div class="field_value"><input id="latitude" type="text" name="latitude"></div><br><br>
			<div class="field_name">Longitude</div><div class="field_value"><input id="longitude" type="text" name="longitude"></div><br><br>
			<div class="field_name">Category</div><div class="field_value"><input id="category" type="text" name="category"></div><br><br>
			<div class="field_name">Email</div><div class="field_value"><input id="email" type="text" name="email"></div><br><br>
			<div class="field_name">Telephone</div><div class="field_value"><input id="telephone" type="text" name="telephone"></div><br><br>
			<div class="field_name">Address</div><div class="field_value"><textarea id="address" name="address" rows="6"></textarea></div><br><br>
			<div class="field_name">Postcode</div><div class="field_value"><input id="postcode" type="text" name="postcode"></div><br><br>
			<div class="field_name">Entry Rates</div><div class="field_value"><textarea id="entry_rates" name="entry_rates" rows="6"></textarea></div><br><br>
			<div class="field_name">Opening Times</div><div class="field_value"><input id="opening_times" type="text" name="opening_times"></div><br><br>
			<div class="field_name">Rating</div><div class="field_value"><input id="rating" type="text" name="rating"></div><br><br>
			<div class="field_name">More Info</div><div class="field_value"><textarea id="more_info" name="more_info" rows="6"></textarea></div><br><br>
			<div class="field_name">Facilities</div><div class="field_value"><textarea id="facilities" name="facilities" rows="6"></textarea></div><br><br>
			<div class="field_name">Good Stuff</div><div class="field_value"><textarea id="good_stuff" name="good_stuff" rows="6"></textarea></div><br><br>
			<div class="field_name">Bad Stuff</div><div class="field_value"><textarea id="bad_stuff" name="bad_stuff" rows="6"></textarea></div><br><br>

			<input id='focus' type='hidden' name='focus' value=''>

			<input type="submit" value="Submit" name="entry" onclick="submit_button='entry';">
			<input type="submit" value="Search" name="search" onclick="submit_button='search';">
		</form<>
	</body>
</html>