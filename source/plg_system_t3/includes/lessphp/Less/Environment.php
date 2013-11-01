<?php

//less.js : lib/less/functions.js


class Less_Environment{

	public $paths = array();			// option - unmodified - paths to search for imports on
	static $files = array();			// list of files that have been imported, used for import-once
	public $relativeUrls;				// option - whether to adjust URL's to be relative
	public $strictImports = false;		// option -
	public $compress = false;			// option - whether to compress
	public $processImports;				// option - whether to process imports. if false then imports will not be imported
	public $currentFileInfo;			// information about the current file - for error reporting and importing and making urls relative etc.

	/**
	 * @var array
	 */
	public $frames = array();


	/**
	 * @var bool
	 */
	public $debug = false;


	/**
	 * @var array
	 */
	public $mediaBlocks = array();

	/**
	 * @var array
	 */
	public $mediaPath = array();

	public $selectors = array();

	public $charset;

	public $parensStack = array();

	public $strictMath = false;

	public $strictUnits = false;

	public function __construct( $options = null ){
		$this->frames = array();


		if( isset($options['compress']) ){
			$this->compress = (bool)$options['compress'];
		}
		if( isset($options['strictUnits']) ){
			$this->strictUnits = (bool)$options['strictUnits'];
		}

	}


	//may want to just use the __clone()?
	public function copyEvalEnv($frames = array() ){
		$new_env = clone $this;
		$new_env->frames = $frames;
		return $new_env;
	}

	public function inParenthesis(){
		$this->parensStack[] = true;
	}

	public function outOfParenthesis() {
		array_pop($this->parensStack);
	}

	public function isMathOn() {
		return $this->strictMath ? ($this->parensStack && count($this->parensStack)) : true;
	}

	public function isPathRelative($path){
		return !preg_match('/^(?:[a-z-]+:|\/)/',$path);
	}

	/**
	 * @return bool
	 */
	public function getCompress(){
		return $this->compress;
	}

	/**
	 * @param bool $compress
	 * @return void
	 */
	public function setCompress($compress){
		$this->compress = $compress;
	}

	/**
	 * @return bool
	 */
	public function getDebug(){
		return $this->debug;
	}

	/**
	 * @param $debug
	 * @return void
	 */
	public function setDebug($debug){
		$this->debug = $debug;
	}

	public function unshiftFrame($frame){
		array_unshift($this->frames, $frame);
	}

	public function shiftFrame(){
		return array_shift($this->frames);
	}

	public function addFrame($frame){
		$this->frames[] = $frame;
	}

	public function addFrames(array $frames){
		$this->frames = array_merge($this->frames, $frames);
	}

	//tree.operate()
	static public function operate ($env, $op, $a, $b){
		switch ($op) {
			case '+': return $a + $b;
			case '-': return $a - $b;
			case '*': return $a * $b;
			case '/': return $a / $b;
		}
	}

	static public function clamp($val){
		return min(1, max(0, $val));
	}

	static public function number($n){

		if ($n instanceof Less_Tree_Dimension) {
			return floatval( $n->unit->is('%') ? $n->value / 100 : $n->value);
		} else if (is_numeric($n)) {
			return $n;
		} else {
			throw new Less_CompilerException("color functions take numbers as parameters");
		}
	}

	static public function scaled($n, $size = 256 ){
		if( $n instanceof Less_Tree_Dimension && $n->unit->is('%') ){
			return (float)$n->value * $size / 100;
		} else {
			return Less_Environment::number($n);
		}
	}



	/** Function **/
	public static function rgb ($r, $g, $b){
		return Less_Environment::rgba($r, $g, $b, 1.0);
	}

	public static function rgba($r, $g, $b, $a){
		$rgb = array($r, $g, $b);
		$rgb = array_map(array('Less_Environment','scaled'),$rgb);

		$a = self::number($a);
		return new Less_Tree_Color($rgb, $a);
	}

	public static function hsl($h, $s, $l){
		return Less_Environment::hsla($h, $s, $l, 1.0);
	}

