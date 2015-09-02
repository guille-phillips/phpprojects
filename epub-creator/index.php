<?php
	ini_set('html_errors', false);

	header('Content-Type: text/plain; charset=utf-8');

	$path = 'text.txt';
	$file = fopen($path,'r') or die ('Unable to open file');
	$contents = fread($file,filesize($path));
	fclose($file);


	mb_internal_encoding("UTF-8");

	$contents = str_replace("\r\n","\n",$contents);
	$len = mb_strlen($contents);
	$chars = array();
	$output = '';

	$navmap = array();
	$manifest_fonts = array();
	$manifest_images = array();
	$manifest_pages = array();
	$spine = array();

	$bold_on = false;
	$underline_on = false;
	$italic_on = false;
	$superscript_on = false;
	$subscript_on = false;
	$file_index = 1;

	for ($pos=0;$pos<=$len;$pos++) {
		for ($char_pos=0;$char_pos<4;$char_pos++) {
			$chars[$char_pos] = mb_substr($contents,$pos+$char_pos,1);
		}

		if ($chars[0]=="\n" && $chars[1]==' ' && $chars[2]==' ' && $chars[3]==' ') {
			$output .= "\n<p indent3>";
			$pos += 3;
		} else if ($chars[0]=="\n" && $chars[1]==' ' && $chars[2]==' ' ) {
			$output .= "\n<p indent2>";
			$pos += 2;
		} else if ($chars[0]=="\n" && $chars[1]==' ' ) {
			$output .= "\n<p indent1>";
			$pos += 1;
		} else if ($chars[0]=="\n" && $chars[1]=='-' && $chars[2]=='-' && $chars[3]=="\n") {
			$output .= "\n<page-break>\n";
			$pos += 3;

			$filename = 'content'.str_pad($file_index,3,'0',STR_PAD_LEFT);
			$file = $filename.'.html';
			$manifest_pages[] = '<item href="'.$file.'" id="'.$filename.'" media-type="application/xhtml+xml"/>';
			$navmap[] = $file;
			$spine[] = $filename;
		} else if ($chars[0]=="\n") {
			$output .= "</p>\n<p>";
		} else if ($chars[0]=='_') {
			$bold_on = !$bold_on;

			if ($bold_on) {
				$output .= '<bold>';
			} else {
				$output .= '</bold>';
			}
		} else if ($chars[0]=='\\' && $chars[1]=='\\') {
			$output .= '\\';
			$pos += 1;
		} else if ($chars[0]=='\\' && $chars[1]=='_') {
			$output .= '_';
			$pos += 1;
		} else if ($chars[0]=='\\' && $chars[1]=='^') {
			$output .= '^';
			$pos += 1;
		} else if ($chars[0]=='\\') {
			$italic_on = !$italic_on;

			if ($italic_on) {
				$output .= '<italic>';
			} else {
				$output .= '</italic>';
			}
		} else if ($chars[0]=='^' && $chars[1]=='^') {
			$subscript_on = !$subscript_on;

			if ($subscript_on) {
				$output .= '<sub>';
			} else {
				$output .= '</sub>';
			}
			$pos += 1;				
		} else if ($chars[0]=='^') {
			$superscript_on = !$superscript_on;

			if ($superscript_on) {
				$output .= '<super>';
			} else {
				$output .= '</super>';
			}
		} else {
			$output .= $chars[0];
		}
	}

	//echo $output;

	//echo CreateOpfFile($manifest_pages);
	
	WriteFile('epub/content.opf',CreateOpfFile($manifest_pages));
	WriteFile('epub/toc.ncx',CreateTocFile($navmap,$spine));

	function tag($tag,$array) {
		if (is_array($array)) {
			return array_map(function($item) use ($tag) {return '<'.$tag.'>'.$item.'</'.$tag.'>';}, $array);
		} else {
			return "<$tag>\n$array\n</$tag>";
		}
	}

	function CreateOpfFile($manifest_pages) {
		$opf = <<<OPF
<?xml version='1.0' encoding='utf-8'?>
<package xmlns="http://www.idpf.org/2007/opf" unique-identifier="uuid_id" version="2.0">
	<metadata xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:opf="http://www.idpf.org/2007/opf" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:calibre="http://calibre.kovidgoyal.net/2009/metadata" xmlns:dc="http://purl.org/dc/elements/1.1/">
		<dc:language>en</dc:language>
		<dc:creator opf:file-as="Unknown" opf:role="aut">Alan Watts</dc:creator>
		<meta name="calibre:timestamp" content="2014-02-17T21:11:22.470710+00:00"/>
		<dc:title>The Way Of Zen</dc:title>
		<dc:identifier id="uuid_id" opf:scheme="uuid">1c977b60-3951-43fa-b806-e54a14be1c09</dc:identifier>
	</metadata>
	<manifest>

OPF;

		$opf .= implode('\n',$manifest_pages);

		$opf .= <<<OPF

		<item href="stylesheet.css" id="css" media-type="text/css"/>
		<item href="toc.ncx" id="ncx" media-type="application/x-dtbncx+xml"/>		
	</manifest>
	<guide/>
</package>
OPF;
		return $opf;
	}

	function CreateTocFile($navmap,$spine) {
		$toc = <<<TOC
<?xml version='1.0' encoding='utf-8'?>
<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1" xml:lang="en-US">
	<head>
		<meta content="1c977b60-3951-43fa-b806-e54a14be1c09" name="dtb:uid"/>
		<meta content="2" name="dtb:depth"/>
		<meta content="calibre (1.20.0)" name="dtb:generator"/>
		<meta content="0" name="dtb:totalPageCount"/>
		<meta content="0" name="dtb:maxPageNumber"/>
	</head>
	<docTitle>
		<text>The Way Of Zen</text>
	</docTitle>
	<navMap>

TOC;

		$playorder = 1;
		foreach ($navmap as $point) {
			$toc .= <<<TOC
			<navPoint id="ufa23ce35-52f9-4096-9c77-74c525e8d6d4" playOrder="$playorder">
				<navLabel>
					<text>Preface</text>
				</navLabel>
				<content src="$point"/>
			</navPoint>
TOC;
			$playorder++;
		}

		$toc .= <<<TOC

	</navMap>
	<spine toc="ncx">

TOC;
		foreach ($spine as $item) {
			$toc .= '<itemref idref="'.$item.'"/>';
		}
		$toc .= <<<TOC

	</spine>
</ncx>
TOC;

		return $toc;
	}

	function CreateSectionFile($content) {
		$section = <<<SECTION
<?xml version='1.0' encoding='utf-8'?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>i_9fbfe539224142f6</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link href="stylesheet.css" rel="stylesheet" type="text/css"/>
	</head>
	<body>		
SECTION;

		$section .= <<<SECTION
	</body>
SECTION;
		return $section;
	}

	function WriteFile($name,$contents) {
		$file = fopen($name,'w');
		fwrite($file,$contents);
		fclose($file);
	}
?>