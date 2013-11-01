<?php


class Less_Tree_Rule{
	//public $type = 'Rule';
	public $name;
	public $value;
	public $important;
	public $index;
	public $inline;
	public $variable;
	public $currentFileInfo;

	public function __construct($name, $value = null, $important = null, $index = null, $currentFileInfo = null,  $inline = false){
		$this->name = $name;
		$this->value = ($value instanceof Less_Tree_Value) ? $value : new Less_Tree_Value(array($value));
		$this->important = $important ? ' ' . trim($important) : '';
		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
		$this->inline = $inline;

		if ($name[0] === '@') {
			$this->variable = true;
		} else {
			$this->variable = false;
		}
	}

	/*
	function accept($visitor) {
		$visitor->visit( $this->value );
	}
	*/

	public function toCSS ($env){
		if ($this->variable) {
			return "";
		} else {
			try {
				return $this->name . ($env->compress ? ':' : ': ')
					. $this->value->toCSS($env)
					. $this->important . ($this->inline ? "" : ";");
			}catch( \Exception $e ){
				$e->index = $this->index;
				$e->filename = $this->currentFileInfo['filename'];
				throw $e;
			}
		}
	}

	public function compile ($env){

		$return = null;
		$strictMathBypass = false;
		if( $this->name === "font" && !$env->strictMath ){
			$strictMathBypass = true;
			$env->strictMath = true;
		}
		try{
			$return = new Less_Tree_Rule($this->name,
										$this->value->compile($env),
										$this->important,
										$this->currentFileInfo,
										$this->index, $this->inline);
		}
		catch(\Exception $e){}

		if( $strictMathBypass ){
			$env->strictMath = false;
		}

		return $return;
	}

	function makeImportant(){
		return new Less_Tree_Rule($this->name, $this->value, '!important', $this->index, $this->currentFileInfo, $this->inline);
	}

}
