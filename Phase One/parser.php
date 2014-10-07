<?php

  $filename = "token.txt";
  $input = file_get_contents($filename);
  $tokens = explode("\n", $input);
  print_r($tokens); //Puts tokens into array, one token per array element.
  //Note that there are n+1 array slots because of the extra endline.

?>