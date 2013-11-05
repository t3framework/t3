<?php

//less.js : /lib/less/tree/ruleset.js


class Less_Tree_Ruleset{
	//public $type = 'Ruleset';
	protected $lookups;
	private $_variables;
	private $_rulesets;

	public $strictImports;

	public $selectors;
	public $rules;
	public $root;
	public $firstRoot;
	public $allowImports;
	public $paths = array();

	public function __construct($selectors, $rules, $strictImports = false){
		$this->selectors = $selectors;
		$this->rules = $rules;
		$this->lookups = array();
		$this->strictImports = $strictImports;
	}

	function accept( $visitor ){
		//$visitor->visit($this->selectors);
		$visitor->visit($this->rules);
	}

	public function compile($env){

		$selectors = array();
		if( $this->selectors ){
			foreach($this->selectors as $s){
				if( Less_Parser::is_method($s,'compile') ){
					$selectors[] = $s->compile($env);
				}
			}
		}
		$ruleset = new Less_Tree_Ruleset($selectors, array_slice($this->rules,0), $this->strictImports);
		$rules = array();

		$ruleset->originalRuleset = $this;
		$ruleset->root = $this->root;
		$ruleset->firstRoot = $this->firstRoot;
		$ruleset->allowImports = $this->allowImports;

		// push the current ruleset to the frames stack
		$env->unshiftFrame($ruleset);

		// currrent selectors
		array_unshift($env->selectors,$this->selectors);


		// Evaluate imports
		if ($ruleset->root || $ruleset->allowImports || !$ruleset->strictImports) {
			$ruleset->evalImports($env);
		}


		// Store the frames around mixin definitions,
		// so they can be evaluated like closures when the time comes.
		foreach($ruleset->rules as $i => $rule) {
			if ($rule instanceof Less_Tree_Mixin_Definition) {
				$ruleset->rules[$i]->frames = array_slice($env->frames,0);
			}
		}

		$mediaBlockCount = 0;
		if( $env instanceof Less_Environment ){
			$mediaBlockCount = count($env->mediaBlocks);
		}

		// Evaluate mixin calls.
		for($i=0; $i < count($ruleset->rules); $i++){
			$rule = $ruleset->rules[$i];
			if( $rule instanceof Less_Tree_Mixin_Call ){
				$rules = $rule->compile($env);

				$temp = array();
				foreach($rules as $r){
					if( ($r instanceof Less_Tree_Rule) && $r->variable ){
						// do not pollute the scope if the variable is
						// already there. consider returning false here
						// but we need a way to "return" variable from mixins
						if( !$ruleset->variable($r->name) ){
							$temp[] = $r;
						}
					}else{
						$temp[] = $r;
					}
				}
				$rules = $temp;
				array_splice($ruleset->rules, $i, 1, $rules);
				$i += count($rules)-1;
				$ruleset->resetCache();
            }
        }


		foreach($ruleset->rules as $i => $rule) {
			if(! ($rule instanceof Less_Tree_Mixin_Definition) ){
				$ruleset->rules[$i] = Less_Parser::is_method($rule,'compile') ? $rule->compile($env) : $rule;
			}
		}


		// Pop the stack
		$env->shiftFrame();
		array_shift($env->selectors);

        if ($mediaBlockCount) {
			for($i = $mediaBlockCount; $i < count($env->mediaBlocks); $i++ ){
				$env->mediaBlocks[$i]->bubbleSelectors($selectors);
			}
        }

		return $ruleset;
	}

    function evalImports($env) {

		for($i=0; $i < count($this->rules); $i++){
			$rule = $this->rules[$i];

			if( $rule instanceof Less_Tree_Import  ){
				$rules = $rule->compile($env);
				if( is_array($rules) ){
					array_splice($this->rules, $i, 1, $rules);
				}else{
					array_splice($this->rules, $i, 1, array($rules));
				}
				if( count($rules) ){
					$i += count($rules)-1;
				}
				$this->resetCache();
			}
		}
    }

	static function makeImportant($selectors = null, $rules = null, $strictImports = false) {

		$important_rules = array();
		foreach($rules as $rule){
			if( Less_Parser::is_method($rule,'makeImportant') && property_exists($rule,'selectors') ){
				$important_rules[] = $rule->makeImportant($rule->selectors, $rule->rules, $strictImports);
			}elseif( Less_Parser::is_method($rule,'makeImportant') ){
				$important_rules[] = $rule->makeImportant();
			}else{
				$important_rules[] = $rule;
			}
		}

		return new Less_Tree_Ruleset($selectors, $important_rules, $strictImports );
	}

	public function matchArgs($args){
		return !is_array($args) || count($args) === 0;
	}

	function resetCache() {
		$this->_rulesets = null;
		$this->_variables = null;
		$this->lookups = array();
	}

	public function variables(){

		if( !$this->_variables ){
			$this->_variables = array();
			foreach( $this->rules as $r){
				if ($r instanceof Less_Tree_Rule && $r->variable === true) {
					$this->_variables[$r->name] = $r;
				}
			}
		}

		return $this->_variables;
	}

