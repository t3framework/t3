<?php


class Less_Tree_Mixin_Call{

	//public $type = 'MixinCall';
	private $selector;
	private $arguments;
	private $index;
	private $currentFileInfo;

	public $important;

	/**
	 * less.js: tree.mixin.Call
	 *
	 */
    public function __construct($elements, $args, $index, $currentFileInfo, $important = false){
        $this->selector = new Less_Tree_Selector($elements);
        $this->arguments = $args;
        $this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
		$this->important = $important;
    }

	/*
	function accept($visitor){
		$visitor->visit($this->selector);
		$visitor->visit($this->arguments);
	}
	*/


	/**
	 * less.js: tree.mixin.Call.prototype()
	 *
	 */
    public function compile($env){

        $rules = array();
        $match = false;
        $isOneFound = false;

		$args = array();
		foreach($this->arguments as $a){
			$args[] = array('name'=> $a['name'], 'value' => $a['value']->compile($env) );
		}

		for($i = 0; $i< count($env->frames); $i++){

			$mixins = $env->frames[$i]->find($this->selector, null, $env);

            if( !$mixins ){
				continue;
			}

			$isOneFound = true;
			foreach( $mixins as $mixin ){

				$isRecursive = false;
				foreach($env->frames as $recur_frame){
					if( !($mixin instanceof Less_Tree_Mixin_Definition) ){
						if( (isset($recur_frame->originalRuleset) && $mixin === $recur_frame->originalRuleset) || ($mixin === $recur_frame) ){
							$isRecursive = true;
							break;
						}
					}
				}
				if( $isRecursive ){
					continue;
				}

				if ($mixin->matchArgs($args, $env)) {
					if( !method_exists($mixin,'matchCondition') || $mixin->matchCondition($args, $env) ){
						try {
							$rules = array_merge($rules, $mixin->compile($env, $args, $this->important)->rules);
						} catch (Exception $e) {
							throw new Less_CompilerException($e->message, $e->index, null, $this->currentFileInfo['filename']);
						}
					}
					$match = true;
				}

			}

			if( $match ){
				return $rules;
			}

        }


        if( $isOneFound ){

			$message = array();
			if( $args ){
				foreach($args as $a){
					$argValue = '';
					if( $a['name'] ){
						$argValue += $a['name']+':';
					}
					if( $a['value'] && method_exists($a['value'],'toCSS') ){
						$argValue += $a['value']->toCSS();
					}else{
						$argValue += '???';
					}
					$message[] = $argValue;
				}
			}
			$message = implode(', ');


			throw new Less_CompilerException('No matching definition was found for `'.
				trim($this->selector->toCSS($env)) . '(' .$message.')',
				$this->index, null, $this->currentFileInfo['filename']);

		}else{
			throw new Less_CompilerException(trim($this->selector->toCSS($env)) . " is undefined", $this->index);
		}
    }
}


