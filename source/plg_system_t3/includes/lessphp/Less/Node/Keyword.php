<?php


class Less_Tree_Keyword{

	//public $type = 'Keyword';
	public function __construct($value){
		$this->value = $value;
	}

	public function toCss(){
		return $this->value;
	}

	public function compile($env){
		return $this;
	}

	public function compare($other) {
		if ($other instanceof Less_Tree_Keyword) {
			return $other->value === $this->value ? 0 : 1;
		} else {
			return -1;
		}
	}
}
