<?php

//var_dump($_POST);

	ini_set('max_execution_time', -1);
	ini_set('memory_limit', '-1');

	class Game {
		static public function Scan() {
			$files = scandir('.');
			$game_files = array();
			foreach ($files as $file) {
				if (preg_match('/^game(\d{3})\.txt$/', $file, $matches)) {
					$game_files[$matches[1]] = $matches[1];
				}
			}
			asort($game_files);
			return $game_files;
		}

		static public function GetNewGameNumber() {
			$highest = 0;
			$game_files = self::Scan();

			foreach ($game_files as $game_file) {
				if ((int) $game_file > $highest) {
					$highest = (int) $game_file;
				}
			}
			$highest++;
			$index = substr('00'.$highest,-3);
			return $index;
		}

		static public function LoadGame($index,&$board,&$letters) {
			$contents = file_get_contents("game$index.txt");
			$info = unserialize($contents);
			$letters = $info[0];
			$board->board = $info[1];
		}

		static public function SaveGame($index, $board, $letters) {
			$contents = serialize(array($letters, $board->board));
			file_put_contents("game$index.txt", $contents);
		}

		static public function DeleteGame($index) {
			@unlink("game$index.txt");
		}
	}

	class Board {
		const NONE = 0;
		const DOUBLE_LETTER = 1;
		const TRIPLE_LETTER = 2;
		const DOUBLE_WORD = 3;
		const TRIPLE_WORD = 4;

		public $board = array();
		private $dictionary;

		public function __construct($dictionary) {
			$this->dictionary = $dictionary;
			$this->board = array_fill(0,15*15,self::NONE);

			$this->SetMirror(3,0,self::TRIPLE_WORD);
			$this->SetMirror(2,1,self::DOUBLE_LETTER);

			$this->SetMirror(6,0,self::TRIPLE_LETTER);
			$this->SetMirror(5,1,self::DOUBLE_WORD);
			$this->SetMirror(4,2,self::DOUBLE_LETTER);
			$this->SetMirror(3,3,self::TRIPLE_LETTER);

			$this->SetMirror(7,3,self::DOUBLE_WORD);
			$this->SetMirror(6,4,self::DOUBLE_LETTER);
			$this->SetMirror(5,5,self::TRIPLE_LETTER);			
		}

		public function BuildBoard($board_array) {
			foreach ($board_array as $index=>$letter) {
				if ($letter!='&nbsp;' && $letter!='') {
					if (Tile::Check($letter)) {
						$this->board[$index] = new Tile($letter);
					}
				}
			}
		}

		public function GetBoardArray() {
			$board_array = array();
			foreach ($this->board as $position) {
				if (is_object($position)) {
					$board_array[] = $position->letter;
				} else {
					$board_array[] = '';
				}
			} 
			return $board_array;
		}

		private function SetMirror($x, $y, $value) {
			$this->SetSquare($x, $y, $value);
			$this->SetSquare(14-$x, $y, $value);
			$this->SetSquare($x, 14-$y, $value);
			$this->SetSquare(14-$x, 14-$y, $value);

			$this->SetSquare($y, $x, $value);
			$this->SetSquare(14-$y, $x, $value);
			$this->SetSquare($y, 14-$x, $value);
			$this->SetSquare(14-$y, 14-$x, $value);

		}

		private function SetSquare($x, $y, $value) {
			$this->board[$y*15+$x] = $value;
		}

		public function GetSlice($x, $y, $length, $direction) {
			$slice = array();
			$step = $direction==0?1:15;
			$position = $y*15+$x;
			for ($index=0; $index<$length; $index++) {
				$slice[] = $this->board[$position];
				$position += $step;
			}
			return $slice;
		}

		public function Place($word, $x, $y, $direction) {
			$letters = str_split($word);
			$step = $direction==0?1:15;
			$position = $y*15+$x;
			for ($index=0; $index<count($letters); $index++) {
				$this->board[$position] = new Tile($letters[$index]);
				$position += $step;
			}
		}

		public function Render() {
			for ($y=0; $y<=14; $y++) {
				for ($x=0; $x<=14; $x++) {
					if (is_object($this->board[$y*15+$x])) {
						echo $this->board[$y*15+$x]->letter;
					} else {
						echo ".";
					}
				}
				echo '<br>';
			}
		}

		private function GetPattern($slice,$size,$position) {
			$hit_tile = false;
			$end_reached = false;
			$pattern = '';

			$position--;
			while ($position>=0 && is_object($slice[$position])) {
				$position--;
			}
			$position++;
			$start_position = $position;
			while (true) {
				if (is_object($slice[$position])) {
					$hit_tile = true;
					$pattern .= $slice[$position]->letter;
				} else {
					if ($size==0) {
						break;
					}
					$pattern .= '.';
					$size--;
				}
				$position++;
				if ($position == 15) {
					$end_reached = true;
					break;
				}
			}

			return array('pattern'=>$pattern, 'end_reached'=>$end_reached, 'start_position'=>$start_position, 'hit_tile'=>$hit_tile);
		}

		public function AllWords($letters) {
			$set = Dictionary::GetSet($letters);
			$size = strlen($letters);
			$all = array();

			for ($this_size = $size; $this_size>1; $this_size--) {
				$pattern = str_repeat('.', $this_size);
				$words = $this->dictionary->FindWords($pattern,$set);
				foreach ($words as $word) {
					$score = 0;
					foreach (str_split($word[0]) as $character) {
						$tile = new Tile($character);
						$score += $tile->value;
					}
					$all["{$word[0]}|0|0|0"] = array($score,$word[2]);
				}
			}
			return $all;
		}

		public function Scan($letters) {
			$set = Dictionary::GetSet($letters);
			$size = strlen($letters);

			$words_play = array();

			for ($direction = 0; $direction<=1; $direction++) {
				for ($index = 0; $index <=14; $index++) {
					switch ($direction) {
						case 0:
							$slice = $this->GetSlice(0,$index,15,0);
							break;
						case 1:
							$slice = $this->GetSlice($index,0,15,1);
							break;					
					}

					for ($this_size=$size; $this_size>0; $this_size--) {
						$position = 0;
						do {
							$pattern_info = $this->GetPattern($slice,$this_size,$position);
							$start_position = $pattern_info['start_position'];
							switch ($direction) {
								case 0:
									$x = $start_position;
									$y = $index;
									break;
								case 1:
									$x = $index;
									$y = $start_position;
									break;
							}

							if ($pattern_info['hit_tile'] || $this->HasAdjoining($x,$y,$direction,$this_size)) {

								$words_found = $this->dictionary->FindWords($pattern_info['pattern'], $set);
								$hit_tile = $pattern_info['hit_tile'];
								foreach ($words_found as $word_found_info) {
									$word_found = $word_found_info[0];
									$blank_positions = $word_found_info[1];
									$tiles_used = $word_found_info[2];
									$word_found_ok = true;
									$score = 0;
									$word_length = strlen($word_found);


									for ($letter_index=0; $letter_index<$word_length; $letter_index++) {
										$letter = $word_found[$letter_index];
										$is_blank = in_array($letter_index, $blank_positions);
										$valid_word = array(true, 0);
										switch ($direction) {
											case 0:
												if (!is_object($this->board[$pattern_info['start_position']+$letter_index+$index*15])) {
													$valid_word = $this->ValidWord($letter, $is_blank, $pattern_info['start_position']+$letter_index,$index,1-$direction);
												}
												break;
											case 1:
												if (!is_object($this->board[$index+($pattern_info['start_position']+$letter_index)*15])) {
													$valid_word = $this->ValidWord($letter, $is_blank, $index, $pattern_info['start_position']+$letter_index,1-$direction);
												}
												break;
										}
										if (!$valid_word[0]) {
											$word_found_ok = false;
											break;
										} else {
											$score += $valid_word[1];
										}
									}

									if ($word_found_ok) {
										
										switch ($direction) {
											case 0:
												$score += $this->Score($word_found,$blank_positions,$start_position,$index,0)+($tiles_used>=7?35:0);
												$words_play["$word_found|$start_position|$index|0"] = array($score,$tiles_used);
												break;
											case 1:
												$score += $this->Score($word_found,$blank_positions,$index,$start_position,1)+($tiles_used>=7?35:0);
												$words_play["$word_found|$index|$start_position|1"] = array($score,$tiles_used);
												break;
										}
										
									}
								}
							}
							$position++;
							
						} while ($pattern_info['end_reached']!==true);
					}

				}
			}
			return $words_play;
		}

		public function HasAdjoining($x,$y,$direction,$length) {
			$position=$x+$y*15;
			$delta = $direction==0?1:15;
			$delta_ra = $direction==0?15:1;
			for ($index=0; $index<$length; $index++) {
				switch ($direction) {
					case 0:
						if ($y>0) {
							if (is_object($this->board[$position-$delta_ra])) {
								return true;
							}
						}
						if ($y<14) {
							if (is_object($this->board[$position+$delta_ra])) {
								return true;
							}							
						}
						break;
					case 1:
						if ($x>0) {
							if (is_object($this->board[$position-$delta_ra])) {
								return true;
							}									
						}
						if ($x<14) {
							if (is_object($this->board[$position+$delta_ra])) {
								return true;
							}									
						}						
						break;
				}
				$position+=$delta;
			}
			return false;
		}

		public function ValidWord($letter,$is_blank,$x,$y,$direction) {
			$blank_positions = array();

			$delta = $direction==0?1:15;
			switch ($direction) {
				case 0:
					$lower_bound = function ($value) {return ($value+15)%15!=14;};
					$upper_bound = function ($value) {return $value%15!=0;};
					break;
				case 1:
					$lower_bound = function ($value) {return $value>=0;};
					$upper_bound = function ($value) {return $value<=224;};				
					break;
			}

			$word_pre = '';
			$position = $y*15+$x-$delta;

			while ($lower_bound($position) && is_object($this->board[$position]) ) {
				$word_pre = $this->board[$position]->letter.$word_pre;
				$position-=$delta;
			}
			
			$start_position = $position+$delta;

			if ($is_blank) {
				$blank_positions[] = strlen($word_pre);
			}

			$word_post = '';
			$position = $y*15+$x+$delta;
			while ($upper_bound($position) && is_object($this->board[$position]) ) {
				$word_post .= $this->board[$position]->letter;
				$position+=$delta;
			}

			$word = $word_pre.$letter.$word_post;
			$word_blank = $word_pre.'.'.$word_post;

			$score = 0;
			if (strlen($word)==1) {
				return array(true, $score);
			}
			$ok = $this->dictionary->CheckWord($word);
			if ($ok) {
				$score = $this->Score($word, $blank_positions, $start_position%15, (int) ($start_position/15), $direction);
			}
			return array($ok, $score);
		}

		public function Score($word, $blank_positions, $x, $y, $direction) {
			$score = 0;
			$position = $y*15+$x;
			$delta = $direction==0?1:15;
			$word_multiplier = 1;
			for ($index=0; $index<strlen($word); $index++) {
				if (is_object($this->board[$position])) {
					$score += $this->board[$position]->value;
				} else {
					$tile_multiplier = 1;
					switch ($this->board[$position]) {
						case Board::NONE:
							$tile_multiplier = 1;
							break;
						case Board::DOUBLE_LETTER:
							$tile_multiplier = 2;
							break;
						case Board::TRIPLE_LETTER:
							$tile_multiplier = 3;
							break;
						case Board::DOUBLE_WORD:
							$word_multiplier = $word_multiplier * 2;
							break;
						case Board::TRIPLE_WORD:
							$word_multiplier = $word_multiplier * 3;
							break;
					}
					if (!in_array($index, $blank_positions)) {
						$temp_tile = new Tile($word[$index]);
						$score += $temp_tile->value*$tile_multiplier;
					}
				}

				$position += $delta;
			}
			$score = $score * $word_multiplier;
			return $score;
		}
		
	}
					
	class Tile {
		public $letter;
		public $value;

		public function __construct($letter) {
			$values = "144214331A524214A11125483A0";
			$keys =   "ABCDEFGHIJKLMNOPQRSTUVWXYZ.";
			$values_real = array_map(function($value){return hexdec($value);}, str_split($values));
			$tiles = array_combine(str_split($keys),$values_real);
			$this->letter = $letter;
			$this->value = $tiles[strtoupper($letter)];
		}
		
		static public function Check($letter) {
			return strpos("ABCDEFGHIJKLMNOPQRSTUVWXYZ.",strtoupper($letter))!==false;
		}
	}

	class Dictionary {
		private $words = array();
		private $cache_found_words = array();

		public function __construct() {
			$words_text = file_get_contents('wwfdictionary.txt');
			$words_array = explode("\r\n", $words_text);
			foreach ($words_array as $word) {
				$this->words[strlen($word)][strtoupper($word)] = strtoupper($word);
			}
		}

		public function CheckWord($word) {
			return isset($this->words[strlen($word)][$word]);
		}

		public function FindWords($pattern, $set) {
			$found = array();
			if (!isset($this->words[strlen($pattern)])) {
				return $found;
			}
			if (!isset($this->cache_found_words[$pattern])) {
				$regex = "/^$pattern$/";
				foreach ($this->words[strlen($pattern)] as $word) {
					$matches = array();
					if (preg_match($regex, $word, $matches)) {
						$set_copy = $set;
						$ok = true;
						$blank_positions = array();
						$tiles_used = 0;
						foreach (str_split($word) as $index=>$letter) {
							if (substr($pattern,$index,1)=='.') {
								if (isset($set_copy[$letter])) {
									$tiles_used++;
									$set_copy[$letter]--;
									if ($set_copy[$letter]==0) {
										unset($set_copy[$letter]);
									}
								} elseif (isset($set_copy['.'])) {
									$blank_positions[] = $index;
									$tiles_used++;
									$set_copy['.']--;
									if ($set_copy['.']==0) {
										unset($set_copy['.']);
									}
								} else {
									$ok = false;
									break;
								}
							}
						}
						if ($ok) {
							$found[] = array($word,$blank_positions,$tiles_used);
						}
					}
				}
				$this->cache_found_words[$pattern] = $found;
				return $found;
			} else {
				return $this->cache_found_words[$pattern];
			}
		}

		static function GetSet($letters) {
			$letters_array = str_split($letters);
			$set = array();
			foreach ($letters_array as $letter) {
				if (isset($set[$letter])) {
					$set[$letter]++;
				} else {
					$set[$letter] = 1;
				}
			}
			return $set;
		}
	}
