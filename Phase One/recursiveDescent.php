<?php

	//require("aptGen.php")

	class Parser{
		private $tokens;
		private $index;
		private $lookahead;
		private $lineNum;
		//private $aptGen; //parse tree generator

		public function __construct($arr){
			$this->tokens = $arr;
			$this->index = 0;
			$this->lineNum = 1;
			$this->lookahead = $this->tokens[$this->index];
		}

		private function pushLookahead(){
			$this->index+=1;
			$this->lookahead=$this->tokens[$this->index];
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

		private function match($matchTo){
			if($matchTo!=$this->lookahead){
				exit("Error on line ".$this->lineNum.": could not match token ".$this->lookahead.", expected ".$matchTo."\n");
			}
			else{
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
			echo("parsing completed successfully! Good job at writing Ramsey!\n");
		}

		private function program(){
			$this->matchNT(array("<fun>")); //there is only one option in array, so no need to check the location of match in the array
			$this->toplvlstmts();

		}

		private function toplvlstmts(){
			$case=$this->matchNT(array("<fun>","<endl>", "<$>"));
			if($case>=0&&$case<=1){
			$this->toplvlstmt();
			$this->match("<endl>");
			$this->toplvlstmts();
			}
			else if($case==1){
				//were done!
			}
			
		}

		private function toplvlstmt(){
			$case=$this->matchNT(array("<fun>", "<endl>"));
			if($case==0){
				$this->match("<fun>");
				$this->match("<ident>");
				$this->match("<l_paren>");
				$this->params();
				$this->match("<r_paren>");
				$this->match("<as>");
				$this->type();
				$this->match("<endl>");
				$this->stmts();
				$this->match("<endfun>");
			}
			else if($case==1){
				//do nothing
			}
		}
		
		private function params(){
		    $case=$this->matchNT(array("<in_type>","<boo_type>","<big_type>","<small_type>","<ident>","<r_paren>"));
		    if($case>=0&&$case<=4){
		    	$this->param();
		    	$this->paramlist();
		    }
		    else if($case==5){
		    	//empty, let it return
		    }
		}
        
        private function paramlist(){
	        $case=$this->matchNT(array("<comma>","<r_paren>"));
	        if($case==0){
	        	$this->match("<comma>");
	        	$this->params();
	        }
	        else if($case==1){
	        	//don't match, let it return
	        }
		}
        
        private function param(){
		   $case=$this->matchNT(array("<in_type>","<boo_type>","<big_type>","<small_type>","<ident>","<literal>","<true>","<false>","<not_op>","<r_paren>"));
		    if($case>=0&&$case<=3){
		    	$this->type();
		    	$this->match("<ident>");
		    }
		    else if($case>=4&&$case<=9){
		    	$this->topexpression();	
		    }
		}
        
        private function stmts(){
        	$case=$this->matchNT(array("<if>", "<while>", "<ident>", "<in_type>", "<boo_type>", "<big_type>", "<small_type>" ,"<literal>","<true>", "<false>", "<not_op>", "<l_paren>", "<toss>", "<endl>", "<endfun>", "<endwhile>", "<endif>", "<elf>", "<else>"));
			if($case>=0&&$case<=13){
			$this->stmt();
			$this->match("<endl>");
			$this->stmts();		
			}
 			else if($case>=14&&$case<=18){
 				//goes to empty str, do nothing
 			}
		}

        private function stmt(){
			$case=$this->matchNT(array("<if>","<while>","<toss>","<ident>","<in_type>","<big_type>","<small_type>","<boo_type>","<endl>"));
			if($case>=0&&$case<=1){
				$this->conditional();
			}
			else if($case==2){
				$this->match("<toss>");
				$this->topexpression();
			}
			else if($case>=3&&$case<=7){
				$this->varhandler();
			}
			else if($case==8){
				//nothing, command goes to empty
			}
        }
        
        private function varhandler(){
        	$case=$this->matchNT(array("<ident>","<in_type>","<big_type>","<small_type>","<boo_type>"));
        	if($case==0){
        		$this->match("<ident>");
        		$this->assignment();
        	}
        	else if($case>=1&&$case<=4){
        		$this->declaration();
        		$this->catassign();
        	}
        }
        
        private function declaration(){
            $case=$this->matchNT(array("<in_type>","<big_type>","<small_type>","<boo_type>"));
            if($case>=0&&$case<=3){
            	$this->match("<ident>");
        	}
        }
        
        private function assignment(){
        	$case=$this->matchNT(array("<assign_op>"));
        	if($case==0){
        		$this->match("<assign_op>");
        		$this->topexpression();
        	}
        }
        
        private function catassign(){
        	$case=$this->matchNT(array("<assign_op>","<endl>"));
        	if($case==0){
        		$this->assignment();
        	}
        	else if($case==1){
        		//do nothing, goes to empty
        	}
        }

        private function conditional(){
        	$case=$this->matchNT(array("<if>","<while>"));
        	if($case==0){
        		$this->match("<if>");
        		$this->match("<l_paren>");
        		$this->topexpression();
        		$this->match("<r_paren>");
        		$this->match("<endl>");
        		$this->stmts();
        		$this->elfears();
        		$this->match("<endif>");
        	}
        	else if($case==1){
        		$this->match("<while>");
        		$this->match("<l_paren>");
        		$this->topexpression();
        		$this->match("<r_paren>");
        		$this->match("<endl>");
        		$this->stmts();
        		$this->match("<endwhile>");
        	}
        }
        
        private function elfears(){
        	$case=$this->matchNT(array("<elf>","<else>","<endif>"));
        	if($case==0){
        		$this->match("<elf>");
        		$this->match("<l_paren>");
        		$this->topexpression();
        		$this->match("<r_paren>");
        		$this->stmts();
        		$this->elfears();
        	}
        	else if($case==1){
        		$this->match("<else>");
        		$this->stmts();
        	}
        	else if($case==2){
        		//empty, do nothing
        	}

        }
        
        private function topexpression(){
        	$case=$this->matchNT(array("<ident>","<literal>","<true>","<false>","<not_op>", "<l_paren>"));
        	if($case>=0&&$case<=4){
        		$this->expression();
        		$this->expressionlist();
        	}
        	else if($case==5){
        		$this->match("<l_paren>");
        		$this->expression();
        		$this->expressionlist();
        		$this->match("<r_paren>");
        		$this->expressionlist();
        	}
        }
        
        private function expressionlist(){
            $case=$this->matchNT(array("<add_op>","<sub_op>","<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>","<endl>","<r_paren>"));
            if($case>=0&&$case<=12){
            	$this->sop();
            	$this->topexpression();
            }
            else if($case>=13&&$case<=14){
            	//empty, do nothing
            }
        }
        
        private function expression(){
        	$case=$this->matchNT(array("<ident>","<literal>","<true>","<false>","<not_op>"));
        	if($case==0){
        		$this->match("<ident>");
        		$this->funcall();
        	}
        	else if($case>=1&&$case<=3){
        		$this->literals();
        	}
        	else if($case==4){
        		$this->match("<not_op>");        	
        		$this->match("<l_paren>");        	
        		$this->topexpression();        	
        		$this->match("<r_paren>");        	
        	}
        }
        
        private function funcall(){
        	$case=$this->matchNT(array("<l_paren>","<add_op>","<sub_op>","<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>","<endl>"));
        	if($case==0){
        		$this->match("<l_paren>");
        		$this->params();
        		$this->match("<r_paren>");
        	}
        	else if($case>=1&&$case<=14){
        		//do nothing
        	}
        }
        
        private function sop(){
			$case=$this->matchNT(array("<add_op>","<sub_op>","<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>"));
			if($case==0){
				$this->match("<add_op>");
			}
			else if($case==1){
				$this->match("<sub_op>");
			}
			else if($case>=2&&$case<=12){
				$this->fop();
			}
        }
        
        private function fop(){
        	$case=$this->matchNT(array("<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>"));
        	if($case==0){
        		$this->match("<mult_op>");
        	}
        	if($case==1){
        		$this->match("<div_op>");
        	}
        	if($case==2){
        		$this->match("<mod_op>");
        	}
        	if($case==3){
        		$this->match("<less_op>");
        	}
        	if($case==4){
        		$this->match("<greater_op>");
        	}
        	if($case==5){
        		$this->match("<lesseq_op>");
        	}
        	if($case==6){
        		$this->match("<greateq_op>");
        	}
        	if($case==7){
        		$this->match("<noteq_op>");
        	}
        	if($case==8){
        		$this->match("<and_op>");
        	}
        	if($case==9){
        		$this->match("<or_op>");
        	}
        	if($case==10){
        		$this->match("<compare_op>");
        	}
        }
        
        private function literals(){
        	$case=$this->matchNT(array("<literal>","<true>","<false>"));
        	if($case==0){
        		$this->match("<literal>");
        	}
        	if($case==1){
        		$this->match("<true>");
        	}
        	if($case==2){
        		$this->match("<false>");
        	}
        }
        
        private function type(){
            $case=$this->matchNT(array("<in_type>","<boo_type>","<big_type>","<small_type>"));
            if($case==0){
            	$this->match("<in_type>");
            }
            else if($case==1){
            	$this->match("<boo_type>");
            }
           else if($case==2){
            	$this->match("<big_type>");
            }
            else if($case==3){
            	$this->match("<small_type>");
            }
        }
        //Done!
	}

?>
