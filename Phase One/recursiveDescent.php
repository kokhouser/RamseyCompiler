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
				exit("Error on line ".$this->lineNum.", expected token ".$matchTo."\n");
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
		}

		private function program(){
			$this->matchNT(array("<fun>")); //there is only one option in array, so no need to check the location of match in the array
			$this->toplvlstmts();

		}

		private function toplvlstmts(){
			$case=$this->matchNT(array("<fun>", "<$>"));
			if($case==0){
			$this->toplvlstmt();
			$this->match("<endl>");
			$this->toplvlstmts();
			}
			else if($case==1){
				//were done!
			}
			
		}

		private function toplvlstmt(){
			$case=$this->matchNT(array("<fun>"));
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
				$this->topexpression;
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
            $case=$this->matchNT(array("<add_op>","<sub_op>","<mult_op>","<div_op>","<mod_op>","<less_op>","<greater_op>","<lesseq_op>","<greateq_op>","<noteq_op>","<and_op>","<or_op>","<compare_op>","<endl>","<elf>","<endwhile>","<endfun>","<else>","<endif>","<r_paren>"));
            if($case>=0&&$case<=12){
            	$this->sop();
            	$this->topexpression();
            }
            else if($case>=13&&$case<=19){
            	//empty, do nothing
            }
        }
        
        private function expression(){
            if ($this->lookahead=="<ident>"){
                //match <ident>
                $this->pushLookahead();
                $this->funcall();
            }
            else if ($this->lookahead=="<literal>"||$this->lookahead=="<true>"||$this->lookahead=="<false>"){
                $this->literals();
            }
            else if ($this->lookahead=="<not_op>"){
                //match <not_op>
                $this->pushLookahead();
                //match <l_paren>
                $this->pushLookahead();
                $this->topexpression();
                //match <r_paren>
                $this->pushLookahead();
            }
            else{
                //echo $this->lookahead; //(I don't think we need lookahead here)
                echo "error:expected token <ident>, <literal>, <true> or <false> on line ".$this->lineNum. "\n";
            }
        }
        
        private function funcall(){
            if ($this->lookahead=="<l_paren>"){
                //match <l_paren>
                $this->pushLookahead();
                $this->params();
                //match <r_paren>
                $this->pushLookahead();
            }
            //Is the following "if" block correct? Is this how we're handling lamdas?
            else if ($this->lookahead=="<add_op>"||$this->lookahead=="<sub_op>"||$this->lookahead=="<mult_op>"||$this->lookahead=="<div_op>"
                ||$this->lookahead=="<mod_op>"||$this->lookahead=="<less_op>"||$this->lookahead=="<greater_op>"||$this->lookahead=="<lesseq_op>"
                ||$this->lookahead=="<greateq_op>"||$this->lookahead=="<noteq_op>"||$this->lookahead=="<and_op>"||$this->lookahead=="<or_op>"
                ||$this->lookahead=="<compare_op>"||$this->lookahead=="<endl>"||$this->lookahead=="<elf>"||$this->lookahead=="<endwhile>"
                ||$this->lookahead=="<endfun>"||$this->lookahead=="<else>"||$this->lookahead=="<endif>"){
                //$this->pushLookahead();
            }
            else{
                //echo $this->lookahead;//(I don't think we need lookahead here)
                echo "error:expected token <l_paren>, <operator>, <endl>, <elf>, <endwhile>, <endfun>, <else> or <endif> on line ".$this->lineNum. "\n";
            }
        }
        
        private function sop(){
            if ($this->lookahead=="<add_op>"){
                //match <add_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<sub_op>"){
                //match <sub_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<mult_op>"||$this->lookahead=="<div_op>"||$this->lookahead=="<mod_op>"||$this->lookahead=="<less_op>"
            ||$this->lookahead=="<greater_op>"||$this->lookahead=="<lesseq_op>"||$this->lookahead=="<greateq_op>"||$this->lookahead=="<noteq_op>"
            ||$this->lookahead=="<and_op>"||$this->lookahead=="<or_op>"||$this->lookahead=="<compare_op>"){
                $this->fop();
            }
            else {
                echo "error:expected token <operator> on line ".$this->lineNum. "\n";
            }
        }
        
        private function fop(){
            if ($this->lookahead=="<mult_op>"){
                //match <mult_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<div_op>"){
                //match <div_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<mod_op>"){
                //match <mod_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<less_op>"){
                //match <less_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<greater_op>"){
                //match <greater_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<lesseq_op>"){
                //match <lesseq_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<greateq_op>"){
                //match <greateq_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<noteq_op>"){
                //match <noteq_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<and_op>"){
                //match <and_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<or_op>"){
                //match <or_op>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<compare_op>"){
                //match <compare_op>
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <operator> on line ".$this->lineNum. "\n";
            }
        }
        
        private function literals(){
            if ($this->lookahead=="<literal>"){
                //match <literal>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<true>"){
                //match <true>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<false>"){
                //match <false>
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <literal>, <true> or <false> on line ".$this->lineNum. "\n";
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
