<?php
/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         https://github.com/t3framework/ 
 *------------------------------------------------------------------------------
 */

// No direct access
defined('_JEXEC') or die();
t3import ('core/template');
jimport('joomla.utilities.utility');

/**
 * T3Template class provides extended template tools used for T3 framework
 *
 * @package T3
 */
class T3TemplateLayout extends T3Template
{
	protected $_block = null;

	/**
	 * Class constructor
	 *
	 * @param   object  $template Current template instance
	 */
	public function __construct($template = null)
	{
		parent::__construct($template);
		$this->setParam('responsive', 0);
		$this->setParam('devmode', 0);
	}

	/**
	* Get current layout tpls
	*
	* @return string Layout name
	*/
	public function getLayout () {
		return JFactory::getApplication()->input->getCmd ('t3layout', $this->_tpl->params->get('mainlayout'));
	}
  
    function countModules($positions)
    {
    	return 1;
    }

    function checkSpotlight($name, $positions){
    	return 1;
    }
  
	/**
	* Load block content
	*
	* @param $block string
	*     Block name - the real block is tpls/blocks/[blockname].php
	*
	* @return string Block content
	*/
	function loadBlock($block, $vars = array())
	{
		if (!$this->_block){
			$this->_block = $block;
		}

		$path = T3Path::getPath ('tpls/system/'.$block.'.php');
		if(!$path){
			$path = T3Path::getPath ('tpls/blocks/'.$block.'.php');
		}
		
		ob_start();
		if ($path) {
			include $path;
		} else {
			echo "<div class=\"error\">Block [$block] not found!</div>";
		}
		$content = ob_get_contents();
		ob_end_clean();

		if (isset($vars['spl'])) {
			$content = preg_replace ('#(<[A-Za-z]+[^>^\/]*)>#', '\1 data-original="' . $block . '"' . (isset($vars['spl']) ? ' data-spotlight="' . $vars['name'] . '"' : '') . '>', $content, 1);
			$this->_block = null;
		}
		
		echo isset($vars['spl']) ? $content : ("<div class=\"t3-admin-layout-section\">".$content."</div>");
	} 

	/**
	* Load block content
	*
	* @param $block string
	*     Block name - the real block is tpls/blocks/[blockname].php
	*
	* @return string Block content
	*/
	function loadLayout($layout)
	{
		$path = T3_TEMPLATE_PATH . '/tpls/'.$layout.'.php';
		if (!is_file ($path)) {
			$path = T3_TEMPLATE_PATH . '/tpls/default.php';
		}

		if (is_file ($path)) {
			// include $path;
			$html = $this->loadFile ($path);

			// parse and replace jdoc
			$html = $this->_parse ($html);
			echo $html;
		} else {
			echo "<div class=\"error\">Layout [$layout] or [Default] not found!</div>";
		}
	}

	/**
	* Generate a spotlight block
	*
	* @param 
	*	$name: string
	*	 		Name of spotlight - identity, ex: 'spotlight-1'
	*	$positions: string
	*			default positions, ex: 'positon-1, position-2'
	*	$info: array
	*			options for spotlight and for every position
	*			ex: array(
	*				'row-fluid' => 1,
	*				'position-1' => array(
	*					'normal' => 'span3 special',
	*					'wide' => 'span3 hidden'
	*					),
	*				'position-2' => array(...)
	*			)
	*/
	function spotlight($name, $positions, $info = array())
	{
		$vars = is_array($info) ? $info : array();
		$defpos = $poss = preg_split('/\s*,\s*/', $positions);
		$cols = $defnumpos = count($defpos);

		$splparams = array();
		for($i = 1; $i <= self::$maxcolumns; $i++){
			$param = $this->getLayoutSetting('block'.$i.'@'.$name);
			if(empty($param)){
				break;
			} else {
				$splparams[] = $param;
			}
		}

		//we have data - configuration saved
		if(!empty($splparams)){
			$poss = array();
			foreach ($splparams as $i => $splparam) {
				$param = (object)$splparam;
				$poss[] = isset($param->position) ? $param->position : $defpos[$i];
			}

			$cols = count($poss);
		} else {
			foreach ($poss as $i => $pos) {
				$splparams[$i] = '';
			}
		}

		// check if there's any modules
		if (!$this->countModules (implode (' or ', $poss))){
			return;
		}

		$original = implode(',', $defpos);
		
		$inits = array(); 
		foreach ($defpos as $i => $dpos) {
			$inits[$i] = $this->parseInfo(isset($vars[$dpos]) ? $vars[$dpos] : '');
		}
		
		$infos = array();
		foreach($splparams as $i => $splparam){
			$infos[$i] = !empty($splparam) ? $this->parseInfo($splparam) : $inits[$i];
		}

		$defwidths = $this->extractKey($inits, 'width');
		$deffirsts = $this->extractKey($inits, 'first');

		$widths = $this->extractKey($infos, 'width');
		$firsts = $this->extractKey($infos, 'first');
		$others = $this->extractKey($infos, 'others');

		//optimize default width if needed
		$this->optimizeWidth($defwidths, $defnumpos);
		$this->optimizeWidth($widths, $defnumpos);

		$visibility = array(
			'name' => $name,
			'vals' => $this->extractKey($infos, 'hidden'),
			'deft' => $this->extractKey($inits, 'hidden'),
		);

		$spldata = array(
			' data-original="',	$original, '"',
			' data-vis="', $this->htmlattr($visibility), '"',
			' data-owidths="', $this->htmlattr($defwidths), '"',
			' data-widths="', $this->htmlattr($widths), '"',
			' data-ofirsts="', $this->htmlattr($deffirsts), '"',
			' data-firsts="', $this->htmlattr($firsts), '"',
			' data-others="', $this->htmlattr($others), '"'
			);

		$default = $widths['default'];
		//
		$vars['name'] = $name;
		$vars['poss'] = $poss;
		$vars['spldata'] = implode('', $spldata);
		$vars['default'] = $default;
		$vars['spl'] = 1;

		//normal
		$this->loadBlock ('spotlight', $vars);
	}

