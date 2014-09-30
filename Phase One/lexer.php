<?php

	/* Plan of attack:
		0. Make sure it's a .ram file
		1. Strip comments.
		2. Create input stream, make sure endlines are intact.

		ISSUES:
		- You HAVE to have a space before the "#" in a comment that's on the same line as a line of code.
	*/

	function multiexplode ($delimiters, $string){
		$ready = str_replace($delimiters, $delimiters, $string);
		$launch = explode($delimiters[0], $ready);
		return $launch;
	}

	$tokens = array("ident", "in_type", "boo_type", "operator", "reserved", "left_paren", "right_paren");
	$filename = $argv[1];
	$input = file_get_contents($filename);
	$lines = explode("\n", $input);
	$no_comments = array();
	$count = 0;
	$tokenStream="";
	foreach($lines as $line){
		$pretoken = explode(" ",$line);
		foreach ($pretoken as $word){
			$no_space = trim($word);
			$count = strlen($no_space);
			$start=0;
			$length=1;
			$token="";
			$val="";
			$isWorddone=false;
			$isLineDone=false;
			for($i=0;$i<$count;$i++){
				$str=substr($no_space,$start,$length);
				switch ($str){
				case "#":
					$isWordDone=true;
					$isLineDone=true;
					break;	
				default:
					$matchTo = '/^[a-zA-Z]\w*/';
					if(preg_match($matchTo, $str)!=1){
						echo "We got to regX!\n";
						$start += $length;
						$length = 0;	
					}
					else{
						$token= "<ident>";
						$val = $str;
					}

				}
			$length++;
			if($token!=""){
				$tokenStream.=$token.$str;
			}

			if($isWordDone){
				$token="";
				break;
			}

		}
		if($isLineDone){
			$tokenStream.="\n";
			break;
		}
		}
		$tokenStream.="\n";

	}
	file_put_contents("token.txt", $tokenStream)
?>