<?php


class Less_Tree_Alpha{
	private $value;
	//public $type = 'Alpha';

	public function __construct($val){
		$this->value = $val;
	}

	/*
	function accept( $visitor ){
		$visitor->visit( $this->value );
	}
	*/

	public function toCss($env){
		return "alpha(opacity=" . (is_string($this->value) ? $this->value : $this->value->toCSS()) . ")";
	}

	public function compile($env){
		if ( ! is_string($this->value)) {
			$this->value = $this->value->compile($env);
		}
		return $this;
	}
}