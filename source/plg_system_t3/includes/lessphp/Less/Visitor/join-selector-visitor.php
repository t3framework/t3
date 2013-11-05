<?php

class Less_joinSelectorVisitor extends Less_visitor{

	public $contexts = array( array() );

	public $visitRuleDeeper = false;
	public $visitMixinDefinition = false;


	function run( $root ){
		$this->visit($root);
	}

	function visitRuleset($rulesetNode) {
		$context = end($this->contexts); //$context = $this->contexts[ count($this->contexts) - 1];
		$paths = array();
		//$this->contexts[] = $paths;
		if( !$rulesetNode->root ){
			$rulesetNode->joinSelectors($paths, $context, $rulesetNode->selectors);
			$rulesetNode->paths = $paths;
		}

		//array_pop($this->contexts);
		$this->contexts[] = $paths;

	}

	function visitRulesetOut( $rulesetNode ){
		array_pop($this->contexts);
	}

	function visitMedia($mediaNode) {
		$context = end($this->contexts); //$context = $this->contexts[ count($this->contexts) - 1];
		$mediaNode->ruleset->root = ( count($context) === 0 || @$context[0]->multiMedia);
	}

}

