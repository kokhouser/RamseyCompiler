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
		private $code;
		
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