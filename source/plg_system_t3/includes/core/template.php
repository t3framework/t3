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

T3::import('extendable/extendable');

/**
 * T3Template class provides extended template tools used for T3 framework
 *
 * @package T3
 */
class T3Template extends ObjectExtendable
{
	/**
	 * Define constants
	 */
	protected static $maxcol = array('default' => 6, 'wide' => 6, 'normal' => 6, 'xtablet' => 4, 'tablet' => 3, 'mobile' => 2);
	protected static $minspan = array('default' => 2, 'wide' => 2, 'normal' => 2, 'xtablet' => 3, 'tablet' => 4, 'mobile' => 6);
	protected static $maxgrid = 12;
	protected static $maxcolumns = 6;


	/**
	 * Current template instance
	 */
	public $_tpl = null;


	/**
	 * Store layout settings if exist
	 */
	protected $_layoutsettings = null;


	/**
	 * page class
	 */
	protected $_pageclass = array();


	/**
	 * Class constructor
	 *
	 * @param   object $template Current template instance
	 */
	public function __construct($template = null)
	{
		$this->_layoutsettings = new JRegistry;

		if ($template) {
			$this->_tpl = $template;
			$this->_extend(array($template));
			// merge layout setting

			$layout = JFactory::getApplication()->input->getCmd('t3layout', '');
			if (empty($layout)) {
				$layout = $template->params->get('mainlayout', 'default');
			}

			$fconfig = JPATH_ROOT . '/templates/' . $template->template . '/etc/layout/' . $layout . '.ini';
			if (is_file($fconfig)) {
				jimport('joomla.filesystem.file');
				$this->_layoutsettings->loadString(JFile::read($fconfig), 'INI', array('processSections' => true));
			}
		}

		JDispatcher::getInstance()->trigger('onT3TplInit', array($this));
	}


	/**
	 * Get template parameter
	 * @param  string  $name     parameter name
	 * @param  mixed   $default  parameter default value
	 *
	 * @return  mixed  parameter value
	 */
	public function getParam($name, $default = null)
	{
		return $this->_tpl->params->get($name, $default);
	}


	/**
	 * Set template parameter. It will not store to database. This should not be used
	 * @param  string  $name   parameter name
	 * @param  mixed   $value  parameter value
	 *
	 * @return  null
	 */
	public function setParam($name, $value)
	{
		return $this->_tpl->params->set($name, $value);
	}


	/**
	 * Get current layout tpls
	 *
	 * @return string Layout name
	 */
	public function getLayout()
	{
		$input = JFactory::getApplication()->input;
		return $input->getCmd('tmpl') ? $input->getCmd('tmpl') : $this->getParam('mainlayout', 'default');
	}


	/**
	 * Get layout settings (Layout Tab)
	 * @param  string  $name     parameter name
	 * @param  mixed   $default  parameter default value
	 *
	 * @return string Layout name
	 */
	public function getLayoutSetting($name, $default = null)
	{
		return isset($this->_layoutsettings) ? $this->_layoutsettings->get($name, $default) : $default;
	}


	/**
	 * Load block content
	 * @param  string  $block  Block name - the real block is tpls/blocks/[block].php
	 * @param  array   $vars   information of block (used in template layout)
	 * @return string Block content
	 */
	function loadBlock($block, $vars = array())
	{
		$path = T3Path::getPath('tpls/blocks/' . $block . '.php');
		if ($path) {
			include $path;
		} else {
			echo "<div class=\"error\">Block [$block] not found!</div>";
		}
	}


	/**
	 * Load block layout
	 *
	 * @param string &layout
	 *     Block name - the real block is tpls/[layout].php
	 *
	 * @return null
	 */
	function loadLayout($layout)
	{
		$path = T3Path::getPath('tpls/' . $layout . '.php', 'tpls/default.php');

		JDispatcher::getInstance()->trigger('onT3LoadLayout', array(&$path, $layout));

		if (is_file($path)) {
			include $path;

			// append modules in debug position
			if ($this->getParam('snippet_debug', 0) && $this->countModules('debug')) {
				$this->getBuffer('modules', 'debug');
			}

		} else {
			echo "<div class=\"error\">Layout [$layout] or [Default] not found!</div>";
		}
	}

