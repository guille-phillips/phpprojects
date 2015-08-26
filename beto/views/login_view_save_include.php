<?php	
	$is_session_post = false;

	if (isset($_POST['login'])) {
		require_once 'authenticate.php';
		$must_be_admin = isset($must_be_admin)?$must_be_admin:false;
		if (($user_info = LogIn($_POST['username'],$_POST['password'],$must_be_admin))===false) {
			$view_data['message'] = 'Username or password not recognised. Please try again.';
		}
		$is_session_post = true;

	} elseif (isset($_POST['logout'])) {
		require_once 'authenticate.php';
		LogOut();
		$is_session_post = true;

	} elseif (isset($_POST['timeout'])) {
		require_once 'authenticate.php';
		LogOut();
		$view_data['message'] = 'Your session has expired. Please log in again to continue.';
		$is_session_post = true;
	}