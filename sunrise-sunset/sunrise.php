<!DOCTYPE html>
<html >
        <head>
                <title>Sunrise</title>
                <script>
                    if (navigator.geolocation) {
                        ok = <?php echo (isset($_POST['latitude']) && isset($_POST['longitude']))?'false':'true';?>;
                        if (ok) {
                            navigator.geolocation.getCurrentPosition(showPosition);
                        }
                    } else {
                        alert('Geolocator not available');
                    }

                    function showPosition(position) {
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                        document.forms[0].submit();
                        }
                </script>
                <style>
                        body {
                                font-family: arial;
                                background-color: black;
                                color: yellow;
								font-size:350%;
                        }
                        div {
                                text-align: center;
                                font-size: 350%;
                        }
                        table {
                                margin-left:auto;
                                margin-right:auto;
                        }
                        td {
                                padding-left:15px;
								padding-right:15px;
                                margin-bottom:3px;
                        }
                        td:first-child {
                                border:none;
                        }
						
						table {
							margin-top:109px;
						}
                </style>
        </head>
        <body>
			<form action="" method="POST">
					<input id="latitude" type="hidden" name="latitude">
					<input id="longitude" type="hidden" name="longitude">
			</form>
			<?php
				echo '<table>';
				echo '<thead>';
				echo '<tr>';
				echo '</tr>';
				echo '</thead>';
				if (isset($_POST['latitude']) && isset($_POST['longitude'])) {
					date_default_timezone_set ('UTC');
					$lat = $_POST['latitude'];
					$long = $_POST['longitude'];

					$now = time();

					echo '<div>'.date_sunrise($now, SUNFUNCS_RET_STRING, $lat, $long).'</div>';
					echo '<div>'.date_sunset($now, SUNFUNCS_RET_STRING, $lat, $long).'</div>';

					for ($day=0; $day<=6; $day++) {
						echo '<tr>';

						$timestamp = $now+$day*86400;

						$row = array();

						$row[] = date('M d',$timestamp);
						$row[] = date_sunrise($timestamp,SUNFUNCS_RET_STRING, $lat, $long,97.5);
						$row[] = date_sunrise($timestamp,SUNFUNCS_RET_STRING, $lat, $long);
						$row[] = date_sunset($timestamp,SUNFUNCS_RET_STRING, $lat, $long);
						$row[] = date_sunset($timestamp,SUNFUNCS_RET_STRING, $lat, $long,97.5);
						
						echo '<td>'.implode('</td><td>',$row).'</td>';

						echo '</tr>';
					}
				}
				echo '</table>';
			?>
        </body>
</html>