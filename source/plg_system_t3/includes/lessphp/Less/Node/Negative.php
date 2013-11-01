<?php


class Less_Tree_Negative{

	//public $type = 'Negative';
	public $value;

	function __construct($node){
		$this->value = $node;
	}

	/*
	function accept($visitor) {
		$visitor->visit($this->value);
	}
	*/

	function toCSS($env){
		return '-'.$this->value->toCSS($env);
	}

	function compile($env) {
		if( $env->isMathOn() ){
			$ret = new Less_Tree_Operation('*', array( new Less_Tree_Dimension(-1), $this->value ) );
			return $ret->compile($env);
		}
		return new Less_Tree_Negative( $this->value->compile($env) );
	}
}