<?php

// less.js : lib/less/tree/element.js


class Less_Tree_Combinator {

	//public $type = 'Combinator';
	public $value;

	public function __construct($value = null) {
		if ($value == ' ') {
			$this->value = ' ';
		} else {
			$this->value = trim($value);
		}
	}

	public function toCSS ($env) {
		$v = array(
			''   => '',
			' '  => ' ',
			':'  => ' :',
			'+'  => $env->compress ? '+' : ' + ',
			'~'  => $env->compress ? '~' : ' ~ ',
			'>'  => $env->compress ? '>' : ' > ',
			'|'  => $env->compress ? '|' : ' | '

		);

		return $v[$this->value];
	}
}
