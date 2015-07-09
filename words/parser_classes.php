<?php

	// These classes are used to create a recursive decent parser 
	// An input stream is matched with the parsing classes and a hierarchy of TreeNodes returned representing the parsed stream
	// Each parsing class represents a different unit of parsing. Parsing classes are built up into a hierarchy.
	// A parse will either match or not match. In either case information about the unit of parsing (an individual parse object) is returned in a TreeNode(s).
	// Certain classes represent the terminal leaf nodes of the parse hierarchy: Text, Set, Any, BOS, EOS, Not, Optional
	// Others are used to build the hierarchy and are non-terminal: And, Or, List
	// Each parse object has optional modifiers for Case Insensitiviy, Non-consuming or Omitting 
	// Case insensitivity applies only to a Text or Set object
	// A non-consuming parse, will match, but not advance the Stream position
	// An Omitting parse will not return the text of the match in the TreeNode
	// If a match is made the Stream position is advanced
	// Typical usage for creating a parse object is like this:
	//
	// for a terminal:
	// $match_text = new ParseText(false,false);$match_text->def(false,"sometext");
	//
	// or for a non-terminal:
	// $string_together = new ParseAnd(true,false);$string_together->def($match_text1,$match_text2,$match_text3);
	// 
	// Except for an EOS match (or Not match), a parse past the end of the text Stream will never match
	//
	// It is possible for sub-parse objects to refer to a parse-object further up the hierarchy, this allows for recursion

	class Stream {
		var $text='';
		var $position = 0;
		var $end = 0;
		
		function __construct($text) {
			$this->text = $text;
			$this->end = strlen($text);
		}
		
		public function Move($step) {
			$this->position += $step;
		}
		
		public function AtEnd($size=1) {
			return ($this->position+$size-1)>=$this->end;
		}

		public function AtBeginning() {
			return $this->position==0;
		}

		public function Reset() {
			$this->position = 0;
		}
	}

	class TreeNode {
		public $start = -1; // Text position of start of match/non-match
		public $end = -1; // End position of match 
		public $index = -1; // Index for OR match, or number of sub TreeNodes
		public $omit = false; // Text of match is omitted/not omitted
		public $nodes = array(); // List of sub TreeNodes
		public $leaf = false; // Is a leaf node
		public $ok = false; // Text matched/non matched
		public $replace = false;

		function __construct($start=-1,$end=-1,$index=-1) {
			$this->start = $start;
			$this->end = $end;
			$this->index = $index;
		}
		
		// The entire text matched between the $start and $end
		function raw_text(Stream &$stream) {
			$depth = func_num_args();
			$result = $this;
			for ($index=1; $index<$depth; $index++) {
				$result = $result->nodes[func_get_arg($index)];
			}
			
			return substr($stream->text,$result->start,$result->end-$result->start+1);
		}
		
		// The text matched taking Omitted text into account
		function text(Stream &$stream) {
			$depth = func_num_args();
			$result = $this;
			for ($index=1; $index<$depth; $index++) {
				$result = $result->nodes[func_get_arg($index)];
			}			
			
			if ($result->replace!==false) {
				return $result->replace;
			} elseif (count($result->nodes)>0) {
				$full_text = '';
				foreach ($result->nodes as &$sub_result) {
					$full_text .= $sub_result->text($stream);
				}
				return $full_text;
			} else {
				if ($result->leaf) {
					return substr($stream->text,$result->start,$result->end-$result->start+1);
				} else {
					return '';
				}
			}
		}
		
		// This allows the index member of the TreeNode at the position in hierachy to be be easily traversed eg index(0,1,2)
		function index() {
			$depth = func_num_args();
			$result = $this;
			for ($index=0; $index<$depth; $index++) {
				$result = $result->nodes[func_get_arg($index)];
			}			
			return $result->index;		
		}

		// This allows the TreeNode hierachy to be be easily traversed eg path(0,1,2)
		function node() {
			$depth = func_num_args();
			$sr = $this;
			for ($index=0; $index<$depth; $index++) {
				if (isset($sr->nodes[func_get_arg($index)])) {
					$sr = $sr->nodes[func_get_arg($index)];
				} else {
					return false;
				}
			}
			return $sr;
		}


	}
	
	// Match the given text literal with the Stream, with an option for case insensitivity 
	class ParseText {
		private $compare_text = '';
		private $length;
		public $omit = false;
		public $non_consuming = false;
		public $case_insensitive = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;
		}
		
		function def($case_insensitive, $text) {
			$this->case_insensitive = $case_insensitive;
			if ($case_insensitive) {
				$this->compare_text = strtoupper($text);
			} else {
				$this->compare_text = $text;
			}
			$this->length = strlen($text);			
		}
		
		public function Parse(Stream &$stream) {
			$result = new TreeNode($stream->position);
			$result->omit = $this->omit;
			$result->leaf = true;
			if ($stream->AtEnd($this->length)) {
			} elseif (!$this->case_insensitive && (substr($stream->text, $stream->position, strlen($this->compare_text))==$this->compare_text)) {
				$result->end = $stream->position+$this->length-1;
				$result->ok = true;
				if (!$this->non_consuming) {
					$stream->Move($this->length);
				}
			} elseif ($this->case_insensitive && (strtoupper(substr($stream->text, $stream->position, strlen($this->compare_text)))==$this->compare_text)) {
				$result->end = $stream->position+$this->length-1;
				$result->ok = true;
				if (!$this->non_consuming) {
					$stream->Move($this->length);
				}				
			}
			
			return $result;
		}
	}
	
	// Match a single character in the Stream with one in the given set of characters
	class ParseSet {
		private $compare_set = '';
		private $length = 0;
		public $omit = false;
		public $non_consuming = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;

		}

		function def($case_insensitive, $text_set) {
			$this->case_insensitive = $case_insensitive;
			if ($case_insensitive) {
				$this->compare_set = strtoupper($text_set);
			} else {
				$this->compare_set = $text_set;
			}					
			$this->length = strlen($text_set);				
		}
		
		public function Parse(Stream &$stream) {
			$result = new TreeNode($stream->position);
			$result->omit = $this->omit;
			$result->leaf = true;
			
			if ($stream->AtEnd(1)) {
				return $result;
			}
			
			if ($this->case_insensitive) {
				$stream_char = strtoupper(substr($stream->text,$stream->position,1));
			} else {
				$stream_char = substr($stream->text,$stream->position,1);	
			}
			for ($index=0; $index<$this->length; $index++) {
				$char = substr($this->compare_set,$index,1);
				
				if ($char==$stream_char) {
					$result->end = $stream->position;
					$result->index = $index+1;
					$result->ok = true;
					
					if (!$this->non_consuming) {
						$stream->Move(1);
					}					
					return $result;
				}
			}
			return $result;
		}	
	}
	
	// Will match any single character in the Stream
	class ParseAny {
		public $omit = false;
		public $non_consuming = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;
		}
		
		public function Parse(Stream &$stream) {
			$result = new TreeNode($stream->position);
			$result->omit = $this->omit;
			$result->leaf = true;
			
			if (!$stream->AtEnd()) {
				$result->end = $stream->position;
				if (!$this->non_consuming) {
					$stream->Move(1);
				}
				$result->ok = true;
			}
			return $result;
		}	
	}	
	
	// Will match if passed the End Of the Stream
	class ParseEOS {
		public $omit = false;
		public $non_consuming = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;
		}
		
		public function Parse(Stream &$stream) {
			$result = new TreeNode($stream->position);
			$result->omit = $this->omit;
			$result->ok = $stream->AtEnd();
			return $result;
		}	
	}

	// Will match if at the Beginning Of the Stream
	class ParseBOS {
		public $omit = false;
		public $non_consuming = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;
		}
		
		public function Parse(Stream &$stream) {
			$result = new TreeNode($stream->position);
			$result->omit = $this->omit;
			$result->ok = $stream->AtBeginning();
			return $result;
		}	
	}	
	
	// Will match if the sub-parse object is not matched and not match if the sub-parse object is matched 
	class ParseNot {
		public $omit = false;
		public $non_consuming = false;
		private $condition;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;		
		}

		function def($condition) {
			$this->condition = $condition;
		}
		
		public function Parse(Stream &$stream) {
			$result = new TreeNode($stream->position);
			$result->omit = $this->omit;
			$sub_result = $this->condition->Parse($stream);
			if ($sub_result->ok) {
				$result->ok = false;
				if ($this->non_consuming) {
					$stream->position = $start;
				}				
			} else {
				$result->ok = true;
			}
			return $result;
		}
	}
	
	// Will match only if all the sub-parse objects match
	class ParseAnd {
		public $set = array();
		public $length = 0;
		public $omit = false;
		public $non_consuming = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;			
			for ($arg=2; $arg<func_num_args(); $arg++) {
				$this->set[]=func_get_arg($arg);
			}
			$this->length = func_num_args()-2;
		}
		
		function def() {
			$this->set = array();
			for ($arg=0; $arg<func_num_args(); $arg++) {
				$this->set[]=func_get_arg($arg);
			}
			$this->length = func_num_args();			
		}
		
		public function Parse(Stream &$stream) {
			$start = $stream->position;
			$result = new TreeNode($start);
			$result->omit = $this->omit;
			foreach ($this->set as $object) {
				$sub_result = $object->Parse($stream);
				if ($sub_result->ok===false) {
					$stream->position = $start;
					return $result;
				} else {
					if (!$sub_result->omit) {
						$result->nodes[] = $sub_result;
					}
				}
			}
			$result->end = $stream->position-1;
			$result->ok = true;

			if ($this->non_consuming) {
				$stream->position = $start;
			}			
			return $result;
		}
	}
	
	// Will match if any of the sub-parse objects match. The Index of the matched sub-parse object is returned in the TreeNode
	class ParseOr {
		public $set = array();
		public $length = 0;
		public $omit = false;
		public $non_consuming = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;			
			for ($arg=2; $arg<func_num_args(); $arg++) {
				$this->set[]=func_get_arg($arg);
			}
			$this->length = func_num_args();
		}

		function def() {
			$this->set = array();
			for ($arg=0; $arg<func_num_args(); $arg++) {
				$this->set[]=func_get_arg($arg);
			}
			$this->length = func_num_args();			
		}
		
		public function Parse(Stream &$stream) {
			$start = $stream->position;
			$result = new TreeNode($start);
			$result->omit = $this->omit;
			$index = 1;
			foreach ($this->set as $object) {
				$sub_result = $object->Parse($stream);
				if ($sub_result->ok) {
					if (!$sub_result->omit) {
						$result->nodes[] = $sub_result;
					}
					$result->end = $sub_result->end;
					$result->index = $index;
					$result->ok = true;

					if ($this->non_consuming) {
						$stream->position = $start;
					}

					return $result;
				}
				$index++;
			}
			$stream->position = $start;
			return $result;
		}
	}
	
	// Will optionally match the sub-parse object
	class ParseOptional {
		private $condition;
		public $omit = false;
		public $non_consuming = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;
		}

		function def($condition) {
			$this->condition = $condition;	
		}
		
		public function Parse(Stream &$stream) {
			$start = $stream->position;
		
			$result = new TreeNode($stream->position);
			$result->omit = $this->omit;

			$sub_result=$this->condition->Parse($stream);
			if ($sub_result->ok) {
				if (!$sub_result->omit) {
					$result->nodes[] = $sub_result;
				}
				$result->index=1;
				$result->end = $stream->position-1;
			} else {
				$result->index=0;
			}

			$result->ok = true;

			if ($this->non_consuming) {
				$stream->position = $start;
			}			
			return $result;
		}
	}

	// Will match repetitively the sub-parse object until a non-match is found. 
	// There is an option for a delimited match, which will insert the delimiter sub-parse object into the resulting TreeNodes.
	// There is an option for a terminating match, which will stop the repetition if the terminator match is made
	// There are options for a minimum and maximum number of repetitive matches. If the minimum is set to zero, it will accept no match at all
	class ParseList {
		public $condition;
		public $delimiter;
		public $terminator;
		public $min;
		public $max;
		public $omit = false;
		public $non_consuming = false;
		
		function __construct($omit, $non_consuming) {
			$this->omit = $omit;
			$this->non_consuming = $non_consuming;			

		}

		function def($condition, $delimiter=null, $terminator=null, $min=1, $max=-1) {
			$this->condition = $condition;
			$this->delimiter = $delimiter;
			$this->terminator = $terminator;
			$this->min = $min;
			$this->max = $max;				
		}
		
		public function Parse(Stream &$stream) {
			$start = $stream->position;
			$count = 0;
			$terminator_found = false;

			$result = new TreeNode($stream->position);
			$result->omit = $this->omit;
			
			while (true) {
				if ($this->terminator!==null) {
					$sub_result=$this->terminator->Parse($stream);
					if ($sub_result->ok) {
						if (!$sub_result->omit) {
							$result->nodes[] = $sub_result;
						}
						$terminator_found = true;
						break;
					}
				}
				if ($this->max!=-1 && $count==$this->max) {
					break;
				}			

				if ($this->delimiter===null || $count===0) {
					$sub_result=$this->condition->Parse($stream);
					if ($sub_result->ok) {
						if (!$sub_result->omit) {
							$result->nodes[] = $sub_result;
						}
					} else {
						break;
					}
				} else {
					$start_delimiter = $stream->position;
					$delimiter_result=$this->delimiter->Parse($stream);
					if (!$delimiter_result->ok) {
						break;
					}		

					$sub_result=$this->condition->Parse($stream);
					if ($sub_result->ok) {
						if (!$delimiter_result->omit) {
							$result->nodes[] = $delimiter_result;
						}
						if (!$sub_result->omit) {
							$result->nodes[] = $sub_result;
						}
					} else {
						$stream->position = $start_delimiter;
						break;
					}
						
				}
				
				$count++;
			}
			if ($this->terminator!==null && !$terminator_found) {
				$stream->position = $start;
				$result->ok = false;
				return $result;		
			}
			
			if ($count>=$this->min) {
				$result->end = $stream->position-1;
				$result->index = $count;
				$result->ok = true;
			} else {
				$stream->position = $start;
				$result->ok = false;
			}
			
			if ($this->non_consuming) {
				$stream->position = $start;
			}			
			return $result;
	
		}
				
	}

?>