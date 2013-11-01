<?php

class Less_Tree_Javascript{

	//public $type = 'Javascript';

	public function __construct($string, $index, $escaped){
		$this->escaped = $escaped;
		$this->expression = $string;
		$this->index = $index;
	}

	public function compile($env){
		return $this;
	}

	public function toCss($env){
		return $env->compress ? '' : '/* Sorry, can not do JavaScript evaluation in PHP... :( */';
	}
}
