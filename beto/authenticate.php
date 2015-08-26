<?php
	require_once 'configuration.php';
	require_once 'database.php';

	function CheckLicenseKey($license_key) {
		$rows = ExecuteQuery(
			"SELECT
				company_id
			FROM
				companies
			WHERE
				license_key = '$license_key' "
		);

		return (count($rows)==1)?$rows[0]['company_id']:false;
	}

	function CheckCredentials($username, $password, $must_be_admin) {
		$rows = ExecuteQuery(
			"SELECT
				*
			FROM
				users
			WHERE
				username = '$username'
				AND password = '$password'
				AND admin = ".($must_be_admin?'1':'0')
		);
		
		return (count($rows)==1)?$rows[0]:false;
	}

	function CreateSession($user_id) {
		$session_id = sha1($user_id); // We need a proper random number here GMP
		
		ExecuteQuery(
			"UPDATE
				users
			SET
				session_id = UNHEX('$session_id')
			WHERE
				user_id = $user_id"
		);

		return $session_id;
	}

	function CheckSessionId($session_id) {
		$rows = ExecuteQuery(
			"SELECT
				u.*,
				c.*
			FROM
				users u
				LEFT JOIN companies c
				ON c.company_id = u.company_id
			WHERE
				session_id = UNHEX('$session_id')"
		);

		return (count($rows)==1)?$rows[0]:false;
	}

	function LogIn($username, $password, $must_be_admin = false) {
		LogOut();
		if (($user_info = CheckCredentials($username, $password, $must_be_admin))!==false) {
			$session_id = CreateSession($user_info['user_id']);
			setcookie(SESSION_COOKIE_NAME,$session_id);
			$_COOKIE[SESSION_COOKIE_NAME] = $session_id;
		}

		return $user_info;
	}

	function LogOut() {
		unset($_COOKIE[SESSION_COOKIE_NAME]);
		setcookie(SESSION_COOKIE_NAME,null);
	}

	function IsLoggedIn() {
		if (isset($_COOKIE[SESSION_COOKIE_NAME])) {
			return CheckSessionId($_COOKIE[SESSION_COOKIE_NAME]);		
		} else {
			return false;
		}
	}

	// Testing
	//var_dump(CheckLicenseKey('12346'));