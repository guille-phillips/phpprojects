<?php

	include 'parser_classes.php';



	// It is possible to build a recursive decent parser just using the parse classes in parser_classes.php

	// But this can be clunky and hard to read and maintain in code (see __construct function below for an example)

	// The Parser class defines a simple language used to create these parse objects in a more efficient manner

	// The language can be written using a text editor and consists of a set of named parsing rules

	// The pipe character is used to end a particular rule or parse definition

	// A parse object is defined with one of the reserved (case insensitive) key words: set, not, opt, and, or, list, any, begin, end

	// Other reserved key words are used as modifiers: min, max, del, until, omit, case, non

	// Any non-reserved text or symbols (except pipe or \\) are taken verbatim as literal text/set matches

	// Any previously defined rule name is used to invoke that parse-rule, and cannot be re-used as a purely literal text match: it must be escaped

	// All white space is ignored and simply used as a separator for readability

	// A typical rule will have the following construction:

	//     rule_geographic geographic | 

	// This will create a ParseText object matching the word 'geographic' in the text Stream, rule_geographic is the name for the rule

	//     rule_geographic case geographic |

	// This will make the match case-insensitive, so 'GEOGRAPHIC' or 'Geographic' will both match

	//     rule_geographic omit case geographic |

	// Again a case-insensitve match with 'geographic', but the text of the result is not returned in the TreeNode object

	//     rule_geographic non omit case geographic |

	// The match is made, but the Stream text position is not advanced, even if the match is made

	// The non and omit modifiers can be used with any parse definition. The case modifier can be used with any literal or set definition:

	//     rule_alpha_character case set abcdefghijklmnopqrstuvwxyz | |

	// this will match any single alphabetic character, upper or lower case. Note the 'set' definition is terminated by its own pipe character, 

	// Other parses taking a single parse definition are: not, opt.

	// The 'and' and 'or' parse objects take a list of rules:

	//     bracketed and ( expression ) | |

	// In this case the 'bracketed' rule consists of an 'and' parse, which expects an opening bracket, followed by an 'expression' followed by a closing bracket

	//     operator or + - * / xor | |

	// Note that symbols (except | and \\) do not have any special significance

	// The most complicated parse object is the list (ParseList), the full syntax is:

	//     numeric_arguments list number del comma until 999 min 2 max 5 | |

	// assuming the 'number' and 'comma' rules have already been defined, this would match a list of numbers, separated by commas until the literal 999 is matched.

	// A minimum of two numbers must be matched or a maximum of five numbers. The 'del', 'until', 'min' and 'max' clauses are all optional

	// The following is an example of a simple (recursive) expression parser

	//    number list set 0123456789 | | |

	//    operator or + - * / | |

	//    operand or bracketed number | |

	//    expression list operand del operator | |

	//    bracketed and ( expression ) | |

	//

	// Non keyboard characters can be defined using the \\123\\ construct, where 123 is the ANSII code of the character:

	//    whitespace set \\11\\\\32\\\\13\\\\9\\ | |

	// or e.g.:

	//    end_line ;\\13\\ |

	// Any literal using an exisiting rule name or reserved key word or containing white space, must be escaped, with two starting pipes and one ending pipe

	//    phrase or ||list| ||just in case| | |

	// A pipe can be represented in a text literal by doubling it:

	//    refererer his||her | 

	//

	// Rules can be forward referenced, and cannot be used as literals even before they are defined.

	// This Parser only compiles the text definition of the parsing tree into parse objects, you must invoke the parse objects directly to perform the parsing

	// The Parser gives a set of parse objects as an Array, with the key to each member matching the rule name.



	class Parser {

		private $rules_parser;

		private $stream;

		public $rules = array();

		

		function __construct() {

			$and = new ParseText(false,false);$and->def(false,"and");

			$or = new ParseText(false,false);$or->def(false,"or");

			$set = new ParseText(false,false);$set->def(false,"set");

			$not = new ParseText(false,false);$not->def(false,"not");

			$opt = new ParseText(false,false);$opt->def(false,"opt");

			$any = new ParseText(false,false);$any->def(false,"any");

			$list = new ParseText(false,false);$list->def(false,"list");

			$min = new ParseText(false,false);$min->def(false,"min");

			$max = new ParseText(false,false);$max->def(false,"max");

			$del = new ParseText(false,false);$del->def(false,"del");

			$until = new ParseText(false,false);$until->def(false,"until");

			$non = new ParseText(false,false);$non->def(false,"non");

			$omit = new ParseText(false,false);$omit->def(false,"omit");

			$case = new ParseText(false,false);$case->def(false,"case");

			$end = new ParseText(false,false);$end->def(false,"end");

			$begin = new ParseText(false,false);$begin->def(false,"begin");

			$slash = new ParseText(true,false);$slash->def(false,'\\');

			$pipe = new ParseText(false,false);$pipe->def(false,"|");

			$pipe_omit = new ParseText(true,false);$pipe_omit->def(false,"|");

			$double_pipe = new ParseAnd(false,false);$double_pipe->def($pipe,$pipe_omit);

			$double_pipe_omit = new ParseAnd(true,false);$double_pipe_omit->def($pipe,$pipe);

			$space = new ParseSet(false,false);$space->def(false," "."\n"."\r"."\t");

			$ws = new ParseList(true,false);$ws->def($space,null,null,0);

			$anychar = new ParseAny(false,false);

			

			$digit = new ParseSet(false,false);$digit->def(false,"0123456789");

			$entity = new ParseAnd(false, false);



			$literal_char = new ParseOr(false,false);$literal_char->def($double_pipe,$entity,$anychar);

			$not_pipe = new ParseNot(false,false);$not_pipe->def($pipe);

			$single_pipe = new ParseAnd(true,false);$single_pipe->def($pipe,$not_pipe);

			

			$not_identifier_char = new ParseOr(true,true);$not_identifier_char->def($space,$single_pipe);

			$identifier = new ParseList(false,false);$identifier->def($literal_char,null,$not_identifier_char); 	

			

			$literal_text = new ParseList(false,false);$literal_text->def($literal_char,null,$single_pipe,0);

			$literal = new ParseAnd(false,false);$literal->def($double_pipe_omit,$literal_text);

			

			$case_opt = new ParseOptional(false,false);$case_opt->def($case);

			$omit_opt = new ParseOptional(false,false);$omit_opt->def($omit);

			$non_opt = new ParseOptional(false,false);$non_opt->def($non);

			

			$omit_non = new ParseAnd(false,false);$omit_non->def($omit_opt,$ws,$non_opt,$ws);

			

			$text = new ParseOr(false,false);$text->def($literal,$identifier);

			$text_case = new ParseAnd(false,false);$text_case->def($case_opt,$ws,$text);

			

			$and_ommited_expression = new ParseAnd(false,false);

			$and_expression = new ParseAnd(false,false);

			$or_expression = new ParseAnd(false,false);

			$not_expression = new ParseAnd(false,false);

			$set_expression = new ParseAnd(false,false);

			$opt_expression = new ParseAnd(false,false);

			$list_expression = new ParseAnd(false,false);

			

			$expression = new ParseOr(false,false);$expression->def($any,$end,$begin,$set_expression,$not_expression,$opt_expression,$and_expression,$or_expression,$list_expression,$text_case);

			$full_expression = new ParseAnd(false,false);$full_expression->def($omit_non,$expression);

			$expression_list = new ParseList(false,false);$expression_list->def($full_expression,$ws);

			

			$and_ommited_expression->def($expression_list,$ws,$pipe);

			$and_expression->def($and,$ws,$expression_list,$ws,$pipe);

			$or_expression->def($or,$ws,$expression_list,$ws,$pipe);

			$not_expression->def($not,$ws,$expression,$ws,$pipe);

			$set_expression->def($set,$ws,$text_case,$ws,$pipe);

			$opt_expression->def($opt,$ws,$expression,$ws,$pipe);

			

			$number = new ParseList(false,false);$number->def($digit);

			$entity->def($slash,$number,$slash);



			$delimit_clause = new ParseAnd(false,false);$delimit_clause->def($ws,$del,$ws,$full_expression);

			$until_clause = new ParseAnd(false,false);$until_clause->def($ws,$until,$ws,$full_expression);

			$min_clause = new ParseAnd(false,false);$min_clause->def($ws,$min,$ws,$number);

			$max_clause = new ParseAnd(false,false);$max_clause->def($ws,$max,$ws,$number);

			

			$delimit_opt = new ParseOptional(false,false);$delimit_opt->def($delimit_clause);

			$until_opt = new ParseOptional(false,false);$until_opt->def($until_clause);

			$min_opt = new ParseOptional(false,false);$min_opt->def($min_clause);

			$max_opt = new ParseOptional(false,false);$max_opt->def($max_clause);

			

			$list_expression->def($list,$ws,$full_expression,$delimit_opt,$until_opt,$min_opt,$max_opt,$ws,$pipe);



			$rule = new ParseAnd(false,false);$rule->def($ws, $identifier,$ws,$full_expression,$ws,$pipe,$ws);

			

			$this->rules_parser = new ParseList(false,false);$this->rules_parser->def($rule,null);

		}



		function CreateParser($definition) {

			$this->stream = new Stream($definition);



			$this->rules = array();

			

			$result = $this->rules_parser->Parse($this->stream);



			echo $result->ok?'':'definition not recognised';

			

			$result->ok or exit;



			return $this->CompileDefinition($result);



		}



		private function CompileDefinition($tree) {

			foreach ($tree->nodes as $tree_rule) {

				$this->InitialiseRule($tree_rule);

			}

			

			foreach ($tree->nodes as $tree_rule) {

				$this->CompileRule($tree_rule);

			}

		}

		

		private function InitialiseRule($tree) {

			$rule_name = $tree->raw_text($this->stream,0);

			$rule_index = $tree->index(1,1);



			for ($type = 0; $type<5; $type++) {

				switch ($type) {

					case 0:

						$omit = $tree->index(1,0,0)==1;

						$non = $tree->index(1,0,1)==1;

						$name = $rule_name;

						break;

					case 1:

						$omit = false;

						$non = false;

						$name = $rule_name.'_';

						break;

					case 2:

						$omit = false;

						$non = true;

						$name = $rule_name.'_n';

						break;

					case 3:

						$omit = true;

						$non = false;

						$name = $rule_name.'_o';

						break;

					case 4:

						$omit = true;

						$non = true;

						$name = $rule_name.'_on';

						break;

				}

				

				switch ($rule_index) {

					case 1: // any

						$object = new ParseAny($omit,$non);

						break;

					case 2: // end 

						$object = new ParseEOS($omit,$non);

						break;

					case 3: // begin 

						$object = new ParseEOS($omit,$non);

						break;						

					case 4: // set

						$object = new ParseSet($omit,$non);

						break;

					case 5: // not

						$object = new ParseNot($omit,$non);

						break;

					case 6: // opt

						$object = new ParseOptional($omit,$non);

						break;

					case 7: // and

						$object = new ParseAnd($omit,$non);

						break;

					case 8: // or

						$object = new ParseOr($omit,$non);

						break;

					case 9: // list 

						$object = new ParseList($omit,$non);

						break;

					case 10: // literal

						$object = new ParseText($omit,$non);

						break;

				}

				

				$this->rules[$name]=$object;

			}

	

		}



		private function CompileRule($tree) {

			$rule_name = $tree->raw_text($this->stream,0);

			$this->CompileExpression($tree->node(1), $rule_name);

			$this->CompileExpression($tree->node(1), $rule_name.'_',false,false);

			$this->CompileExpression($tree->node(1), $rule_name.'_o',false,true);

			$this->CompileExpression($tree->node(1), $rule_name.'_n',true.false);

			$this->CompileExpression($tree->node(1), $rule_name.'_on',true,true);

		}

		

		private function &CompileExpression($tree, $rule_name='',$omit_override = null, $non_override = null) {

			$omit = $omit_override===null?($tree->node(0,0)!==false?($tree->index(0,0)==1):false):$omit_override;

			$non = $non_override===null?($tree->node(0,1)!==false?($tree->index(0,1)==1):false):$non_override;



			$expression_type = $tree->nodes[1]->index;

			

			if ($rule_name==='') {

				switch ($expression_type) {

					case 1: // any

						$object = new ParseAny($omit,$non);

						break;

					case 2: // end

						$object = new ParseEOS($omit,$non);

						break;

					case 3: // begin

						$object = new ParseBOS($omit,$non);

						break;						

					case 4: // set

						$object = new ParseSet($omit,$non);

						break;

					case 5: // not

						$object = new ParseNot($omit,$non);

						break;

					case 6: // opt

						$object = new ParseOptional($omit,$non);

						break;

					case 7: // and

						$object = new ParseAnd($omit,$non);

						break;

					case 8: // or

						$object = new ParseOr($omit,$non);

						break;

					case 9: // list 

						$object = new ParseList($omit,$non);

						break;

					case 10: // literal

						$object = new ParseText($omit,$non);

						break;

				}	

			} else {

				$object = &$this->rules[$rule_name];

			}



			switch ($expression_type) {

				case 1: // any

					break;

				case 2: // end

					break;

				case 3: // begin

					break;					

				case 4: // set

					$case = $tree->index(1,0,1,0)==1;

					$literal_set = '';

					

					foreach ($tree->node(1,0,1,1,0)->nodes as $char) {

						switch ($char->index) {

							case 1:

								$literal_set .= '|';

								break;

							case 2:

								$literal_set .= chr($char->nodes[0]->text($this->stream));

								break;

							case 3:	

								$literal_set .= $char->nodes[0]->text($this->stream);

								break;								

						}

					}

					$object->def($case,$literal_set);

					break;

				case 5: // not

				case 6: // opt

					$condition = &$this->CompileExpression($tree->node(1,0));

					$object->def($condition);

					break;

				case 7: // and

				case 8:	// or	

					$object->set = array();

					foreach ($tree->node(1,0,1)->nodes as $node) {

						$object->set[] = &$this->CompileExpression($node);

					}

					$object->length = count($object->set);

					break;

				case 9: // list

					$object->condition = &$this->CompileExpression($tree->node(1,0,1));

					if ($tree->index(1,0,2)==1) {

						$object->delimiter = &$this->CompileExpression($tree->node(1,0,2,0,1));

					} else {

						$object->delimiter = null;

					}



					if ($tree->index(1,0,3)==1) {

						$object->terminator = &$this->CompileExpression($tree->node(1,0,3,0,1));

					} else {

						$object->terminator = null;

					}

					

					$object->min = $tree->index(1,0,4)==1?$tree->text($this->stream, 1,0,4,0,1):1;

					$object->max = $tree->index(1,0,5)==1?$tree->text($this->stream, 1,0,5,0,1):-1;

					break;

				case 10: // literal

					$case = $tree->index(1,0,0)==1;

					$text = $tree->text($this->stream,1,0,1,0);



					$encoded_text = '';

					

					foreach ($tree->node(1,0,1,0)->nodes as $char) {

						switch ($char->index) {

							case 1:

								$encoded_text .= '|';

								break;

							case 2:

								$encoded_text .= chr($char->nodes[0]->text($this->stream));

								break;

							case 3:	

								$encoded_text .= $char->nodes[0]->text($this->stream);

								break;								

						}

					}



					switch ($tree->index(1,0,1)) {

						case 1: // escaped

							$object->def($case,$encoded_text);

							break; 

						case 2: // non escaped

							if (array_key_exists($text, $this->rules)) {

								$object = &$this->rules[$text];

								$o = $object->omit || $omit;

								$n = $object->non_consuming || $non;

								$object = &$this->rules[$text.'_'.($o?'o':'').($n?'n':'')];

							} else {

								$object->def($case,$encoded_text);

							}

							break;

					}

					break;

			}

			

			return $object;

		}

		

		function Parse($rule, $stream) {

			$stream->position=0;

			return $this->rules[$rule]->Parse($stream);

		}



	}



/*

	$x = new Parser();

	$x->CreateParser("test_rule case cat |");

	

	$file= new Stream("CATabcdefghijklmn");

	$result = $x->rules["test_rule"]->Parse($file);

	

	echo $result->text($file);

*/	

?>