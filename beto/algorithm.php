<?php

	require_once 'database.php';
	require_once 'historical.php';
	
	function CalculateAlgorithm($user_id) {
		$rows = ExecuteQuery(
	        "SELECT
                *
            FROM
                algorithm s
            WHERE
                id = 1"
		);

        //var_dump($rows);

		$data = array(
			'hot_tip'=>'Guillermo to win 15:30',
			'advert'=>'Win an iPad',
			'teaser'=>'15/1 Odds on favourite');

		return $data;
	}