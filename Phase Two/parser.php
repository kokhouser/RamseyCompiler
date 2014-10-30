<?php

	require("recursiveDescent.php");

	$filename = "token.txt";
	$input = file_get_contents($filename);
	$tokens = explode("\n", $input);//Puts tokens into array, one token per array element.

	$sapling = new Parser($tokens); //because, at the end of it, we get a parse tree >.> lol
	$sapling->parse();

	//Note that there are n+1 array slots because of the extra endline.

?>