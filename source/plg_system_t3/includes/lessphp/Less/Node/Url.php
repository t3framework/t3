<?php


class Less_Tree_Url{
	//public $type = "Url";
	public $attrs;
	public $value;
	public $currentFileInfo;

	public function __construct($value, $currentFileInfo = null){
		$this->value = $value;
		$this->currentFileInfo = $currentFileInfo;
	}

	/*
	function accept( $visitor ){
		$visitor->visit($this->value);
	}
	*/

	public function toCSS(){
		return "url(" . $this->value->toCSS() . ")";
	}

	public function compile($ctx){
		$val = $this->value->compile($ctx);

		// Add the base path if the URL is relative
		if( $this->currentFileInfo && is_string($val->value) && $ctx->isPathRelative($val->value) ){
			$rootpath = $this->currentFileInfo['uri_root'];
			if ( !$val->quote ){
				$rootpath = preg_replace('/[\(\)\'"\s]/', '\\$1', $rootpath );
			}
			$val->value = $rootpath . $val->value;
		}


		return new Less_Tree_URL($val, null);
	}

}
