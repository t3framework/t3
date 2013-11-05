<?php

class Less_visitor{

	function visit($nodes){

		if( !is_array($nodes) ){
			$nodes = array($nodes);
		}

		foreach($nodes as $node){

			if( !is_object($node) ){
				continue;
			}

			$class = get_class($node);
			$funcName = 'visit' . substr( $class, strrpos( $class, '_')+1 );


			if( method_exists($this,$funcName) ){
				$this->$funcName( $node );
			}

			$deeper_property = $funcName.'Deeper';
			if( !isset($this->$deeper_property) && Less_Parser::is_method($node,'accept') ){
				$node->accept($this);
			}

			$funcName = $funcName . "Out";
			if( method_exists($this,$funcName) ){
				$this->$funcName( $node );
			}
		}
	}

}