	/**
	 * Load spotlight block
	 * @param  string  $name       Name of the spotlight. Default will load positions base on this name: [name]-1, [name]-2...
	 * @param  string  $positions  The positions of spotlight, separated by comma
	 * @param  array   $info       Other information of spotlight
	 *
	 * @return null
	 */
	function spotlight($name, $positions, array $info = array())
	{
		$defpos = preg_split('/\s*,\s*/', $positions);
		$vars = is_array($info) ? $info : array();
		$cols = count($defpos);
		$poss = $defpos;

		$splparams = array();
		for ($i = 1; $i <= self::$maxcolumns; $i++) {
			$param = $this->getLayoutSetting('block' . $i . '@' . $name);
			if (empty($param)) {
				break;
			} else {
				$splparams[] = $param;
			}
		}

		//we have configuration in setting file
		if (!empty($splparams)) {
			$poss = array();
			foreach ($splparams as $idx => $splparam) {
				$param = (object)$splparam;
				$poss[] = isset($param->position) ? $param->position : $defpos[$idx];
			}

			$cols = count($poss);
		}

		// check if there's any modules
		if (!$this->countModules(implode(' or ', $poss))) {
			return;
		}

		//empty - so we will use default configuration
		if (empty($splparams)) {
			//generate a optimize default width
			$default = $this->genWidth('default', $cols);
			foreach ($poss as $i => $pos) {
				//is there any configuration param
				$var = isset($vars[$pos]) ? $vars[$pos] : '';

				$param = new stdClass;
				$param->position = $pos;

				$param->default = ($var && isset($var['default'])) ? $var['default'] : 'span' . $default[$i];
				if ($var) {
					if (isset($var['wide'])) {
						$param->wide = $var['wide'];
					}
					if (isset($var['normal'])) {
						$param->normal = $var['normal'];
					}
					if (isset($var['xtablet'])) {
						$param->xtablet = $var['xtablet'];
					}
					if (isset($var['tablet'])) {
						$param->tablet = $var['tablet'];
					}
					if (isset($var['mobile'])) {
						$param->mobile = $var['mobile'];
					}
				}

				$splparams[$i] = $param;
			}
		}

		//build data
		$datas = array();
		foreach ($splparams as $idx => $splparam) {
			$param = (object)$splparam;

			$data = '';
			$data .= isset($param->default) ? ' data-default="' . $param->default . '"' : '';
			$data .= isset($param->wide) ? ' data-wide="' . $param->wide . '"' : '';
			$data .= isset($param->normal) ? ' data-normal="' . $param->normal . '"' : '';
			$data .= isset($param->xtablet) ? ' data-xtablet="' . $param->xtablet . '"' : '';
			$data .= isset($param->tablet) ? ' data-tablet="' . $param->tablet . '"' : '';
			$data .= isset($param->mobile) ? ' data-mobile="' . $param->mobile . '"' : '';

			$datas[] = $data;
		}

		//
		$vars['name'] = $name;
		$vars['splparams'] = $splparams;
		$vars['datas'] = $datas;
		$vars['cols'] = $cols;

		JDispatcher::getInstance()->trigger('onT3Spotlight', array(&$vars, $name, $positions));

		$this->loadBlock('spotlight', $vars);
	}


	/**
	 * Render megamenu markup
	 * @param  string  $menutype  The menutype to render
	 *
	 * @deprecated  Use <jdoc:include type="megamenu" menutype="$menutype" /> instead
	 */
	function megamenu($menutype)
	{
		T3::import('renderer/megamenu');

		$doc      = JFactory::getDocument();
		$renderer = new JDocumentRendererMegamenu($doc);

		echo $renderer->render(null, array('menutype' => $menutype));
	}

	/**
	 * Get data property for layout - responsive layout
	 * @param  object   $layout  Layout configuration object
	 * @param  number   $col     Column number, start from 0
	 * @param  boolean  $array   Return array or string
	 *
	 * @return  mixed  Block content
	 */
	function getData($layout, $col, $array = false)
	{
		if ($array) {
			$data = array();
			foreach ($layout as $device => $width) {
				if (!isset ($width[$col]) || !$width[$col]) continue;
				$data[$device] = $width[$col];
			}

		} else {
			$data = '';
			foreach ($layout as $device => $width) {
				if (!isset ($width[$col]) || !$width[$col]) continue;
				$data .= " data-$device=\"{$width[$col]}\"";
			}
		}

		return $data;
	}


