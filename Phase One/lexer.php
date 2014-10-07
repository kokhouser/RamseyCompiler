<?php

	/* Plan of attack:
		0. Make sure it's a .ram file
		1. explode file into array by line
		2. explode line into array by words. KABOOM!
		3. trim leading spaces and tabs from each word
		4. check for tokens, maximum munch style
		5. output tokens into separate text file.
		6. store identifier values in XML file
	*/
	
	//Token class test
	
	class Token{
	  public $id;
	}
	
	class IdentToken extends Token{
	  public $name;
	}
	
	class LiteralToken extends Token{
	  public $value;
	}

	$filename = $argv[1];								// currently not checking for .ram extension
	$input = file_get_contents($filename);
	$lines = explode("\n", $input);						// creates array, where each index is a line of the input file
	$count = 0;
	$lineNumber=1;										//for error reporting
	$tokenStream="";
	$lexingError="";									// to be used in the event of an error
	$tokenId= 0;
	$tokenArray = array();
	foreach($lines as $line){							// iterates through array of lines
		$pretoken = explode(" ",$line);					// creates array of words in line
		$multiendl = true;
		foreach ($pretoken as $word){					// iterates through words
			$no_space = trim($word);					// eliminates leading and trailing spaces and tabs
			$count = strlen($no_space);					// need to know how long word is
			$start=0;									// multiple tokens can be in one word, so start needs to be dynamic
			$length=1;									// as with above, length needs to be dynamic as well
			$token="";									// for assigning a token to a case
			$val="";									// for keeping the value of a token, for identifiers
			$isCurrentMatch=false;						//this tracks if the current string has a match to any token that has not been tokenized
			$isTokenDone=false;							// since default case checks for identifier and most substrings are valid identifiers, we need to store a token only with the largest string that is an identifier
			$isWordDone=false;							//is the word finished? more than token can be in a word. bug: variable was not camelcase, meaning conditional below was always not false
			$isLineDone=false;
			$strname = "";
			for($i=0;$i<$count;$i++){
				$str=substr($no_space,$start,$length);	//substring to be checked for a token
				switch ($str){
				case "":
					break;
				case "\t":
					$token="";
					$isCurrentMatch=true;
					$multiendl = false;
					break;
				case "#":								// no comments allowed. no need to check the rest of the line
					$isWordDone=true;
					$isLineDone=true;
					break;
				case "(":
					$token="<l_paren>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case ")":
					//echo "debugging is fun\n";
					$token="<r_paren>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "=":
					$token="<compare_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "+":
					$token="<add_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "mod":
					$token="<mod_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "*":
					$token="<mult_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "/":
					$token="<div_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "-":
					$token="<sub_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "<":
					$token="<less_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case ">":
					$token="<greater_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "<=":
					$token="<lesseq_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case ">=":
					$token="<greateq_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "!=":
					$token="<noteq_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "AND":
					$token="<and_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "OR":
					$token="<or_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "<-":
					$token="<assign_op>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "in":
					$token="<in_type>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "small":
					$token="<small_type>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "big":
					$token="<big_type>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "boo":
					$token="<boo_type>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case ",":
					$token="<comma>";
					$isCurrentMatch=true;
					$multiendl = false;
				case "as":
					$token="<as>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "fun":
					$token="<fun>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "endfun":
					$token="<endfun>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "if":
					$token="<if>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "endif":
					$token="<endif>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "toss":
					$token="<toss>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "else":
					$token="<else>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "elf":
					$token="<elf>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "while":
					$token="<while>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				case "endwhile":
					$token="<endwhile>";
					$isCurrentMatch = true;
					$multiendl = false;
					break;
				default: 								//as far as we know, php switch statements must have string, and literals need regex matching which does not return string
					$matchToNumLiteral ='/^[0-9]*$/';		//currently assuming that a number literal CANNOT begin with a decimal
					if(preg_match($matchToNumLiteral, $str)==1){
							$token= "<literal>";
							$isCurrentMatch = true;
							$multiendl = false;
							//save val somehow here
					}
					else{
						/*   since give and take aren't allowed, not sure if there is a need for string literals

						$matchToNumLiteral ='/^ idkyet $/';  //really, idk
						if(preg_match($matchToStrLiteral, $str)==1){
								$start += ($length-1);		// NOT regex match, longer than 1 char, go to last valid location
								$length = 0;				//prepare for length increment
								$isTokenDone=true;			//store last token as identifier
								$token= "<literal>";
								//save val somehow here
						}
						*/

						$matchToIdent = '/^[a-zA-Z]\w*$/';		// bug: this was not anchored to end of string, so if there was a match at any point in the string, it passed
						if(preg_match($matchToIdent, $str)!=1){	// if current str does NOT match regex, there are NO tokens it matches. can only reach here if at end of valid token, or invalid symbol
							if(!$isCurrentMatch){					// this case is an invalid character, and therefore a lexing error
								$lexingError="--Lexing Error: invalid symbol ".$str." on line ".$lineNumber."\n";
							}
							else{
								$start += ($length-1);		// NOT regex match, but previous did match, go to last valid location
								$length = 0;				//prepare for length increment
								$isTokenDone=true;			//store last token as identifier
								$i--;						//since we begin checking the char that was just checked, we have not progressed and need to change i accordingly

								//save val somehow here
							}
						}
						else{								//matches regex
							$token= "<ident>";
							$isCurrentMatch = true;
							$multiendl = false;
              $strname = $str;
							//save val here somehow
						}
					}

				}		// end switch
				
				if( ($start+$length)==$count&&$isCurrentMatch){			//check to see if at end of word and if we have token
					$isTokenDone=true;
				}
				if($isTokenDone){
					if($token != "")
						$tokenStream.=$token."\n";
					$isTokenDone=false;
					$isCurrentMatch=false;
					//Token class test
					if ($token == "<ident>"){
					  $newToken = new IdentToken();
					  $newToken -> id = $tokenId;
					  $newToken -> name = $strname;
					  $strname = "";
					  $tokenId++; //Increment token ID counter.
					  $tokenArray[] = $newToken;
					  //print_r($tokenArray); //Debugging statement.
					}
					else if ($token == "<literal>"){
					  $newLitToken = new LiteralToken();
					  $newLitToken -> id = $tokenId;
					  $newLitToken -> value = $strname;
					  $strname = "";
					  $tokenId++;
					  $tokenArray[] = $newLitToken;
					}
					$token="";
				}
				$length++;

				if($isWordDone||$lexingError!=""){
					break;
				}
			}  // end for loop, char by char iteration
			if($isLineDone||$lexingError!=""){
				//$tokenStream.="<endl>\n";
				break;
			}
      
		} //end foreach word
		if($lexingError!=""){
			break;
		}
		//$tokenStream.="\n";
		$lineNumber++;
		if ($multiendl == false){
		  $tokenStream.="<endl>\n";
		  $multiendl = true;
		}
	} //end foreach line
	if($lexingError!=""){
		echo $lexingError;
	}
	else
		echo $tokenStream;
		//file_put_contents("token.txt", $tokenStream);
		//print_r($tokenArray); //Debugging statement.
		
		//Recursive-Descent parser starts here.
		
?>
