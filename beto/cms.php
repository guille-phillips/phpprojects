<?php
	
	require_once 'database.php';

	function LoadCompanyTemplates($company_id) {
		$company_templates = ExecuteQuery(
			"SELECT 
				*
			FROM
				company_templates
			WHERE
				company_id = $company_id
			ORDER BY
				priority"
		);
		//var_dump($company_templates); exit;
		return $company_templates;
	}

	function SaveCompanyTemplates($company_id, $templates) {
		foreach ($templates as $key=>$template) {

			$escaped_template = EscapeString($template);

			ExecuteQuery(
				"UPDATE
					company_templates
				SET 
					copy = '$escaped_template'
				WHERE
					company_id = $company_id
					AND id = $key"
			);

		}
	}

	function LoadCompanyTheme($company_id) {
		$user_theme = ExecuteQuery(
			"SELECT 
				*
			FROM
				company_themes
			WHERE
				company_id = $company_id"
		);
		
		return $user_theme[0];
	}

	function SaveCompanyTheme($company_id, $user_theme) {
		ExecuteQuery(
			"UPDATE
				company_themes
			SET 
				background_colour = '{$user_theme['background_colour']}'
			WHERE
				company_id = $company_id"
		);			
	}


	function LoadCompanyFeeds($company_id) {
		$company_feeds = ExecuteQuery(
			"SELECT 
				feed_identifier
			FROM
				company_feeds
			WHERE
				company_id = $company_id"
		);
		//var_dump($company_feeds); exit;
		return $company_feeds;
	}

	// incomplete
	function SaveCompanyFeeds($company_id) {
		ExecuteQuery(
			"UPDATE
				company_feeds
			SET 
				feed_identifier = 'xyz'
			WHERE
				company_id = $company_id"
		);			
	}

	function LoadUsers($company_id) {
		$users = ExecuteQuery(
			"SELECT
				*
			FROM
				users
			WHERE
				company_id = $company_id
			ORDER BY
				username"
		);

		return $users;
	}

	function LoadCompanies() {
		$users = ExecuteQuery(
			"SELECT
				*
			FROM
				companies
			ORDER BY
				name"
		);

		return $users;
	}

	function SaveUsers() {
		// ExecuteQuery(
		// 	""
		// );
	}
