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
		}
		//else, location is the index where we want to change the data
		$this->symbolTable[$location]["data"]=$inData;
		
	}
	public function get_symbol($inName){ // a symbol has been referenced. this looks for valid declaration and returns false if no valid declaration exists
		$trackData="";
		$trackScope=0;
		foreach($this->symbolTable as $row){
			if($row["name"]==$inName){ //found a declaration
				if($row['depth']>=$trackScope){ //this row's scope is more local than current data, less or equal in case at zero depth
					$trackData = $row["data"];
					$trackScope = $row["depth"];
				}
				// else would check a scope not as local as tracked, and if track is greater, we are storing 'lowest' data
			}
		}
		//need to be sure there was at least one match
		if($trackData==""){
			exit("Variable '".$inName."' was referenced without having an active declaration\n");
		}
		else{
			return $trackData;	//we wanted type/info, so that's what is returned
		}
	}

	public function printSelf(){
		foreach($this->symbolTable as $key => $row){
			echo("variable ".$key." is named '".$row["name"]."', at depth ".$row["depth"].", and holds the type ".$row["data"].".\n");
	}
}

?>