	/**
	 * Get layout column class
	 * @param  object  $layout  Layout configuration object
	 * @param  number  $col     Column number, start from 0
	 *
	 * @return string  Block content
	 */
	function getClass($layout, $col)
	{
		$width = $layout->default;
		if (!isset ($width[$col]) || !$width[$col]) return "";
		return $width[$col];
	}


	/**
	 * Add page class
	 */
	function addPageClass($class)
	{
		$this->_pageclass = array_merge($this->_pageclass, (array)($class));
	}

	/**
	 * Add page class
	 *
	 * @deprecated
	 */
	function addBodyClass($class)
	{
		$this->_pageclass = array_merge($this->_pageclass, (array)($class));
	}

	/**
	 * get page class
	 */
	function getPageClass()
	{
		return $this->_pageclass;
	}

	/**
	 * Render page class
	 *
	 * @deprecated  Use <jdoc:include type="pageclass" /> instead
	 */
	function bodyClass()
	{
		$input = JFactory::getApplication()->input;

		if ($input->getCmd('option', '')) {
			$this->_pageclass[] = $input->getCmd('option', '');
		}
		if ($input->getCmd('view', '')) {
			$this->_pageclass[] = 'view-' . $input->getCmd('view', '');
		}
		if ($input->getCmd('layout', '')) {
			$this->_pageclass[] = 'layout-' . $input->getCmd('layout', '');
		}
		if ($input->getCmd('task', '')) {
			$this->_pageclass[] = 'task-' . $input->getCmd('task', '');
		}
		if ($input->getCmd('Itemid', '')) {
			$this->_pageclass[] = 'itemid-' . $input->getCmd('Itemid', '');
		}

		$menu = JFactory::getApplication()->getMenu();
		if ($menu) {
			$active = $menu->getActive();
			$default = $menu->getDefault();

			if ($active) {
				if ($default && $active->id == $default->id) {
					$this->_pageclass[] = 'home';
				}

				if ($active->params && $active->params->get('pageclass_sfx')) {
					$this->_pageclass[] = $active->params->get('pageclass_sfx');
				}
			}
		}

		$this->_pageclass[] = 'j' . str_replace('.', '', (number_format((float)JVERSION, 1, '.', '')));
		$this->_pageclass = array_unique($this->_pageclass);

		JDispatcher::getInstance()->trigger('onT3BodyClass', array(&$this->_pageclass));

		echo implode(' ', $this->_pageclass);
	}


	/**
	 * Render snippet
	 *
	 * @return null
	 */
	function snippet()
	{

		$places = array();
		$contents = array();

		if (($openhead = $this->getParam('snippet_open_head', ''))) {
			$places[] = '<head>';
			$contents[] = "<head>\n" . $openhead;
		}
		if (($closehead = $this->getParam('snippet_close_head', ''))) {
			$places[] = '</head>';
			$contents[] = $closehead . "\n</head>";
		}
		if (($openbody = $this->getParam('snippet_open_body', ''))) {
			$places[] = '<body>';
			$contents[] = "<body>\n" . $openbody;
		}
		
		// append modules in debug position
		if ($this->getParam('snippet_debug', 0) && $this->countModules('debug')) {
			$places[] = '</body>';
			$contents[] = '<div class="t3-debug">' . $this->getBuffer('modules', 'debug') . "</div>\n</body>";
		}
		
		if (($closebody = $this->getParam('snippet_close_body', ''))) {
			$places[] = '</body>';
			$contents[] = $closebody . "\n</body>";
		}

		if (count($places)) {
			$body = JResponse::getBody();
			$body = str_replace($places, $contents, $body);

			JResponse::setBody($body);
		}
	}


	/**
	 * Wrap of document countModules function, get position from configuration before calculate
	 * @param   string  $positions  Positions string
	 * @return  boolean  The position key is available or not
	 */
	function countModules($positions)
	{
		$pos = $this->getPosname($positions);
		return $this->_tpl && method_exists($this->_tpl, 'countModules') ? $this->_tpl->countModules($pos) : 0;
	}


