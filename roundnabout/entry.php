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
	
	function FindImageURLFromName($name) {
		$slug = strtolower(str_replace(' ','-',$name));
		$image_extensions = array('jpg','png','gif');
		$image_url='images/no-image.png';
		foreach ($image_extensions as $image_extension) {
			if (file_exists('images/'.$slug.'.'.$image_extension)) {
				$image_url = 'images/'.$slug.'.'.$image_extension;
			}
		}		
		
		return $image_url;
	}

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

	$id = 0;
	$name = '';
	$latitude = '';
	$longitude = '';
	$category = '[]';
	$email = '';
	$telephone = '';
	$address = '';
	$postcode = '';
	$website = '';
	$entry_rates = '';
	$opening_times = '';
	$rating = '';
	$more_info = '';
	$facilities = '';
	$disabled_facilities = '';
	$good_stuff = '';
	$bad_stuff = '';
	$category_array = array();
	$category_field = '[]';
	$category_js = '';
	
	$image_url = FindImageURLFromName(''); // default No Image image

	//print_r($_POST);
	if (isset($_POST['entry']) && $_POST['id']=='0') {			
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
			$id = $db->insert_id;
			$stmt->close();

			$crop_image_post = $_POST['crop_image_post'];
			$crop_image_post = str_replace('data:image/png;base64,', '', $crop_image_post);
			$crop_image_post = str_replace(' ', '+', $crop_image_post);
			$data = base64_decode($crop_image_post);
			$slug = strtolower(str_replace(' ','-',$name));
			$file = "images/$slug.png";
			$success = file_put_contents($file, $data);
			
			$image_url = FindImageURLFromName($name);
			
			echo 'New record inserted<br><br>';
		} else {
			echo htmlspecialchars($db->error);
		}
	} elseif (isset($_POST['entry']) && $_POST['id']!='0') {
//echo '<pre>';print_r($_POST);echo '</pre>';exit;
		$id = $_POST['id'];
		$name = StripSpace($_POST['name']);
		$latitude = StripSpace($_POST['latitude']);
		$longitude = StripSpace($_POST['longitude']);	
		$category = $_POST['category_list'];
		$email = Nullable(StripSpace($_POST['email']),true);
		$telephone = CleanUp($_POST['telephone']);
		$address = CleanUp($_POST['address']);
		$postcode = StripSpace($_POST['postcode']);
		$website = StripSpace($_POST['website']);
		$entry_rates = CleanUp($_POST['entry_rates']);
		$opening_times = Nullable(CleanUp($_POST['opening_times']),true);
		$rating = Nullable(StripSpace($_POST['rating']));
		$more_info = StripSpace($_POST['more_info']);
		$facilities = StripSpace($_POST['facilities']);
		$disabled_facilities = StripSpace($_POST['disabled_facilities']);
		$good_stuff = StripSpace($_POST['good_stuff']);
		$bad_stuff = StripSpace($_POST['bad_stuff']);
		
		$sql = <<<SQL
			UPDATE
				places
			SET
				`name` = ?,
				`latitude` = ?,
				`longitude` = ?,
				`category` = ?,
				`email` = ?,
				`telephone` = ?,
				`address` = ?,
				`postcode` = ?,
				`website` = ?,
				`entry_rates` = ?,
				`opening_times` = ?,
				`rating` = ?,
				`more_info` = ?,
				`facilities` = ?,
				`disabled_facilities` = ?,
				`good_stuff` = ?,
				`bad_stuff` = ?
			WHERE
				id = ?
SQL;

		if ($stmt = $db->prepare($sql)) {	
			$stmt->bind_param("sddssssssssdsssssi",
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
					$disabled_facilities,
					$facilities,
					$good_stuff,
					$bad_stuff,
					$id);		

			$stmt->execute();
			//echo $db->error;
			$stmt->close();

			$telephone = implode(', ',json_decode($telephone));
			$address = implode(",\n",json_decode($address));
			$entry_rates = implode(",\n",json_decode($entry_rates));
			$opening_times = implode(",\n",json_decode($opening_times));
			$category_array = json_decode($category);
			$category_js = implode(',',array_map(function($member){return "\"$member\":true";},$category_array));
			$category_field = $category;
			
			$crop_image_post = $_POST['crop_image_post'];
			if ($crop_image_post != '') {
				$crop_image_post = str_replace('data:image/png;base64,', '', $crop_image_post);
				$crop_image_post = str_replace(' ', '+', $crop_image_post);
				$data = base64_decode($crop_image_post);
				$slug = strtolower(str_replace(' ','-',$name));
				$file = "images/$slug.png";
				$success = file_put_contents($file, $data);
			}
			$image_url = FindImageURLFromName($name);
			
			echo 'Record updated<br><br>';
		} else {
			echo htmlspecialchars($db->error);
		}
	} elseif (isset($_POST['search'])) {
		$name = '%'.StripSpace($_POST['name']).'%';
		
		$id = 0;

		$sql = <<<SQL
			SELECT
				*
			FROM
				places
			WHERE
				name LIKE ?
				AND id > ?
			ORDER BY
				id
SQL;
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("si",$name,$id);
			$stmt->execute();
			$stmt->bind_result(
				$id,
				$name,
				$latitude,
				$longitude,
				$category_field,
				$email,
				$telephone,
				$address,
				$postcode,
				$website,
				$entry_rates,
				$opening_times,
				$rating,
				$more_info,
				$disabled_facilities,
				$facilities,
				$good_stuff,
				$bad_stuff);

			$count = 0;
			while ($stmt->fetch()) {
				echo "<div class='selectname' onclick='location=\"entry.php?id=$id\";'>$name</div>";
				$count++;
			}
			$stmt->close();
			
			if ($count==1) {
				$telephone = implode(', ',json_decode($telephone));
				$address = implode(",\n",json_decode($address));
				$entry_rates = implode(",\n",json_decode($entry_rates));
				$opening_times = implode(",\n",json_decode($opening_times));
				$category_array = json_decode($category_field);
				$category_js = implode(',',array_map(function($member){return "\"$member\":true";},$category_array));
				
				$image_url = FindImageURLFromName($name);
			} elseif ($count>1) {
				$exit_early = true;
			}
		}
		
	} elseif (isset($_POST['delete'])) {
		$id = $_POST['id'];
		
		$sql = <<<SQL
			DELETE
			FROM
				places
			WHERE
				id = ?
SQL;
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$stmt->close();

			$id = 0;
			
			echo 'Record deleted<br><br>';
		}
		
	} elseif (isset($_GET['id'])) {
		$id = $_GET['id'];

		$sql = <<<SQL
			SELECT
				*
			FROM
				places
			WHERE
				id = ?
			ORDER BY
				id
SQL;
		if ($stmt = $db->prepare($sql)) {
			$stmt->bind_param("i",$id);
			$stmt->execute();
			$stmt->bind_result(
				$id,
				$name,
				$latitude,
				$longitude,
				$category_field,
				$email,
				$telephone,
				$address,
				$postcode,
				$website,
				$entry_rates,
				$opening_times,
				$rating,
				$more_info,
				$disabled_facilities,
				$facilities,
				$good_stuff,
				$bad_stuff);

			$stmt->fetch();
			$stmt->close();

			$telephone = implode(', ',json_decode($telephone));
			$address = implode(",\n",json_decode($address));
			$entry_rates = implode(",\n",json_decode($entry_rates));
			$opening_times = implode(",\n",json_decode($opening_times));
			$category_array = json_decode($category_field);
			$category_js = implode(',',array_map(function($member){return "\"$member\":true";},$category_array));
			
			if (isset($_GET['lat'])) {
				$latitude = $_GET['lat'];
			}
			if (isset($_GET['long'])) {
				$longitude = $_GET['long'];
			}
			
			$image_url = FindImageURLFromName($name);		
		}
	}
	
	$sql = "SELECT category FROM places";
	if (!$list = $db->query($sql)) {
		Error('There was an error running the query [' . $db->error . ']');
	}

	$categories = array();
	$rows = array();
	while ($row = $list->fetch_assoc()){
		$category_list = json_decode($row['category']);
		if (count($category_list)>0) {
			foreach ($category_list as $category) {
				$categories[$category] = $category;
			}
		}
	}
	ksort($categories);
	$categories_html = '';
	foreach ($categories as $category) {
		$selected = '';
		if (in_array($category,$category_array)) {
			$selected = ' category-selected';
		}
		$categories_html .= '<div class="category'.$selected.'">'.$category.'</div>';
	}

