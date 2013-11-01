<?php


class Less_Tree_Assignment {

	//public $type = 'Assignment';
	private $key;
	private $value;

	function __construct($key, $val) {
		$this->key = $key;
		$this->value = $val;
	}

	/*
	function accept( $visitor ){
		$visitor->visit( $this->value );
	}
	*/

    public function toCss($env) {
        return $this->key . '=' . (is_string($this->value) ? $this->value : $this->value->toCSS());
    }

    public function compile($env) {
		if( is_object($this->value) && method_exists($this->value,'compile') ){
			return new Less_Tree_Assignment( $this->key, $this->value->compile($env));
        }
        return $this;
    }

}