	/**
	 * Wrap of document countModules function, used to detect if a spotlight is available to render or not
	 * @param  string  $name       The spotlight name
	 * @param  string  $positions  The positions name separated by comma
	 *
	 * @return  boolean  The spotlight is available or not
	 */
	function checkSpotlight($name, $positions)
	{
		$poss = array();

		for ($i = 1; $i <= self::$maxcolumns; $i++) {
			$param = $this->getLayoutSetting('block' . $i . '@' . $name);
			if (empty($param)) {
				break;
			} else {
				$param = (object)$param;
				$poss[] = isset($param->position) ? $param->position : '';
			}
		}

		if (empty($poss)) {
			$poss = preg_split('/\s*,\s*/', $positions);
		}

		return $this->_tpl && method_exists($this->_tpl, 'countModules') ? $this->_tpl->countModules(implode(' or ', $poss)) : 0;
	}


	/**
	 * Check system messages
	 *
	 * @return  boolean  The system message queue has any message or not
	 */
	function hasMessage()
	{
		// Get the message queue
		$messages = JFactory::getApplication()->getMessageQueue();
		return !empty($messages);
	}


	/**
	 * Get mapped position name
	 * @param  string  $condition  The position key(name)
	 *
	 * @return  string  The mapped position
	 */
	function getPosname($condition)
	{
		$operators = '(,|\+|\-|\*|\/|==|\!=|\<\>|\<|\>|\<=|\>=|and|or|xor)';
		$words = preg_split('# ' . $operators . ' #', $condition, null, PREG_SPLIT_DELIM_CAPTURE);
		for ($i = 0, $n = count($words); $i < $n; $i += 2) {
			// odd parts (modules)
			$name = strtolower($words[$i]);
			$words[$i] = $this->getLayoutSetting($name, $name);
		}

		$poss = '';
		foreach ($words as $word) {
			if (is_string($word)) {
				$poss .= ' ' . $word;
			} else {
				$poss .= ' ' . (is_array($word) ? $word['position'] : (isset($word->position) ? $word->position : $name));
			}
		}
		$poss = trim($poss);

		return $poss;
	}


	/**
	 * Render position name
	 * @param  string  $condition  The key used in block
	 *
	 * @return  null
	 */
	function posname($condition)
	{
		echo $this->getPosname($condition);
	}

	/**
	 * Alias of posname
	 * @param  string  $condition
	 * @return null
	 */
	function _p($condition)
	{
		$this->posname($condition);
	}


	/**
	 * Add position additional class (show/hide)
	 * @param  string  $name  The position name
	 * @param  array   $cls   The responsive array style for responsive layout [lg, md, ...]
	 *
	 * @return null
	 */
	function _c($name, $cls = array())
	{
		$data = '';
		$param = $this->getLayoutSetting($name, '');

		if (empty($param)) {
			if (is_string($cls)) {
				$data = ' ' . $cls;
			} else if (is_array($cls)) {
				$param = (object)$cls;
			}
		}

		if (!empty($param)) {

			$data = '"';
			$data .= isset($param->default) ? ' data-default="' . $param->default . '"' : '';
			$data .= isset($param->normal) ? ' data-normal="' . $param->normal . '"' : '';
			$data .= isset($param->wide) ? ' data-wide="' . $param->wide . '"' : '';
			$data .= isset($param->xtablet) ? ' data-xtablet="' . $param->xtablet . '"' : '';
			$data .= isset($param->tablet) ? ' data-tablet="' . $param->tablet . '"' : '';
			$data .= isset($param->mobile) ? ' data-mobile="' . $param->mobile . '"' : '';

			if ($data == '"') {
				$data = '';
			} else {
				$data = (isset($param->default) ? ' ' . $param->default : '') . ' t3respon' . substr($data, 0, strrpos($data, '"'));
			}
		}

		echo $data;
	}

