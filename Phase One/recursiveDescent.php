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
			$this->lookahead = $this->tokens[$this->index];
		}

		private function pushLookahead(){
			$this->index+=1;
			$this->lookahead=$this->tokens[$this->index];
		}

		public function parse(){
			$this->program();
		}

		private function program(){
			if($this->lookahead=="<fun>"){
				$this->toplvlstmts();
			}
			else{
				echo "Expected token <fun> on line ".$this->lineNum. "\n";
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
				//match <ident>
				$this->pushLookahead();
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
				echo "error: expected token <fun> on line ".$this->lineNum."\n";
			}
		}
		
		private function params(){
		    if($this->lookahead=="<in_type>"||$this->lookahead=="<boo_type>"
		    	||$this->lookahead=="<big_type>"||$this->lookahead=="<small_type>" ){
		    	$this->param();
		        $this->paramlist();
            }
            // Is the following "if" block correct? Is this how we handle going to lambda?
            else if ($this->lookahead=="<r_paren>"){
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <type> or <r_paren> on line ".$this->lineNum. "\n";
            }
		}
        
        private function paramlist(){
	        if($this->lookahead== "<comma>"){
		        //match <comma>
		        $this->pushLookahead();
		        $this->params();
            }
            // Is the following "if" block correct? Is this how we handle going to lambda?
            else if($this->lookahead=="<r_paren>"){
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <comma> or <r_paren> on line ".$this->lineNum. "\n";
            }
		}
        
        private function param(){
		    if($this->lookahead=="<in_type>"||$this->lookahead=="<boo_type>"
		    	||$this->lookahead=="<big_type>"||$this->lookahead=="<small_type>" ){
		        $this->type();
		        //match <ident>
			$this->pushLookahead();
		    }
		    else{
		        echo "error: expected token <type> on line ".$this->lineNum."\n";
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
		        echo "error: expected token <if>, <while>, <ident>, <type>, <literal>, or <not_op> on line ".$this->lineNum."\n";
		    }
		}
		
		private function morestmts(){
		    if ($this->lookahead=="<endl>"){
		        //match <endl>
		        $this->pushLookahead();
		        $this->lineNum+=1;
		        $this->stmts();
		    }
		    // Is the following "if" block correct? Is this how we handle going to lambda?
		    else if ($this->lookahead=="<elf>"||$this->lookahead=="<endwhile>"||$this->lookahead=="<endfun>"||$this->lookahead=="<else>"
		    ||$this->lookahead=="<endif>"){
		        $this->pushLookahead();
		    }
		    else{
                echo "error:expected token <endl>, <elf>, <endwhile>, <endfun>, <else> or <endif> on line ".$this->lineNum. "\n";
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
            else if ($this->lookahead=="<ident>"||$this->lookahead=="<in_type>"||$this->lookahead=="<boo_type>"||$this->lookahead=="<big_type>"
            ||$this->lookahead=="<small_type>"/*||$this->lookahead=="<literal>"*/){
                $this->varhandler();
            }
            // Is the following "if" block correct? Is this how we handle going to lambda?
            else if ($this->lookahead=="<endl>"||$this->lookahead=="<elf>"||$this->lookahead=="<endwhile>"||$this->lookahead=="<endfun>"
            ||$this->lookahead=="<else>"||$this->lookahead=="<endif>"){
                //Is the following correct??
                if ($this->lookahead=="<endl>"){
                    $this->lineNum+=1;
                }
                $this->pushLookahead();
            }
            else{
		        echo "error: expected token <if>, <while>, <ident>, <type>, <literal>, or <not_op> on line ".$this->lineNum."\n";
            }

        }
        
        private function varhandler(){
            if ($this->lookahead=="<ident>"){
                //match <ident>
                $this->pushLookahead();
                $this->assignment();
            }
            else if ($this->lookahead=="<in_type>"||$this->lookahead=="<boo_type>"||$this->lookahead=="<big_type>"||$this->lookahead=="<small_type>"){
                $this->declaration();
                $this->catassign();
            }/* might need this
            else if ($this->lookahead=="<literal>"){
                $this -> topexpression();
            }*/
            else{
                echo "error: expected token <identifier> or <type> on line ".$this->lineNum. "\n";
            }
        }
        
        private function declaration(){
            if ($this->lookahead=="<in_type>"||$this->lookahead=="<boo_type>"||$this->lookahead=="<big_type>"||$this->lookahead=="<small_type>"){
                $this->type();
                //match <ident>
                $this->pushLookahead();
            }
            else{
                echo "error: expected token <type> on line ".$this->lineNum. "\n";
            }
        }
        
        private function assignment(){
            if ($this->lookahead=="<assign_op>"){
                //match <assign_op>
                $this->pushLookahead();
                $this->topexpression();
            }
            else{
                echo "error: expected token <assign_op> on line ".$this->lineNum. "\n";
            }
        }
        
        private function catassign(){
            if ($this->lookahead=="<assign_op>"){
                $this->assignment();
            }
            //Is the following if block correct?
            else if ($this->lookahead=="<endl>"||$this->lookahead=="<elf>"||$this->lookahead=="<endwhile>"||$this->lookahead=="<endfun>"||$this->lookahead=="<else>"
            ||$this->lookahead=="<endif>"){
                //Is the following correct??
                if ($this->lookahead=="<endl>"){
                    $this->lineNum+=1;
                }
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <assign_op>, <endl>, <elf>, <endwhile>, <endfun>, <else> or <endif> on line ".$this->lineNum. "\n";
            }
        }
        
        private function conditional(){
            if ($this->lookahead=="<if>"){
                //match <if>
                $this->pushLookahead();
                //match <lparen>
                $this->pushLookahead();
                $this->topexpression();
                //match <r_paren>
                $this->pushLookahead();
                //match <endl>
                $this->pushLookahead();
                $this->lineNum+=1;
                $this->stmts();
                $this->elfears();
                //match <endif>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<while>"){
                //match <while>
                $this->pushLookahead();
                //match <lparen>
                $this->pushLookahead();
                $this->expression();
                //match <r_paren>
                $this->pushLookahead();
                //match <endl>
                $this->pushLookahead();
                $this->lineNum+=1;
                $this->stmts();
                //match <endwhile>
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <if> or <while> on line ".$this->lineNum. "\n";
            }
        }
        
        private function elfears(){
            if ($this->lookahead=="<elf>"){
                //match <elf>
                $this->pushLookahead();
                //match <lparen>
                $this->pushLookahead();
                $this->expression();
                //match <r_paren>
                $this->pushLookahead();
                $this->stmts();
                $this->elfears();
                //match <r_paren>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<else>"){
                //match <else>
                $this->pushLookahead();
                $this->stmts();
            }
            //Is the following "if" block correct? Is this how we're handling lamdas?
            else if ($this->lookahead=="<endif>"){
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <elf> or <else> on line ".$this->lineNum. "\n";
            }
        }
        
        private function topexpression(){
            if ($this->lookahead=="<ident>"||$this->lookahead=="<literal>"||$this->lookahead=="<true>"||$this->lookahead=="<false>"||$this->lookahead=="<not_op>"){
                echo "Here \n";
                $this->expression();
                $this->expressionlist();
            }
            else if ($this->lookahead=="<lparen>"){
                //match <lparen>
                $this->pushLookahead();
                $this->expression();
                $this->expressionlist();
                //match <r_paren>
                $this->pushLookahead();
                $this->expressionlist();
            }
            else{
                echo "error:expected token <ident>, <literal>, <true>, <false>, <not_op> or <lparen> on line ".$this->lineNum. "\n";
            }
        }
        
        private function expressionlist(){
            if ($this->lookahead=="<add_op>"||$this->lookahead=="<sub_op>"||$this->lookahead=="<mult_op>"||$this->lookahead=="div_op"
                ||$this->lookahead=="<mod_op>"||$this->lookahead=="<less_op>"||$this->lookahead=="<greater_op>"||$this->lookahead=="lesseq_op"
                ||$this->lookahead=="<greateq_op>"||$this->lookahead=="<noteq_op>"||$this->lookahead=="<and_op>"||$this->lookahead=="<or_op>"
                ||$this->lookahead=="<compare_op>"){
                $this->sop();
                $this->topexpression();
            }
            //Is the following "if" block correct? Is this how we're handling lamdas?
            else if ($this->lookahead=="<endl>"||$this->lookahead=="<elf>"||$this->lookahead=="<endwhile>"||$this->lookahead=="<endfun>"
                ||$this->lookahead=="<else>"||$this->lookahead=="<endif>"||$this->lookahead=="<r_paren>"){
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <operator>, <endl>, <elf>, <endwhile>, <endfun>, <else>, <endif> or <r_paren> on line ".$this->lineNum. "\n";
		echo $this->lookahead."\n";
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
                //match <lparen>
                $this->pushLookahead();
                $this->topexpression();
                //match <r_paren>
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <ident>, <literal>, <true> or <false> on line ".$this->lineNum. "\n";
            }
        }
        
        private function funcall(){
            if ($this->lookahead=="<lparen>"){
                //match <lparen>
                $this->pushLookahead();
                $this->params();
                //match <r_paren>
                $this->pushLookahead();
            }
            //Is the following "if" block correct? Is this how we're handling lamdas?
            else if ($this->lookahead=="<add_op>"||$this->lookahead=="<sub_op>"||$this->lookahead=="<mult_op>"||$this->lookahead=="div_op"
                ||$this->lookahead=="<mod_op>"||$this->lookahead=="<less_op>"||$this->lookahead=="<greater_op>"||$this->lookahead=="lesseq_op"
                ||$this->lookahead=="<greateq_op>"||$this->lookahead=="<noteq_op>"||$this->lookahead=="<and_op>"||$this->lookahead=="<or_op>"
                ||$this->lookahead=="<compare_op>"||$this->lookahead=="<endl>"||$this->lookahead=="<elf>"||$this->lookahead=="<endwhile>"
                ||$this->lookahead=="<endfun>"||$this->lookahead=="<else>"||$this->lookahead=="<endif>"){
                $this->pushLookahead();
            }
            else{
                echo "error:expected token <lparen>, <operator>, <endl>, <elf>, <endwhile>, <endfun>, <else> or <endif> on line ".$this->lineNum. "\n";
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
            else if ($this->lookahead=="<mult_op>"||$this->lookahead=="div_op"||$this->lookahead=="<mod_op>"||$this->lookahead=="<less_op>"
            ||$this->lookahead=="<greater_op>"||$this->lookahead=="lesseq_op"||$this->lookahead=="<greateq_op>"||$this->lookahead=="<noteq_op>"
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
            if ($this->lookahead=="<in_type>"){
                //match <in_type>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<boo_type>"){
                //match <boo_type>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<big_type>"){
                //match <big_type>
                $this->pushLookahead();
            }
            else if ($this->lookahead=="<small_type>"){
                //match <small_type>
                $this->pushLookahead();
            }
        }
        //Done!
	}

?>