?>

<?php
	if (isset($_POST['game'])) {
		$board = new Board(new Dictionary());
		Game::LoadGame($_POST['game'],$board,$letters);
		$_POST['board'] = $board->GetBoardArray();
		$_POST['letters'] = $letters;
		$_POST['game_number'] = $_POST['game'];
	} elseif (isset($_POST['save']) || isset($_POST['find'])) {
		if (isset($_POST['board'])) {
			$board = new Board(new Dictionary());
			$board->BuildBoard($_POST['board']);
		}	
		Game::SaveGame($_POST['game_number'], $board, $_POST['letters']);
	} elseif (isset($_POST['new'])) {
		unset($_POST['game_number']);
		$_POST['board'] = array_fill(0, 255, '');
		$_POST['letters'] = '';
	} elseif (isset($_POST['delete'])) {
		Game::DeleteGame($_POST['game_number']);
	}
?>
<html style="width:100%">
	<head>
		<script src="jquery.js"></script>
		<script type="text/javascript">
			var previous_selected;
			var in_letters = false;

			function SelectTile(index) {
				in_letters = false;
				document.getElementById("T"+index).className = "tile selected";
				if (previous_selected) document.getElementById("T"+previous_selected).className = "tile";
				previous_selected = index;
			}

			function AddVector(v1,v2) {
				var result = [];
				for (var index in v1) {
					result[index] = v1[index]+v2[index];
				}
				return result;
			}

			function VectorOf(position) {
				return [position%15,Math.floor(position/15)];
			}

			function ScalarOf(positionv) {
				return positionv[0]+positionv[1]*15;
			}

			function ValidVector(vector) {
				if (vector[0]>14 || vector[0]<0 || vector[1]>14 || vector[1]<0) {
					return false;
				}
				return true;
			}

			function NextTile(positionv, directionv, overwrite) {
				positionv = AddVector(positionv, directionv);
				while (ValidVector(positionv)) {
					if (!overwrite) {
						if ($('#T'+ScalarOf(positionv)).html()=='&nbsp;') {
							return positionv;
						}
					} else {
						return positionv;
					}
					positionv = AddVector(positionv, directionv);
				}
				return false;
			}

			$(document).on("keypress", function (e){
				if (in_letters) return;

				if (previous_selected!==void 0) {					
					if (shift_keys[16]) {
						directionv=[0,1];
					} else {
						directionv=[1,0];
					}

					var char = false;
					if (e.which>=97 && e.which<=122) {
						char = String.fromCharCode(e.which-32);
					} else if (e.which>=65 && e.which<=90) {
						char = String.fromCharCode(e.which);
					} else if (e.which==46) {
						char = '&nbsp';
					}
					
					if (char!==false) {
						$('#T'+previous_selected).html(char);
						$('#H'+previous_selected).val(char);
						var next = NextTile(VectorOf(previous_selected),directionv,shift_keys[17]|e.which==46);
						if (next!==false) {
							SelectTile(ScalarOf(next));
						}
					}
				}
			});

			shift_keys=[];
			$(document).on("keydown", function (e){
				shift_keys[e.which] = true;
			});

			$(document).on("keyup", function (e){
				delete shift_keys[e.which];
			});

			function ShowWords() {
				var sort_type = 0;
				if (arguments.length>0) {
					sort_type = arguments[0];
				}

				switch (sort_type) {
					case 0: // score
						words.sort(function(a,b){return (b[3]*200+b[2])-(a[3]*200+a[2]);});
						break;
					case 1: // no. tiles
						words.sort(function(a,b){return (b[2]*200+b[3])-(a[2]*200+a[3]);});
						break;
					case 2: // column
						words.sort(function(a,b){return (a[4]*200+a[5])-(b[4]*200+b[5]);});
						break;
					case 3: // row
						words.sort(function(a,b){return (a[5]*200+a[4])-(b[5]*200+b[4]);});
						break;
				}

				word_panel = document.getElementById("word_panel");
				$('.word').remove();
				
				for (index in words) {
					var div = document.createElement("DIV");
					div.className = 'word';
					div.innerHTML = words[index][3]+' '+words[index][0]+' '+words[index][2];
					div.setAttribute('data-info',JSON.stringify(words[index]));
					div.onmouseover = function () {HandleHighlightWord(this.getAttribute('data-info'));};
					div.onmouseout = function () {HandleHighlightWord('[]');};
					div.onclick = function () {LayWord(this.getAttribute('data-info'));};

					word_panel.appendChild(div);
				}
			}

			function HandleHighlightWord(json_info) {
				if (typeof previous_info != 'undefined') {
					HighlightWord(previous_info, false);
				}
				HighlightWord(json_info, true);
			}

			function HighlightWord(json_info, add_highlight) {
				var info = JSON.parse(json_info);
				if (info.length==0) return;
				var size = info[0].length;
				var direction = info[1];
				var x = info[4];
				var y = info[5];

				var start = x+y*15;
				var step = direction==0?1:15;

				for (var index=0; index<size; index++) {
					if (add_highlight) {
						$('#T'+start).addClass('highlight');
						if ($('#T'+start).html()=='&nbsp;') {
							$('#T'+start).html(info[0].substr(index,1));
							$('#T'+start).addClass('faketext');
						}
					} else {
						$('#T'+start).removeClass('highlight');
						if ($('#T'+start).hasClass('faketext')) {
							$('#T'+start).html('&nbsp;');
							$('#T'+start).removeClass('faketext');							
						}
					}
					start += step;
				}

				previous_info = json_info;
			}

			function LayWord(json_info) {
				var info = JSON.parse(json_info);
				if (info.length==0) return;
				var size = info[0].length;
				var direction = info[1];
				var x = info[4];
				var y = info[5];

				var start = x+y*15;
				var step = direction==0?1:15;

				for (var index=0; index<size; index++) {
					$('#T'+start).removeClass();
					$('#T'+start).addClass('tile');
					$('#T'+start).html(info[0].substr(index,1));
					$('#H'+start).val(info[0].substr(index,1));
					start += step;
				}				
			}
		</script>
		<style>
			input {
				text-align:left;
				font-weight:bold;
				font-size:16px;
			}

			.dl {
				background-color: #77B2D2;
			}

			.tl {
				background-color: #85D277;
			}

			.dw {
				background-color: #DC7E74;
			}

			.tw {
				background-color: #F0BC7F;
			}

			.tile {
				border:1px solid #ccc;
				width:24px;
				height:24px;
				display:inline-block;
				text-align: center;
				vertical-align: middle;
				cursor: default;
				font-size:20px;
			}

			.tile:hover {
				border:1px solid red;
			}

			.selected {
				border: 1px solid black;
			}

			.axis {
				width:26px;
				height:26px;
				display:inline-block;
				text-align: center;
				vertical-align: middle;				
			}

			.word {
				padding:4px;
				min-width:110px;
				display:inline-block;
				border:1px dotted #888;
				margin:3px;
				cursor:default;
			}

			.word:hover {
				background-color: #FEFFA5;
				font-weight: bold;
			}

			.highlight {
				background-color: #FEFFA5;
			}

			.faketext {
				color: #777;
			}
		</style>
	</head>
	<body style="width:100%">