	function mainnav(){
		echo '<jdoc:include type="modules" name="mainnav" style="raw" />';
	}

	function getPosname ($condition) {
		return parent::getPosname ($condition) . '" data-original="'.$condition;
	}

	function _c ($name, $cls = array()) {
		
		$posparams = $this->getLayoutSetting($name, '');

		$cinfo = $oinfo = $this->parseInfo($cls);
		if(!empty($posparams)){
			$cinfo = $this->parseInfo($posparams);
		}

		$visible = array(
			'name' => $name,
			'vals' => $this->extractKey(array($cinfo), 'hidden'),
			'deft' => $this->extractKey(array($oinfo), 'hidden')
		);

		echo '" data-vis="' . $this->htmlattr($visible) . '" data-others="' . $this->htmlattr($this->extractKey(array($oinfo), 'others'));
	}

	protected function _parse($html) {
		$html = preg_replace_callback('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', array($this, '_parseJDoc'), $html);
		return $html;		
	}

	protected function _parseJDoc ($matches) {
		$type = $matches[1];
		if ($type == 'head') {
			return $matches[0];
		} 
		$attribs = empty($matches[2]) ? array() : JUtility::parseAttributes($matches[2]);
		$attribs['type'] = $type;
		if (!isset($attribs['name'])){
			$attribs['name'] = $attribs['type'];
		}

		$tp = 'tpls/system/tp.php';
		$path = '';
		if (is_file (T3_TEMPLATE_PATH . '/' . $tp)) {
			$path = T3_TEMPLATE_PATH . '/' . $tp;
		} else if (is_file (T3_PATH . '/' . $tp)) {
			$path = T3_PATH . '/' . $tp;
		}

		return $this->loadFile ($path, $attribs);
	}

