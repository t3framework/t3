<?php



//
// CSS @import node
//
// The general strategy here is that we don't want to wait
// for the parsing to be completed, before we start importing
// the file. That's because in the context of a browser,
// most of the time will be spent waiting for the server to respond.
//
// On creation, we push the import path to our import queue, though
// `import,push`, we also pass it a callback, which it'll call once
// the file has been fetched, and parsed.
//
class Less_Tree_Import{

	//public $type = 'Import';
	public $options;
	public $index;
	public $path;
	public $features;
	public $currentFileInfo;
	public $css;
	public $skip;
	public $root;

	function __construct($path, $features, $options, $index, $currentFileInfo = null ){
		$this->options = $options;
		$this->index = $index;
		$this->path = $path;
		$this->features = $features;
		$this->currentFileInfo = $currentFileInfo;


		if( isset($this->options['less']) ){
			$this->css = !$this->options['less'];
		} else {
			$pathValue = $this->getPath();
			if( $pathValue && preg_match('/css([\?;].*)?$/',$pathValue) ){
				$this->css = true;
			}
		}
    }

//
// The actual import node doesn't return anything, when converted to CSS.
// The reason is that it's used at the evaluation stage, so that the rules
// it imports can be treated like any other rules.
//
// In `eval`, we make sure all Import nodes get evaluated, recursively, so
// we end up with a flat structure, which can easily be imported in the parent
// ruleset.
//
	/*
	function accept($visitor) {
		$visitor->visit($this->features);
		$visitor->visit($this->path);
		$visitor->visit($this->root);
	}
	*/

	function toCSS($env) {
		$features = $this->features ? ' ' . $this->features->toCSS($env) : '';

		if ($this->css) {
			return "@import " . $this->path->toCSS() . $features . ";\n";
		} else {
			return "";
		}
	}

	function getPath(){
		if ($this->path instanceof Less_Tree_Quoted) {
			$path = $this->path->value;
			return ( isset($this->css) || preg_match('/(\.[a-z]*$)|([\?;].*)$/',$path)) ? $path : $path . '.less';
		} else if ($this->path instanceof Less_Tree_URL) {
			return $this->path->value->value;
		}
		return null;
	}

	function compileForImport( $env ){
		return new Less_Tree_Import( $this->path->compile($env), $this->features, $this->options, $this->index, $this->currentFileInfo);
	}

	function compilePath($env) {
		$path = $this->path->compile($env);
		if( $this->currentFileInfo && $this->currentFileInfo['rootpath'] && !($path instanceof Less_Tree_URL)) {
			$pathValue = $path->value;
			// Add the base path if the import is relative
			if( $pathValue && $env->isPathRelative($pathValue) ){
				$path->value = Less_Environment::NormPath($this->currentFileInfo['uri_root']. $pathValue);
			}
		}
		return $path;
	}

	function compile($env) {

		$evald = $this->compileForImport($env);
		$uri = $full_path = false;

		//get path & uri
		$evald_path = $evald->getPath();
		if( $evald_path && $env->isPathRelative($evald_path) ){
			foreach(Less_Parser::$import_dirs as $rootpath => $rooturi){
				$temp = $rootpath.$evald_path;
				if( file_exists($temp) ){
					$full_path = Less_Environment::NormPath($temp);
					$uri = Less_Environment::NormPath(dirname($rooturi.$evald_path));
					break;
				}
			}
		}

		if( !$full_path ){
			$uri = $evald_path;
			$full_path = $evald_path;
		}

		//import once
		$realpath = realpath($full_path);
		if( !isset($evald->options['multiple']) && $realpath && Less_Parser::FileParsed($realpath) ){
			$evald->skip = true;
		}

		$features = ( $evald->features ? $evald->features->compile($env) : null );

		if ($evald->skip) { return array(); }

		if( $evald->css ){
			$temp = $this->compilePath( $env);
			return new Less_Tree_Import( $this->compilePath( $env), $features, $this->options, $this->index);
		}

		$parser = new Less_Parser($env);
		$evald->root = $parser->parseFile($full_path, $uri, true);
		$ruleset = new Less_Tree_Ruleset(array(), $evald->root->rules );
		$ruleset->evalImports($env);

		return $this->features ? new Less_Tree_Media($ruleset->rules, $this->features->value) : $ruleset->rules;
	}
}

