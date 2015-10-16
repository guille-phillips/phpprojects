<?php

	$data = array();

	$courses = GetFolders('.');



	foreach ($courses as $course) {
		$groups = GetFolders("./$course");

		foreach ($groups as $group) {
			$files = GetFiles("./$course/$group");
			foreach ($files as $file) {
				$data["$group/{$file[1]}"][$course] = true;
			}
		}
	}

	print_r($data);

	//echo '<table>'.implode('',$table).'</table>';

	function TableRow($array_data) {
		return "<tr><td>".implode('</td><td>',$array_data)."</td></tr>";
	}

	function GetFolders($base) {
		$folders = array();
		$scan = scandir($base);
		foreach ($scan as $folder) {
			if (is_dir("$base/$folder")) {
				switch ($folder) {
					case '.':
					case '..':
						break;
					default:
						$folders[] = $folder;
				}
			}
		}
		return $folders;
	}

	function GetFiles($base) {
		$files = array();
		$scan = scandir($base);
		foreach ($scan as $file) {
			if (!is_dir("$base/$file")) {
				switch ($file) {
					case '.':
					case '..':
						break;
					default:
						$files[] = array(hash_file('md5',"$base/$file"),$file);
				}
			}
		}
		return $files;
	}
?>