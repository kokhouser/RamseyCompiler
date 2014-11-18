<?php

$arr=array("a","b","c","d");
unset($arr[2]);
foreach($arr as $key=>$val){
	echo("entry ".$key." is ".$val."\n");
}
//echo($tab->set_symbol("x", "bark"));
//echo($tab->get_symbol("a")."\n");


//if(array_key_exists("x", $arr[0])){   
	//echo("inside x\n");

?>