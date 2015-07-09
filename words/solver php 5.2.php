<?php

//var_dump($_POST);

	//ini_set('max_execution_time', -1);
	//ini_set('memory_limit', '-1');

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
				if ($letter!='') {
					if (Tile::Check($letter)) {
						$this->board[$index] = new Tile(strtoupper($letter));
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
												$score += $this->Score($word_found,$blank_positions,$start_position,$index,0);
												$words_play["$word_found|$start_position|$index|0"] = $score;
												break;
											case 1:
												$score += $this->Score($word_found,$blank_positions,$index,$start_position,1);
												$words_play["$word_found|$index|$start_position|1"] = $score;
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
					$lower_bound = 'LowerBoundA';
					$upper_bound = 'UpperBoundA';
					break;
				case 1:
					$lower_bound = 'LowerBoundB';
					$upper_bound = 'UpperBoundB';			
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

	function LowerBoundA($value) {return ($value+15)%15!=14;};
	function UpperBoundA($value) {return $value%15!=0;};
	function LowerBoundB($value) {return $value>=0;};
	function UpperBoundB($value) {return $value<=224;};		
	function ConvertToDec($value){return hexdec($value);};
	
	class Tile {
		public $letter;
		public $value;

		public function __construct($letter) {
			$values = "144214331A524214A11125483A0";
			$keys =   "ABCDEFGHIJKLMNOPQRSTUVWXYZ.";
			$values_real = array_map('ConvertToDec', str_split($values));
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
						foreach (str_split($word) as $index=>$letter) {
							if (substr($pattern,$index,1)=='.') {
								if (isset($set_copy[$letter])) {
									$set_copy[$letter]--;
									if ($set_copy[$letter]==0) {
										unset($set_copy[$letter]);
									}
								} elseif (isset($set_copy['.'])) {
									$blank_positions[] = $index;
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
							$found[] = array($word,$blank_positions);
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
	} elseif (isset($_POST['find'])) {
	
	} else {
		
	}
?>
<html>
	<head>
		<script type="text/javascript">
			function SetNextSelection() {
				if (typeof last_selection !== 'undefined') {
					if (typeof this_selection !== 'undefined') {
						var x=this_selection % 15;
						var y=Math.floor(this_selection/15);
						var possible = [-1,1,-15,15];
						var index = possible.indexOf(this_selection-last_selection);
						
						if (index!=-1) {
							document.getElementById('b'+(parseInt(this_selection)+possible[index])).focus();
						} else {
							document.getElementById('b'+parseInt(this_selection)).focus();
						}
					}
				}
			}
		</script>
		<style>
			input {
				text-align:center;
			}
		</style>
	</head>
	<body>
		<form action='solver2.php' method='post'>
<?php
	
	function ToUpper($tile){return strtoupper($tile);};
	
	if (isset($_POST['board'])) {
		$board = array_map('ToUpper', $_POST['board']);
	} else {
		$board = array_fill(0, 225, '');
	}


	echo "<div style='width: 20px; display:inline-block;'>&nbsp;</div>";
	for ($x=0; $x<=14; $x++) {
		echo "<div style='width: 20px; display:inline-block;'>$x</div>";
	}
	echo '<br>';

	for ($y=0; $y<=14; $y++) {
		echo "<div style='width: 20px; display:inline-block;'>$y</div>";
		for ($x=0; $x<=14; $x++) {
			echo "<input id='b".($x+$y*15)."' type='text' value='".$board[$y*15+$x]."' maxlength='1' style='width:20px;' name='board[".($y*15+$x)."]' onfocus='this.setSelectionRange(0,1);' onkeyup='this_selection=this.id.substr(1); SetNextSelection(); last_selection = this_selection;'>";
		}
		echo '<br>';
	}

	if (isset($_POST['letters'])) {
		$letters = strtoupper($_POST['letters']);
	} else {
		$letters = '';
	}

	echo "<br>";

	$game = new Game();
	$games_list = $game->Scan();

	$game_number = (isset($_POST['game_number'])?$_POST['game_number']:Game::GetNewGameNumber());
	echo " Game ".$game_number."<input type='hidden' value='".$game_number."' name='game_number'><br><br>";

	foreach ($games_list as $item) {
		echo "<input type='submit' value='$item' name='game'>";
	}
	echo "<input type='submit' value='New' name='new'>";
?>
			<br>
			<br>
			Letters
			<input type='text' value='<?php echo $letters;?>' name='letters'>
			<br>
			<br>
			<input type='submit' value='Save' name="save"> <input type='submit' value='Find' name="find">
		</form>

<?php
	function SortFunc($a,$b) {return $a>$b?-1:1;};
	
	if (isset($_POST['find'])) {
		$dictionary = new Dictionary();
		$board = new Board($dictionary);

		if (isset($_POST['board'])) {
			$board->BuildBoard($_POST['board']);
		}		
		if (isset($_POST['letters'])) {
			$play = $board->Scan(strtoupper($_POST['letters']));
			uasort($play, 'SortFunc');
			foreach ($play as $info=>$word) {
				$info = explode("|", $info);
				echo "<div style='min-width:200px;display:inline-block;border:1px dotted #888;margin:3px;'>$word {$info[0]} [{$info[1]},{$info[2]}] ".($info[3]==0?'&rarr;':'&darr;')."</div>";
				//var_dump($play);
			}
		}		

	}
?>

	</body>
</hmtl>