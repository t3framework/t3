<?php

class Less_Tree_Paren {

	//public $type = 'Paren';
	public $value;

	public function __construct($value) {
		$this->value = $value;
	}

	/*
	function accept($visitor){
		$visitor->visit($this->value);
	}
	*/

	public function toCSS($env) {
		return '(' . trim($this->value->toCSS($env)) . ')';
	}

	public function compile($env) {
		return new Less_Tree_Paren($this->value->compile($env));
	}

}
