<?php
/**
 * $JA#COPYRIGHT$
 */

// No direct access
defined('_JEXEC') or die();

t3import ('extendable/extendable');

/**
 * T3Template class provides extended template tools used for T3 framework
 *
 * @package T3
 */
class T3Template extends ObjectExtendable
{
	/**
	 * Define constants
	 *
	 */
	protected static $maxcol = array( 'default' => 6, 'wide' => 6, 'normal' => 6, 'xtablet' => 4, 'tablet' => 3, 'mobile' => 2 );
	protected static $minspan = array( 'default' => 2, 'wide' => 2, 'normal' => 2, 'xtablet' => 3, 'tablet' => 4, 'mobile' => 6 );
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
	 * Class constructor
	 *
	 * @param   object  $template Current template instance
	 */
	public function __construct($template = null)
	{
		$this->_layoutsettings = new JRegistry;

		if ($template) {
			$this->_tpl = $template;			
			$this->_extend(array($template));
			// merge layout setting
			
			$layout = JFactory::getApplication()->input->getCmd('t3layout', '');
			if(empty($layout)){
				$layout = $template->params->get('mainlayout', 'default');
			}
			$fconfig = JPATH_ROOT . '/templates/' . $template->template . '/etc/layout/' . $layout . '.ini';		
			if(is_file($fconfig)){
				$this->_layoutsettings->loadString (JFile::read($fconfig), 'INI', array('processSections' => true));
			}
		}
	}

	/**
	 * get template parameter
	 *
	 * @param   $name  parameter name
	 * @param   $default  parameter default value
	 *
	 * @return   parameter value
	 */
	public function getParam ($name, $default = null) {
		return $this->_tpl->params->get ($name, $default);
	}
	
	public function setParam ($name, $value) {
		return $this->_tpl->params->set ($name, $value);
	}

	/**
	* Get current layout tpls
	*
	* @return string Layout name
	*/
	public function getLayout () {
		return JFactory::getApplication()->input->getCmd ('tmpl') ? JFactory::getApplication()->input->getCmd ('tmpl') : $this->getParam('mainlayout');
	}

	/**
	* Get layout settings
	*
	* @return string Layout name
	*/
	public function getLayoutSetting ($name, $default = null) {
		return isset($this->_layoutsettings) ? $this->_layoutsettings->get($name, $default) : $default;
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
		$path = T3Path::getPath ('tpls/blocks/'.$block.'.php');
		if ($path) {
			include $path;
		} else {
			echo "<div class=\"error\">Block [$block] not found!</div>";
		}
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
		$path = T3Path::getPath ('tpls/'.$layout.'.php', 'tpls/default.php');

		if (is_file ($path)) {
			include $path;
		} else {
			echo "<div class=\"error\">Layout [$layout] or [Default] not found!</div>";
		}
	} 

