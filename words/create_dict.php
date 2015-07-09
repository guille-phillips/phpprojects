<?php
	
	ini_set('memory_limit', '-1');

	class LetterNode {
		public $nodes = array();
		public $leaf = false;
		public $letter;

		public function __construct($letter='', $leaf=false) {
			$this->letter = $letter;
			$this->leaf = $leaf;
		}

		public function AddNode($letter, $leaf=false) {
			if (isset($this->nodes[$letter])) {
				if ($leaf) {
					$this->nodes[$letter]->leaf = true;
				}
				return $this->nodes[$letter];
			} else {
				$node = new LetterNode($letter, $leaf);
				$this->nodes[$letter] = $node;
				return $this->nodes[$letter];
			}
		}

		public function AddWord($word) {
			$node = $this;
			$letters=str_split($word);
			$last = count($letters)-1;
			for ($index=0; $index<=$last; $index++) {
				$node = $node->AddNode($letters[$index],$index==$last);
			}
		}
	}

	class DictionaryHandler {
		static function FileTextToTree () {
			$words_text = file_get_contents('wwfdictionary.txt');
			$words_array = explode("\r\n", $words_text);

			$words = array();
			foreach ($words_array as $word) {
				$words[strtoupper($word)] = strtoupper($word);
			}
			unset($words_array);

			$base_node = new LetterNode();
			foreach ($words as $word) {
				$base_node->AddWord($word);
			}

			return $base_node;
		}

		static function TreeToCompressedText($node) {
			$text = '';
			$count=count($node->nodes);
			$text .= substr('0'.dechex($count+($node->leaf?128:0)),-2);
			$text .= substr('0'.dechex(ord($node->letter)),-2);

			foreach ($node->nodes as $subnode) {
				$text .= DictionaryHandler::TreeToCompressedText($subnode);
			}

			return $text;
		}

		static function CompressedTextToFile($text) {
			$file = fopen('compressed.bin','wb');
			fwrite($file, pack("H*",$text));
			fclose($file);
		}

		static function CompressedFileToArray() {
			$file = fopen('compressed.bin','rb');
			$data = fread($file, filesize('compressed.bin'));
			fclose($file);

			return unpack('C*',$data);
		}

		static function ArrayToTree($array, $position=1) {
			$count = $array[$position] & 0x7F;
			$leaf = ($array[$position] & 0x80)==0x80;
			$letter = chr($array[$position+1]);

			$node = new LetterNode($letter, $leaf);

			$position += 2;
			for ($index=0; $index<$count; $index++) {
				$info = ArrayToTree($array, $position);
				$node->nodes[$info[1]->letter] = $info[1];
				$position = $info[0];
			}

			return array($position,$node);
		}
	}

/*
	$root = new LetterNode();
	$root->AddWord("CA");
	$root->AddWord("CAR");
	$root->AddWord("TREE");
*/
/*
	$root = DictionaryHandler::FileTextToTree();

	$compressed = DictionaryHandler::TreeToCompressedText($root);
	//echo pack("H*",$compressed); exit;
	DictionaryHandler::CompressedTextToFile($compressed);
	*/

	$array = DictionaryHandler::CompressedFileToArray();
	$root = DictionaryHandler::ArrayToTree($array);



?>