	public static function hsla($h, $s, $l, $a){

		$h = fmod(self::number($h), 360) / 360; // Classic % operator will change float to int
		$s = self::clamp(self::number($s));
		$l = self::clamp(self::number($l));
		$a = self::clamp(self::number($a));

		$m2 = $l <= 0.5 ? $l * ($s + 1) : $l + $s - $l * $s;

		$m1 = $l * 2 - $m2;

		return Less_Environment::rgba( self::hsla_hue($h + 1/3, $m1, $m2) * 255,
							self::hsla_hue($h, $m1, $m2) * 255,
							self::hsla_hue($h - 1/3, $m1, $m2) * 255,
							$a);
	}

	static function hsla_hue($h, $m1, $m2){
		$h = $h < 0 ? $h + 1 : ($h > 1 ? $h - 1 : $h);
		if	  ($h * 6 < 1) return $m1 + ($m2 - $m1) * $h * 6;
		else if ($h * 2 < 1) return $m2;
		else if ($h * 3 < 2) return $m1 + ($m2 - $m1) * (2/3 - $h) * 6;
		else				 return $m1;
	}

	public static function hsv($h, $s, $v) {
		return Less_Environment::hsva($h, $s, $v, 1.0);
	}

	public static function hsva($h, $s, $v, $a) {
		$h = ((Less_Environment::number($h) % 360) / 360 ) * 360;
		$s = Less_Environment::number($s);
		$v = Less_Environment::number($v);
		$a = Less_Environment::number($a);

		$i = floor(($h / 60) % 6);
		$f = ($h / 60) - $i;

		$vs = array( $v,
				  $v * (1 - $s),
				  $v * (1 - $f * $s),
				  $v * (1 - (1 - $f) * $s));

		$perm = array(array(0, 3, 1),
					array(2, 0, 1),
					array(1, 0, 3),
					array(1, 2, 0),
					array(3, 1, 0),
					array(0, 1, 2));

		return Less_Environment::rgba($vs[$perm[$i][0]] * 255,
						 $vs[$perm[$i][1]] * 255,
						 $vs[$perm[$i][2]] * 255,
						 $a);
	}

	public static function hue($color){
		$c = $color->toHSL();
		return new Less_Tree_Dimension(round($c['h']));
	}

	public static function saturation($color){
		$c = $color->toHSL();
		return new Less_Tree_Dimension(round($c['s'] * 100), '%');
	}

	public static function lightness($color){
		$c = $color->toHSL();
		return new Less_Tree_Dimension(round($c['l'] * 100), '%');
	}

	public static function hsvhue( $color ){
		$hsv = $color->toHSV();
		return new Less_Tree_Dimension( round($hsv['h']) );
	}


	public static function hsvsaturation( $color ){
		$hsv = $color->toHSV();
		return new Less_Tree_Dimension( round($hsv['s'] * 100), '%' );
	}

	public static function hsvvalue( $color ){
		$hsv = $color->toHSV();
		return new Less_Tree_Dimension( round($hsv['v'] * 100), '%' );
	}

	public static function red($color) {
		return new Less_Tree_Dimension( $color->rgb[0] );
	}

	public static function green($color) {
		return new Less_Tree_Dimension( $color->rgb[1] );
	}

	public static function blue($color) {
		return new Less_Tree_Dimension( $color->rgb[2] );
	}

	public static function alpha($color){
		$c = $color->toHSL();
		return new Less_Tree_Dimension($c['a']);
	}

	public static function luma ($color) {
		return new Less_Tree_Dimension(round( $color->luma() * $color->alpha * 100), '%');
	}

	public static function saturate($color, $amount){
		$hsl = $color->toHSL();

		$hsl['s'] += $amount->value / 100;
		$hsl['s'] = self::clamp($hsl['s']);

		return Less_Environment::hsla($hsl['h'], $hsl['s'], $hsl['l'], $hsl['a']);
	}

	public static function desaturate($color, $amount){
		$hsl = $color->toHSL();

		$hsl['s'] -= $amount->value / 100;
		$hsl['s'] = self::clamp($hsl['s']);

		return Less_Environment::hsla($hsl['h'], $hsl['s'], $hsl['l'], $hsl['a']);
	}



	public static function lighten($color, $amount){
		$hsl = $color->toHSL();

		$hsl['l'] += $amount->value / 100;
		$hsl['l'] = self::clamp($hsl['l']);

		return Less_Environment::hsla($hsl['h'], $hsl['s'], $hsl['l'], $hsl['a']);
	}