	function loadFile ($path, $vars = array()) {
		ob_start();
		include $path;
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	* Add T3 basic head 
	*/
	function addHead () {
		//TODO: should we return null here
		//we do not really need a header here

		// BOOTSTRAP CSS
		$this->addCss ('bootstrap', false); 
		$this->addCss ('t3-admin-layout-preview', false); 

		// Add scripts
		$this->addScript (T3_URL.'/bootstrap/js/jquery.js');
		$this->addScript (T3_URL.'/bootstrap/js/bootstrap.js');
	}

	function megamenu($menutype){
		echo "<div class='t3-admin-layout-pos block-nav t3-admin-layout-uneditable'> <h3>Megamenu [$menutype]</h3></div>";
	}

	/**
	*  Parse information
	*  @var 
	*  posinfo should be an object in setting file
	*  $posinfo = array(
	*		'normal' => 'span4 spanfirst'
	*		'wide' => 'span3 spanfirst'
	*		'mobile' => 'span50 hidden'
	*	)
	**/
	function parseInfo($posinfo = array()){
		
		//convert to array
		if(empty($posinfo)){
			$posinfo = array();
		} else {
			$posinfo = is_array($posinfo) ? $posinfo : get_object_vars($posinfo);
		}

		//$posinfo = array(
		//	'normal' => "span4 spanfirst pull-right hidden otherclass",
		//	'wide' => "span4 spanfirst pull-right hidden offset4"	
		//)
		
		$result = array(
			'default' => array(),
			'normal' => array(),
			'wide' => array(),
			'xtablet' => array(),
			'tablet' => array(),
			'mobile' => array()
		);

		$defcls = isset($posinfo['default']) ? $posinfo['default'] : '';

		foreach ($result as $device => &$info) {
			//class presentation string
			$cls = isset($posinfo[$device]) ? $posinfo[$device] : '';

			//extend other device
			if(!empty($defcls) && $device != 'default'){
				$cls = $this->addclass($cls, $defcls);
			}

			//if isset
			if(!empty($cls)){
				//check if this position is hidden
				$hidden = $this->hasclass($cls, 'hidden');
				if($hidden){
					$cls = $this->removeclass($cls, 'hidden');
				}

				//check if this position is first position
				$first = $this->hasclass($cls, 'spanfirst');
				if($first){
					$cls = $this->removeclass($cls, 'spanfirst');
				}

				//check for width of this position
				$width = preg_replace('/(.*?)span(\d+)(.*)/', '$2', $cls);
				if(intval($width) > 0){
					$width = $this->convertWidth($width, $device);
				} else {
					$width = self::$maxgrid;
				}

				//other class
				$others = trim(preg_replace('/(\s*)span(\d+)(\s*)/', ' ', $cls));
			} else {
				$hidden = 0;
				$first = 0;
				$width = $device == 'mobile' ? self::$maxgrid : 0;
				$others = '';
			}

			$info['hidden'] = $hidden;
			$info['first'] = $first;
			$info['width'] = $width;
			$info['others'] = $others;
		}

		return $result;
	}

	/**
	*  Extract a value key from object
	**/
	function extractKey($infos, $key){
		//$info = array(
		//	[0] => array(
		//		'normal' => array(
		//			'hiddden' => 0
		//			'first' => 0
		//			'width' => 2
		//			'others' => ''
		//			),
		//		'wide' => array(
		//			'hiddden' => 0
		//			'first' => 0
		//			'width' => 2
		//			'others' => ''
		//			),
		//		...
		//		),
		//
		//	[1] => array(
		//		'normal' => array(
		//			'hiddden' => 0
		//			'first' => 0
		//			'width' => 2
		//			'others' => ''
		//			)
		//		)
		//	),
		//  ...

		$result = array('default' => array(), 'wide' => array(), 'normal' => array(), 'xtablet' => array(), 'tablet' => array(), 'mobile' => array());

		foreach ($infos as $i => $devices) {
			foreach ($devices as $device => $info) {
				$result[$device][$i] = $info[$key];
			}
		}

		return $result;
	}

	/**
	*  Optimize width of a spotlight
	*   - we try to fit all position of a spotlight to one row
	* 	$widths = array(
	*		'normal' => array(3,3,3,3),
	*		'wide' => array(1,2,3,4)
	*	)
	**/
	function optimizeWidth(&$widths, $newcols = false){
		foreach ($widths as $device => &$width) {
			if(array_sum($width) < self::$maxgrid || $width[0] == 0){ //test if default empty width
				$widths[$device] = $this->genWidth($device, $newcols ? $newcols : count($width));
			}
		}
	}

	/**
	*  Convert width of mobile - mobile have special width number
	**/
	function convertWidth($width, $device){
		//convert back - width of mobile should be [33%,] 50% and 100%
		//there might be some case when we enter the width of other device ( < 12) => return 100% (12)
		return $device == 'mobile' ? ($width < 12 ? 12 : floor($width / 100 * 12)) : $width;
	}

	/**
	*  Utility function - check if a HTML class is exist in a HTML class list 
	**/
	function hasclass($clsname, $cls){
		return intval(strpos(' ' . $clsname . ' ', ' ' . $cls . ' ') !== false);
	}

	/**
	*  Utility function - remove a HTML class in a HTML class list 
	**/
	function removeclass($clsname, $cls){
		return preg_replace('/(^|\s)' . $cls . '(?:\s|$)/', '$1', $clsname);
	}

	/**
	*  Utility function - remove a HTML class in a HTML class list 
	**/
	function addclass($clsname, $cls){
		$haswidth = preg_match('/(.*?)span(\d+)(.*)/', $clsname);
		if($haswidth){
			$cls = trim(preg_replace('/(\s*)span(\d+)(\s*)/', ' ', $cls));
		}

		$cls = explode(' ', $cls);

		foreach ($cls as $cl) {
			if(!$this->hasclass($clsname, $cl)){
				$clsname .= ' ' . $cl;
			}
		}

		return implode(' ', array_unique(explode(' ', $clsname)));
	}

	/**
	*  Utility function - embed json to HTML attritube
	**/
	function htmlattr($obj){
		return htmlentities(json_encode($obj), ENT_QUOTES);
	}
}
?>