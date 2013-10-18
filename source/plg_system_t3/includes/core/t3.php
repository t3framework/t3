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
	 * Initialize T3
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

		define ('T3_TEMPLATE', $xml->tplname);
		define ('T3_TEMPLATE_URL', JURI::root(true).'/templates/'.T3_TEMPLATE);
		define ('T3_TEMPLATE_PATH', JPATH_ROOT . '/templates/' . T3_TEMPLATE);
		define ('T3_TEMPLATE_REL', 'templates/' . T3_TEMPLATE);

		//load T3 Framework language
		JFactory::getLanguage()->load(T3_PLUGIN, JPATH_ADMINISTRATOR);
		
		if ($input->getCmd('themer', 0)){
			define ('T3_THEMER', 1);
		}

		if (!$app->isAdmin()) {
			$t3assets = $app->getTemplate(true)->params->get ('t3-assets', 't3-assets');
			define ('T3_DEV_FOLDER', $t3assets . '/dev');
		}

		if($input->getCmd('t3lock', '')){
			JFactory::getSession()->set('T3.t3lock', $input->getCmd('t3lock', ''));
			$input->set('t3lock', null);
		}

		// load core library
		T3::import ('core/path');
		
		if (!$app->isAdmin()) {
			
			if(version_compare(JVERSION, '3.0', 'ge')){
				// override core joomla class
				// JViewLegacy
				if (!class_exists('JViewLegacy', false)) T3::import ('joomla30/viewlegacy');
				// JModuleHelper
				if (!class_exists('JModuleHelper', false)) T3::import ('joomla30/modulehelper');
				// JPagination
				if (!class_exists('JPagination', false)) T3::import ('joomla30/pagination');
			} else {
				// override core joomla class
				// JView
				if (!class_exists('JView', false)) T3::import ('joomla25/view');
				// JModuleHelper
				if (!class_exists('JModuleHelper', false)) T3::import ('joomla25/modulehelper');
				// JPagination
				if (!class_exists('JPagination', false)) T3::import ('joomla25/pagination');
			}

			// import renderer
			T3::import('renderer/pageclass');
			T3::import('renderer/megamenu');
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

	public static function getAdmin(){
		T3::import ('core/admin');
		return new T3Admin();
	}

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

	public static function error($msg, $code = 500){
		if (JError::$legacy) {
			JError::setErrorHandling(E_ERROR, 'die');
			JError::raiseError($code, $msg);
			
			exit;
		} else {
			throw new Exception($msg, $code);
		}
	}

	public static function detect(){
		static $t3;

		if (!isset($t3)) {
			$t3 = false; // set false
			$app = JFactory::getApplication();
			$input = $app->input;
			
			// get template name
			$tplname = '';
			if($input->getCmd ('t3action') && ($styleid = $input->getInt('styleid', ''))) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('template, params');
				$query->from('#__template_styles');
				$query->where('client_id = 0');
				$query->where('id = '.$styleid);

				$db->setQuery($query);
				$template = $db->loadObject();
				if ($template) {
					$tplname = $template->template;
					$registry = new JRegistry;
					$registry->loadString($template->params);
					$input->set ('tplparams', $registry);
				}
			} elseif ($app->isAdmin()) {
				// if not login, do nothing
				$user = JFactory::getUser();
				if (!$user->id){
					return false;
				}

				if($input->getCmd('option') == 'com_templates' && 
					(preg_match('/style\./', $input->getCmd('task')) || $input->getCmd('view') == 'style' || $input->getCmd('view') == 'template')
					){
					$db = JFactory::getDBO();
					$query = $db->getQuery(true);
					$id = $input->getInt('id');

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
	 *
	 * Ge default template style
	 */
	public static function getDefaultTemplate(){
		static $defaultTemplate;

		if (!isset($defaultTemplate)) {

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('id, template, s.params')
				->from('#__template_styles as s')
				->where('s.client_id = 0')
				->where('s.home = 1')
				->where('e.enabled = 1')
				->leftJoin('#__extensions as e ON e.element=s.template AND e.type='.$db->quote('template').' AND e.client_id=s.client_id');

			$db->setQuery($query);
			$result = $db->loadObject();

			$defaultTemplate = !empty($result) ? $result->template : false;
		}

		return $defaultTemplate;
	}

	/**
	 *
	 * Ge template style params
	 */
	public static function getTemplateParams()
	{
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$params = $input->get('tplparams', '', 'raw'); //check for tplparams first

		if(!($params instanceof JRegistry)){
			$id = $input->getCmd('styleid', $input->getCmd('id'));
			if($id){
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query
					->select('template, params')
					->from('#__template_styles')
					->where('client_id = 0')
					->where('id = ' . $id);
				$db->setQuery($query);
				$template = $db->loadObject();
				
				if ($template) {
					$params = new JRegistry;
					$params->loadString($template->params);
				}
			}
		}
		
		return $params instanceof JRegistry ? $params : null;
	}
}
