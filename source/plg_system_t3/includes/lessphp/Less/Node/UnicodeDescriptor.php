<?php


class Less_Tree_UnicodeDescriptor{
	//public $type = 'UnicodeDescriptor';
	public function __construct($value){
		$this->value = $value;
	}

	public function toCss($env){
		return $this->value;
	}

	public function compile($env){
		return $this;
	}
}

