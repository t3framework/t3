<?php

//less.js : lib/less/tree/media.js

class Less_Tree_Media {

	//public $type = 'Media';
	public $features;
	public $ruleset;

	public function __construct($value = array(), $features = array()) {
		$selectors = $this->emptySelectors();
		$this->features = new Less_Tree_Value($features);
		$this->ruleset = new Less_Tree_Ruleset($selectors, $value);
		$this->ruleset->allowImports = true;
	}

	function accept( $visitor ){
		//$visitor->visit($this->features);
		$visitor->visit($this->ruleset);
	}

	public function toCSS($env) {
		$features = $this->features->toCSS($env);
		return '@media ' . $features . ($env->compress ? '{' : " {\n  ")
			. str_replace("\n", "\n  ", trim($this->ruleset->toCSS($env)))
			. ($env->compress ? '}' : "\n}\n");
	}

	public function compile($env) {

		$media = new Less_Tree_Media(array(), array());

		$strictMathBypass = false;
		if( $env->strictMath === false) {
			$strictMathBypass = true;
			$env->strictMath = true;
		}
		try {
			$media->features = $this->features->compile($env);
		}catch(\Exception $e){}

		if( $strictMathBypass ){
			$env->strictMath = false;
		}

		$env->mediaPath[] = $media;
		$env->mediaBlocks[] = $media;

		array_unshift($env->frames, $this->ruleset);
		$media->ruleset = $this->ruleset->compile($env);
		array_shift($env->frames);

		array_pop($env->mediaPath);

		return count($env->mediaPath) == 0 ? $media->compileTop($env) : $media->compileNested($env);
	}

	// TODO: Not sure if this is right...
	public function variable($name) {
		return $this->ruleset->variable($name);
	}

	public function find($selector) {
		return $this->ruleset->find($selector, $this);
	}

	public function rulesets() {
		return $this->ruleset->rulesets();
	}

	public function emptySelectors(){
		$el = new Less_Tree_Element('','&', 0);
		return array(new Less_Tree_Selector(array($el)));
	}


	// evaltop
	public function compileTop($env) {
		$result = $this;

		if (count($env->mediaBlocks) > 1) {
			$selectors = $this->emptySelectors();
			$result = new Less_Tree_Ruleset($selectors, $env->mediaBlocks);
			$result->multiMedia = true;
		}

		$env->mediaBlocks = array();
		$env->mediaPath = array();

		return $result;
	}

	public function compileNested($env) {
		$path = array_merge($env->mediaPath, array($this));

		// Extract the media-query conditions separated with `,` (OR).
		foreach ($path as $key => $p) {
			$value = $p->features instanceof Less_Tree_Value ? $p->features->value : $p->features;
			$path[$key] = is_array($value) ? $value : array($value);
		}

		// Trace all permutations to generate the resulting media-query.
		//
		// (a, b and c) with nested (d, e) ->
		//	a and d
		//	a and e
		//	b and c and d
		//	b and c and e

		$permuted = $this->permute($path);
		$expressions = array();
		foreach($permuted as $path){

			for( $i=0, $len=count($path); $i < $len; $i++){
				$path[$i] = method_exists($path[$i], 'toCSS') ? $path[$i] : new Less_Tree_Anonymous($path[$i]);
			}

			for( $i = count($path) - 1; $i > 0; $i-- ){
				array_splice($path, $i, 0, array(new Less_Tree_Anonymous('and')));
			}

			$expressions[] = new Less_Tree_Expression($path);
		}
		$this->features = new Less_Tree_Value($expressions);



		// Fake a tree-node that doesn't output anything.
		return new Less_Tree_Ruleset(array(), array());
	}

	public function permute($arr) {
		if (!$arr)
			return array();

		if (count($arr) == 1)
			return $arr[0];

		$result = array();
		$rest = $this->permute(array_slice($arr, 1));
		foreach ($rest as $r) {
			foreach ($arr[0] as $a) {
				$result[] = array_merge(
					is_array($a) ? $a : array($a),
					is_array($r) ? $r : array($r)
				);
			}
		}

		return $result;
	}

    function bubbleSelectors($selectors) {
		$this->ruleset = new Less_Tree_Ruleset( array_slice($selectors,0), array($this->ruleset) );
    }

}
