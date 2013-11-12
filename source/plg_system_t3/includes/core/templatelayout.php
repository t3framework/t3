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
 * @Link:         http://t3-framework.org
 *------------------------------------------------------------------------------
 */

// No direct access
defined('_JEXEC') or die();
T3::import('core/template');
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
	 * @param  object  $template  Current template instance
	 */
	public function __construct($template = null)
	{
		parent::__construct($template);
		if(!$this->responcls){
			$this->setParam('responsive', 0);
		}
		$this->setParam('devmode', 0);
	}

	/**
	 * Get current layout tpls
	 *
	 * @return  string  Layout name
	 */
	public function getLayout()
	{
		return JFactory::getApplication()->input->getCmd('t3layout', $this->_tpl->params->get('mainlayout'));
	}

	/**
	 * Check a module condition is true or not
	 * @param string $positions
	 * @return  true  always return true
	 */
	function countModules($positions)
	{
		return 1;
	}

	/**
	 * Check for a spotlight if it can be render or not
	 * @param   string  $name       spotlight name
	 * @param   string  $positions  default position values
	 *
	 * @return  true    always return true
	 */
	function checkSpotlight($name, $positions)
	{
		return 1;
	}

	
	/**
	 * Check for the message queue
	 *
	 * @return  true    always return true
	 */
	function hasMessage(){
		return 1;
	}

	/**
	 * Load block content
	 *
	 * @param $block string block name - the real block is tpls/blocks/[blockname].php
	 * @param $vars  array  information of block (used in template layout)
	 *
	 * @return string Block content
	 */
	function loadBlock($block, $vars = array())
	{
		if (!$this->_block) {
			$this->_block = $block;
		}

		$path = T3Path::getPath('tpls/system/' . $block . '.php');
		if (!$path) {
			$path = T3Path::getPath('tpls/blocks/' . $block . '.php');
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
			$content = preg_replace('#(<[A-Za-z]+[^>^\/]*)>#', '\1 data-original="' . $block . '"' . (isset($vars['spl']) ? ' data-spotlight="' . $vars['name'] . '"' : '') . '>', $content, 1);
			$this->_block = null;
		}

		echo isset($vars['spl']) ? $content : ('<div class="t3-admin-layout-section">' . $content . '</div>');
	}

	/**
	 * Load layout content
	 * @param $layout string  Block name - the real block is tpls/blocks/[blockname].php
	 *
	 * @return none
	 */
	function loadLayout($layout)
	{
		$path = T3_TEMPLATE_PATH . '/tpls/' . $layout . '.php';
		if (!is_file($path)) {
			$path = T3_TEMPLATE_PATH . '/tpls/default.php';
		}

		if (is_file($path)) {
			// include $path;
			$html = $this->loadFile($path);

			// parse and replace jdoc
			$html = $this->_parse($html);
			echo $html;
		} else {
			echo "<div class=\"error\">Layout [$layout] or [Default] not found!</div>";
		}
	}

	/**
	 * Generate a spotlight block
	 *
	 * @param  $name  string  Name of spotlight - identity, ex: 'spotlight-1'
	 * @param  $positions string default positions, ex: 'positon-1, position-2'
	 * @param  $info array
	 *            options for spotlight and for every position
	 *            ex: array(
	 *                'row-fluid' => 1,
	 *                'position-1' => array(
	 *                    '[dv1]' => 'span3 special',
	 *                    '[dv2]' => 'span3 hidden'
	 *                    ),
	 *                'position-2' => array(...)
	 *            )
	 * @return  none  render spotlight block
	 */
	function spotlight($name, $positions, $info = array())
	{
		$vars = is_array($info) ? $info : array();
		$defpos = $poss = preg_split('/\s*,\s*/', $positions);
		$defnumpos = count($defpos);

		$splparams = array();
		for ($i = 1; $i <= $this->maxgrid; $i++) {
			$param = $this->getLayoutSetting('block' . $i . '@' . $name);
			if (empty($param)) {
				break;
			} else {
				$splparams[] = $param;
			}
		}

		//we have data - configuration saved
		if (!empty($splparams)) {
			$poss = array();
			foreach ($splparams as $i => $splparam) {
				$param = (object)$splparam;
				$poss[] = isset($param->position) ? $param->position : $defpos[$i];
			}

		} else {
			foreach ($poss as $i => $pos) {
				$splparams[$i] = '';
			}
		}

		$original = implode(',', $defpos);

		$inits = array();
		foreach ($defpos as $i => $dpos) {
			$inits[$i] = $this->parseInfo(isset($vars[$dpos]) ? $vars[$dpos] : '');
		}

		$infos = array();
		foreach ($splparams as $i => $splparam) {
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
			' data-original="', $original, '"',
			' data-vis="', $this->htmlattr($visibility), '"',
			' data-owidths="', $this->htmlattr($defwidths), '"',
			' data-widths="', $this->htmlattr($widths), '"',
			' data-ofirsts="', $this->htmlattr($deffirsts), '"',
			' data-firsts="', $this->htmlattr($firsts), '"',
			' data-others="', $this->htmlattr($others), '"'
		);

		$default = $widths[$this->defdv];
		//
		$vars['name'] = $name;
		$vars['poss'] = $poss;
		$vars['spldata'] = implode('', $spldata);
		$vars['default'] = $default;
		$vars['spl'] = 1;

		//normal
		$this->loadBlock('spotlight', $vars);
	}

	/**
	 * Render mainnav block (joomla default navigation)
	 */
	function mainnav()
	{
		echo '<jdoc:include type="modules" name="mainnav" style="raw" />';
	}

	/**
	 * Render position name
	 * @param   string  $condition
	 * @return  string  the position value
	 */
	function getPosname($condition)
	{
		return parent::getPosname($condition) . '" data-original="' . $condition;
	}


	/**
	 * Add additional class and parse for visibility of block
	 * @param   string  $name
	 * @param   array   $cls
	 * @return  null|void
	 */
	function _c($name, $cls = array())
	{
		$posparams = $this->getLayoutSetting($name, '');

		$cinfo = $oinfo = $this->parseVisibility(is_string($cls) ? array($this->defdv => $cls) : (is_array($cls) ? $cls : array()));
		if (!empty($posparams)) {
			$cinfo = $this->parseVisibility($posparams);
		}

		$data = '';
		$visible = array(
			'name' => $name,
			'vals' => $this->extractKey(array($cinfo), 'hidden'),
			'deft' => $this->extractKey(array($oinfo), 'hidden')
		);

		if (empty($posparams)) {
			if (is_string($cls)) {
				$data = ' ' . $cls;
			}
		}

		//remove hidden class
		$data = preg_replace('@("|\s)?'. preg_quote(T3_BASE_HIDDEN_PATTERN) .'(\s|")?@iU', '$1$2', $data);

		echo $data . '" data-vis="' . $this->htmlattr($visible) . '" data-others="' . $this->htmlattr($this->extractKey(array($oinfo), 'others'));
	}

	/**
	 * Internal function, use to parse layout blocks
	 * @param   string  $html  html markup string
	 * @return  string  mixed  layout markup
	 */
	protected function _parse($html)
	{
		$html = preg_replace_callback('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', array($this, '_parseJDoc'), $html);
		return $html;
	}

	/**
	 * Parse each <jdoc /> and return the corresponding content
	 * @param   $matches  <jdoc /> infomation
	 * @return  string    block markup
	 */
	protected function _parseJDoc($matches)
	{
		$type = $matches[1];
		if ($type == 'head') {
			return $matches[0];
		}
		$attribs = empty($matches[2]) ? array() : JUtility::parseAttributes($matches[2]);
		$attribs['type'] = $type;
		if (!isset($attribs['name'])) {
			$attribs['name'] = $attribs['type'];
		}

		$tp = 'tpls/system/tp.php';
		$path = '';
		if (is_file(T3_TEMPLATE_PATH . '/' . $tp)) {
			$path = T3_TEMPLATE_PATH . '/' . $tp;
		} else if (is_file(T3_PATH . '/' . $tp)) {
			$path = T3_PATH . '/' . $tp;
		}

		return $this->loadFile($path, $attribs);
	}

	/**
	 * Render a file in memory
	 * @param   string  $path  file path to render
	 * @param   array   $vars  additional information
	 * @return  string  the renderred content
	 */
	function loadFile($path, $vars = array())
	{
		ob_start();
		include $path;
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Add T3 basic head
	 */
	function addHead()
	{
		//TODO: should we return null here
		//we do not really need a header here

		// BOOTSTRAP CSS
		//$this->addCss ('bootstrap', false); 
		//$this->addCss ('t3-admin-layout-preview', false); 

		// Add scripts
		//$this->addScript (T3_URL.'/bootstrap/js/jquery.js');
		//$this->addScript (T3_URL.'/bootstrap/js/bootstrap.js');
	}

	/**
	 * Render dummy megamenu block in layout
	 * @param string $menutype
	 */
	function megamenu($menutype)
	{
		echo "<div class='t3-admin-layout-pos block-nav t3-admin-layout-uneditable'> <h3>Megamenu [$menutype]</h3></div>";
	}

	/**
	 * Parse information
	 * @param  $posinfo  array  should be an object in setting file
	 *         $posinfo = array(
	 *            '[dv1]' => 'col-lg-3',
	 *            '[dv2]' => 'col-md-4',
	 *            '[dv3]' => 'col-xs-6 hidden'
	 *         )
	 * @return  array  positions information
	 */
	function parseInfo($posinfo = array())
	{
		//convert to array
		if (empty($posinfo)) {
			$posinfo = array();
		} else {
			$posinfo = is_array($posinfo) ? $posinfo : get_object_vars($posinfo);
		}

		// init empty result
		$result = array();
		foreach ($this->devices as $device) {
			$result[$device] = array();
		}

		$defcls = !$this->responcls && isset($posinfo[$this->defdv]) ? $posinfo[$this->defdv] : '';

		foreach ($result as $device => &$info) {
			//class presentation string
			$cls = isset($posinfo[$device]) ? $posinfo[$device] : '';

			//extend other device
			if (!empty($defcls) && $device != $this->defdv) {
				$cls = $this->addclass($cls, $defcls);
			}
			//if isset
			if (!empty($cls)) {
				//check if this position is hidden
				$hidden = T3_BASE_HIDDEN_PATTERN && $this->hasclass($cls, T3_BASE_HIDDEN_PATTERN);
				if ($hidden) {
					$cls = $this->removeclass($cls, T3_BASE_HIDDEN_PATTERN);
				}

				//check if this position is first position
				$first = T3_BASE_FIRST_PATTERN && $this->hasclass($cls, T3_BASE_FIRST_PATTERN);
				if ($first) {
					$cls = $this->removeclass($cls, T3_BASE_FIRST_PATTERN);
				}

				//check for width of this position
				$width = $this->maxgrid;
				if(preg_match($this->spancls, $cls, $match)){
					$width = array_pop(array_filter($match, 'is_numeric'));
					$width = (isset($width[0]) ? $width[0] : $this->maxgrid);
				}

				if (!$this->responcls && intval($width) > 0) {
					$width = $this->convertWidth($width, $device);
				}

				//other class
				$others = trim(preg_replace($this->spancls, ' ', $cls));
			} else {
				$hidden = 0;
				$first = 0;
				$width = 0;
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
	 *  Parse visibility information
	 *  @param  $posinfo  array  should be an object in setting file
	 *          $posinfo = array(
	 *            '[dv1]' => 'col-lg-3',
	 *            '[dv2]' => 'col-md-4',
	 *            '[dv3]' => 'col-xs-6 hidden'
	 *          )
	 *
	 *  We focus on visibility value only, other information will be placed in others
	 *  @return  array  visibility information
	 **/
	function parseVisibility($posinfo = array())
	{

		//convert to array
		if (empty($posinfo)) {
			$posinfo = array();
		} else {
			$posinfo = is_array($posinfo) ? $posinfo : get_object_vars($posinfo);
		}

		// init empty result
		$result = array();
		foreach ($this->devices as $device) {
			$result[$device] = array();
		}

		foreach ($result as $device => &$info) {
			//class presentation string
			$cls = isset($posinfo[$device]) ? $posinfo[$device] : '';

			//if isset
			if (!empty($cls)) {
				//check if this position is hidden
				$hidden = T3_BASE_HIDDEN_PATTERN && $this->hasclass($cls, T3_BASE_HIDDEN_PATTERN);
				if ($hidden) {
					$cls = $this->removeclass($cls, T3_BASE_HIDDEN_PATTERN);
				}

				//other class
				$others = trim($cls);
			} else {
				$hidden = 0;
				$others = '';
			}

			$info['hidden'] = $hidden;
			$info['others'] = $others;
		}

		return $result;
	}

	/**
	 *  Extract a value key from object
	 **/
	function extractKey($infos, $key)
	{
		//$info = array(
		//	[0] => array(
		//		'[dv1]' => array(
		//			'hidden' => 0
		//			'first' => 0
		//			'width' => 2
		//			'others' => ''
		//			),
		//		'[dv2]' => array(
		//			'hidden' => 0
		//			'width' => 2
		//			'others' => ''
		//			),
		//		...
		//		),
		//
		//	[1] => array(
		//		'[dv1]' => array(
		//			'hidden' => 0
		//			'width' => 2
		//			'others' => ''
		//			)
		//		)
		//	),
		//  ...

		// init empty result
		$result = array();
		foreach ($this->devices as $device) {
			$result[$device] = array();
		}

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
	 *    $widths = array(
	 *        '[dv1]' => array(3,3,3,3),
	 *        '[dv2]' => array(1,2,3,4)
	 *    )
	 **/
	function optimizeWidth(&$widths, $newcols = false)
	{
		foreach ($widths as $device => &$width) {
			if (array_sum($width) < $this->maxgrid || $width[0] == 0) { //test if default empty width
				$widths[$device] = $this->genWidth($device, $newcols ? $newcols : count($width));
			}
		}
	}

	/**
	 *  Convert width of mobile - mobile have special width number
	 **/
	function convertWidth($width, $device)
	{
		//convert back - width of mobile should be [33%,] 50% and 100%
		//there might be some case when we enter the width of other device ( < 12) => return 100% (12)
		return $device == 'mobile' ? ($width < 12 ? 12 : floor($width / 100 * 12)) : $width;
	}

	/**
	 *  Utility function - check if a HTML class is exist in a HTML class list
	 **/
	function hasclass($clsname, $cls)
	{
		return intval(strpos(' ' . $clsname . ' ', ' ' . $cls . ' ') !== false);
	}

	/**
	 *  Utility function - remove a HTML class in a HTML class list
	 **/
	function removeclass($clsname, $cls)
	{
		return preg_replace('/(^|\s)' . $cls . '(?:\s|$)/', '$1', $clsname);
	}

	/**
	 *  Utility function - remove a HTML class in a HTML class list
	 *  The result will contains only 1 width class (col-xx-yy)
	 **/
	function addclass($clsname, $cls)
	{
		$haswidth = preg_match($this->spancls, $clsname);
		if ($haswidth) {
			$cls = trim(preg_replace($this->spancls, ' ', $cls));
		}

		$cls = explode(' ', $cls);

		foreach ($cls as $cl) {
			if (!$this->hasclass($clsname, $cl)) {
				$clsname .= ' ' . $cl;
			}
		}

		return implode(' ', array_unique(explode(' ', $clsname)));
	}

	/**
	 * Utility function - embed json to HTML attribute
	 * @param   mixed $obj  Object to encode
	 * @return  string  The escape html string
	 **/
	function htmlattr($obj)
	{
		return htmlentities(json_encode($obj), ENT_QUOTES);
	}
}

?>