	/**
	 * Add current template css base on template setting.
	 * @param $name           string  file name, without .css
	 * @param $addresponsive  bool    add responsive part or not
	 *
	 * @return string Block content
	 */
	function addCss($name, $addresponsive = true)
	{
		$devmode    = $this->getParam('devmode', 0);
		$themermode = $this->getParam('themermode', 1);
		$responsive = $addresponsive ? $this->getParam('responsive', 1) : false;

		if (($devmode || ($themermode && defined('T3_THEMER'))) && ($url = T3Path::getUrl('less/' . $name . '.less', '', true))) {
			T3::import('core/less');
			T3Less::addStylesheet($url);
		} else {
			$url = T3Path::getUrl('css/' . $name . '.css');
			// Add this css into template
			if ($url) {
				$this->addStyleSheet($url);
			}
		}

		if ($responsive) {
			$this->addCss($name . '-responsive', false);
		}
	}

	/**
	 * Add T3 basic head
	 *
	 * @return  null
	 */
	function addHead()
	{
		$responsive = $this->getParam('responsive', 1);
		$navtype    = $this->getParam('navigation_type', 'joomla');
		$navtrigger = $this->getParam('navigation_trigger', 'hover');
		$offcanvas  = $this->getParam('navigation_collapse_offcanvas', 1);

		// BOOTSTRAP CSS
		$this->addCss('bootstrap', false);
		// TEMPLATE CSS
		$this->addCss('template', false);

		if ($responsive) {
			// BOOTSTRAP RESPONSIVE CSS
			$this->addCss('bootstrap-responsive');
			// RESPONSIVE CSS
			$this->addCss('template-responsive');
		}

		// add core megamenu.css in plugin
		// deprecated - will extend the core style into template megamenu.less & megamenu-responsive.less
		// to use variable overridden in template
		if($navtype == 'megamenu'){

			// If the template does not overwrite megamenu.less & megamenu-responsive.less
			// We check and included predefined megamenu style in base
			if(!is_file(T3_TEMPLATE_PATH . '/less/megamenu.less')){
				$this->addStyleSheet(T3_URL . '/css/megamenu.css');

				if ($responsive){
					$this->addStyleSheet(T3_URL . '/css/megamenu-responsive.css');
				}
			}

			// megamenu.css override in template
			$this->addCss('megamenu');
		}


		// Add scripts
		if (version_compare(JVERSION, '3.0', 'ge')) {
			JHtml::_('jquery.framework');
		} else {
			$scripts = @$this->_scripts;
			$jqueryIncluded = 0;
			if (is_array($scripts) && count($scripts)) {
				//simple detect for jquery library. It will work for most of cases
				$pattern = '/(^|\/)jquery([-_]*\d+(\.\d+)+)?(\.min)?\.js/i';
				foreach ($scripts as $script => $opts) {
					if (preg_match($pattern, $script)) {
						$jqueryIncluded = 1;
						break;
					}
				}
			}

			if (!$jqueryIncluded) {
				$this->addScript(T3_URL . '/js/jquery-1.8.3' . ($this->getParam('devmode', 0) ? '' : '.min') . '.js');
				$this->addScript(T3_URL . '/js/jquery.noconflict.js');
			}
		}

		define('JQUERY_INCLUED', 1);


		// As joomla 3.0 bootstrap is buggy, we will not use it
		if (version_compare(JVERSION, '3.1.4', 'lt')) {
			$this->addScript(T3_URL . '/bootstrap/js/bootstrap.js');
		}

		// add css/js for off-canvas
		if ($offcanvas && $responsive) {
			$this->addCss('off-canvas', false);
			$this->addScript(T3_URL . '/js/off-canvas.js');
		}

		$this->addScript(T3_URL . '/js/script.js');

		//menu control script
		if ($navtrigger == 'hover') {
			$this->addScript(T3_URL . '/js/menu.js');
		}

		//reponsive script
		if ($responsive) {
			$this->addScript(T3_URL . '/js/responsive.js');
		}

		//check and add additional assets
		$this->addExtraAssets();
	}

