<?php
	//var_dump($_POST);

	require_once 'login_view_save_include.php';

	if ($is_session_post) {
		// do nothing
	} elseif (isset($_POST['settings'])) {
		require_once 'cms.php';

		SaveCompanyTemplates($_POST['user_id'], $_POST['templates']);

		$user_theme = array(
			'background_colour'=>$_POST['background_colour']
		);

		SaveCompanyTheme($_POST['user_id'], $user_theme);

	} else {
		exit; // strange POST
	}