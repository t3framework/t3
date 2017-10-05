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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * T3 class
 * Singleton class for T3
 * @package		T3
 */

class T3 {

	protected static $t3app = null;

	protected static $tmpl  = null;
	/**
	 * Import T3 Library
	 *
	 * @param string  $package  Object path that seperate by backslash (/)
	 *
	 * @return void
	 */
	public static function import($package){
		$path = T3_ADMIN_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . strtolower($package) . '.php';
		if (file_exists($path)) {
			include_once $path;
		} else {
			trigger_error('T3::import not found object: ' . $package, E_USER_ERROR);
		}
	}

	/**
	 * Register class with Joomla Loader. Override joomla core if $import_key avaiable
	 *
	 * @return void
	 */
  public static function register ($class, $path, $import_key = null) {
    if (!empty($import_key)) jimport($import_key);
    JLoader::register ($class, $path);
  }

	/**
	 * @param   object  $tpl  template object to initialize if needed
	 * @return  bool|null|T3Admin
	 */
	public static function getApp($tpl = null){
		if(empty(self::$t3app)){
			$japp = JFactory::getApplication();
			self::$t3app = $japp->isAdmin() ? self::getAdmin() : self::getSite($tpl);
		}

		return self::$t3app;
	}

	/**
	 * initialize T3
	 */
	public static function init ($xml) {
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$coretheme = isset($xml->t3) && isset($xml->t3->base) ? trim((string)$xml->t3->base) : 'base';

		// check coretheme in media/t3/themes folder
		// if not exists, use default base theme in T3
		if (!$coretheme){
			$coretheme = 'base';
		}

		foreach(array(T3_EX_BASE_PATH, T3_ADMIN_PATH) as $basedir){
			if(is_dir($basedir . '/' . $coretheme)){

				if(is_file($basedir . '/' . $coretheme . '/define.php')){
					include_once ($basedir . '/' . $coretheme . '/define.php');
				}

				break;
			}
		}

		if(!defined('T3')){
			// get ready for the t3 core base theme
			include_once (T3_CORE_BASE_PATH . '/define.php');
		}

		if(!defined('T3')){
			T3::error(JText::sprintf('T3_MSG_FAILED_INIT_BASE', $coretheme));
			exit;
		}

		define ('T3_TEMPLATE', (String)$xml->tplname);
		define ('T3_TEMPLATE_URL', JURI::root(true).'/templates/'.T3_TEMPLATE);
		define ('T3_TEMPLATE_PATH', str_replace ('\\', '/', JPATH_ROOT) . '/templates/' . T3_TEMPLATE);
		define ('T3_TEMPLATE_REL', 'templates/' . T3_TEMPLATE);

		define ('T3_LOCAL_URL', T3_TEMPLATE_URL . '/' . T3_LOCAL_DIR);
		define ('T3_LOCAL_PATH', T3_TEMPLATE_PATH . '/' . T3_LOCAL_DIR);
		define ('T3_LOCAL_REL', T3_TEMPLATE_REL . '/' . T3_LOCAL_DIR);

		if ($input->getCmd('themer', 0)){
			define ('T3_THEMER', 1);
		}

		if (!$app->isAdmin()) {
			$params = $app->getTemplate(true)->params;
			define ('T3_DEV_FOLDER', $params->get ('t3-assets', 't3-assets') . '/dev');
			define ('T3_DEV_MODE', $params->get ('devmode', 0));
		} else {
			$params = self::getTemplate()->params;
			define ('T3_DEV_FOLDER', $params->get ('t3-assets', 't3-assets') . '/dev');
		}
		if (!is_dir(JPATH_ROOT.'/'.T3_DEV_FOLDER)) {
			jimport('joomla.filesystem.folder');
			JFolder::create(JPATH_ROOT.'/'.T3_DEV_FOLDER);
		}

		if($input->getCmd('t3lock', '')){
			JFactory::getSession()->set('T3.t3lock', $input->getCmd('t3lock', ''));
			$input->set('t3lock', null);
		}

		// load core library
		T3::import ('core/path');
		T3::import ('core/t3j');

		if (!$app->isAdmin()) {
			if(version_compare(JVERSION, '3.8', 'ge')){
				// override core joomla class
				// JViewLegacy
		        T3::register('JViewLegacy',   T3_ADMIN_PATH . '/includes/joomla4/HtmlView.php');
				// JModuleHelper
		        T3::register('JModuleHelper',   T3_ADMIN_PATH . '/includes/joomla4/ModuleHelper.php');
				// JPagination
		        T3::register('JPagination',   T3_ADMIN_PATH . '/includes/joomla4/Pagination.php');
		        // Register T3 Layout File to put a t3 base layer for layout files
		        T3::register('JLayoutFile',   T3_ADMIN_PATH . '/includes/joomla4/FileLayout.php');
			} else if(version_compare(JVERSION, '3.0', 'ge')){
				// override core joomla class
				// JViewLegacy
		        T3::register('JViewLegacy',   T3_ADMIN_PATH . '/includes/joomla30/viewlegacy.php');
		        T3::register('JViewHtml',   T3_ADMIN_PATH . '/includes/joomla30/viewhtml.php');
						// JModuleHelper
		        T3::register('JModuleHelper',   T3_ADMIN_PATH . '/includes/joomla30/modulehelper.php');
						// JPagination
		        T3::register('JPagination',   T3_ADMIN_PATH . '/includes/joomla30/pagination.php');
		        // Register T3 Layout File to put a t3 base layer for layout files
		        T3::register('JLayoutFile',   T3_ADMIN_PATH . '/includes/joomla30/layoutfile.php');
			} else {
				// override core joomla class
				// JView
				T3::register('JView',       T3_ADMIN_PATH . '/includes/joomla25/view.php', 'joomla.application.component.view');
				// JModuleHelper
				T3::register('JModuleHelper',       T3_ADMIN_PATH . '/includes/joomla25/modulehelper.php', 'joomla.application.module.helper');
				// JPagination
				T3::register('JPagination',       T3_ADMIN_PATH . '/includes/joomla25/pagination.php', 'joomla.html.pagination');

				//register layout
				T3::register('JLayout',       T3_ADMIN_PATH . '/includes/joomla25/layout/layout.php');
				T3::register('JLayoutBase',   T3_ADMIN_PATH . '/includes/joomla25/layout/base.php');
				T3::register('JLayoutFile',   T3_ADMIN_PATH . '/includes/joomla25/layout/file.php');
				T3::register('JLayoutHelper', T3_ADMIN_PATH . '/includes/joomla25/layout/helper.php');
				T3::register('JHtmlBootstrap', T3_ADMIN_PATH . '/includes/joomla25/html/bootstrap.php');
				T3::register('JHtmlBehavior', T3_ADMIN_PATH . '/includes/joomla25/html/behavior.php');
		        T3::register('JHtmlString', T3_ADMIN_PATH . '/includes/joomla25/html/string.php');
		        T3::register('JHtmlJquery', T3_ADMIN_PATH . '/includes/joomla25/html/jquery.php');

		        // load j25 compat language
		        JFactory::getLanguage()->load('plg_system_t3.j25.compat', JPATH_ADMINISTRATOR);
			}

			// import renderer
			T3::import('renderer/pageclass');
			T3::import('renderer/megamenu');
			T3::import('renderer/t3bootstrap');
		} else {

		}

		// capture for tm=1 => show theme magic
		if ($input->getCmd('tm') == 1) {
			$input->set('t3action', 'theme');
			$input->set('t3task', 'thememagic');
		}
	}

