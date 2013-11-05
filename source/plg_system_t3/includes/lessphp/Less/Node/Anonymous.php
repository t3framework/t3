<?php


class Less_Tree_Anonymous{
	public $value;
	public $quote;
	//public $type = 'Anonymous';

	public function __construct($value){
		$this->value = is_string($value) ? $value : $value->value;
	}

	public function toCss(){
		return $this->value;
	}

	public function compile($env){
		return $this;
	}

	function compare($x){
		if( !Less_Parser::is_method( $x, 'toCSS' ) ){
			return -1;
		}

		$left = $this->toCSS();
		$right = $x->toCSS();

		if( $left === $right ){
			return 0;
		}

		return $left < $right ? -1 : 1;
	}
}
