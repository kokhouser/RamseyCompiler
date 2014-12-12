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

	$astGenerator -> prune(0);
	$codeStream="	SECTION .text \n";
	$astGenerator->buildSymTab(0);
	$astGenerator->symTable->printSelf();

	$astGenerator -> traverse(0,$codeStream);
	$finalCode = $astGenerator->getCode();
	file_put_contents("code.asm", $finalCode);
	//print_r ($astGenerator->get_nodes());
	//print_r($tokenArray);
	//print_r ($astGenerator -> get_tokenArray());//debugging statement
	//Note that there are n+1 array slots because of the extra endline.

?>