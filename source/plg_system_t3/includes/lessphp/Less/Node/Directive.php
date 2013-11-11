<?php

class Less_Tree_Directive{

	//public $type = 'Directive';
    public $name;
    public $value;
    public $ruleset;

    public function __construct($name, $value = null){
        $this->name = $name;
        if (is_array($value)) {
            $this->ruleset = new Less_Tree_Ruleset(false, $value);
            $this->ruleset->allowImports = true;
        } else {
            $this->value = $value;
        }
    }


	function accept( $visitor ){
		$visitor->visit( $this->ruleset );
		//$visitor->visit( $this->value );
	}

	public function toCSS( $env){
		if ($this->ruleset) {
			$this->ruleset->root = true;
			return $this->name . ($env->compress ? '{' : " {\n  ") .
				   preg_replace('/\n/', "\n  ", trim($this->ruleset->toCSS($env))) .
				   ($env->compress ? '}': "\n}\n");
		} else {
			return $this->name . ' ' . $this->value->toCSS($env) . ";\n";
		}
	}

    public function compile($env)
    {
        $evaldDirective = $this;
        if( $this->ruleset ){
			$env->unshiftFrame($this);
            $evaldDirective = new Less_Tree_Directive( $this->name );
            $evaldDirective->ruleset = $this->ruleset->compile($env);
            $env->shiftFrame();
        }
        return $evaldDirective;
    }
    // TODO: Not sure if this is right...
    public function variable($name)
    {
        return $this->ruleset->variable($name);
    }

    public function find($selector)
    {
        return $this->ruleset->find($selector, $this);
    }

}
