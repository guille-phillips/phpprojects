<?php
	define('KEY_TIMEOUT',5*60);
	
	require_once 'db.php';
	
	function CreateKey() {
		$key = '';
		for ($index=0;$index<32;$index++) {
			$key .= chr(($a=rand(0,35))<=9?$a+48:$a+55);
		}
		return $key;
	}

	function InsertKey() {
		global $db;
		$key = CreateKey();
		$time = time()+KEY_TIMEOUT;
		$sql = "INSERT INTO `keys` (`key`,`valid_till`) VALUES ('$key',$time)";
		$db->query($sql);
		return $key;
	}
	
	function RemoveExpiredKeys() {
		global $db;
		$time = time();
		$sql = "DELETE FROM `keys` WHERE `valid_till` < $time";
		$db->query($sql);
	}
	
	function IsValidKey($key) {
		global $db;
		$sql = "SELECT COUNT(*) total FROM `keys` WHERE `key` = '$key'";
		$result = $db->query($sql);
		$row = $result->fetch_assoc();
		return $row['total']>0;
	}
	
	function RemoveKey($key) {
		global $db;
		$sql = "DELETE FROM `keys` WHERE `key` = '$key'";
		$db->query($sql);
	}
