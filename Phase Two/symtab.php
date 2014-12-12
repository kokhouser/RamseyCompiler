<?php
/*
public function openScope()						//opens a new, local scope
public function closeScope()					// signifies end of life for declarations in current scope, therefore symbols are no longer needed and are deleted
public function check_scope($inName)			// checks current scope for an existing var name. returns -1 if non exist, index of var if exists
public function set_symbol($inName, $inData)	//a symbol has been declared or reassigned
public function get_symbol($inName)				// a symbol has been referenced. this looks for valid declaration and returns false if no valid declaration exists

*/
class Symtab{
	private $symbolTable;
	private $currentScope;
	private $currentIndex;	//where the tail of the symbol table is.

	public function __construct(){
		$this->currentScope=0;
		$this->symbolTable = array();
		$this->currentIndex=0;
	}
	public function openScope(){
		$this->currentScope++;
	}

	public function closeScope(){	// signifies end of life for declarations in current scope, therefore symbols are no longer needed and are deleted
		$location = $this->currentIndex - 1;
		while($this->symbolTable[$location]["depth"]==$this->currentScope){
			$this->symbolTable[$location]["live"]=false;
			$location--;
		}
		$this->currentScope--;
	}

	public function checkScope($inName, $decl){ 	// checks current scope for an existing var name. returns -1 if non exist, index of most local version of var if exists
		$trackScope =0;
		$trackLoc = -1;
		foreach($this->symbolTable as $key => $row){
			if($row["name"]==$inName && $row["live"]){					//found a live match of name
				if($row["depth"]==$this->currentScope){ // check if match is in current scope
					if($decl){
						exit("Variable ".$inName." is declared incorrectly\n");
					}
					else{
						return $key;
					}
				}
				//if we match to a live version but are out of scope, we would want to set the most local scope
				else if($trackScope<=$row["depth"]){
					$trackScope = $row["depth"];
					$trackLoc = $key;
				}
			}
		}
		if($trackLoc== -1 && $decl==false){
			exit("Variable ".$inName." has not been declared in an accessible scope\n");
		}
		return $trackLoc;

	}

	public function set_symbol($inName, $inData, $decl){		//a symbol has been declared if decl=true, or reassigned if decl=false
		$location = $this->checkScope($inName, $decl);
		if($decl){			//call is a declaration, make new entry
			$location=$this->currentIndex;	//to add onto the tail of the "list"
			$this->currentIndex++;
			$this->symbolTable[$location] = array(); //only make new array if adding to the array
			// each row is an array (map thanks to php), holding keys: name, depth, live, and data, where data is type and any info associated (not variable values, however), and live is if 
			$this->symbolTable[$location]["name"]=$inName; 		
			$this->symbolTable[$location]["depth"]=$this->currentScope;
			$this->symbolTable[$location]["live"]=true;
			$this->symbolTable[$location]["data"]=$inData;
		}
		//else, we set nothing because it is a reference

		
	}

	public function search_symbol($inIndex){ // for use to get data from a symbol
		$trackData="";
		$trackScope=0;
		$resultArr=array();
		$resultArr["name"]=$this->symbolTable[$inIndex]["name"];
		$resultArr["type"]=$this->symbolTable[$inIndex]["data"];
		return $resultArr;	
	}

	public function printSelf(){
		foreach($this->symbolTable as $key => $row){
			echo("variable ".$key." is named '".$row["name"]."', at depth ".$row["depth"].", and holds the type ".$row["data"].".\n");
		}
	}
}

?>