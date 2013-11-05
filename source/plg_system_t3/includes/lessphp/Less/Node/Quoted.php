<?php


class Less_Tree_Quoted{
	//public $type = 'Quoted';
	public $value;
	public $content;
	public $index;
	public $currentFileInfo;

	public function __construct($str, $content = '', $escaped = false, $index = false, $currentFileInfo = null ){
		$this->escaped = $escaped;
		$this->value = $content;
		$this->quote = $str[0];
		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
	}

	public function toCSS (){
		if ($this->escaped) {
			return $this->value;
		} else {
			return $this->quote . $this->value . $this->quote;
		}
	}

	public function compile($env){

		$value = $this->value;
		if( preg_match_all('/`([^`]+)`/', $this->value, $matches) ){
			foreach($matches as $i => $match){
				$js = new Less_Tree_JavaScript($matches[1], $this->index, true);
				$js = $js->compile($env)->value;
				$value = str_replace($matches[0][$i], $js, $value);
			}
		}

		if( preg_match_all('/@\{([\w-]+)\}/',$value,$matches) ){
			foreach($matches[1] as $i => $match){
				$v = new Less_Tree_Variable('@' . $match, $this->index, $this->currentFileInfo );
				$v = $v->compile($env,true);
				$v = ($v instanceof Less_Tree_Quoted) ? $v->value : $v->toCSS($env);
				$value = str_replace($matches[0][$i], $v, $value);
			}
		}

		return new Less_Tree_Quoted($this->quote . $value . $this->quote, $value, $this->escaped, $this->index);
	}

	function compare($x) {

		if( !Less_Parser::is_method($x, 'toCSS') ){
			return -1;
		}

		$left = $this->toCSS();
		$right = $x->toCSS();

		if ($left === $right) {
			return 0;
		}

		return $left < $right ? -1 : 1;
	}
}
