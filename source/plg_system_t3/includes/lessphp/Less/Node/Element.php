<?php

//less.js : lib/less/tree/element.js

class Less_Tree_Element{
	//public $type = 'Element';
	public $combinator;
    public $value;
	public $index;

    public function __construct($combinator, $value, $index = null) {
        if ( ! ($combinator instanceof Less_Tree_Combinator)) {
            $combinator = new Less_Tree_Combinator($combinator);
        }

		if (is_string($value)) {
			$this->value = trim($value);
		} elseif ($value) {
			$this->value = $value;
		} else {
			$this->value = "";
		}

        $this->combinator = $combinator;
		$this->index = $index;
    }

	/*
	function accept( $visitor ){
		$visitor->visit( $this->combinator );
		$visitor->visit( $this->value );
	}
	*/

	//less.js : tree.Element.prototype.toCSS
    public function toCSS ($env) {

        $value = $this->value;
        if( !is_string($value) ){
			$value = $value->toCSS($env);
		}

		if( $value == '' && strlen($this->combinator->value) && $this->combinator->value[0] == '&' ){
			return '';
		}
		return $this->combinator->toCSS($env) . $value;
    }

	public function compile($env) {
		return new Less_Tree_Element($this->combinator,
			is_string($this->value) ? $this->value : $this->value->compile($env),
			$this->index
		);
	}
}
