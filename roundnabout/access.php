<?php

	require_once 'db.php';
	
	function InsertAccess() {
		global $db;
		
		$ip_address = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'';
		$agent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
		$forwarded_ip_address = isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:'';
		$created = isset($_SERVER['REQUEST_TIME'])?gmdate("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']):date('Y-m-d H:i:s');
		$sql = "INSERT INTO access (ip_address,forwarded_ip_address,agent,created) VALUES (?,?,?,?)";
		$stmt = $db->prepare($sql);
		$stmt->bind_param("ssss",
				$ip_address,
				$forwarded_ip_address,
				$agent,
				$created);		
		$stmt->execute();
		$stmt->close();
	}
	
	