?>
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

			.long_field_value {
				width:50%;
				margin-top:8px;
				margin-bottom:8px;
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

			#crop_image {
				width:200px;
				height:200px;
				border:1px solid black;
				background-repeat:no-repeat;
				background-color:#888;
			}
			
			#upload_image_img {
				display:none;
			}
			
			.selectname {
				background-color:white;
				color:black;	
				border:1px solid black;
				width:50%;
				margin-bottom:2px;
				cursor:default;
			}
			.selectname:hover{
				background-color:black;
				color:white;			
			}
		</style>
		<script src="javascript/jquery-2.1.4.min.js"></script>
		<script>
			var categories={<?=$category_js?>};

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

			function SetImageForPost() {
				if (image_controller.has_image) { 
					document.getElementById("crop_image_post").value = document.getElementById('crop_image').toDataURL('image/png');
				}
			}
			
			function Validate() {
				switch (submit_button) {
					case 'entry':
						if (!ValidateBlank('name',$('#name').val())) {return false;}
						if (!ValidateNumeric('latitude',$('#latitude').val())) {return false;}
						if (!ValidateNumeric('longitude',$('#longitude').val())) {return false;}

						SetImageForPost();
						
						return true;
						break;
					case 'delete':
						return confirm("This will permanently delete this record. Are you sure?");
						break;
					default:
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


			
			function ImageController() {
				var self = this;
				var dragging = false;
				var drag_start;
				var image_offset=[0,0];
				var image_zoom=100; // percent
				var cursor;
				var image_source = new Image();
				var canvas = document.getElementById('crop_image');
				var context = canvas.getContext("2d");			
				this.has_image = false;
				
				function DrawImage(x,y) {
					context.fillStyle = '#fff';
					context.fillRect(0, 0, canvas.width, canvas.height);
					context.drawImage(image_source, x, y, image_zoom*image_source.width/100, image_zoom*image_source.height/100);
				}
	
				this.LoadURL = function(url,dragable,callback){
					image_source.onload = function() {
						DrawImage(0,0);
						if (dragable) {
							InitMouseEvents();
						}						
					};
					if (dragable) {
						image_source.src = url;
					} else {
						image_source.src = url+'?'+(new Date().getTime());
					}
				};
				
				this.DisplayImage = function(inputbox) {
					if (inputbox.files && inputbox.files[0]) {
						dragging = false;
						image_offset=[0,0];
						image_zoom=100;
						var reader = new FileReader();
						reader.onload = function(e) {
							self.has_image = true;
							self.LoadURL(e.target.result, true);
						}
						reader.readAsDataURL(inputbox.files[0]);
					}
				}				
				
				this.StartDrag = function(x,y) {
					drag_start = [x,y];
					dragging = true;
				};
				
				this.Drag = function(x,y) {
					if (dragging) {
						var drag_offset = [x-drag_start[0],y-drag_start[1]];
						var offset = [image_offset[0]+drag_offset[0],image_offset[1]+drag_offset[1]];
						DrawImage(offset[0],offset[1]);
					}
					var position = $("#crop_image").position();
					cursor = [x-position.left,y-position.top];
				};
				
				this.StopDrag = function(x,y) {
					if (dragging) {
						image_offset = [image_offset[0]+x-drag_start[0],image_offset[1]+y-drag_start[1]];
					}
					dragging = false;
				};
				
				this.ChangeZoom = function(direction) {
					var ratio;
					var ok = false;
					switch (direction) {
						case -1:
							if (image_zoom>10) {
								var image_zoom_previous = image_zoom;
								image_zoom = image_zoom/1.1;
								ratio = image_zoom/image_zoom_previous;
								ok = true;
							}
							break;
						case 1:
							if (image_zoom<1000) {
								var image_zoom_previous = image_zoom;
								image_zoom = image_zoom*1.1;
								ratio = image_zoom/image_zoom_previous;
								ok = true;
							}
							break;
					}
					if (ok) {
						image_offset = [cursor[0]+ratio*(image_offset[0]-cursor[0]),cursor[1]+ratio*(image_offset[1]-cursor[1])];
						DrawImage(image_offset[0],image_offset[1]);
					}
				}
				
				function InitMouseEvents() {
					$('#crop_image').mousedown( function(e) {
						self.StartDrag(e.pageX,e.pageY);
					});
					$('#crop_image').mousemove( function(e) {
						self.Drag(e.pageX,e.pageY);
					});
					$('#crop_image').mouseup( function(e) {
						self.StopDrag(e.pageX,e.pageY);
					});
					$('#crop_image').mouseleave( function(e) {
						self.StopDrag(e.pageX,e.pageY);
					});
					
					 //Firefox
					$('#crop_image').on('DOMMouseScroll', function(e){
						self.ChangeZoom((e.originalEvent.detail>0)?-1:1);
						return false;
					});

					 //IE, Opera, Safari
					 $('#crop_image').on('mousewheel', function(e){
						self.ChangeZoom((e.originalEvent.wheelDelta>0)?-1:1);
						return false;
					 });
				}
			}
			
			var image_controller;
			
			$(document).ready(
				function () {
					$('input,textarea').focus(function(){$('#focus').val(this.name);} );
					$('.category').click(ToggleCategory);
					
					image_controller = new ImageController();
					image_controller.LoadURL('<?=$image_url?>',false);
				}
			);
		</script>
	</head>
	<?php if(isset($exit_early)) exit; ?>
	<body>
		<form method="post" action="entry.php" onsubmit="SetImageForPost(); return Validate();" enctype="multipart/form-data">
			<input type="hidden" name="id" value="<?=$id?>">
			<div class="field_name">Name</div><div class="field_value"><input id="name" type="text" name="name" value="<?=$name?>"></div><br><br>
			<div class="field_name">Latitude</div><div class="field_value"><input id="latitude" type="text" name="latitude" value="<?=$latitude?>"></div><br><br>
			<div class="field_name">Longitude</div><div class="field_value"><input id="longitude" type="text" name="longitude" value="<?=$longitude?>"></div><br><br>
			<div class="field_name">Category</div>
			<div class="field_value">
				<div id="db_categories">
					<?=$categories_html;?>
				</div>
				<input id="category" type="text" name="category" placeholder='Add New Category (Tab to add)' onkeydown='e = event || window.event;if (e.keyCode==9) return AddNewCategory();'>
				<input id="category_list" type="hidden" name="category_list" value='<?=$category_field?>'>
			</div><br><br>
			<div class="field_name">Email</div><div class="field_value"><input id="email" type="text" name="email" value="<?=$email?>"></div><br><br>
			<div class="field_name">Telephone</div><div class="field_value"><input id="telephone" type="text" name="telephone" value="<?=$telephone?>"></div><br><br>
			<div class="field_name">Address</div><div class="field_value"><textarea id="address" name="address" rows="6"><?=$address?></textarea></div><br><br>
			<div class="field_name">Postcode</div><div class="field_value"><input id="postcode" type="text" name="postcode" value="<?=$postcode?>"></div><br><br>
			<div class="field_name">Website</div><div class="field_value"><input id="website" type="text" name="website" value="<?=$website?>"></div><br><br>
			<div class="field_name">Entry Rates</div><div class="field_value"><textarea id="entry_rates" name="entry_rates" rows="6"><?=htmlspecialchars($entry_rates, ENT_QUOTES, "UTF-8")?></textarea></div><br><br>
			<div class="field_name">Opening Times</div><div class="field_value"><input id="opening_times" type="text" name="opening_times" value="<?=$opening_times?>"></div><br><br>
			<div class="field_name">Rating</div><div class="field_value"><input id="rating" type="text" name="rating" value="<?=$rating?>"></div><br><br>
			<div class="field_name">More Info</div><div class="field_value"><textarea id="more_info" name="more_info" rows="6"><?=htmlspecialchars($more_info, ENT_QUOTES, "UTF-8")?></textarea></div><br><br>
			<div class="field_name">Facilities</div><div class="field_value"><textarea id="facilities" name="facilities" rows="6"><?=htmlspecialchars($facilities, ENT_QUOTES, "UTF-8")?></textarea></div><br><br>
			<div class="field_name">Disabled Facilities</div><div class="field_value"><textarea id="disabled_facilities" name="disabled_facilities" rows="6"><?=htmlspecialchars($disabled_facilities, ENT_QUOTES, "UTF-8")?></textarea></div><br><br>
			<div class="field_name">Good Stuff</div><div class="field_value"><textarea id="good_stuff" name="good_stuff" rows="6"><?=htmlspecialchars($good_stuff, ENT_QUOTES, "UTF-8")?></textarea></div><br><br>
			<div class="field_name">Bad Stuff</div><div class="field_value"><textarea id="bad_stuff" name="bad_stuff" rows="6"><?=htmlspecialchars($bad_stuff, ENT_QUOTES, "UTF-8")?></textarea></div><br><br>
			<div class="field_name">Picture</div><div class="field_value"><input id="upload_image" type="file" onchange="image_controller.DisplayImage(this);" accept="image/*"></div>
			<br>
			<!--div class="field_name">OR...</div><input class="long_field_value" placeholder="Enter URL and press Tab to get image" onkeydown='e = event || window.event;if (e.keyCode==9) {image_controller.LoadURL(this.value,true);return false;}'-->
			<br>
			<canvas id="crop_image" width="200" height="200"></canvas>
			<br>
			<input type="button" value="Zoom In" onclick="image_controller.ChangeZoom(1);">
			<br>
			<input type="button" value="Zoom Out" onclick="image_controller.ChangeZoom(-1);">
			<br>
			Or use mouse wheel over image to zoom.
			<input type="hidden" name="crop_image_post" id="crop_image_post">
			<br><br>
		
			<input id='focus' type='hidden' name='focus' value=''>

			<input type="submit" value="Submit" name="entry" onclick="submit_button='entry';">
			<input type="submit" value="Search" name="search" onclick="submit_button='search';">
			<input type="submit" value="New" name="new" onclick="submit_button='new';">
			<input type="submit" value="Delete" name="delete" onclick="submit_button='delete';">
		</form>
	</body>
</html>