<?php
	if (isset($_POST['board'])) {
		$board = $_POST['board'];
	} else {
		$board = array_fill(0, 225, '&nbsp;');
	}	

	$blank_board = new Board(null);
	if (isset($_POST['board'])) {
		$blank_board->BuildBoard($board);
	}	
	
?>
<div id="word_panel" style="width:100%;">
	<div style="width:480px; float:left; vertical-align:top;">
		<form action='solver.php' method='post'>
			<div style="width:420px;height:416px;">
				<?php
					$tile = 0;
					echo "<div class='axis'>&nbsp;</div>";
					for ($x=0; $x<=14; $x++) {
						echo "<div class='axis'>".$x."</div>";
					}
					for ($y=0; $y<=14; $y++) {
						echo "<div class='axis'>".$y."</div>";
						for ($x=0; $x<=14; $x++) {
							$letter = '&nbsp;';
							$class = '';
							if (!is_object($blank_board->board[$x+$y*15])) {
								switch ($blank_board->board[$x+$y*15]) {
									case 0:
										$class ='';
										break;
									case 1:
										$class ='dl';
										break;
									case 2:
										$class ='tl';
										break;
									case 3:
										$class ='dw';
										break;
									case 4:
										$class ='tw';
										break;
								}
							} else {
								$letter = $blank_board->board[$x+$y*15]->letter;
							}	

							echo "<div id='T$tile' class='tile $class' onclick='SelectTile(parseInt(this.id.substr(1)));' >$letter</div>";
							echo "<input type='hidden' id='H$tile' name='board[".($y*15+$x)."]' value='$letter'>";
							$tile++;
						}
					}
				?>
			</div>			

