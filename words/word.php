<html>
	<head>
		<style>
			body {
				font-size:20px;
			}
			input {
				font-size:20px;
			}
		</style>
	</head>
	<body>
	<?php

		ini_set('memory_limit', '-1');

		if (isset($_POST['submit'])) {

			include 'parser_compiler.php';

			$cache_file = 'serialized_dict.txt';
			if (!file_exists($cache_file)) {
				$words_text = file_get_contents('wwfdictionary.txt');
				$words_array = explode("\r\n", $words_text);

				$words = array();
				foreach ($words_array as $word) {
					$words[strtoupper($word)] = strtoupper($word);
				}
				unset($words_array);
				file_put_contents($cache_file, serialize($words));
			} else {
				$words = unserialize(file_get_contents($cache_file));
			}
			//var_dump($words); exit;

			$p = new Parser();

			$rules_text = <<<RULESTEXT
				number list set 0123456789 | | |
				upto and - number | |
				bracketed and omit ( letters omit ) | |
				letters list set case ABCDEFGHIJKLMNOPQRSTUVWXYZ | | |
				pattern list or * upto number letters bracketed | | |
RULESTEXT;

			$p->CreateParser($rules_text);
			$parser = $p->rules["pattern"];

			$stream = new Stream($_POST['pattern']);
			$result = $parser->Parse($stream);

			$matched_words = array();

			if ($result->ok) {
				$reg_ex = '/^';
				$fixed_pattern = array();
				$has_fixed_pattern = true;
				$remove_parts = array();
				$index = 1;
				foreach ($result->nodes as $node) {
					switch ($node->index) {
						case 1: // zero or many
							$reg_ex .= "(.{0,15})";
							$has_fixed_pattern = false;
							break;
						case 2: // upto
							$number = $node->node(0,1)->text($stream);
							$reg_ex .= "(.{0,$number})";
							$has_fixed_pattern = false;
							break;
						case 3: // numbers
							$number = $node->text($stream);
							$reg_ex .= "(.{".$number.",$number})";
							$fixed_pattern = array_merge($fixed_pattern,array_fill(0,$number,'?'));
							break;
						case 4: // letters
							$letters = strtoupper($node->text($stream));
							$reg_ex .= "($letters)";
							$remove_parts[] = $index;
							$fixed_pattern = array_merge($fixed_pattern,str_split($letters));
							break;
						case 5: // bracketed
							$letters = strtoupper($node->text($stream));
							$fixed_pattern = array_merge($fixed_pattern,array(array($letters)));
							break;
					}
					$index++;
				}
				$reg_ex .= "$/";

				foreach ($words as $word) {
					$matches = array();
					if (preg_match($reg_ex, $word, $matches)) {
						unset($matches[0]);
						foreach ($remove_parts as $remove_part) {
							unset($matches[$remove_part]);
						}
						$matched_words[] = array($word, implode($matches));
					}
				}				
			} else {
				echo "Bad pattern";
			}
		}
	?>

		<form action="" method="POST">
			Letters<br>
			<input type="text" name="letters" value="<?php echo isset($_POST['letters'])?$_POST['letters']:'';?>">
			<br>
			<br>
			Pattern<br>
			<input type="text" name="pattern" value="<?php echo isset($_POST['pattern'])?$_POST['pattern']:'';?>">
			<br>
			<br>

			<input type="submit" name="submit" value="check">
		</form>

	<?php
		if (isset($_POST['submit'])) {
			if ($has_fixed_pattern) {

				$letters = strtoupper($_POST['letters']);
				$letters_count = array();
				foreach (str_split($letters) as $letter) {
					if (!isset($letters_count[$letter])) {
						$letters_count[$letter]=1;
					} else {
						$letters_count[$letter]++;
					}
				}

				$allowable_words = array();
				foreach ($words as $word) {
					$letters_count_copy = $letters_count;
					$ok = true;
					foreach (str_split($word) as $word_letter) {
						if (!isset($letters_count_copy[$word_letter])) {
							$ok = false;
							break;
						} elseif (isset($letters_count_copy['?'])) {
							$letters_count_copy['?']--;
							if ($letters_count_copy['?']==0) {
								unset($letters_count_copy['?']);
							}							
						} else {
							$letters_count_copy[$word_letter]--;
							if ($letters_count_copy[$word_letter]==0) {
								unset($letters_count_copy[$word_letter]);
							}
						}
					}

					if ($ok) {
						$allowable_words[$word] = $word;
					}
				}

				$right_words = array();
				$left_words = array();
				
				foreach ($allowable_words as $word) {
					if (strlen($word)<=count($fixed_pattern)) {
						$letters = str_split($word);
						$final_position = count($fixed_pattern)-count($letters);
						for ($position = 0; $position <= $final_position; $position++) {
							$ok = true;
							$gap_count = 0;
							foreach ($letters as $index=>$letter) {
								if ($fixed_pattern[$position+$index]!='?') {
									$composite = $letter.(is_array($fixed_pattern[$position+$index])?$fixed_pattern[$position+$index][0]:$fixed_pattern[$position+$index]);
									if (!isset($words[$composite])) {
										$ok = false;
										break;
									}
								} else {
									$gap_count++;
								}
							}
							if ($ok && $gap_count<($index+1)) {
								$slice = array_slice($fixed_pattern,$position,strlen($word));
								$slice = array_map(function($element){return is_array($element)?'('.$element[0].')':$element;}, $slice);
								$left_words[] = array($word, implode($slice) );
							}

							$ok = true;
							$gap_count = 0;
							foreach ($letters as $index=>$letter) {
								if ($fixed_pattern[$position+$index]!='?') {
									$composite = (is_array($fixed_pattern[$position+$index])?$fixed_pattern[$position+$index][0]:$fixed_pattern[$position+$index]).$letter;
									if (!isset($words[$composite])) {
										$ok = false;
										break;
									}
								} else {
									$gap_count++;
								}
							}
							if ($ok && $gap_count<($index+1)) {
								$slice = array_slice($fixed_pattern,$position,strlen($word));
								$slice = array_map(function($element){return is_array($element)?'('.$element[0].')':$element;}, $slice);
								$right_words[] = array(implode($slice), $word);
							}							
						}
					}
				}

				usort($left_words, function($a,$b) {
					return strlen($a[0])<strlen($b[0])?1:-1;
				});
				
				usort($right_words, function($a,$b) {
					return strlen($a[1])<strlen($b[1])?1:-1;
				});
				
				foreach ($left_words as $left_word) {
					echo "{$left_word[0]} ({$left_word[1]})<br>";
				}
				
				echo '<br>';
			
				foreach ($right_words as $right_word) {
					echo "({$right_word[0]}) {$right_word[1]}<br>";
				}				
				
				
				echo '<br>';
			}
		}

		if (isset($_POST['submit'])) {
			$letters = strtoupper($_POST['letters']);
			$letters_count = array();
			foreach (str_split($letters) as $letter) {
				if (!isset($letters_count[$letter])) {
					$letters_count[$letter]=1;
				} else {
					$letters_count[$letter]++;
				}
			}

			$user_matches = array();
			foreach ($matched_words as $match) {
				$ok = true;
				$letters_count_copy = $letters_count;
				for ($x=0; $x<strlen($match[1]); $x++) {
					$letter = substr($match[1],$x,1);
					if (isset($letters_count_copy[$letter])) {
						$letters_count_copy[$letter]--;
						if ($letters_count_copy[$letter]==0) {
							unset($letters_count_copy[$letter]);
						}
					} elseif (isset($letters_count_copy['?'])) {
						$letters_count_copy['?']--;
						if ($letters_count_copy['?']==0) {
							unset($letters_count_copy['?']);
						}						
					} else {
						$ok = false;
						break;
					}
				}
				if ($ok) {
					$user_matches[] = $match[0];
				}
			}
			usort($user_matches, function($a,$b){
				$al=strlen($a);$bl=strlen($b);
				return ($al==$bl)?(($a<$b)?1:-1):(($al<$bl)?1:-1);});

			foreach ($user_matches as $user_match) {
				echo "$user_match<br>";
			}
		}
	?>
	</body>
</html>