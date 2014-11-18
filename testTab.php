<?php

require("Phase Two/symtab.php");
$tab=new Symtab();
$tab->set_symbol("x", "boo", true);
$tab->set_symbol("y", "boot", true);
$tab->set_symbol("z", "boots", true);
echo($tab->get_symbol("x")."\n");
echo($tab->get_symbol("y")."\n");
echo($tab->get_symbol("z")."\n");
$tab->openScope();
$tab->set_symbol("y", "book" ,true);
$tab->set_symbol("z", "booke",true);
$tab->set_symbol("a", "bookend",true);
echo($tab->get_symbol("x")."\n");
echo($tab->get_symbol("y")."\n");
echo($tab->get_symbol("z")."\n");
echo($tab->get_symbol("a")."\n");
$tab->closeScope();
echo($tab->get_symbol("x")."\n");
echo($tab->get_symbol("y")."\n");
echo($tab->get_symbol("z")."\n");
$tab->openScope();
$tab->set_symbol("y", "in" ,true);
$tab->set_symbol("z", "win",true);
echo($tab->get_symbol("x")."\n");
echo($tab->get_symbol("y")."\n");
echo($tab->get_symbol("z")."\n");
$tab->openScope();
$tab->set_symbol("x", "Holla" , false);
$tab->set_symbol("y", "booz" ,true);
$tab->set_symbol("z", "booze",true);
$tab->set_symbol("a", "brooze",true);
echo($tab->get_symbol("x")."\n");
echo($tab->get_symbol("y")."\n");
echo($tab->get_symbol("z")."\n");
echo($tab->get_symbol("a")."\n");
$tab->closeScope();
$tab->closeScope();

echo($tab->get_symbol("x")."\n");
//echo($tab->set_symbol("x", "bark"));
//echo($tab->get_symbol("a")."\n");


//if(array_key_exists("x", $arr[0])){   
	//echo("inside x\n");

?>