	/**
	 * Update head - detect if devmode or themermode is enabled and less file existed, use less file instead of css
	 * We also detect and update jQuery, Bootstrap to use T3 assets
	 *
	 * @return  null
	 */
	function updateHead()
	{
		//state parameters
		$devmode = $this->getParam('devmode', 0);
		$themermode = $this->getParam('themermode', 1) && defined('T3_THEMER');
		$theme = $this->getParam('theme', '');
		$minify = $this->getParam('minify', 0);


		// As Joomla 3.0 bootstrap is buggy, we will not use it
		// We also prevent both Joomla bootstrap and T3 bootsrap are loaded
		// And upgrade jquery as our Framework require jquery 1.7+ if we are loading jquery from google
		$doc = JFactory::getDocument();
		$scripts = array();

		if (version_compare(JVERSION, '3.0', 'ge') && version_compare(JVERSION, '3.1.4', 'lt')) {
			$t3bootstrap = false;
			$jabootstrap = false;

			foreach ($doc->_scripts as $url => $script) {
				if (strpos($url, T3_URL . '/bootstrap/js/bootstrap.js') !== false) {
					$t3bootstrap = true;
					if ($jabootstrap) { //we already have the Joomla bootstrap and we also replace to T3 bootstrap
						continue;
					}
				}

				if (preg_match('@media/jui/js/bootstrap(.min)?.js@', $url)) {
					if ($t3bootstrap) { //we have T3 bootstrap, no need to add Joomla bootstrap
						continue;
					} else {
						$scripts[T3_URL . '/bootstrap/js/bootstrap.js'] = $script;
					}

					$jabootstrap = true;
				} else {
					$scripts[$url] = $script;
				}
			}

			$doc->_scripts = $scripts;
			$scripts = array();
		}

		// VIRTUE MART / JSHOPPING compatible
		foreach ($doc->_scripts as $url => $script) {
			$replace = false;

			if ((strpos($url, '//ajax.googleapis.com/ajax/libs/jquery/') !== false && preg_match_all('@/jquery/(\d+(\.\d+)*)?/@msU', $url, $jqver)) ||
				(preg_match_all('@(^|\/)jquery([-_]*(\d+(\.\d+)+))?(\.min)?\.js@i', $url, $jqver))) {

				$idx = strpos($url, '//ajax.googleapis.com/ajax/libs/jquery/') !== false ? 1 : 3;

				if (is_array($jqver) && isset($jqver[$idx]) && isset($jqver[$idx][0])) {
					$jqver = explode('.', $jqver[$idx][0]);

					if (isset($jqver[0]) && (int)$jqver[0] <= 1 && isset($jqver[1]) && (int)$jqver[1] < 7) {
						$scripts[T3_URL . '/js/jquery-1.8.3' . ($devmode ? '' : '.min') . '.js'] = $script;
						$replace = true;
					}
				}
			}

			if (!$replace) {
				$scripts[$url] = $script;
			}
		}

		$doc->_scripts = $scripts;
		// end update javascript

		// detect RTL
		$dir    = $doc->direction;
		$is_rtl = ($dir == 'rtl');

		// not in devmode and in default theme, do nothing
		if (!$devmode && !$themermode && !$theme && !$minify && !$is_rtl) {
			return;
		}


		//Update css/less based on devmode and themermode
		$root        = JURI::root(true);
		$current     = JURI::current();
		$regex       = '#' . T3_TEMPLATE_URL . '/css/([^/]*)\.css((\?|\#).*)?$#i';

		$stylesheets = array();

		foreach ($doc->_styleSheets as $url => $css) {
			// detect if this css in template css
			if (preg_match($regex, $url, $match)) {
				$fname = $match[1];

				if ($devmode || $themermode) {
					if (is_file(T3_TEMPLATE_PATH . '/less/' . $fname . '.less')) {
						if ($themermode) {
							$newurl = T3_TEMPLATE_URL . '/less/' . $fname . '.less';
							$css['mime'] = 'text/less';
						} else {
							$newurl = $current . '?t3action=lessc&amp;s=templates/' . T3_TEMPLATE . '/less/' . $fname . '.less';
						}
						$stylesheets[$newurl] = $css;
						continue;
					}
				} else {
					$subpath = $is_rtl ? 'rtl/' . ($theme ? $theme . '/' : '') : ($theme ? 'themes/' . $theme . '/' : '');
					if ($subpath && is_file(T3_TEMPLATE_PATH . '/css/' . $subpath . $fname . '.css')) {
						$newurl = T3_TEMPLATE_URL . '/css/' . $subpath . $fname . '.css';
						$stylesheets[$newurl] = $css;
						continue;
					}
				}
			}

			$stylesheets[$url] = $css;
		}
		// update back
		$doc->_styleSheets = $stylesheets;

		//only check for minify if devmode is disabled
		if (!$devmode && $minify) {
			T3::import('core/minify');
			T3Minify::optimizecss($this);
		}
	}

