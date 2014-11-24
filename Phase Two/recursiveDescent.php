<?php

/*
    Parser for the simple, but non-trivial language Ramsey. 
    Hand-crafted out of love and pain (mostly) by Hao Zhe Kok (Houser)
*/

	//require("astGen.php");

	class Parser{
		private $tokens;
		private $index;
		private $lookahead;
		private $lineNum;
		private $astGenerator; //abstract syntax tree generator

		public function __construct($arr, $astIn){
			$this->tokens = $arr;
			$this->index = 0;
			$this->lineNum = 1;
			$this->lookahead = $this->tokens[$this->index];
            $this->astGenerator = $astIn;
            //print_r($this->astGenerator->get_tokenArray()); //Debugging statement
		}

		private function pushLookahead(){
			$this->index+=1;
            if ($this->index < count($this->tokens))
			     $this->lookahead=$this->tokens[$this->index];
		}

        private function addNodesToAst($token, $parentIndex, $currentIndex){ //function to add nodes into the ast. Pass in the token name and parent index and it'll do the rest.
            $newNode = new Node ($token); //Create new node
            $this->astGenerator->addNode($newNode); //Adds node to tree
            $nodes = $this->astGenerator->get_nodes(); 
            $nodes[$parentIndex]->addChild($currentIndex); //Link parent and new node
            //print_r($nodes); //Debugging statement
        }

		private function matchNT($array){ 	//match function forces only one possibility, so for multiple rules and not a terminal, use this one , stands for matchNonTerminal
			$hasMatch=false;
			$location;
			foreach($array as $key => $val){
				if($this->lookahead==$val){
					$hasMatch=true;
					$location=$key;
					//echo("matchNT looking for ".$this->lookahead."\n");
				}
			}
			if($hasMatch){
				return $location;		//can use location to determine which function is called next
			}
			else{
				$error="";
				foreach($array as $val){
					$error.=$val."\n";
				}
				exit("Error on line ".$this->lineNum.": could not match token ".$this->lookahead.", expected one of the following:\n".$error);
			}
		}

		private function match($matchTo, $parentIndex){
            $currentIndex = $this->astGenerator->get_index();
			if($matchTo!=$this->lookahead){
				exit("Error on line ".$this->lineNum.": could not match token ".$this->lookahead.", expected ".$matchTo."\n");
			}
			else{
                $this->addNodesToAst($matchTo, $parentIndex, $currentIndex);
				//echo("I matched a ".$matchTo." on line ".$this->lineNum."\n");
				if($matchTo=="<endl>"){
					$this->lineNum+=1;
				}
				//$this->aptGen.formTree($inToken) //calls aptGen to make a node in the parse tree with behavior based on the input token
				$this->pushLookahead();
			}
		}

		public function parse(){
			$this->program();
			echo("Parsing completed successfully! Good job at writing Ramsey!\n");
            //print_r($this->astGenerator->get_nodes()); //To see the entire tree (Not recommended as it'll blow up your terminal)
            //print_r($this->astGenerator->get_nodes()[0]); //Change the index to see individual nodes
		}

		private function program(){
            $currentIndex = $this->astGenerator->get_index();
            $newNode = new Node("<program>");
            $this->astGenerator->addNode($newNode);
			$case=$this->matchNT(array("<fun>", "<endl>")); //there is only one option in array, so no need to check the location of match in the array
            if($case==0){
                //$this->addNodesToAst("<toplvlstmts>", $parentIndex, $currentIndex);
                $this->toplvlstmts($currentIndex);
            }
            else if ($case==1){
                //$this->addNodesToAst("<endl>", $parentIndex, $currentIndex);
                $this->match("<endl>", $currentIndex);
                //$this->addNodesToAst("<program>", $parentIndex, $currentIndex);
                $this->program($currentIndex);
            }
		}

		private function toplvlstmts($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<toplvlstmts>", $parentIndex, $currentIndex);
			$case=$this->matchNT(array("<fun>","<endl>", "<$>"));
			if($case>=0&&$case<=1){
            //$this->addNodesToAst("<toplvlstmt>", $parentIndex, $currentIndex);
			$this->toplvlstmt($currentIndex);
            //$this->addNodesToAst("<endl>", $parentIndex, $currentIndex);
			$this->match("<endl>", $currentIndex);
            //$this->addNodesToAst("<toplvlstmt>", $parentIndex, $currentIndex);
			$this->toplvlstmts($currentIndex);
			}
			else if($case==2){
                $this->match("<$>", $currentIndex);
				//were done!
			}
			
		}

		private function toplvlstmt($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<toplvlstmt>", $parentIndex, $currentIndex);
			$case=$this->matchNT(array("<fun>", "<endl>"));
			if($case==0){
                //$this->addNodesToAst("<fun>", $parentIndex, $currentIndex);
				$this->match("<fun>", $currentIndex);
                //$this->addNodesToAst("<ident>", $parentIndex, $currentIndex);
				$this->match("<ident>", $currentIndex);
                //$this->addNodesToAst("<l_paren>", $parentIndex, $currentIndex);
				$this->match("<l_paren>", $currentIndex);
                //$this->addNodesToAst("<params>", $parentIndex, $currentIndex);
				$this->params($currentIndex);
                //$this->addNodesToAst("<r_paren>", $parentIndex, $currentIndex);
				$this->match("<r_paren>", $currentIndex);
                //$this->addNodesToAst("<as>", $parentIndex, $currentIndex);
				$this->match("<as>", $currentIndex);
                //$this->addNodesToAst("<type>", $parentIndex, $currentIndex);
				$this->type($currentIndex);
                //$this->addNodesToAst("<endl>", $parentIndex, $currentIndex);
				$this->match("<endl>", $currentIndex);
                //$this->addNodesToAst("<stmts>", $parentIndex, $currentIndex);
				$this->stmts($currentIndex);
                //$this->addNodesToAst("<endfun>", $parentIndex, $currentIndex);
				$this->match("<endfun>", $currentIndex);
			}
			else if($case==1){
				//do nothing
			}
		}
		
		private function params($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<params>", $parentIndex, $currentIndex);
		    $case=$this->matchNT(array("<in_type>","<boo_type>","<big_type>","<small_type>","<ident>","<r_paren>"));
		    if($case>=0&&$case<=4){
                //$this->addNodesToAst("<param>", $parentIndex, $currentIndex);
		    	$this->param($currentIndex);
                //$this->addNodesToAst("<paramlist>", $parentIndex, $currentIndex);
		    	$this->paramlist($currentIndex);
		    }
		    else if($case==5){
		    	//empty, let it return
		    }
		}
        
        private function paramlist($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<paramlist>", $parentIndex, $currentIndex);
	        $case=$this->matchNT(array("<comma>","<r_paren>"));
	        if($case==0){
                //$this->addNodesToAst("<comma>", $parentIndex, $currentIndex);
	        	$this->match("<comma>", $currentIndex);
                //$this->addNodesToAst("<params>", $parentIndex, $currentIndex);
	        	$this->params($currentIndex);
	        }
	        else if($case==1){
	        	//don't match, let it return
	        }
		}
        
        private function param($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<param>", $parentIndex, $currentIndex);
		    $case=$this->matchNT(array("<in_type>","<boo_type>","<big_type>","<small_type>","<ident>","<literal>","<true>","<false>","<not_op>","<r_paren>"));
		    if($case>=0&&$case<=3){
                //$this->addNodesToAst("<type>", $parentIndex, $currentIndex);
		    	$this->type($currentIndex);
                //$this->addNodesToAst("<ident>", $parentIndex, $currentIndex);
		    	$this->match("<ident>", $currentIndex);
		    }
		    else if($case>=4&&$case<=9){
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
		    	$this->topexpression($currentIndex);
		    }
		}
        
        private function stmts($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<stmts>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<if>", "<while>", "<ident>", "<in_type>", "<boo_type>", "<big_type>", "<small_type>" ,"<literal>","<true>", "<false>", "<not_op>", "<l_paren>", "<toss>", "<endl>", "<endfun>", "<endwhile>", "<endif>", "<elf>", "<else>"));
			if($case>=0&&$case<=13){
            //$this->addNodesToAst("<stmt>", $parentIndex, $currentIndex);
			$this->stmt($currentIndex);
            //$this->addNodesToAst("<endl>", $parentIndex, $currentIndex);
			$this->match("<endl>", $currentIndex);
            //$this->addNodesToAst("<stmts>", $parentIndex, $currentIndex);
			$this->stmts($currentIndex);
			}
 			else if($case>=14&&$case<=18){
 				//goes to empty str, do nothing
 			}
		}

        private function stmt($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<stmt>", $parentIndex, $currentIndex);
			$case=$this->matchNT(array("<if>","<while>","<toss>","<ident>","<in_type>","<big_type>","<small_type>","<boo_type>","<endl>"));
			if($case>=0&&$case<=1){
                //$this->addNodesToAst("<conditional>", $parentIndex, $currentIndex);
				$this->conditional($currentIndex);
			}
			else if($case==2){
                //$this->addNodesToAst("<toss>", $parentIndex, $currentIndex);
				$this->match("<toss>", $currentIndex);
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
				$this->topexpression($currentIndex);
			}
			else if($case>=3&&$case<=7){
                //$this->addNodesToAst("<varhandler>", $parentIndex, $currentIndex);
				$this->varhandler($currentIndex);
			}
			else if($case==8){
				//nothing, command goes to empty
			}
        }
        
        private function varhandler($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<varhandler>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<ident>","<in_type>","<big_type>","<small_type>","<boo_type>"));
        	if($case==0){
                //$this->addNodesToAst("<ident>", $parentIndex, $currentIndex);
        		$this->match("<ident>", $currentIndex);
                //$this->addNodesToAst("<assignment>", $parentIndex, $currentIndex);
        		$this->assignment($currentIndex);
        	}
        	else if($case>=1&&$case<=4){
                //$this->addNodesToAst("<declaration>", $parentIndex, $currentIndex);
        		$this->declaration($currentIndex);
                //$this->addNodesToAst("<catassign>", $parentIndex, $currentIndex);
        		$this->catassign($currentIndex);
        	}
        }
        
        private function declaration($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<declaration>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<in_type>","<big_type>","<small_type>","<boo_type>"));
            if($case>=0&&$case<=3){
                //$this->addNodesToAst("<type>", $parentIndex, $currentIndex);
                $this->type($currentIndex);
                //$this->addNodesToAst("<ident>", $parentIndex, $currentIndex);
            	$this->match("<ident>", $currentIndex);
        	}
        }
        
        private function assignment($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<assignment>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<assign_op>"));
        	if($case==0){
                //$this->addNodesToAst("<assign_op>", $parentIndex, $currentIndex);
        		$this->match("<assign_op>", $currentIndex);
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
        		$this->topexpression($currentIndex);
        	}
        }
        
        private function catassign($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<catassign>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<assign_op>","<endl>"));
        	if($case==0){
                //$this->addNodesToAst("<assignment>", $parentIndex, $currentIndex);
        		$this->assignment($currentIndex);
        	}
        	else if($case==1){
        		//do nothing, goes to empty
        	}
        }

        private function conditional($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<conditional>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<if>","<while>"));
        	if($case==0){
                //$this->addNodesToAst("<if>", $parentIndex, $currentIndex);
        		$this->match("<if>", $currentIndex);
                //$this->addNodesToAst("<l_paren>", $parentIndex, $currentIndex);
        		$this->match("<l_paren>", $currentIndex);
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
        		$this->topexpression($currentIndex);
                //$this->addNodesToAst("<r_paren>", $parentIndex, $currentIndex);
        		$this->match("<r_paren>", $currentIndex);
                //$this->addNodesToAst("<endl>", $parentIndex, $currentIndex);
        		$this->match("<endl>", $currentIndex);
                //$this->addNodesToAst("<stmts>", $parentIndex, $currentIndex);
        		$this->stmts($currentIndex);
                //$this->addNodesToAst("<elfears>", $parentIndex, $currentIndex);
        		$this->elfears($currentIndex);
                //$this->addNodesToAst("<endif>", $parentIndex, $currentIndex);
        		$this->match("<endif>", $currentIndex);
        	}
        	else if($case==1){
                //$this->addNodesToAst("<while>", $parentIndex, $currentIndex);
        		$this->match("<while>", $currentIndex);
                //$this->addNodesToAst("<l_paren>", $parentIndex, $currentIndex);
        		$this->match("<l_paren>", $currentIndex);
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
        		$this->topexpression($currentIndex);
                //$this->addNodesToAst("<r_paren>", $parentIndex, $currentIndex);
        		$this->match("<r_paren>", $currentIndex);
                //$this->addNodesToAst("<endl>", $parentIndex, $currentIndex);
        		$this->match("<endl>", $currentIndex);
                //$this->addNodesToAst("<stmts>", $parentIndex, $currentIndex);
        		$this->stmts($currentIndex);
                //$this->addNodesToAst("<endwhile>", $parentIndex, $currentIndex);
        		$this->match("<endwhile>", $currentIndex);
        	}
        }
        
        private function elfears($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<elfears>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<elf>","<else>","<endif>"));
        	if($case==0){
                //$this->addNodesToAst("<elf>", $parentIndex, $currentIndex);
        		$this->match("<elf>", $currentIndex);
                //$this->addNodesToAst("<l_paren>", $parentIndex, $currentIndex);
        		$this->match("<l_paren>", $currentIndex);
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
        		$this->topexpression($currentIndex);
                //$this->addNodesToAst("<r_paren>", $parentIndex, $currentIndex);
        		$this->match("<r_paren>", $currentIndex);
                //$this->addNodesToAst("<stmts>", $parentIndex, $currentIndex);
        		$this->stmts($currentIndex);
                //$this->addNodesToAst("<elfears>", $parentIndex, $currentIndex);
        		$this->elfears($currentIndex);
        	}
        	else if($case==1){
                //$this->addNodesToAst("<else>", $parentIndex, $currentIndex);
        		$this->match("<else>", $currentIndex);
                //$this->addNodesToAst("<stmts>", $parentIndex, $currentIndex);
        		$this->stmts($currentIndex);
        	}
        	else if($case==2){
        		//empty, do nothing
        	}

        }
        
        /* OLD
        private function topexpression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<ident>","<literal>","<true>","<false>","<not_op>", "<l_paren>"));
        	if($case>=0&&$case<=4){
                //$this->addNodesToAst("<expression>", $parentIndex, $currentIndex);
        		$this->expression($currentIndex);
                //$this->addNodesToAst("<expressionlist>", $parentIndex, $currentIndex);
        		$this->expressionlist($currentIndex);
        	}
        	else if($case==5){
                //$this->addNodesToAst("<l_paren>", $parentIndex, $currentIndex);
        		$this->match("<l_paren>", $currentIndex);
                //$this->addNodesToAst("<expression>", $parentIndex, $currentIndex);
        		$this->expression($currentIndex);
                //$this->addNodesToAst("<expressionlist>", $parentIndex, $currentIndex);
        		$this->expressionlist($currentIndex);
                //$this->addNodesToAst("<r_paren>", $parentIndex, $currentIndex);
        		$this->match("<r_paren>", $currentIndex);
                //$this->addNodesToAst("<expressionlist>", $parentIndex, $currentIndex);
        		$this->expressionlist($currentIndex);
        	}
        }
        */
        private function topexpression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<ident>","<literal>","<true>","<false>","<not_op>", "<l_paren>"));
            if($case>=0&&$case<=5){
                //$this->addNodesToAst("<expression>", $parentIndex, $currentIndex);
                $this->relationalopexpression($currentIndex);
            }
        }
        
        /* OLD
        private function expressionlist($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<expressionlist>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<add_op>","<sub_op>","<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>","<endl>","<r_paren>"));
            if($case>=0&&$case<=12){
                //$this->addNodesToAst("<sop>", $parentIndex, $currentIndex);
            	$this->sop($currentIndex);
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
            	$this->topexpression($currentIndex);
            }
            else if($case>=13&&$case<=14){
            	//empty, do nothing
            }
        }*/
        
        /* OLD
        private function expression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<expression>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<ident>","<literal>","<true>","<false>","<not_op>"));
        	if($case==0){
                //$this->addNodesToAst("<ident>", $parentIndex, $currentIndex);
        		$this->match("<ident>", $currentIndex);
                //$this->addNodesToAst("<funcall>", $parentIndex, $currentIndex);
        		$this->funcall($currentIndex);
        	}
        	else if($case>=1&&$case<=3){
                //$this->addNodesToAst("<literals>", $parentIndex, $currentIndex);
        		$this->literals($currentIndex);
        	}
        	else if($case==4){
                //$this->addNodesToAst("<not_op>", $parentIndex, $currentIndex);
        		$this->match("<not_op>", $currentIndex);
                //$this->addNodesToAst("<l_paren>", $parentIndex, $currentIndex);
        		$this->match("<l_paren>", $currentIndex);
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
        		$this->topexpression($currentIndex);
                //$this->addNodesToAst("<r_paren>", $parentIndex, $currentIndex);
        		$this->match("<r_paren>", $currentIndex);
        	}
        }
        */

        private function relationalopexpression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<relationalopexpression>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<ident>","<literal>","<true>","<false>","<not_op>", "<l_paren>"));
            if($case>=0&&$case<=5){
                //$this->addNodesToAst("<expression>", $parentIndex, $currentIndex);
                $this->addsubexpression($currentIndex);
                $this->relationalopoptionexpression($currentIndex);
            }
        }

        private function relationalopoptionexpression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<relationalopoptionexpression>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<compare_op>", "<noteq_op>", "<and_op>", "<or_op>", 
                                        "<endl>", "<elf>", "<endwhile>", "<endfun>", "<else>", "<endif>", "<r_paren>"));
            if ($case>=0&&$case<=7){
                $this->top($currentIndex);
                $this->addsubexpression($currentIndex);
                $this->relationalopoptionexpression($currentIndex);
            }
            else if ($case>=8&&$case<=14){
                //empty, do nothing
            }
        }

        private function addsubexpression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<addsubexpression>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<ident>","<literal>","<true>","<false>","<not_op>", "<l_paren>"));
            if($case>=0&&$case<=5){
                //$this->addNodesToAst("<expression>", $parentIndex, $currentIndex);
                $this->muldivexpression($currentIndex);
                $this->addsuboptionexpression($currentIndex);
            }
        }

        private function addsuboptionexpression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<addsuboptionexpression>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<add_op>","<sub_op>",
                                        "<endl>", "<elf>", "<endwhile>", "<endfun>", "<else>", "<endif>", "<r_paren>", "<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<compare_op>", "<noteq_op>", "<and_op>", "<or_op>"));
            if ($case>=0&&$case<=1){
                $this->sop($currentIndex);
                $this->muldivexpression($currentIndex);
                $this->addsuboptionexpression($currentIndex);
            } 
            else if ($case>=2&&$case<=16){
                //empty, do nothing
            }
        }

        private function muldivexpression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<muldivexpression>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<not_op>", "<ident>", "<literal>", "<true>", "<false>", "<l_paren>"));
            if ($case>=0&&$case<=5){
                $this->expression($currentIndex);
                $this->muldivoptionexpression($currentIndex);
            }
        }

        private function muldivoptionexpression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<addsuboptionexpression>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<mult_op>", "<div_op>", "<mod_op>",
                                        "<endl>", "<elf>", "<endwhile>", "<endfun>", "<else>", "<endif>", "<r_paren>", "<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<compare_op>", "<noteq_op>", "<and_op>", "<or_op>",
                                        "<add_op>","<sub_op>"));
            if ($case>=0&&$case<=2){
                $this->fop($currentIndex);
                $this->expression($currentIndex);
                $this->muldivoptionexpression($currentIndex);
            }
            else if ($case>=3&&$case<=19){
                //empty, do nothing
            }

        }

        private function expression($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<expression>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<ident>","<literal>","<true>","<false>","<not_op>", "<l_paren>"));
            if($case==0){
                //$this->addNodesToAst("<ident>", $parentIndex, $currentIndex);
                $this->match("<ident>", $currentIndex);
                //$this->addNodesToAst("<funcall>", $parentIndex, $currentIndex);
                $this->funcall($currentIndex);
            }
            else if($case>=1&&$case<=3){
                //$this->addNodesToAst("<literals>", $parentIndex, $currentIndex);
                $this->literals($currentIndex);
            }
            else if($case==4){
                //$this->addNodesToAst("<not_op>", $parentIndex, $currentIndex);
                $this->match("<not_op>", $currentIndex);
                //$this->addNodesToAst("<l_paren>", $parentIndex, $currentIndex);
                $this->match("<l_paren>", $currentIndex);
                //$this->addNodesToAst("<topexpression>", $parentIndex, $currentIndex);
                $this->topexpression($currentIndex);
                //$this->addNodesToAst("<r_paren>", $parentIndex, $currentIndex);
                $this->match("<r_paren>", $currentIndex);
            }
            else if ($case==5){
                $this->match("<l_paren>", $currentIndex);
                $this->topexpression($currentIndex);
                $this->match("<r_paren>", $currentIndex);
            }
        }
        
        private function funcall($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<funcall>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<l_paren>","<add_op>","<sub_op>","<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>","<endl>", "<r_paren>"));
        	if($case==0){
                //$this->addNodesToAst("<l_paren>", $parentIndex, $currentIndex);
        		$this->match("<l_paren>", $currentIndex);
                //$this->addNodesToAst("<params>", $parentIndex, $currentIndex);
        		$this->params($currentIndex);
                //$this->addNodesToAst("<r_paren>", $parentIndex, $currentIndex);
        		$this->match("<r_paren>", $currentIndex);
        	}
        	else if($case>=1&&$case<=15){
        		//do nothing
        	}
        }
        
        /* OLD
        private function sop($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<sop>", $parentIndex, $currentIndex);
			$case=$this->matchNT(array("<add_op>","<sub_op>","<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>"));
			if($case==0){
                //$this->addNodesToAst("<add_op>", $parentIndex, $currentIndex);
				$this->match("<add_op>", $currentIndex);
			}
			else if($case==1){
                //$this->addNodesToAst("<sub_op>", $parentIndex, $currentIndex);
				$this->match("<sub_op>", $currentIndex);
			}
			else if($case>=2&&$case<=12){
                //$this->addNodesToAst("<fop>", $parentIndex, $currentIndex);
				$this->fop($currentIndex);
			}
        }
        */
        private function sop($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<sop>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<add_op>","<sub_op>"));
            if($case==0){
                //$this->addNodesToAst("<add_op>", $parentIndex, $currentIndex);
                $this->match("<add_op>", $currentIndex);
            }
            else if($case==1){
                //$this->addNodesToAst("<sub_op>", $parentIndex, $currentIndex);
                $this->match("<sub_op>", $currentIndex);
            }
        }
        
        /* OLD
        private function fop($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<fop>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>"));
        	if($case==0){
                //$this->addNodesToAst("<mult_op>", $parentIndex, $currentIndex);
        		$this->match("<mult_op>", $currentIndex);
        	}
        	if($case==1){
                //$this->addNodesToAst("<div_op>", $parentIndex, $currentIndex);
        		$this->match("<div_op>", $currentIndex);
        	}
        	if($case==2){
                //$this->addNodesToAst("<mod_op>", $parentIndex, $currentIndex);
        		$this->match("<mod_op>", $currentIndex);
        	}
        	if($case==3){
                //$this->addNodesToAst("<less_op>", $parentIndex, $currentIndex);
        		$this->match("<less_op>", $currentIndex);
        	}
        	if($case==4){
                //$this->addNodesToAst("<greater_op>", $parentIndex, $currentIndex);
        		$this->match("<greater_op>", $currentIndex);
        	}
        	if($case==5){
                //$this->addNodesToAst("<lesseq_op>", $parentIndex, $currentIndex);
        		$this->match("<lesseq_op>", $currentIndex);
        	}
        	if($case==6){
                //$this->addNodesToAst("<greateq_op>", $parentIndex, $currentIndex);
        		$this->match("<greateq_op>", $currentIndex);
        	}
        	if($case==7){
                //$this->addNodesToAst("<noteq_op>", $parentIndex, $currentIndex);
        		$this->match("<noteq_op>", $currentIndex);
        	}
        	if($case==8){
                //$this->addNodesToAst("<and_op>", $parentIndex, $currentIndex);
        		$this->match("<and_op>", $currentIndex);
        	}
        	if($case==9){
                //$this->addNodesToAst("<or_op>", $parentIndex, $currentIndex);
        		$this->match("<or_op>", $currentIndex);
        	}
        	if($case==10){
                //$this->addNodesToAst("<compare_op>", $parentIndex, $currentIndex);
        		$this->match("<compare_op>", $currentIndex);
        	}
        }
        */ 

        private function fop($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<fop>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<mult_op>","<div_op>","<mod_op>"));
            if($case==0){
                //$this->addNodesToAst("<mult_op>", $parentIndex, $currentIndex);
                $this->match("<mult_op>", $currentIndex);
            }
            if($case==1){
                //$this->addNodesToAst("<div_op>", $parentIndex, $currentIndex);
                $this->match("<div_op>", $currentIndex);
            }
            if($case==2){
                //$this->addNodesToAst("<mod_op>", $parentIndex, $currentIndex);
                $this->match("<mod_op>", $currentIndex);
            }
        }

        private function top($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<top>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>"));
            if($case==0){
                //$this->addNodesToAst("<less_op>", $parentIndex, $currentIndex);
                $this->match("<less_op>", $currentIndex);
            }
            if($case==1){
                //$this->addNodesToAst("<greater_op>", $parentIndex, $currentIndex);
                $this->match("<greater_op>", $currentIndex);
            }
            if($case==2){
                //$this->addNodesToAst("<lesseq_op>", $parentIndex, $currentIndex);
                $this->match("<lesseq_op>", $currentIndex);
            }
            if($case==3){
                //$this->addNodesToAst("<greateq_op>", $parentIndex, $currentIndex);
                $this->match("<greateq_op>", $currentIndex);
            }
            if($case==4){
                //$this->addNodesToAst("<noteq_op>", $parentIndex, $currentIndex);
                $this->match("<noteq_op>", $currentIndex);
            }
            if($case==5){
                //$this->addNodesToAst("<and_op>", $parentIndex, $currentIndex);
                $this->match("<and_op>", $currentIndex);
            }
            if($case==6){
                //$this->addNodesToAst("<or_op>", $parentIndex, $currentIndex);
                $this->match("<or_op>", $currentIndex);
            }
            if($case==7){
                //$this->addNodesToAst("<compare_op>", $parentIndex, $currentIndex);
                $this->match("<compare_op>", $currentIndex);
            }
        }
        
        private function literals($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<literals>", $parentIndex, $currentIndex);
        	$case=$this->matchNT(array("<literal>","<true>","<false>"));
        	if($case==0){
                //$this->addNodesToAst("<literal>", $parentIndex, $currentIndex);
        		$this->match("<literal>", $currentIndex);
        	}
        	if($case==1){
                //$this->addNodesToAst("<true>", $parentIndex, $currentIndex);
        		$this->match("<true>", $currentIndex);
        	}
        	if($case==2){
                //$this->addNodesToAst("<false>", $parentIndex, $currentIndex);
        		$this->match("<false>", $currentIndex);
        	}
        }
        
        private function type($parentIndex){
            $currentIndex = $this->astGenerator->get_index();
            $this->addNodesToAst("<type>", $parentIndex, $currentIndex);
            $case=$this->matchNT(array("<in_type>","<boo_type>","<big_type>","<small_type>"));
            if($case==0){
                //$this->addNodesToAst("<in_type>", $parentIndex, $currentIndex);
            	$this->match("<in_type>", $currentIndex);
            }
            else if($case==1){
                //$this->addNodesToAst("<boo_type>", $parentIndex, $currentIndex);
            	$this->match("<boo_type>", $currentIndex);
            }
           else if($case==2){
                //$this->addNodesToAst("<big_type>", $parentIndex, $currentIndex);
            	$this->match("<big_type>", $currentIndex);
            }
            else if($case==3){
                //$this->addNodesToAst("<small_type>", $parentIndex, $currentIndex);
            	$this->match("<small_type>", $currentIndex);
            }
        }
        //Done!
	}

?>
