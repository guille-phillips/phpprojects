<?php

	require_once 'configuration.php';
	require_once 'view_helper.php';
	require_once 'authenticate.php';

	if (!isset($_GET['name'])) {
		exit;
	} else {
		$view_name = $_GET['name'];
	}
	
	//var_dump($_POST);
	//var_dump($_COOKIE);

	$view_data = array();	

	if (count($_POST)>0) {
		require_once VIEW_BASE_PATH."{$view_name}_view_save.php";
	}

	if (($user_info = IsLoggedIn())===false) {
		$view_data['is_logged_in'] = false;
	} else {
		$view_data['is_logged_in'] = true;
		$view_data['user_id'] = $user_info['user_id'];
		$view_data['user_info'] = $user_info;	
	}

	if (($html = LoadFile(VIEW_BASE_PATH."{$view_name}_view.html",$view_data))!==false) {
		echo $html;
	}