	public static function checkAction () {
		// excute action by T3
		if ($action = JFactory::getApplication()->input->getCmd ('t3action')) {
			T3::import ('core/action');
			T3Action::run ($action);
		}
	}

	/**
	 * check for t3ajax action
	 */
	public static function checkAjax () {
		// excute action by T3
		$input = JFactory::getApplication()->input;

		if ($input->getCmd ('t3ajax')) {
			T3::import('core/ajax');
			T3::import('renderer/t3ajax');

			//T3Ajax::processAjaxRule();

			JFactory::getApplication()->getTemplate(true)->params->set('mainlayout', 'ajax.' . $input->getCmd('f', 'html'));
		}
	}

	/**
	 * get T3Admin object
	 * @return T3Admin
	 */
	public static function getAdmin(){
		T3::import ('core/admin');
		return new T3Admin();
	}

	/**
	 * get T3Template object for frontend
	 * @param $tpl
	 * @return bool
	 */
	public static function getSite($tpl){
		//when on site, the JDocumentHTML parameter must be pass
		if(empty($tpl)){
			return false;
		}

		$type = 'Template'. JFactory::getApplication()->input->getCmd ('t3tp', '');
		T3::import ('core/' . $type);

		// create global t3 template object
		$class = 'T3' . $type;
		return new $class($tpl);
	}

	/**
	 * @param $msg
	 * @param int $code
	 * @throws Exception
	 */
	public static function error($msg, $code = 500){
		if (JError::$legacy) {
			JError::setErrorHandling(E_ERROR, 'die');
			JError::raiseError($code, $msg);

			exit;
		} else {
			throw new Exception($msg, $code);
		}
	}

