<?php

	class Parser{
		private $tokens;
		private $index;
		private $lookahead;
		private $lineNum;

		public function __construct($arr){
			$this->tokens = $arr;
			$this->index = 0;
			$this->lineNum = 1;
			$this->lookahead = $tokens[$index];
		}

		private function pushLookahead(){
			$this->index+=1;
			$this->lookahead=$tokens[$index];
		}

		public function parse(){
			$this->program();
		}

		private function program(){
			if($this->lookahead=="<fun>"){
				$this->toplvlstmts();
			}
		}

		private function toplvlstmts(){
			if(($this->lookahead)=="<fun>"){
				$this->toplvlstmt();

				//match endl
				$this->lineNum+=1;	//this may be the only affect of matching endl
				
				$this->toplvlstmts();
			}
			else if(is_null($this->lookahead)){ 	//do we want to append end of input token to end of stream to be used instead of null? (I think we should!)
				return NULL;
			}
			else{
				//throw error, invalid token in lookahead
			}
			
		}

		private function toplvlstmt(){
			if($this->lookahead=="<fun>"){
				//match <fun>
				$this->pushLookahead();
				$this->ident();
				//match <l_paren>
				$this->pushLookahead();
				$this->params();
				//match <r_paren>
				$this->pushLookahead();
				//match as
				$this->pushLookahead();
				$this->type();
				//match endl
				$this->lineNum+=1;
				$this->pushLookahead();
				$this->stmts();
				//match endfun
				$this->pushLookahead();
			}
			else{
				return "error: expected token <fun> on line ".$lineNum."\n";
			}
		}
		
		private function params(){
		    if($this->lookahead=="<in_type>"||$this->lookahead=="<boo_type>"
		    	||$this->lookahead=="<big_type>"||$this->lookahead=="<small_type>" ){
		    	$this->param();
		        $this->paramlist();
            }
		}
        
        private function paramlist(){
	        if($this->lookahead== "<comma>"){
		        //match <comma>
		        $this->pushLookahead();
		        $this->params();
            }
		}
        
        private function param(){
		    if($this->lookahead=="<in_type>"||$this->lookahead=="<boo_type>"
		    	||$this->lookahead=="<big_type>"||$this->lookahead=="<small_type>" ){
		        $this->type();
		        $this->ident();
		    }
		    else{
		        return "error: expected token <type> on line ".$lineNum."\n";
		    }
		}
        
        private function stmts(){
		    if($this->lookahead=="<if>"||$this->lookahead=="<while>"||$this->lookahead=="<ident>"||$this->lookahead=="<in_type>"
		    ||$this->lookahead=="<boo_type>"||$this->lookahead=="<big_type>"||$this->lookahead=="<small_type>"|$this->lookahead=="<literal>"
		    ||$this->lookahead=="<true>"||$this->lookahead=="<false>"||$this->lookahead=="<not_op>"||$this->lookahead=="<toss>" ){
		        $this->stmt();
		        $this->morestmts();
		    }
		    else{
		        return "error: expected token <if>, <while>, <ident>, <type>, <literal>, or <not_op> on line ".$lineNum."\n";
		    }
		}
		
		private function morestmts(){
		    if ($this->lookahead=="<endl>"){
		        //match <endl>
		        $this->pushLookahead();
		        $this->stmts();
		    }
		}

        private function stmt(){
            if ($this->lookahead=="<if>" || $this->lookahead=="<while>"){
                $this->conditional();
            }
            else if ($this->lookahead=="<toss>"){
                //match <toss>
                $this->pushLookahead();
                $this->expression();
            }
            else if ($this->lookahead=="<ident>"||$this->lookahead=="<in_type>"||$this->lookahead=="<boo_type>"||$this->lookahead=="<big_type>"||$this->lookahead=="<small_type>"){
                $this->varhandler();
            }
            else{
		        return "error: expected token <if>, <while>, <ident>, <type>, <literal>, or <not_op> on line ".$lineNum."\n";
            }

        }
        
        private function varhandler(){
            if ($this->lookahead=="<ident>"){
                //match <ident>
                $this->pushLookahead();
            }
        }
        
        
	}

?>
