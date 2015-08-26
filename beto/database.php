<?php

	require_once 'configuration.php';
	
	$db = null;

	function ConnectDatabase() {
		global $db;

		if ($db===null) {
			$db = new mysqli(MYSQLI_HOST, MYSQLI_USERNAME, MYSQLI_PASSWORD, MYSQLI_DATABASE_NAME);
	        if($db->connect_errno > 0){
	            die('Unable to connect to database [' . $db->connect_error . ']');
	        }
	        $db->set_charset('utf8');
		}
	}

	function ExecuteQuery($sql) {
		global $db;

		ConnectDatabase();

        if(!$list = $db->query($sql)){
            die('There was an error running the query [' . $db->error . ']');
        }

        if ($list!==true) { // check if we have results
	        $rows = array();
	        while ($row = $list->fetch_assoc()){
	            $rows[]=$row;
	        }
	        return $rows;
	    }
	}

	function DisconnectDatabase() {
		global $db;

		if ($db!==null) {
			$db->close();	
		}
		
	}

	function EscapeString($string) {
		global $db;

		ConnectDatabase();

		return $db->real_escape_string($string);
	}