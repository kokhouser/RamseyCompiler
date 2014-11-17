<?php

	require("recursiveDescent.php");
	require("astGen.php");
	include ('lexer.php');
	
	$filename = "token.txt";
	$input = file_get_contents($filename);
	$tokens = explode("\n", $input);//Puts tokens into array, one token per array element.

	$astGenerator = new ast();
	$astGenerator -> set_tokenArray($tokenArray);

	$sapling = new Parser($tokens,$astGenerator); //because, at the end of it, we get a parse tree >.> lol
	$sapling->parse();

	//print_r($tokenArray);
	//print_r ($astGenerator -> get_tokenArray());//debugging statement
	//Note that there are n+1 array slots because of the extra endline.

?>