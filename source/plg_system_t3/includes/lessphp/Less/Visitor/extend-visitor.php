<?php


class Less_extendFinderVisitor extends Less_visitor{

	public $contexts = array();
	public $allExtendsStack;
	public $foundExtends;

	public $visitRuleDeeper = false;
	public $visitMixinDefinitionDeeper = false;

	function __construct(){
		$this->contexts = array();
		$this->allExtendsStack = array(array());
	}

	function run($root) {
		$this->visit($root);
		$root->allExtends =& $this->allExtendsStack[0];
	}

	function visitRuleset($rulesetNode) {

		if( $rulesetNode->root ){
			return;
		}

		$allSelectorsExtendList = array();

		// get &:extend(.a); rules which apply to all selectors in this ruleset
		for( $i = 0; $i < count($rulesetNode->rules); $i++ ){
			if( $rulesetNode->rules[$i] instanceof Less_Tree_Extend ){
				$allSelectorsExtendList[] = $rulesetNode->rules[$i];
			}
		}

		// now find every selector and apply the extends that apply to all extends
		// and the ones which apply to an individual extend
		for($i = 0; $i < count($rulesetNode->paths); $i++ ){

			$selectorPath = $rulesetNode->paths[$i];
			$selector = end($selectorPath); //$selectorPath[ count($selectorPath)-1];


			$list = array_slice($selector->extendList,0);
			$list = array_merge($list, $allSelectorsExtendList);

			$extendList = array();
			foreach($list as $allSelectorsExtend){
				$extendList[] = clone $allSelectorsExtend;
			}

			for($j = 0; $j < count($extendList); $j++ ){
				$this->foundExtends = true;
				$extend = $extendList[$j];
				$extend->findSelfSelectors( $selectorPath );
				$extend->ruleset = $rulesetNode;
				if( $j === 0 ){ $extend->firstExtendOnThisSelectorPath = true; }

				$temp = count($this->allExtendsStack)-1;
				$this->allExtendsStack[ $temp ][] = $extend;
			}
		}

		$this->contexts[] = $rulesetNode->selectors;
	}

	function visitRulesetOut( $rulesetNode ){
		if( !$rulesetNode->root) {
			array_pop($this->contexts);
		}
	}

	function visitMedia( $mediaNode ){
		$mediaNode->allExtends = array();
		$this->allExtendsStack[] =& $mediaNode->allExtends;
	}

	function visitMediaOut( $mediaNode ){
		array_pop($this->allExtendsStack);
	}

	function visitDirective( $directiveNode ){
		$directiveNode->allExtends = array();
		$this->allExtendsStack[] =& $directiveNode->allExtends;
	}

	function visitDirectiveOut( $directiveNode ){
		array_pop($this->allExtendsStack);
	}
}


