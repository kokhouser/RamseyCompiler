<?php 
	require("symtab.php");
	class node{ 
		
		//properties
		public $token;
		public $value; // if there is a value with token, i.e. the string of the ident
		public $name;
		public $type;
		private $children = array(); //array of indeces for children 
		private $index; //current index of array so that only the end is added onto

		//methods
		public function __construct($input){
			$this->token=$input;
			$this->index=0;
		}
		public function addChild($input){ //input is the index from the tree object where the child is
			$this->children[$this->index]=$input;
			$this->index++;
		}
		public function get_children(){ //returns the whole array, to be iterated over later
			return $this->children;
		}
		public function set_child($currentIndex, $newChildIndex){
			$this->children[$currentIndex] = $newChildIndex;
		}

		public function output_code(&$codeStream){
			//$codeStream.=$this->token.="\n";
			/*
			if ($this->token=="<fun>"){
				$codeStream.="GLOBAL placeholder(replace with function name) \nplaceholder: \n 	push ebp \n 	mov ebp,esp\n 	push ebx\n";
			}
			*/
			if ($this->token=="<ident>"){
				if ($this->type=="<fun>"){
					$codeStream.="GLOBAL ".$this->name." \n".$this->name.": \n 	push ebp \n 	mov ebp,esp\n 	push ebx\n";
				}
			}
			else if ($this->token=="<toss>"){
				//TO DO - Move return value to eax
				$codeStream.="	pop ebx \n 	pop ebp \n 	ret\n";
			}
			else{
				//$codeStream.=$this->token.="\n";
			}
			//echo $this->token."\n";
		}
	}

	class ast{
		private $nodes; //array of nodes, root is always at 0
		private $index; //current end for adding to
		private $tokenArray;
		public $symTable;
		private $tokenIndex;	//for use with the recursive traverse function
		private $declaration;	//for recursive call to build symtable`, need to track assign or declaration
		private $type;			//for knowing what type a ident is
		private $trackToss;		//for semantic analysis toss stmt
		private $lineNum;		//error reporting
		private $code; 			//for code generation 
		

		public function getCode(){
			return $this->code;
		}

		public function get_tokenArray(){
			return $this->tokenArray;
		}

		public function set_tokenArray($input){
			$this->tokenArray = $input;
		}
		
		public function __construct(){
			$this->index=0;
			$this->tokenIndex=0;
			$this->lineNum=1;
			$this->declaration=false;
			$this->symTable = new symtab();
		}

		public function addNode($inNode){ 
			$this->nodes[$this->index]=$inNode;
			$this->index++;
			//print_r($inNode);  //Debugging statement
			//print_r($this->nodes); //Debugging statement
		}

		public function get_index(){
			return $this->index;
		}

		public function set_index($input){
			$this->index = $input;
		}

		public function get_nodes(){
			return $this->nodes;
		}

		public function prune($inNode){ //Function to prune off excess nodes to turn our parse tree into a true AST
			$currentNode = $inNode;
			$children = $this->nodes[$inNode]->get_children(); //getting the array of children to inspect THEIR children
			for($i = 0; $i<count($children); $i++){
				$grandchildren = $this->nodes[$children[$i]]->get_children();
				if (count($grandchildren) == 1){
					//echo $currentNode ." has child ". $children[$i] ." that has a single child!\n"; //Debugging
					$newChild = $grandchildren[0];
					while (count($grandchildren) == 1){
						$newChild = $grandchildren[0];
						$grandchildren = $this->nodes[$newChild]->get_children();
					}
					$this->nodes[$inNode]->set_child($i, $newChild);
					//echo "Setting " .$inNode ." 's child to be ". $newChild ."\n"; //Debugging
				}
			}
			for($i = 0; $i<count($children); $i++){
				$this->prune($children[$i]);
			}
		}

		public function buildSymTab($inNode){
			$children = $this->nodes[$inNode]->get_children();
			$myToken = $this->nodes[$inNode]->token;
			if($myToken=="<ident>"){
				$targetIndex=$this->symTable->set_symbol($this->tokenArray[$this->tokenIndex]->name, $this->type, $this->declaration);	
				$tempArr=$this->symTable->search_symbol($targetIndex);
				$this->nodes[$inNode]->name=$tempArr["name"];
				$this->nodes[$inNode]->type=$tempArr["type"];
				$this->declaration=false;
				if($this->type=="<fun>"){
					$this->symTable->openScope(); //special case, we had to wait until after the func ident was made to open scope
				}
				$this->tokenIndex++;	
			}
			else if($myToken=="<literal>"){
				$this->nodes[$inNode]->value=$this->tokenArray[$this->tokenIndex]->value;
				$this->declaration=false;		
				$this->tokenIndex++;	
			}else if($myToken=="<fun>" || $myToken=="<in_type>" ||$myToken=="<small_type>" ||$myToken=="<big_type>" || $myToken=="<boo_type>"){
				$this->declaration = true;
				$this->type = $myToken;
			}
			else{
				$this->declaration=false;
			}
			if($myToken=="<while>" || $myToken=="<if>"){
				$this->symTable->openScope();
			}
			else if($myToken=="<elf>" ||$myToken=="<else>"){
				$this->symTable->closeScope();
				$this->symTable->openScope();
			}
			else if($myToken=="<endfun>" || $myToken=="<endwhile>" || $myToken=="<endif>"){
				$this->symTable->closeScope();
			}
			for ($i = 0; $i<count($children); $i++){
				$this->buildSymTab($children[$i]);
			}

		}

		public function semanticAnalysis($inNode){
			$children = $this->nodes[$inNode]->get_children();
			$typeArr=array();
			for ($i = 0; $i<count($children); $i++){
				$typeArr[$i]=$this->semanticAnalysis($children[$i]);
			}
			$myType=NULL;
			$currentType=NULL;
			$myToken=$this->nodes[$inNode]->token;
			foreach($typeArr as $rtype){	//make sure children are all same type and ignore null
				if($rtype!=NULL){
					if($rtype=="<toss>"||$rtype=="<fun>"||$rtype=="<and_op>"||$rtype=="<or_op>"){
						$rtype=NULL;
					}
					if($currentType==NULL){
						$currentType=$rtype;
					}
					else{
						if($currentType=="<num_type>"){
							if($rtype !="<big_type>"&&$rtype!="<small_type>"&&$rtype!="<in_type>"){
								exit("semantics FAILED: type mismatch on line ".$this->lineNum."\n");
							}
						}
						else if($rtype=="<num_type>"){
							if($currentType !="<big_type>"&&$currentType!="<small_type>"&&$currentType!="<in_type>"){
								exit("semantics FAILED: type mismatch on line ".$this->lineNum."\n");
							}
						}
						else if($currentType!=$rtype){
							exit("semantics FAILED: type mismatch on line ".$this->lineNum."\n");
						}
					}				
				}
			}
			$myType=$currentType;
			//echo("mytoken = ".$myToken." and mytype =".$myType."\n");
			switch($myToken){	//define special retrn rules for cases where parent needs to see other than children
				case "<endl>":
					$this->lineNum++;
					break;
				case "<ident>":
					$myType=$this->nodes[$inNode]->type;
					//echo("i saw an ident of type ".$myType."\n");
					break;
				case "<literal>":
					$myType="<num_type>";
					break;
				case "<big_type>":
					$myType="<big_type>";
					break;
				case "<small_type>":
					$myType="<small_type>";
					break;
				case "<true>":
					$myType="<boo_type>";
					break;
				case "<false>":
					$myType="<boo_type>";
					break;
				case "<and_op>":
					$myType="<and_op>";
					break;
				case "<or_op>":
					$myType="<or_op>";
					break;
				case "<param>":
					$myType=NULL;
					break;
				case "<varhandler>":
					$myType=NULL;
					break;
				case "<stmts>":
					$myType=NULL;
					break;
				case "<toss>":
					$myType="<toss>";
					break;
				case "<in_type>":
					$myType="<in_type>";
					break;
				case "<conditional>":
					if($typeArr[2]!="<boo_type>"){
						exit("semantics FAILED: expected a <boo_type> in a conditional, but saw ".$typeArr[2]." on line ".$this->lineNum."\n");
					}
					$myType=NULL;
					break;
				case "<elfears>":
					if(isset($typeArr[2]) && $typeArr[2]!="<boo_type>"){
						exit("semantics FAILED: expected a <boo_type> in a conditional, but saw ".$typeArr[2]." on line ".$this->lineNum."\n");
					}
					$myType=NULL;
					break;
				case "<not_op>":
					if($typeArr[2]!="boo_type"){
						exit("semantics FAILED: expected a <boo_type> in a conditional, but saw ".$typeArr[2]." on line ".$this->lineNum."\n");
					}
					break;
				case "<stmt>":
					if(isset($typeArr[0])&&$typeArr[0]=="<toss>"){
						$this->trackToss=$typeArr[1];
					}
					$myType=NULL;
					break;
				case "<toplvlstmt>":
					if(isset($typeArr[6])&&$typeArr[6]!=$this->trackToss){
						exit("semantics FAILED: expected to return a ".$typeArr[6]." but saw ".$this->trackToss." on line ".$this->lineNum."\n");
					}
					$myType=NULL;
					$this->trackToss=NULL;
					break;
				case "<addsubexpression>":
					if(isset($typeArr[1])&&$myType=="<boo_type>"){
						exit("semantics FAILED: expected in type for operator on line ".$this->lineNum."\n");
					}
					break;
				case "<relationalopexpression>":
					if(isset($typeArr[1])){
							$myType="<boo_type>";
					}
					break;
				case "<relationalopoptionexpression>":
					if(isset($typeArr[0])){
							if($typeArr[1]!="<boo_type>"){
								exit("semantics FAILED: expected boo type for operator on line ".$this->lineNum."\n");
							}
					}
					break;
			}
			return $myType;
		}

		public function traverse($inNode,&$codeStream){
			$children = $this->nodes[$inNode]->get_children();
			for ($i = 0; $i<count($children); $i++){
				$this->traverse($children[$i],$codeStream);
			}
			$this->nodes[$inNode]->output_code($codeStream);
			$this->code=$codeStream;
		}
	}

?>