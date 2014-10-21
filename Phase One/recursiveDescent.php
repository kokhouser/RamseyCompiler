<?php

	class Parser{
		private $tokens;
		private $index;
		private $lookahead;
		private $lineNum

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
			if($this->lookahead)=="<fun>"){
				$this->toplvlstmt();

				//match endl
				$this->lineNum+=1;	//this may be the only affect of matching endl
				
				$this->toplvlstmts();
			}
			else if(is_null($this->lookahead)){ 	//do we want to append end of input token to end of stream to be used instead of null?
				return NULL;
			}
			else{
				//throw error, invalid token in lookahead
			}
			
		}

		private function toplvlstmt(){
			if($this->lookahead=="<fun>"){
				//match <fun>
				$this->pushLookahead;
				
				$this->ident();
				
				//match <l_paren>
				$this->pushLookahead;

				$this->params();

				//match <r_paren>
				$this->pushLookahead;

				//match as 
				$this->pushLookahead;

				$this->type();

				//match endl
				$this->lineNum+=1;
				$this->pushLookahead;

				$this->stmts();

				//match endfun
				$this->pushLookahead;
			}
			else{
				return "error: expected token <fun> on line ".$lineNum."\n";
			}
		}

	}

?>