	/**
	* Load spotlight block
	*
	* @param $name string
	*     Name of the spotlight. Default will load positions base on this name: [name]-1, [name]-2...
	* @param $default_cols integer
	*     Default columns in the spotlight, changable in admin 
	*
	*/
	function spotlight($name, $positions, array $info = array())
	{
		$defpos = preg_split('/\s*,\s*/', $positions);
		$vars = is_array($info) ? $info : array();
		$cols = count($defpos);
		$poss = $defpos;

		$splparams = array();
		for($i = 1; $i <= self::$maxcolumns; $i++){
			$param = $this->getLayoutSetting('block'.$i.'@'.$name);
			if(empty($param)){
				break;
			} else {
				$splparams[] = $param;
			}
		}

		//we have configuration in setting file
		if(!empty($splparams)){
			$poss = array();
			foreach ($splparams as $idx => $splparam) {
				$param = (object)$splparam;
				$poss[] = isset($param->position) ? $param->position : $defpos[$idx];
			}

			$cols = count($poss);
		}

		// check if there's any modules
		if (!$this->countModules (implode(' or ', $poss))){
			return;
		}

		//empty - so we will use default configuration
		if(empty($splparams)){
			//generate a optimize default width
			$default = $this->genWidth('default', $cols);
			foreach ($poss as $i => $pos) {
				//is there any configuration param
				$var = isset($vars[$pos]) ? $vars[$pos] : '';
				
				$param = new stdClass;
				$param->position = $pos;

				$param->default = ($var && isset($var['default'])) ? $var['default'] : 'span' . $default[$i];
				if($var){
					if(isset($var['wide'])){
						$param->wide = $var['wide'];
					}
					if(isset($var['normal'])){
						$param->normal = $var['normal'];
					}
					if(isset($var['xtablet'])){
						$param->xtablet = $var['xtablet'];
					}
					if(isset($var['tablet'])){
						$param->tablet = $var['tablet'];
					}
					if(isset($var['mobile'])){
						$param->mobile = $var['mobile'];
					}
				}
				
				$splparams[$i] = $param;
			}
		}

		//build data
		$datas = array();
		foreach ($splparams as $idx => $splparam){
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

		$this->loadBlock ('spotlight', $vars);
	}

	function megamenu($menutype){
			t3import('menu/megamenu');

			$file = T3_TEMPLATE_PATH.'/etc/megamenu.ini';
			$currentconfig = json_decode(@file_get_contents ($file), true);
			$mmconfig = ($currentconfig && isset($currentconfig[$menutype])) ? $currentconfig[$menutype] : array();

			$menu = new T3MenuMegamenu ($menutype, $mmconfig);
			$menu->render();          

			// add core megamenu.css in plugin
			$this->addStyleSheet(T3_URL.'/css/megamenu.css');
			// megamenu.css override in template
			$this->addCss ('megamenu');	
	}

	/**
	* Get data property for layout - responsive layout
	*
	* @param $layout object
	*     Layout configuration
	* @param $col int
	*     Column number, start from 0
	*
	* @return string Block content
	*/
	function getData ($layout, $col) {
		$data = '';
		foreach ($layout as $device => $width) {
			if (!isset ($width[$col]) || !$width[$col]) continue;
			$data .= " data-$device=\"{$width[$col]}\"";
		}
		return $data;
	}

	/**
	* Get layout column class
	*
	* @param $layout object
	*     Layout configuration
	* @param $col int
	*     Column number, start from 0
	*
	* @return string Block content
	*/
	function getClass ($layout, $col) {
		$width = $layout->default;
		if (!isset ($width[$col]) || !$width[$col]) return "";
		return $width[$col];
	}

	/**
	 * Wrap of document countModules function, get position from configuration before calculate
	 */
	function countModules($positions)
	{
		$pos = $this->getPosname ($positions);
		return $this->_tpl && method_exists($this->_tpl, 'countModules') ? $this->_tpl->countModules ($pos) : 0;
	}

	/**
	 * Wrap of document countModules function, get position from configuration before calculate
	 */
	function checkSpotlight($name, $positions)
	{
		$poss = array();

		for($i = 1; $i <= self::$maxcolumns; $i++){
			$param = $this->getLayoutSetting('block'.$i.'@'.$name);
			if(empty($param)){
				break;
			} else {
				$param = (object)$param;
				$poss[] = isset($param->position) ? $param->position : '';
			}
		}

		if(empty($poss)){
			$poss = preg_split('/\s*,\s*/', $positions);
		}

		return $this->_tpl && method_exists($this->_tpl, 'countModules') ? $this->_tpl->countModules (implode(' or ', $poss)) : 0;
	}

	/**
	* Get position name
	*
	* @param $poskey string
	*     the key used in block
	*/
	function getPosname ($condition) {
		$operators = '(,|\+|\-|\*|\/|==|\!=|\<\>|\<|\>|\<=|\>=|and|or|xor)';
		$words = preg_split('# ' . $operators . ' #', $condition, null, PREG_SPLIT_DELIM_CAPTURE);
		for ($i = 0, $n = count($words); $i < $n; $i += 2) {
			// odd parts (modules)
			$name = strtolower($words[$i]);
			$words[$i] = $this->getLayoutSetting ($name, $name);
		}
	
		$poss = '';
		foreach ($words as $word) {
			if(is_string($word)){
				$poss .= ' ' . $word;	
			} else {
				$poss .= ' ' . (is_array($word) ? $word['position'] : (isset($word->position) ? $word->position : $name));
			}
		}
		$poss = trim($poss);
	
		return $poss;
	}

	/**
	* echo position name
	*
	* @param $poskey string
	*     the key used in block
	*/
	function posname ($condition) {
		echo $this->getPosname ($condition);
	}

	/**
	* Alias of posname
	*
	*/
	function _p ($condition) {
		$this->posname ($condition);
	}

	/** 
	* add position additinal class
	*
	* @param $poskey string
	*     the key used in block
	*/

	function _c ($name, $cls = array()){
		$data = '';
		$param = $this->getLayoutSetting($name, '');

		if(empty($param) && is_string($cls)){
			$data = ' ' . $cls;
		} else if (is_array($cls)){
			if(empty($param)){
				$param = (object)$cls;
			}

			$data = '"';
			$data .= isset($param->default) ? ' data-default="' . $param->default . '"' : '';
			$data .= isset($param->normal) ? ' data-normal="' . $param->normal . '"' : '';
			$data .= isset($param->wide) ? ' data-wide="' . $param->wide . '"' : '';
			$data .= isset($param->xtablet) ? ' data-xtablet="' . $param->xtablet . '"' : '';
			$data .= isset($param->tablet) ? ' data-tablet="' . $param->tablet . '"' : '';
			$data .= isset($param->mobile) ? ' data-mobile="' . $param->mobile . '"' : '';

			if($data == '"'){
				$data = '';
			} else {
				$data = substr($data, 0, strrpos($data, '"'));
			}
		}
		
		echo $data;
	}

	/**
	* Add current template css base on template setting. 
	*
	* @param $name String
	*     file name, without .css
	*
	* @return string Block content
	*/
	function addCss ($name) {
		$devmode = $this->getParam('devmode', 0);
		$themermode = $this->getParam('themermode', 0);
		if (($devmode || ($themermode && defined ('T3_THEMER'))) && ($url = T3Path::getUrl('less/'.$name.'.less', '', true))) {
			t3import ('core/less');
			T3Less::addStylesheet ($url);
		} else {
			$url = T3Path::getUrl ('css/'.$name.'.css');
			// Add this css into template
			if ($url) {
				$this->addStyleSheet($url);
			}
		}
	}

	/**
	* Add T3 basic head 
	*/
	function addHead () {
		$responsive = $this->getParam('responsive', 1);

		// BOOTSTRAP CSS
		$this->addCss ('bootstrap'); 
		// TEMPLATE CSS
		$this->addCss ('template'); 

		if ($responsive) {
		// BOOTSTRAP RESPONSIVE CSS
			$this->addCss ('bootstrap-responsive'); 
		// RESPONSIVE CSS
			$this->addCss ('template-responsive'); 
		}

		// Add scripts
		//if(version_compare(JVERSION, '3.0', 'ge')){
		//	JHtml::_('jquery.framework');
		//} else {
			$this->addScript (T3_URL.'/js/jquery-1.8.3' . ($this->getParam('devmode', 0) ? '' : '.min') . '.js');
		//}
		define('JQUERY_INCLUED', 1);

		// As joomla 3.0 bootstrap is buggy, we will not use it
		$this->addScript (T3_URL.'/bootstrap/js/bootstrap.js');	
		$this->addScript (T3_URL.'/js/noconflict.js');
		$this->addScript (T3_URL.'/js/touch.js');
		$this->addScript (T3_URL.'/js/script.js');

		if ($responsive) {
			$this->addScript (T3_URL.'/js/responsive.js');
		}

		//check and add additional assets
		$this->addExtraAssets();
	}

	/**
	* Update head - detect if devmode or themermode is enabled and less file existed, use less file instead of css
	*/
	function updateHead () {
		$devmode = $this->getParam('devmode', 0);
		$themermode = $this->getParam('themermode', 0) && defined ('T3_THEMER');
		$theme = $this->getParam('theme', '');
		$cssmin = $this->getParam('cssminify', 1) ? '.min' : '';

		// not in devmode and in default theme, do nothing
		if (!$devmode && !$themermode && !$theme && !$cssmin){
			return;
		}

		$doc = JFactory::getDocument();
		$root = JURI::root(true);
		$regex = '#'.T3_TEMPLATE_URL.'/css/([^/]*)\.css((\?|\#).*)?$#i';
		$stylesheets = array();
		foreach ($doc->_styleSheets as $url => $css) {
			// detect if this css in template css 
			if (preg_match($regex, $url, $match)) {
				$fname = $match[1];
				if ($devmode || $themermode) {
					if (is_file (T3_TEMPLATE_PATH.'/less/'.$fname.'.less')) {
						if ($themermode) {
							$newurl = T3_TEMPLATE_URL.'/less/'.$fname.'.less';
							$css ['mime'] = 'text/less';
						} else {
							$newurl = JURI::current().'?t3action=lessc&s=templates/'.T3_TEMPLATE.'/less/'.$fname.'.less';
						}
						$stylesheets[$newurl] = $css;
						continue;
					}
				} 

				// check if css exists in current theme
				if (is_file (T3_TEMPLATE_PATH.'/css/themes/'.$theme.'/'.$fname.$cssmin.'.css')) {
					$newurl = T3_TEMPLATE_URL.'/css/themes/'.$theme.'/'.$fname.$cssmin.'.css';
					$stylesheets[$newurl] = $css;
					continue;
				}
			}
			if ($cssmin) {
				// bypass bootstrap css
				if (preg_match('#bootstrap(-responsive)?\.css#', $url)){
					continue;
				}

				if (!preg_match('#\.min\.css#i', $url)) { //if this link does has minify marker
					$trurl = preg_replace('#\.css(\?.*?)?$#', '.min.css$1' , $url);
					$turl = preg_replace('#(\?.*|\#.*)#', '', $trurl);
					$tfile = '';

					if(substr($turl, 0, 2) === '//'){ //check and append if url is omit http
						$turl = 'http:' . $turl; 
					}

					if(preg_match('#(http|https)://([a-zA-Z0-9.]|%[0-9A-Za-z]|/|:[0-9]?)*#iu', $turl)){ //this is a full link
						if(JURI::isInternal($turl)){ // is internal
							$tfile = JPath::clean(JPATH_ROOT . '/' . substr($turl, strlen(JURI::base())));
						}
					} else {
						//sure, should be internal
						$tfile = JPath::clean(JPATH_ROOT . '/' . ($root && strpos($turl, $root) == 0 ? substr($turl, strlen($root)) : $turl));
					}

					if($tfile && is_file($tfile)){
						$url = $trurl;
					}
				}
			}
			$stylesheets[$url] = $css;
		}
		// update back
		$doc->_styleSheets = $stylesheets;
	}

	/**
	* Add some other condition assets (css, javascript)
	*/
	function addExtraAssets(){
		$base = JURI::base(true);
		$regurl = '#(http|https)://([a-zA-Z0-9.]|%[0-9A-Za-z]|/|:[0-9]?)*#iu';
		foreach(array(T3_PATH, T3_TEMPLATE_PATH) as $bpath){
			//full path
			$afile = $bpath . '/etc/assets.xml';
			if(is_file($afile)){
				
				//load xml
				$axml = JFactory::getXML($afile);
				//parse stylesheets first if exist
				if($axml){
					foreach($axml as $node => $nodevalue){
						//ignore others node
						if($node == 'stylesheets' || $node == 'scripts'){
							foreach ($nodevalue->file as $file) {
								$compatible = $file['compatible'];
								if($compatible) {
									$parts = explode(' ', $compatible);
									$operator = '='; //exact equal to
									$operand = $parts[1];
									if(count($parts) == 2){
										$operator = $parts[0];
										$operand = $parts[1];
									}

									//compare with Joomla version
									if(!version_compare(JVERSION, $operand, $operator)){
										continue;
									}
								}

								$url = (string)$file;
								if(substr($url, 0, 2) == '//'){ //external link
									
								} else if ($url[0] == '/'){ //absolute link from based folder
									$url = is_file(JPATH_ROOT . $url) ? $base . $url : false; 
								} else if (!preg_match($regurl, $url)) { //not match a full url -> sure internal link
									$url = T3Path::getUrl($url);		// so get it
								}

								if($url){
									if($node == 'stylesheets'){
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

	function paramToStyle($style, $paramname = '', $isurl = false){
		if($paramname == ''){
			$paramname = $style;
		}
		$param = $this->getParam($paramname);
		
		if (!$param) return '';

		if ($isurl) {
			return "$style:url($param);";
		} else {
			return "$style:$param".(is_numeric($param) ? 'px;':';');
		}
	}

	/**
	* Auto generate optimize width in a row fit to 12 grid
	* @var (int) numpos: number columns in row
	*/
	function fitWidth($numpos){
		$result = array();
		$avg = floor(self::$maxgrid / $numpos);
		$sum = 0;

		for($i = 0; $i < $numpos - 1; $i++){
			$result[] = $avg;
			$sum += $avg;
		}

		$result[] = self::$maxgrid - $sum;

		return $result;
	}

	/**
	* Generate auto calculate width
	*/
	function genWidth($layout, $numpos){
		
		$cmaxcol = self::$maxcol[$layout];
		$cminspan = ($layout == 'mobile') ? self::$maxgrid : self::$minspan[$layout];
		$total = $cminspan * $numpos;
		$sum = 0;

		if($total < self::$maxgrid) {
			return $this->fitWidth($numpos);
		} else {
			$result = array();
			$rows = ceil($total / self::$maxgrid);
			$cols = ceil($numpos / $rows);

			for($i = 0; $i < $rows - 1; $i++){
				$result = array_merge($result, $this->fitWidth($cols));
				$numpos -= $cols;
			}

			$result = array_merge($result, $this->fitWidth($numpos));
		}
		
		return $result;
	}
}
?>