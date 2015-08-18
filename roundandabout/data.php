<?php
	header('Content-Type: text/plain');
	ini_set('html_errors', false);
	
	//$db = new mysqli('localhost', 'root', 'almeria72', 'roundandabout');
	$db = new mysqli('localhost', 'root', '', 'roundandabout');

	if($db->connect_errno > 0){
		Error('Unable to connect to database [' . $db->connect_error . ']');
	}

	switch ($_GET['method']) {
		case 'GetPlaces':
			$sql = <<<SQL
				SELECT
					*
				FROM
					places
SQL;
			if (!$list = $db->query($sql)) {
				Error('There was an error running the query [' . $db->error . ']');
			};

			$rows = array();
			while($row = $list->fetch_assoc()){
				$rows[]=array('id'=>(int) $row['id'],'name'=>$row['name'],'latitude'=>(float) $row['latitude'],'longitude'=>(float) $row['longitude']);
			}
			
			echo json_encode($rows);
			break;
	}
	
	function Error($description) {
		die(json_encode(array('error' => $description)));
	}	
?>