	public static function darken($color, $amount){

		if( $color instanceof Less_Tree_Color ){
			$hsl = $color->toHSL();

			$hsl['l'] -= $amount->value / 100;
			$hsl['l'] = self::clamp($hsl['l']);

			return Less_Environment::hsla($hsl['h'], $hsl['s'], $hsl['l'], $hsl['a']);
		}

		Less_Environment::Expected('color',$color);
	}

	public static function fadein($color, $amount){
		$hsl = $color->toHSL();
		$hsl['a'] += $amount->value / 100;
		$hsl['a'] = self::clamp($hsl['a']);
		return Less_Environment::hsla($hsl['h'], $hsl['s'], $hsl['l'], $hsl['a']);
	}

	public static function fadeout($color, $amount){
		$hsl = $color->toHSL();
		$hsl['a'] -= $amount->value / 100;
		$hsl['a'] = self::clamp($hsl['a']);
		return Less_Environment::hsla($hsl['h'], $hsl['s'], $hsl['l'], $hsl['a']);
	}

	public static function fade($color, $amount){
		$hsl = $color->toHSL();

		if ($amount->unit == '%') {
			$hsl['a'] = $amount->value / 100;
		} else {
			$hsl['a'] = $amount->value;
		}
		$hsl['a'] = self::clamp($hsl['a']);

		return Less_Environment::hsla($hsl['h'], $hsl['s'], $hsl['l'], $hsl['a']);
	}



	public static function spin($color, $amount){
		$hsl = $color->toHSL();
		$hue = fmod($hsl['h'] + $amount->value, 360);

		$hsl['h'] = $hue < 0 ? 360 + $hue : $hue;

		return Less_Environment::hsla($hsl['h'], $hsl['s'], $hsl['l'], $hsl['a']);
	}

	//
	// Copyright (c) 2006-2009 Hampton Catlin, Nathan Weizenbaum, and Chris Eppstein
	// http://sass-lang.com
	//
	public static function mix($color1, $color2, $weight = null){
		if (!$weight) {
			$weight = new Less_Tree_Dimension('50', '%');
		}

		$p = $weight->value / 100.0;
		$w = $p * 2 - 1;
		$hsl1 = $color1->toHSL();
		$hsl2 = $color2->toHSL();
		$a = $hsl1['a'] - $hsl2['a'];

		$w1 = (((($w * $a) == -1) ? $w : ($w + $a) / (1 + $w * $a)) + 1) / 2;
		$w2 = 1 - $w1;

		$rgb = array($color1->rgb[0] * $w1 + $color2->rgb[0] * $w2,
					 $color1->rgb[1] * $w1 + $color2->rgb[1] * $w2,
					 $color1->rgb[2] * $w1 + $color2->rgb[2] * $w2);

		$alpha = $color1->alpha * $p + $color2->alpha * (1 - $p);

		return new Less_Tree_Color($rgb, $alpha);
	}

	public static function greyscale($color){
		return Less_Environment::desaturate($color, new Less_Tree_Dimension(100));
	}


	public static function contrast( $color, $dark = false, $light = false, $threshold = false) {
		// filter: contrast(3.2);
		// should be kept as is, so check for color
		if( !property_exists($color,'rgb') ){
			return null;
		}
		if( $light === false ){
			$light = Less_Environment::rgba(255, 255, 255, 1.0);
		}
		if( $dark === false ){
			$dark = Less_Environment::rgba(0, 0, 0, 1.0);
		}
		//Figure out which is actually light and dark!
		if( $dark->luma() > $light->luma() ){
			$t = $light;
			$light = $dark;
			$dark = $t;
		}
		if( $threshold === false ){
			$threshold = 0.43;
		} else {
			$threshold = Less_Environment::number($threshold);
		}

		if( ($color->luma() * $color->alpha) < $threshold ){
			return $light;
		} else {
			return $dark;
		}
	}

	public static function e ($str){
		return new Less_Tree_Anonymous($str instanceof Less_Tree_JavaScript ? $str->evaluated : $str);
	}

	public static function escape ($str){
		return new Less_Tree_Anonymous(urlencode($str->value));
	}