<?php

	if (isset($_POST['letters'])) {
		$letters = strtoupper($_POST['letters']);
	} else {
		$letters = '';
	}
?>
			<br>
			<br>
			Letters
			<input type='text' value='<?php echo $letters;?>' name='letters' onfocus='in_letters=true;'>
			<br>
			<br>
			<input type='submit' value='Delete' name='delete'>			
<?php
	$game = new Game();
	$games_list = $game->Scan();

	$game_number = (isset($_POST['game_number'])?$_POST['game_number']:Game::GetNewGameNumber());
	echo " Game ".$game_number."<input type='hidden' value='".$game_number."' name='game_number'><br><br>";

	foreach ($games_list as $item) {
		echo "<input type='submit' value='$item' name='game'>";
	}

?>
			<input type='submit' value='New' name='new'>
			<br>
			<br>
			<input type='submit' value='Save' name="save"> 
			<input type='submit' value='Find' name="find">
			<input type='submit' value='Find All' name="find_all">
		</form>			
		<br>
		<input type='button' value='Find by Score' onclick="ShowWords(0);">
		<input type='button' value='Find by Tiles' onclick="ShowWords(1);">
	</div>
<?php
	if (isset($_POST['find']) || isset($_POST['find_by_tiles']) || isset($_POST['find_all']) ) {
		$dictionary = new Dictionary();
		$board = new Board($dictionary);

		if (isset($_POST['board'])) {
			$board->BuildBoard($_POST['board']);
		}		
		if (isset($_POST['letters'])) {
			if (isset($_POST['find_all'])) {
				$play = $board->AllWords(strtoupper($_POST['letters']));
			} else {
				$play = $board->Scan(strtoupper($_POST['letters']));
			}
			
			if (isset($_POST['find'])) {
				uasort($play, function ($a,$b) {return ($a[0]*256+$a[1])>($b[0]*256+$b[1])?-1:1;});
			} else {
				uasort($play, function ($a,$b) {return ($a[1]*256+$a[0])>($b[1]*256+$b[0])?-1:1;});
			}
			
			$words_js = array();
			foreach ($play as $info=>$word) {
				$info = explode("|", $info);

				$words_js[] = <<<DICT
['{$info[0]}',{$info[3]},{$word[1]},{$word[0]},{$info[1]},{$info[2]}]
DICT;
			}
			$words_js = 'var words = ['.implode(',', $words_js).'];';
			echo <<<WORDS
				<script>
					$words_js
					ShowWords();
				</script>
WORDS;
		}		

	}
?>
	</div>
	</body>
</html>