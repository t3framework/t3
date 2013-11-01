<?php


class Less_Tree_Attribute{

	//public $type = "Attribute";
	public $key;
	public $op;
	public $value;

	function __construct($key, $op, $value){
		$this->key = $key;
		$this->op = $op;
		$this->value = $value;
	}

	/*
	function accept($visitor){
		$visitor->visit($this->value);
	}
	*/

	function compile($env){
		return new Less_Tree_Attribute( (method_exists($this->key,'compile') ? $this->key->compile($env) : $this->key),
			$this->op, ($this->value && method_exists($this->value,'compile')) ? $this->value->compile($env) : $this->value);
	}

	function toCSS($env){
		$value = $this->key;

		if( $this->op ){
			$value .= $this->op;
			$value .= ( method_exists($this->value,'toCSS') ? $this->value->toCSS($env) : $this->value);
		}

		return '[' . $value . ']';
	}
}