	public static function _percent(){
		$numargs = func_num_args();
		$quoted = func_get_arg(0);

		$args = func_get_args();
		array_shift($args);
		$str = $quoted->value;

		foreach($args as $arg){
			if( preg_match('/%[sda]/i',$str, $token) ){
				$token = $token[0];
				$value = stristr($token, 's') ? $arg->value : $arg->toCSS();
				$value = preg_match('/[A-Z]$/', $token) ? urlencode($value) : $value;
				$str = preg_replace('/%[sda]/i',$value, $str, 1);
			}
		}
		$str = str_replace('%%', '%', $str);

		return new Less_Tree_Quoted('"' . $str . '"', $str);
	}

	public static function unit($val, $unit = null ){
		return new Less_Tree_Dimension($val->value, $unit ? $unit->toCSS() : "");
	}

	public static function convert($val, $unit){
		return $val->convertTo($unit->value);
	}

	public static function round($n, $f = false) {

		$fraction = 0;
		if( $f !== false ){
			$fraction = $f->value;
		}

		return Less_Environment::_math('round',null, $n, $fraction);
	}

	public static function pi(){
		return new Less_Tree_Dimension(M_PI);
	}

	public static function mod($a, $b) {
		return new Less_Tree_Dimension( $a->value % $b->value, $a->unit);
	}



	public static function pow($x, $y) {
		if( is_numeric($x) && is_numeric($y) ){
			$x = new Less_Tree_Dimension($x);
			$y = new Less_Tree_Dimension($y);
		}elseif( !($x instanceof Less_Tree_Dimension) || !($y instanceof Less_Tree_Dimension) ){
			throw new Less_CompilerException('Arguments must be numbers');
		}

		return new Less_Tree_Dimension( pow($x->value, $y->value), $x->unit );
	}

	// var mathFunctions = [{name:"ce ...
	public static function ceil( $n ){		return Less_Environment::_math('ceil', null, $n); }
	public static function floor( $n ){	return Less_Environment::_math('floor', null, $n); }
	public static function sqrt( $n ){		return Less_Environment::_math('sqrt', null, $n); }
	public static function abs( $n ){		return Less_Environment::_math('abs', null, $n); }

	public static function tan( $n ){		return Less_Environment::_math('tan', '', $n);	}
	public static function sin( $n ){		return Less_Environment::_math('sin', '', $n);	}
	public static function cos( $n ){		return Less_Environment::_math('cos', '', $n);	}

	public static function atan( $n ){		return Less_Environment::_math('atan', 'rad', $n);	}
	public static function asin( $n ){		return Less_Environment::_math('asin', 'rad', $n);	}
	public static function acos( $n ){		return Less_Environment::_math('acos', 'rad', $n);	}

	private static function _math() {
		$args = func_get_args();
		$fn = array_shift($args);
		$unit = array_shift($args);

		if ($args[0] instanceof Less_Tree_Dimension) {

			if( $unit === null ){
				$unit = $args[0]->unit;
			}else{
				$args[0] = $args[0]->unify();
			}
			$args[0] = (float)$args[0]->value;
			return new Less_Tree_Dimension( call_user_func_array($fn, $args), $unit);
		} else if (is_numeric($args[0])) {
			return call_user_func_array($fn,$args);
		} else {
			throw new Less_CompilerException("math functions take numbers as parameters");
		}
	}

	public static function argb($color) {
		return new Less_Tree_Anonymous($color->toARGB());
	}

	public static function percentage($n) {
		return new Less_Tree_Dimension($n->value * 100, '%');
	}

	public static function color($n) {
		if ($n instanceof Less_Tree_Quoted) {
			return new Less_Tree_Color(substr($n->value, 1));
		} else {
			throw new Less_CompilerException("Argument must be a string");
		}
	}


	public static function iscolor($n) {
		return Less_Environment::_isa($n, 'Less_Tree_Color');
	}

	public static function isnumber($n) {
		return Less_Environment::_isa($n, 'Less_Tree_Dimension');
	}

	public static function isstring($n) {
		return Less_Environment::_isa($n, 'Less_Tree_Quoted');
	}

	public static function iskeyword($n) {
		return Less_Environment::_isa($n, 'Less_Tree_Keyword');
	}

	public static function isurl($n) {
		return Less_Environment::_isa($n, 'Less_Tree_Url');
	}

	public static function ispixel($n) {
		return Less_Environment::isunit($n, 'px');
	}

	public static function ispercentage($n) {
		return Less_Environment::isunit($n, '%');
	}

	public static function isem($n) {
		return Less_Environment::isunit($n, 'em');
	}