	public function variable($name){
		$vars = $this->variables();
		return isset($vars[$name]) ? $vars[$name] : null;
	}


	public function rulesets(){
		$rulesets = array();
		foreach($this->rules as $r){
			if( ($r instanceof Less_Tree_Ruleset) || ($r instanceof Less_Tree_Mixin_Definition) ){
				$rulesets[] = $r;
			}
		}
		return $rulesets;
	}


	public function find( $selector, $self = null, $env = null){

		if( !$self ){
			$self = $this;
		}

		$key = $selector->toCSS($env);

		if( !array_key_exists($key, $this->lookups) ){
			$this->lookups[$key] = array();;

			foreach( $this->rulesets() as $rule ){
				if( $rule == $self ){
					continue;
				}

				foreach( $rule->selectors as $ruleSelector ){
					if( $selector->match($ruleSelector) ){

						if (count($selector->elements) > count($ruleSelector->elements)) {
							$this->lookups[$key] = array_merge($this->lookups[$key], $rule->find( new Less_Tree_Selector(array_slice($selector->elements, 1)), $self, $env));
						} else {
							$this->lookups[$key][] = $rule;
						}
						break;
					}
				}
			}
		}

		return $this->lookups[$key];
	}

	//
	// Entry point for code generation
	//
	//	 `context` holds an array of arrays.
	//
	public function toCSS($env){
		$css = array();	  // The CSS output
		$rules = array();	// node.Rule instances
		$_rules = array();
		$rulesets = array(); // node.Ruleset instances


		// Compile rules and rulesets
		foreach($this->rules as $rule) {
			if (isset($rule->rules) || ($rule instanceof Less_Tree_Media)) {
				$rulesets[] = $rule->toCSS($env);

			} else if ( $rule instanceof Less_Tree_Directive ){
				$cssValue = $rule->toCSS($env);
                // Output only the first @charset definition as such - convert the others
                // to comments in case debug is enabled
                if ($rule->name === "@charset") {
                    // Only output the debug info together with subsequent @charset definitions
                    // a comment (or @media statement) before the actual @charset directive would
                    // be considered illegal css as it has to be on the first line
                    if ($env->charset) {
                        continue;
                    }
                    $env->charset = true;
                }
                $rulesets[] = $cssValue;

			} else if ($rule instanceof Less_Tree_Comment) {
				if (!$rule->silent) {
					if ($this->root) {
						$rulesets[] = $rule->toCSS($env);
					} else {
						$rules[] = $rule->toCSS($env);
					}
				}
			} else {
				if( Less_Parser::is_method($rule, 'toCSS') && (!isset($rule->variable) || !$rule->variable) ){
                    if( $this->firstRoot && $rule instanceof Less_Tree_Rule ){
						throw new Less_CompilerError("properties must be inside selector blocks, they cannot be in the root.");
                    }
					$rules[] = $rule->toCSS($env);
				} else if (isset($rule->value) && $rule->value && ! $rule->variable) {
					$rules[] = (string) $rule->value;
				}
			}
		}

        // Remove last semicolon
		if( $env->compress && count($rules) ){
			$rule =& $rules[ count($rules)-1 ];
			if( substr($rule, -1 ) === ';' ){
				$rule = substr($rule,0,-1);
			}
		}

		$rulesets = implode('', $rulesets);

		// If this is the root node, we don't render
		// a selector, or {}.
		// Otherwise, only output if this ruleset has rules.
		if ($this->root) {
			$css[] = implode($env->compress ? '' : "\n", $rules);
		} else {
			if (count($rules)) {

				$selector = array();
				foreach($this->paths as $p){
					$_p = array();
					foreach($p as $s){
						$_p[] = $s->toCSS($env);
					}
					$selector[] = trim(implode('',$_p));
				}
				$selector = implode($env->compress ? ',' : ",\n", $selector);

				// Remove duplicates
				for ($i = count($rules) - 1; $i >= 0; $i--) {
					if( substr($rules[$i],0,2) === "/*" || !in_array($rules[$i], $_rules) ){
						array_unshift($_rules, $rules[$i]);
					}
				}
				$rules = $_rules;

				$css[] = $selector;
				$css[] = ($env->compress ? '{' : " {\n  ") .
						 implode($env->compress ? '' : "\n  ", $rules) .
						 ($env->compress ? '}' : "\n}\n");
			}
		}
		$css[] = $rulesets;

		return implode('', $css) . ($env->compress ? "\n" : '' );
	}


	public function joinSelectors( &$paths, $context, $selectors ){
		if( is_array($selectors) ){
			foreach($selectors as $selector) {
				$this->joinSelector($paths, $context, $selector);
			}
		}
	}

