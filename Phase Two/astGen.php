<?php 

	class node{ 
		
		//properties
		private $token;
		private $value; // if there is a value with token, i.e. the string of the ident
		private $children = array(); //array of indeces for children 
		private $index; //current index of array so that only the end is added onto
		
		//methods
		public function __construct(){
			$this->index=0;
		}
		public function addChild($input){ //input is the index from the tree object where the child is
			$this->children[$this->index]=$input;
			$this->index++;
		}
		public function get_children(){ //returns the whole array, to be iterated over later
			return $this->children;
		}
	}

	class ast{
		private $nodes; //array of nodes, root is always at 0
		private $index; //current end for adding to
		
	}

?>