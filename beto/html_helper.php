<?php

	function CreateTable($array) {
		$table_html = '';

		foreach ($array as $row) {
			$row_html = '';
			foreach ($row as $cell) {
				$row_html .= Tag('td',$cell);
			}
			$table_html .= Tag('tr',$row_html);
		}

		$html = Tag('table',$table_html);
		
		return $html;
	}

	function Tag($name,$content) {
		return "<$name>$content</$name>";
	}