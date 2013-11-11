<?php

//less.js : lib/less/tree/selector.js


class Less_Tree_Selector {

	//public $type = 'Selector';
	public $elements;
	public $extendList = array();
	private $_css;

	public function __construct($elements, $extendList = array() ){
		$this->elements = $elements;
		$this->extendList = $extendList;
	}

	/*
	function accept($visitor) {
		$visitor->visit($this->elements);
		$visitor->visit($this->extendList);
	}
	*/

	public function match($other) {
		$len   = count($this->elements);

		$olen = $offset = 0;
		if( $other && count($other->elements) ){

			if( $other->elements[0]->value === "&" ){
				$offset = 1;
			}
			$olen = count($other->elements) - $offset;
		}

		$max = min($len, $olen);

		if( !$max ){
			return false;
		}

		for ($i = 0; $i < $max; $i ++) {
			if ($this->elements[$i]->value !== $other->elements[$i + $offset]->value) {
				return false;
			}
		}

		return true;
	}





	public function compile($env) {
		$extendList = array();

		for($i = 0, $len = count($this->extendList); $i < $len; $i++){
			$extendList[] = $this->extendList[$i]->compile($this->extendList[$i]);
		}

		$elements = array();
		for( $i = 0, $len = count($this->elements); $i < $len; $i++){
			$elements[] = $this->elements[$i]->compile($env);
		}

		return new Less_Tree_Selector($elements, $extendList);
	}

	public function toCSS ($env){

		if ($this->_css) {
			return $this->_css;
		}

		if (is_array($this->elements) && isset($this->elements[0]) &&
			$this->elements[0]->combinator instanceof Less_Tree_Combinator &&
			$this->elements[0]->combinator->value === '') {
				$this->_css = ' ';
		}else{
			$this->_css = '';
		}

		foreach($this->elements as $e){
			if( is_string($e) ){
				$this->_css .= ' ' . trim($e);
			}else{
				$this->_css .= $e->toCSS($env);
			}
		}

		return $this->_css;
	}

}
