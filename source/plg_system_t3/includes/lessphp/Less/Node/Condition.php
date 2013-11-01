<?php

class Less_Tree_Condition {

	//public $type = 'Condition';
	private $op;
	private $lvalue;
	private $rvalue;
	private $index;
	private $negate;

	public function __construct($op, $l, $r, $i = 0, $negate = false) {
		$this->op = trim($op);
		$this->lvalue = $l;
		$this->rvalue = $r;
		$this->index = $i;
		$this->negate = $negate;
	}

	/*
	public function accept($visitor){
		$visitor->visit( $this->lvalue );
		$visitor->visit( $this->rvalue );
	}
	*/

    public function compile($env) {
		$a = $this->lvalue->compile($env);
		$b = $this->rvalue->compile($env);

		$i = $this->index;

		switch( $this->op ){
			case 'and':
				$result = $a && $b;
			break;

			case 'or':
				$result = $a || $b;
			break;

			default:
				$aReflection = new \ReflectionClass($a);
				$bReflection = new \ReflectionClass($b);
				if ($aReflection->hasMethod('compare')) {
					$result = $a->compare($b);
				} elseif ($bReflection->hasMethod('compare')) {
					$result = $b->compare($a);
				} else {
					throw new Less_CompilerException('Unable to perform comparison', $this->index);
				}
				switch ($result) {
					case -1:
					$result = $this->op === '<' || $this->op === '=<';
					break;

					case  0:
					$result = $this->op === '=' || $this->op === '>=' || $this->op === '=<';
					break;

					case  1:
					$result = $this->op === '>' || $this->op === '>=';
					break;
				}
			break;
		}

		return $this->negate ? !$result : $result;
    }

}