	/**
	 * Add some other condition assets (css, javascript). Use to parse /etc/assets.xml
	 *
	 * @return  null
	 */
	function addExtraAssets()
	{
		$base = JURI::base(true);
		$regurl = '#(http|https)://([a-zA-Z0-9.]|%[0-9A-Za-z]|/|:[0-9]?)*#iu';

		foreach (array(T3_PATH, T3_TEMPLATE_PATH) as $bpath) {
			//full path
			$afile = $bpath . '/etc/assets.xml';
			if (is_file($afile)) {

				//load xml
				$axml = JFactory::getXML($afile);

				//process if exist
				if ($axml) {
					foreach ($axml as $node => $nodevalue) {
						//ignore others node
						if ($node == 'stylesheets' || $node == 'scripts') {
							foreach ($nodevalue->file as $file) {
								$compatible = $file['compatible'];
								if ($compatible) {
									$parts = explode(' ', $compatible);
									$operator = '='; //exact equal to
									$operand = $parts[1];
									if (count($parts) == 2) {
										$operator = $parts[0];
										$operand = $parts[1];
									}

									//compare with Joomla version
									if (!version_compare(JVERSION, $operand, $operator)) {
										continue;
									}
								}

								$url = (string)$file;
								if (substr($url, 0, 2) == '//') { //external link

								} else if ($url[0] == '/') { //absolute link from based folder
									$url = is_file(JPATH_ROOT . $url) ? $base . $url : false;
								} else if (!preg_match($regurl, $url)) { //not match a full url -> sure internal link
									$url = T3Path::getUrl($url); // so get it
								}

								if ($url) {
									if ($node == 'stylesheets') {
										$this->addStylesheet($url);
									} else {
										$this->addScript($url);
									}
								}
							}
						}
					}
				}
			}
		}
	}


	/**
	 * Turn a param to DOM style value
	 * @param   string   $style  The style property
	 * @param   string   $pname  The parameter name
	 * @param   boolean  $isurl  Is url?
	 *
	 * @return  string   The css style string
	 * @deprecated   This function is no longer used in T3
	 */
	function paramToStyle($style, $pname = '', $isurl = false)
	{
		if ($pname == '') {
			$pname = $style;
		}
		$param = $this->getParam($pname);

		if (!$param) return '';

		if ($isurl) {
			return "$style:url($param);";
		} else {
			return "$style:$param" . (is_numeric($param) ? 'px;' : ';');
		}
	}

	/**
	 * Internal function, auto generate optimize width in a row fit to 12 grid
	 * @param  number  $numpos  number columns in row
	 *
	 * @return  array  The span width layout columns for a row
	 */
	function fitWidth($numpos)
	{
		$result = array();
		$avg = floor(self::$maxgrid / $numpos);
		$sum = 0;

		for ($i = 0; $i < $numpos - 1; $i++) {
			$result[] = $avg;
			$sum += $avg;
		}

		$result[] = self::$maxgrid - $sum;

		return $result;
	}

	/**
	 * Internal function, generate auto calculate width
	 * @param   string   $layout  The target layout
	 * @param   number   $numpos  Number of columns (block)
	 *
	 * @return  array  The span width layout columns
	 */
	function genWidth($layout, $numpos)
	{

		$cmaxcol = self::$maxcol[$layout];
		$cminspan = ($layout == 'mobile') ? self::$maxgrid : self::$minspan[$layout];
		$total = $cminspan * $numpos;
		$sum = 0;

		if ($total < self::$maxgrid) {
			return $this->fitWidth($numpos);
		} else {
			$result = array();
			$rows = ceil($total / self::$maxgrid);
			$cols = ceil($numpos / $rows);

			for ($i = 0; $i < $rows - 1; $i++) {
				$result = array_merge($result, $this->fitWidth($cols));
				$numpos -= $cols;
			}

			$result = array_merge($result, $this->fitWidth($numpos));
		}

		return $result;
	}
}

?>
