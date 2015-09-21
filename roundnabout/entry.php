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

			.category {
				border:1px solid #888;
				cursor:default;
				/* width:150px; */
				padding:2px;
				display:inline-block;
				margin:1px;
			}

			.category-selected {
				background-color: black;
				color: white;
			}

			.crop_image {
				width:200px;
				height:200px;
				border:1px solid black;
			}
		</style>
		<script src="javascript/jquery-2.1.4.min.js"></script>
		<script>
			var categories={};

			$(document).ready(
				function () {
					$('input,textarea').focus(function(){$('#focus').val(this.name);} );
					$('.category').click(ToggleCategory);
				}
			);

			function ToggleCategory() {
				$(this).toggleClass('category-selected');
				if ($(this).hasClass('category-selected')) {
					categories[$(this).html()] = true;
				} else {
					delete categories[$(this).html()];
				}
				var category_list = [];
				for (category in categories) {
					category_list.push(category);
				}

				$('#category_list').val(JSON.stringify(category_list));
			}

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
			
			function AddNewCategory() {
				var category = Capitalise($('#category').val().trim());
				if (category=='') return;
				
				var div = document.createElement('div');
				div.onclick = ToggleCategory;
				div.className = 'category category-selected';
				div.innerHTML = category;
				
				var db_categories = document.getElementById('db_categories');
				var added = false;
				for (node_index in db_categories.childNodes) {
					if (category < db_categories.childNodes[node_index].innerHTML) {
						console.log(db_categories.childNodes[node_index].innerHTML);
						db_categories.insertBefore(div, db_categories.childNodes[node_index]);
						added = true;
						break;
					}
				}
				if (!added) {
					db_categories.appendChild(div);
				}

				categories[category] = true;
				var category_list = [];
				for (category_name in categories) {
					category_list.push(category_name);
				}

				$('#category_list').val(JSON.stringify(category_list));
				
				$('#category').val('');
				
				return false;
			}
			
			function Capitalise(str) {
				return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
			}

			function DisplayImage(inputbox) {
				if (inputbox.files && inputbox.files[0]) {
					var reader = new FileReader();
					reader.onload = function(e) {
						$('#image_preview').attr('src',e.target.result);
					}
					reader.readAsDataURL(inputbox.files[0]);
				}
			}
		</script>
	</head>
	<body>
		<?php

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

			//print_r($_POST);
			if (isset($_POST['entry'])) {
				$telephone = CleanUp($_POST['telephone']);
				$address = CleanUp($_POST['address']);
				$entry_rates = CleanUp($_POST['entry_rates']);
				$opening_times = Nullable(CleanUp($_POST['opening_times']),true);
				$name = StripSpace($_POST['name']);
				$latitude = StripSpace($_POST['latitude']);
				$longitude = StripSpace($_POST['longitude']);
				$category = $_POST['category_list'];
				$email = Nullable(StripSpace($_POST['email']),true);
				$postcode = StripSpace($_POST['postcode']);
				$website = StripSpace($_POST['website']);
				$rating = Nullable(StripSpace($_POST['rating']));

				$more_info = StripSpace($_POST['more_info']);
				$facilities = StripSpace($_POST['facilities']);
				$disabled_facilities = StripSpace($_POST['disabled_facilities']);
				$good_stuff = StripSpace($_POST['good_stuff']);
				$bad_stuff = StripSpace($_POST['bad_stuff']);

				// $db = new mysqli('localhost', 'rnadb', 'almeria72', 'roundnabout'); // site
				$db = new mysqli('localhost', 'root', 'almeria72', 'roundnabout'); // work
				// $db = new mysqli('localhost', 'root', '', 'roundnabout'); // home

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
			} elseif (isset($_POST['search'])) {
			}
			

			// $db = new mysqli('localhost', 'rnadb', 'almeria72', 'roundnabout'); // site
			$db = new mysqli('localhost', 'root', 'almeria72', 'roundnabout'); // work
			// $db = new mysqli('localhost', 'root', '', 'roundnabout'); // home

			if($db->connect_errno > 0){
				die('Unable to connect to database [' . $db->connect_error . ']');
			}

			$sql = "SELECT category FROM places";
			if (!$list = $db->query($sql)) {
				Error('There was an error running the query [' . $db->error . ']');
			};

			$categories = array();
			$rows = array();
			while ($row = $list->fetch_assoc()){
				$category_list = json_decode($row['category']);
				foreach ($category_list as $category) {
					$categories[$category] = $category;
				}
			}
			ksort($categories);
			$categories_html = '';
			foreach ($categories as $category) {
				$categories_html .= '<div class="category">'.$category.'</div>';
			}

		?>
		<form method="post" action="entry.php" onsubmit="return Validate();"> 
			<div class="field_name">Name</div><div class="field_value"><input id="name" type="text" name="name"></div><br><br>
			<div class="field_name">Latitude</div><div class="field_value"><input id="latitude" type="text" name="latitude"></div><br><br>
			<div class="field_name">Longitude</div><div class="field_value"><input id="longitude" type="text" name="longitude"></div><br><br>
			<div class="field_name">Category</div>
			<div class="field_value">
				<div id="db_categories">
					<?=$categories_html;?>
				</div>
				<input id="category" type="text" name="category" placeholder='Add New Category (Tab to add)' onkeydown='e = event || window.event;if (e.keyCode==9) return AddNewCategory();'>
				<input id="category_list" type="hidden" name="category_list" value="[]">
			</div><br><br>
			<div class="field_name">Email</div><div class="field_value"><input id="email" type="text" name="email"></div><br><br>
			<div class="field_name">Telephone</div><div class="field_value"><input id="telephone" type="text" name="telephone"></div><br><br>
			<div class="field_name">Address</div><div class="field_value"><textarea id="address" name="address" rows="6"></textarea></div><br><br>
			<div class="field_name">Postcode</div><div class="field_value"><input id="postcode" type="text" name="postcode"></div><br><br>
			<div class="field_name">Website</div><div class="field_value"><input id="website" type="text" name="website"></div><br><br>
			<div class="field_name">Entry Rates</div><div class="field_value"><textarea id="entry_rates" name="entry_rates" rows="6"></textarea></div><br><br>
			<div class="field_name">Opening Times</div><div class="field_value"><input id="opening_times" type="text" name="opening_times"></div><br><br>
			<div class="field_name">Rating</div><div class="field_value"><input id="rating" type="text" name="rating"></div><br><br>
			<div class="field_name">More Info</div><div class="field_value"><textarea id="more_info" name="more_info" rows="6"></textarea></div><br><br>
			<div class="field_name">Facilities</div><div class="field_value"><textarea id="facilities" name="facilities" rows="6"></textarea></div><br><br>
			<div class="field_name">Disabled Facilities</div><div class="field_value"><textarea id="disabled_facilities" name="disabled_facilities" rows="6"></textarea></div><br><br>
			<div class="field_name">Good Stuff</div><div class="field_value"><textarea id="good_stuff" name="good_stuff" rows="6"></textarea></div><br><br>
			<div class="field_name">Bad Stuff</div><div class="field_value"><textarea id="bad_stuff" name="bad_stuff" rows="6"></textarea></div><br><br>
			<div class="field_name">Picture</div><div class="field_value"><input id="upload_image" type="file" onchange="DisplayImage(this);" accept="image/*"></div>
			<img id="image_preview" src="#">
			<canvas class="crop_image" DOMMouseScroll="alert('zoom');" mousewheel="alert('zoom');"></canvas>			
			<br><br>


			<input id='focus' type='hidden' name='focus' value=''>

			<input type="submit" value="Submit" name="entry" onclick="submit_button='entry';">
			<input type="submit" value="Search" name="search" onclick="submit_button='search';">

		</form>
	</body>
</html>