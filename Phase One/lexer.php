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
	foreach($lines as $line){
		$pretoken = explode(" ",$line);
		foreach ($pretoken as $word){
			$no_space = trim($word);
			if (substr($no_space,0,1) == '#'){
				break;
			}
			echo "$no_space \n";
		}
	}

?>