	public static function isunit( $n, $unit ){
		return ($n instanceof Less_Tree_Dimension) && $n->unit->is( ( property_exists($unit,'value') ? $unit->value : $unit) ) ? new Less_Tree_Keyword('true') : new Less_Tree_Keyword('false');
	}

	private static function _isa($n, $type) {
		return is_a($n, $type) ? new Less_Tree_Keyword('true') : new Less_Tree_Keyword('false');
	}

	/* Blending modes */

	public static function multiply($color1, $color2) {
		$r = $color1->rgb[0] * $color2->rgb[0] / 255;
		$g = $color1->rgb[1] * $color2->rgb[1] / 255;
		$b = $color1->rgb[2] * $color2->rgb[2] / 255;
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function screen($color1, $color2) {
		$r = 255 - (255 - $color1->rgb[0]) * (255 - $color2->rgb[0]) / 255;
		$g = 255 - (255 - $color1->rgb[1]) * (255 - $color2->rgb[1]) / 255;
		$b = 255 - (255 - $color1->rgb[2]) * (255 - $color2->rgb[2]) / 255;
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function overlay($color1, $color2) {
		$r = $color1->rgb[0] < 128 ? 2 * $color1->rgb[0] * $color2->rgb[0] / 255 : 255 - 2 * (255 - $color1->rgb[0]) * (255 - $color2->rgb[0]) / 255;
		$g = $color1->rgb[1] < 128 ? 2 * $color1->rgb[1] * $color2->rgb[1] / 255 : 255 - 2 * (255 - $color1->rgb[1]) * (255 - $color2->rgb[1]) / 255;
		$b = $color1->rgb[2] < 128 ? 2 * $color1->rgb[2] * $color2->rgb[2] / 255 : 255 - 2 * (255 - $color1->rgb[2]) * (255 - $color2->rgb[2]) / 255;
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function softlight($color1, $color2) {
		$t = $color2->rgb[0] * $color1->rgb[0] / 255;
		$r = $t + $color1->rgb[0] * (255 - (255 - $color1->rgb[0]) * (255 - $color2->rgb[0]) / 255 - $t) / 255;
		$t = $color2->rgb[1] * $color1->rgb[1] / 255;
		$g = $t + $color1->rgb[1] * (255 - (255 - $color1->rgb[1]) * (255 - $color2->rgb[1]) / 255 - $t) / 255;
		$t = $color2->rgb[2] * $color1->rgb[2] / 255;
		$b = $t + $color1->rgb[2] * (255 - (255 - $color1->rgb[2]) * (255 - $color2->rgb[2]) / 255 - $t) / 255;
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function hardlight($color1, $color2) {
		$r = $color2->rgb[0] < 128 ? 2 * $color2->rgb[0] * $color1->rgb[0] / 255 : 255 - 2 * (255 - $color2->rgb[0]) * (255 - $color1->rgb[0]) / 255;
		$g = $color2->rgb[1] < 128 ? 2 * $color2->rgb[1] * $color1->rgb[1] / 255 : 255 - 2 * (255 - $color2->rgb[1]) * (255 - $color1->rgb[1]) / 255;
		$b = $color2->rgb[2] < 128 ? 2 * $color2->rgb[2] * $color1->rgb[2] / 255 : 255 - 2 * (255 - $color2->rgb[2]) * (255 - $color1->rgb[2]) / 255;
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function difference($color1, $color2) {
		$r = abs($color1->rgb[0] - $color2->rgb[0]);
		$g = abs($color1->rgb[1] - $color2->rgb[1]);
		$b = abs($color1->rgb[2] - $color2->rgb[2]);
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function exclusion($color1, $color2) {
		$r = $color1->rgb[0] + $color2->rgb[0] * (255 - $color1->rgb[0] - $color1->rgb[0]) / 255;
		$g = $color1->rgb[1] + $color2->rgb[1] * (255 - $color1->rgb[1] - $color1->rgb[1]) / 255;
		$b = $color1->rgb[2] + $color2->rgb[2] * (255 - $color1->rgb[2] - $color1->rgb[2]) / 255;
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function average($color1, $color2) {
		$r = ($color1->rgb[0] + $color2->rgb[0]) / 2;
		$g = ($color1->rgb[1] + $color2->rgb[1]) / 2;
		$b = ($color1->rgb[2] + $color2->rgb[2]) / 2;
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function negation($color1, $color2) {
		$r = 255 - abs(255 - $color2->rgb[0] - $color1->rgb[0]);
		$g = 255 - abs(255 - $color2->rgb[1] - $color1->rgb[1]);
		$b = 255 - abs(255 - $color2->rgb[2] - $color1->rgb[2]);
		return Less_Environment::rgb($r, $g, $b);
	}

	public static function tint($color, $amount) {
		return Less_Environment::mix( Less_Environment::rgb(255,255,255), $color, $amount);
	}

	public static function shade($color, $amount) {
		return Less_Environment::mix(Less_Environment::rgb(0, 0, 0), $color, $amount);
	}

	public static function extract($values, $index ) {
		$index = $index->value - 1; // (1-based index)
		return $values->value[$index];
	}

	function datauri($mimetypeNode, $filePathNode = null ) {

		$filePath = ( $filePathNode ? $filePathNode->value : null );
		$mimetype = $mimetypeNode->value;
		$useBase64 = false;

		$args = 2;
		if( !$filePath ){
			$filePath = $mimetype;
			$args = 1;
		}

		$filePath = str_replace('\\','/',$filePath);
		if( Less_Environment::isPathRelative($filePath) ){
			if( $this->relativeUrls ){
				$filePath = Less_Environment::NormPath(rtrim($this->currentFileInfo['currentDirectory'],'/').'/'.$filePath);
			} else {
				$filePath = Less_Environment::NormPath(rtrim($this->currentFileInfo['entryPath'],'/').'/'.$filePath);
			}
		}


		// detect the mimetype if not given
		if( $args < 2 ){

			/* incomplete
			$mime = require('mime');
			mimetype = mime.lookup(path);

			// use base 64 unless it's an ASCII or UTF-8 format
			var charset = mime.charsets.lookup(mimetype);
			useBase64 = ['US-ASCII', 'UTF-8'].indexOf(charset) < 0;
			if (useBase64) mimetype += ';base64';
			*/

			$mimetype = Less_Mime::lookup($filePath);

			$charset = Less_Mime::charsets_lookup($mimetype);
			$useBase64 = !in_array($charset,array('US-ASCII', 'UTF-8'));
			if ($useBase64) $mimetype .= ';base64';

		}else{
			$useBase64 = preg_match('/;base64$/',$mimetype);
		}

		if( file_exists($filePath) ){
			$buf = @file_get_contents($filePath);
		}else{
			$buf = false;
		}


		// IE8 cannot handle a data-uri larger than 32KB. If this is exceeded
		// and the --ieCompat flag is enabled, return a normal url() instead.
		$DATA_URI_MAX_KB = 32;
		$fileSizeInKB = round( strlen($buf) / 1024 );
		if( $fileSizeInKB >= $DATA_URI_MAX_KB ){
			$url = new Less_Tree_Url( ($filePathNode ? $filePathNode : $mimetypeNode), $this->currentFileInfo);
			return $url->compile($this);
		}

		if( $buf ){
			$buf = $useBase64 ? base64_encode($buf) : rawurlencode($buf);
			$filePath = "'data:" . $mimetype . ',' . $buf . "'";
		}

		return new Less_Tree_Url( new Less_Tree_Anonymous($filePath) );
	}


	private static function Expected( $type, $arg ){

		$debug = debug_backtrace();
		array_shift($debug);
		$last = array_shift($debug);
		$last = array_intersect_key($last,array('function'=>'','class'=>'','line'=>''));

		$message = 'Object of type '.get_class($arg).' passed to darken function. Expecting `Color`. '.$arg->toCSS().'. '.print_r($last,true);
		throw new Less_CompilerException($message);

	}


	/**
	 * Canonicalize a path by resolving references to '/./', '/../'
	 * Does not remove leading "../"
	 * @param string path or url
	 * @return string Canonicalized path
	 *
	 */
	static function NormPath($path){

		$temp = explode('/',$path);
		$result = array();
		foreach($temp as $i => $p){
			if( $p == '.' ){
				continue;
			}
			if( $p == '..' ){
				for($j=$i-1;$j>0;$j--){
					if( isset($result[$j]) ){
						unset($result[$j]);
						continue 2;
					}
				}
			}
			$result[$i] = $p;
		}

		return implode('/',$result);
	}

}
