<?php

class Less_Tree_Operation{

	//public $type = 'Operation';

	public function __construct($op, $operands, $isSpaced = false){
		$this->op = trim($op);
		$this->operands = $operands;
		$this->isSpaced = $isSpaced;
	}

	/*function accept($visitor) {
		$visitor->visit($this->operands);
	}*/

	public function compile($env){
		$a = $this->operands[0]->compile($env);
		$b = $this->operands[1]->compile($env);


		if( $env->isMathOn() ){
			if( $a instanceof Less_Tree_Dimension && $b instanceof Less_Tree_Color ){
				if ($this->op === '*' || $this->op === '+') {
					$temp = $b;
					$b = $a;
					$a = $temp;
				} else {
					throw new Less_CompilerError("Operation on an invalid type");
				}
			}
			if ( !$a || !method_exists($a,'operate') ) {
				throw new Less_CompilerError("Operation on an invalid type");
			}

			return $a->operate($env,$this->op, $b);
		} else {
			return new Less_Tree_Operation($this->op, array($a, $b), $this->isSpaced );
		}
	}

	function toCSS($env){
		$separator = $this->isSpaced ? " " : "";
		return $this->operands[0]->toCSS($env) . $separator . $this->op . $separator . $this->operands[1]->toCSS($env);
	}
}
