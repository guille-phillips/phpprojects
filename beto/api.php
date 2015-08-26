<?php
	require_once 'authenticate.php';
	require_once 'view_helper.php';
	
	function Error($code,$reason) {
		return array('code'=>$code,'reason'=>$reason);
	}

	$response = array();
	$error = Error(0,'');
	if (isset($_GET['method'])) {
		$method = $_GET['method'];
		if (isset($_GET['client_key'])) {
			$client_key = $_GET['client_key'];

			if (($company_id = CheckLicenseKey($client_key))===false) {
				$error = Error(4, "Client Key $client_key not authorised.");
			} else {
				switch ($method) {
					case 'ComposeLeaderboardView':
						$response['view'] = LoadView('leaderboard', $company_id);
						$response['view']['trigger_function'] = 'Animate';
						break;
					case 'ComposeSkyscraperView':
						$response['view'] = LoadView('skyscraper', $company_id);
						$response['view']['trigger_function'] = 'Animate';
						break;
					case 'ComposeMPUView':
						$response['view'] = LoadView('mpu', $company_id);
						$response['view']['trigger_function'] = 'Animate';
						break;
					case 'ComposeLargeView':
						$response['view'] = LoadView('large', $company_id);
						$response['view']['trigger_function'] = 'Animate';
						break;						
					default:
						$error = Error(3, "Method '$method' not defined.");
				}
			}
		} else {
			$error = Error(2, "Malformed request. 'client_key' parameter missing.");
		}
	} else {
		$error = Error(1, "Malformed request. 'method' parameter missing.");
	}

	$response['error'] = $error;
	$response['timestamp'] = date('c');
	echo json_encode($response);