	public function joinSelector (&$paths, $context, $selector){

		$hasParentSelector = false; $newSelectors; $el; $sel; $parentSel;
		$newSelectorPath; $afterParentJoin; $newJoinedSelector;
		$newJoinedSelectorEmpty; $lastSelector; $currentElements;
		$selectorsMultiplied;

		foreach($selector->elements as $el) {
			if( $el->value === '&') {
				$hasParentSelector = true;
			}
		}

		if( !$hasParentSelector ){
			if( count($context) > 0 ) {
				foreach($context as $context_el){
					$paths[] = array_merge($context_el, array($selector) );
				}
			}else {
				$paths[] = array($selector);
			}
			return;
		}


		// The paths are [[Selector]]
		// The first list is a list of comma seperated selectors
		// The inner list is a list of inheritance seperated selectors
		// e.g.
		// .a, .b {
		//   .c {
		//   }
		// }
		// == [[.a] [.c]] [[.b] [.c]]
		//

		// the elements from the current selector so far
		$currentElements = array();
		// the current list of new selectors to add to the path.
		// We will build it up. We initiate it with one empty selector as we "multiply" the new selectors
		// by the parents
		$newSelectors = array(array());


		foreach( $selector->elements as $el){

			// non parent reference elements just get added
			if( $el->value !== '&' ){
				$currentElements[] = $el;
			} else {
				// the new list of selectors to add
				$selectorsMultiplied = array();

				// merge the current list of non parent selector elements
				// on to the current list of selectors to add
				if( count($currentElements) > 0) {
					$this->mergeElementsOnToSelectors( $currentElements, $newSelectors);
				}

				// loop through our current selectors
				foreach($newSelectors as $sel){

					// if we don't have any parent paths, the & might be in a mixin so that it can be used
					// whether there are parents or not
					if( !count($context) ){
						// the combinator used on el should now be applied to the next element instead so that
						// it is not lost
						if( count($sel) > 0 ){
							$sel[0]->elements = array_slice($sel[0]->elements,0);
							$sel[0]->elements[] = new Less_Tree_Element($el->combinator, '', 0); //new Element(el.Combinator,  ""));
						}
						$selectorsMultiplied[] = $sel;
					}else {

						// and the parent selectors
						foreach($context as $parentSel){
							// We need to put the current selectors
							// then join the last selector's elements on to the parents selectors

							// our new selector path
							$newSelectorPath = array();
							// selectors from the parent after the join
							$afterParentJoin = array();
							$newJoinedSelectorEmpty = true;

							//construct the joined selector - if & is the first thing this will be empty,
							// if not newJoinedSelector will be the last set of elements in the selector
							if ( count($sel) > 0) {
								$newSelectorPath = array_slice($sel,0);
								$lastSelector = array_pop($newSelectorPath);
								$newJoinedSelector = new Less_Tree_Selector( array_slice($lastSelector->elements,0), $selector->extendList);
								$newJoinedSelectorEmpty = false;
							}
							else {
								$newJoinedSelector = new Less_Tree_Selector( array(), $selector->extendList);
							}

							//put together the parent selectors after the join
							if ( count($parentSel) > 1) {
								$afterParentJoin = array_merge($afterParentJoin, array_slice($parentSel,1) );
							}

							if ( count($parentSel) > 0) {
								$newJoinedSelectorEmpty = false;

								// join the elements so far with the first part of the parent
								$newJoinedSelector->elements[] = new Less_Tree_Element( $el->combinator, $parentSel[0]->elements[0]->value, 0 );

								$newJoinedSelector->elements = array_merge( $newJoinedSelector->elements, array_slice($parentSel[0]->elements, 1) );
							}

							if (!$newJoinedSelectorEmpty) {
								// now add the joined selector
								$newSelectorPath[] = $newJoinedSelector;
							}

							// and the rest of the parent
							$newSelectorPath = array_merge($newSelectorPath, $afterParentJoin);

							// add that to our new set of selectors
							$selectorsMultiplied[] = $newSelectorPath;
						}
					}
				}

				// our new selectors has been multiplied, so reset the state
				$newSelectors = $selectorsMultiplied;
				$currentElements = array();
			}
		}

		// if we have any elements left over (e.g. .a& .b == .b)
		// add them on to all the current selectors
		if( count($currentElements) > 0) {
			$this->mergeElementsOnToSelectors($currentElements, $newSelectors);
		}
		foreach( $newSelectors as $new_sel){
			if( count($new_sel) ){
				$paths[] = $new_sel;
			}
		}
	}

	function mergeElementsOnToSelectors( $elements, &$selectors){

		if( count($selectors) == 0) {
			$selectors[] = array( new Less_Tree_Selector($elements) );
			return;
		}


		foreach( $selectors as &$sel){

			// if the previous thing in sel is a parent this needs to join on to it
			if ( count($sel) > 0) {
				$last = count($sel)-1;
				$sel[$last] = new Less_Tree_Selector( array_merge( $sel[$last]->elements, $elements), $sel[$last]->extendList );
			}else{
				$sel[] = new Less_Tree_Selector( $elements );
			}
		}
	}
}
