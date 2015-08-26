<?php
	//var_dump($_POST);

	$must_be_admin = true;
	require_once 'login_view_save_include.php';

	if ($is_session_post) {
		// do nothing
	} elseif (isset($_POST['user_preferences'])) {
		require_once 'cms.php';

		//SaveUser($_POST['user_id'], $_POST['templates']);
	} else {
		exit; // strange POST
	}