	/**
	 * detect function to check a current template is T3 template
	 * @return bool|SimpleXMLElement
	 */
	public static function detect(){
		static $t3;

		if (!isset($t3)) {
			$t3 = false; // set false
			$app = JFactory::getApplication();
			$input = $app->input;

			// get template name
			$tplname = '';

			if($input->getCmd ('t3action') && $input->getInt('styleid', '')) {

				$tplname = self::getTemplate(true);

			} elseif ($app->isAdmin()) {
				// if not login, do nothing
				$user = JFactory::getUser();
				if (!$user->id){
					return false;
				}

				if($input->getCmd('option') == 'com_templates' &&
					(preg_match('/style\./', $input->getCmd('task')) ||
						$input->getCmd('view') == 'style' ||
						$input->getCmd('view') == 'template')){

					$db    = JFactory::getDBO();
					$query = $db->getQuery(true);
					$id    = $input->getInt('id');

					//when in POST the view parameter does not set
					if ($input->getCmd('view') == 'template') {
						$query
							->select('element')
							->from('#__extensions')
							->where('extension_id='.(int)$id . ' AND type=' . $db->quote('template'));
					} else {
						$query
							->select('template')
							->from('#__template_styles')
							->where('id='.(int)$id);
					}

					$db->setQuery($query);
					$tplname = $db->loadResult();
				}

			} else {
				$tplname = $app->getTemplate(false);
			}

			if ($tplname) {
					// parse xml
				$filePath = JPath::clean(JPATH_ROOT.'/templates/'.$tplname.'/templateDetails.xml');
				if (is_file ($filePath)) {
					$xml = $xml = simplexml_load_file($filePath);
					// check t3 or group=t3 (compatible with previous definition)
					if (isset($xml->t3) || (isset($xml->group) && strtolower($xml->group) == 't3')) {
						$xml->tplname = $tplname;
						$t3 = $xml;
					}
				}
			}
		}
		return $t3;
	}

	/**
	 * get default template style
	 */
	public static function getDefaultTemplate($name = false){
		static $template;

		if (!isset($template)) {

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('id, home, template, s.params')
				->from('#__template_styles as s')
				->where('s.client_id = 0')
				->where('s.home = \'1\'')
				->where('e.enabled = 1')
				->leftJoin('#__extensions as e ON e.element=s.template AND e.type='.$db->quote('template').' AND e.client_id=s.client_id');

			$db->setQuery($query);
			$result = $db->loadObject();

			$template = !empty($result) ? $result : false;
		}

		if($name && $template){
			return $template->template;
		}

		return $template;
	}

	/**
	 * get the template object or template name
	 * @param bool $name
	 * @return mixed template object or template name
	 */
	public static function getTemplate($name = false)
	{
		if(!isset(self::$tmpl) || !self::$tmpl){

			$app   = JFactory::getApplication();
			$input = $app->input;
			$id    = $input->getInt('styleid', $input->getInt('id'));

			if($id){
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query
					->select('template, params')
					->from('#__template_styles')
					->where('client_id = 0');

				if($app->isAdmin() && $input->get('view') == 'template' && defined('T3_TEMPLATE')){
					$query->where('template='. $db->quote(T3_TEMPLATE));
				} else {
					$query->where('id='. $id);
				}

				$db->setQuery($query);
				$template = $db->loadObject();

				if ($template) {
					$registry = new JRegistry;
					$registry->loadString($template->params);
					$template->params = $registry;
				}

				self::$tmpl = $template;
			}
		}

		if($name && self::$tmpl){
			return self::$tmpl->template;
		}

		return self::$tmpl;
	}

	/**
	 * set caching template and its parameters
	 * @param string $name
	 * @param string $params
	 */
	public static function setTemplate($name = '', $params = ''){
		if(!self::$tmpl){
			self::$tmpl = new stdClass;
		}

		if($name && $params){
			self::$tmpl->template = $name;
			self::$tmpl->params = $params;
		}
	}

	/**
	 * get template parameters
	 * @return JRegistry
	 */
	public static function getTplParams()
	{
		$tmpl = self::getTemplate();
		return $tmpl ? $tmpl->params : new JRegistry; //empty registry ? or throw error
	}

	/**
	 * check if current page is homepage
	 */
	public static function isHome(){
		$active = JFactory::getApplication()->getMenu()->getActive();
		return (!$active || $active->home);
	}

	/**
	 * fix ja back link
	 * @param $buffer
	 * @return mixed
	 */
	public static function fixJALink($buffer){

		if(!self::isHome()){
			$buffer = preg_replace_callback('@<a[^>]*>JoomlArt.com</a>@i', array('T3', 'removeBacklink'), $buffer);
		}

		return $buffer;
	}

	/**
	 * fix t3-framework.org back link
	 * @param $buffer
	 * @return mixed
	 */
	public static function fixT3Link($buffer){
		if(!self::isHome()){
			$buffer = preg_replace_callback('@<a[^>]*>([^>]*)>T3 Framework</strong></a>@mi', array('T3', 'removeBacklink'), $buffer);
		}

		return $buffer;
	}

	/**
	 * check nofollow attribute
	 * @param $match
	 * @return mixed
	 */
	public static function removeBacklink($match){

		if($match && isset($match[0]) && strpos($match[0], 'rel="nofollow"') === false){
			$match[0] = str_replace('<a ', '<a rel="nofollow" ', $match[0]);
		}

		return $match[0];
	}

	/**
	 * Check alternative template style for current style
	 * If exists, then split access to alternative style
	 */
	public static function checkAltStyle () {
		T3::import ('core/ab');
		T3AB::checkAltStyle();
	}


	public static function getPageCacheKey () {
		$pagekey = array();
		// TODO: handle pagekey for page cache - From Joomla 3.8

		// if available A/B testing, handle key for each variation
		T3::import ('core/ab');
		$abpagekey = T3AB::getPageCacheKey ();
		if ($abpagekey) $pagekey[] = $abpagekey;

		return count($pagekey) ? implode(';', $pagekey) : NULL;
	}
}
