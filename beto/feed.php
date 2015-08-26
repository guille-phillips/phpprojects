<?php

	require_once 'database.php';
	require_once 'beteasy_feed.php';

	// URL with GET parameters
	function Curl($url) {
		$curl = curl_init();
		if ($curl===false) {
			echo 'Failed to initialise curl';
			return;
		}
		
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => $url
		));

		$response = curl_exec($curl);
		curl_close($curl);

		//var_dump($response);

		return $response;
	}

	function LoadCompanyFeedsSettings($user_id) {
		$company_feeds = ExecuteQuery(
			"SELECT 
				feed_identifier
			FROM
				company_feeds
			WHERE
				user_id = $user_id"
		);
		//var_dump($company_templates); exit;
		return $company_feeds;
	}

	function ProcessFeeds($user_id) {
		$company_feeds = LoadCompanyFeedsSettings($user_id);


		foreach ($company_feeds as $user_feed) {
			switch ($user_feed['feed_identifier']) {
				case 'beteasy':
					$feed = new BetEasyFeed();
					$feed->ProcessFeed();
					break;
				default:
					// unknown/unset feed: ignore
			}
		}
	}

	ProcessFeeds(1);