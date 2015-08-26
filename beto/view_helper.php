<?php

	require_once 'configuration.php';

	function LoadFile($path, $view_data) {
		if (file_exists($path)) {
			ob_start();
			include $path;
			$file_contents = ob_get_contents();
			ob_end_clean();

			return $file_contents;
		} else {
			return false;
		}
	}

	function ArrayAppend(&$array, $key, $value) {
		if ($value===false) {
			// do nothing
		} else {
			$array[$key] = $value;
		}
	}

	function LoadView($name, $company_id) {
		global $view_base_path;
		
		$view_data = array();
		$view_data['company_id'] = $company_id;

		$view_info = array();

		ArrayAppend($view_info, 'css', LoadFile(VIEW_BASE_PATH."{$name}_view.css", $view_data));
		ArrayAppend($view_info, 'js', LoadFile(VIEW_BASE_PATH."{$name}_view.js", $view_data));
		ArrayAppend($view_info, 'html', LoadFile(VIEW_BASE_PATH."{$name}_view.html", $view_data));

		return $view_info;
	}