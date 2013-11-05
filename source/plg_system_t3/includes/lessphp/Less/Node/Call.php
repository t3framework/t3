<?php


//
// A function call node.
//

class Less_Tree_Call{
    public $type = 'Call';
    private $value;

    var $name;
    var $args;
    var $index;
    var $currentFileInfo;

	public function __construct($name, $args, $index, $currentFileInfo = null ){
		$this->name = $name;
		$this->args = $args;
		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
	}

	/*
	function accept( $visitor ){
		$visitor->visit( $this->args );
	}
	*/

    //
    // When evaluating a function call,
    // we either find the function in `tree.functions` [1],
    // in which case we call it, passing the  evaluated arguments,
    // or we simply print it out as it appeared originally [2].
    //
    // The *functions.js* file contains the built-in functions.
    //
    // The reason why we evaluate the arguments, is in the case where
    // we try to pass a variable to a function, like: `saturate(@color)`.
    // The function should receive the value, not the variable.
    //
    public function compile($env){
		$args = array();
		foreach($this->args as $a){
			$args[] = $a->compile($env);
		}

		$name = $this->name;
		if( $name == '%' ){
			$name = '_percent';
		}elseif( $name == 'data-uri' ){
			$name = 'datauri';
		}

		if( Less_Parser::is_method($env, $name) ){ // 1.
			try {

				$result = call_user_func_array( array($env, $name), $args);
				if( $result != null ){
					return $result;
				}

			} catch (Exception $e) {
				throw Less_CompilerException('error evaluating function `' . $this->name . '` '.$e->getMessage().' index: '. $this->index);
			}
		}

		// 2.
		$temp = array();
		foreach($args as $a){
			$temp[] = $a->toCSS($env);
		}
		return new Less_Tree_Anonymous($this->name .
				   "(" . implode(', ', $temp) . ")");
    }

    public function toCSS ($env) {
        return $this->compile($env)->toCSS();
    }

}
