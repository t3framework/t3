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


/**
 * Import T3 Library
 *
 * @param string $package    Object path that seperate by backslash (/)
 *
 * @return void
 */
function t3import($package)
{
	$path = T3_ADMIN_PATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . strtolower($package) . '.php';
	if (file_exists($path)) {
		include_once $path;
	} else {
		trigger_error('t3import not found object: ' . $package, E_USER_ERROR);
	}
}

/**
 * T3 Class
 * Singleton class for T3
 */
class T3 {
	
	protected static $t3app = null;

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
	public static function init () {
		// load core library
		t3import ('core/path');
		
		$app = JFactory::getApplication();
		if (!$app->isAdmin()) {
			$jversion  = new JVersion;
			if($jversion->isCompatible('3.0')){
				// override core joomla class
				// JViewLegacy
				if (!class_exists('JViewLegacy', false)) t3import ('joomla30/viewlegacy');
				// JModuleHelper
				if (!class_exists('JModuleHelper', false)) t3import ('joomla30/modulehelper');
				// JPagination
				if (!class_exists('JPagination', false)) t3import ('joomla30/pagination');
			} else {
				// override core joomla class
				// JViewLegacy
				if (!class_exists('JView', false)) t3import ('joomla25/view');
				// JModuleHelper
				if (!class_exists('JModuleHelper', false)) t3import ('joomla25/modulehelper');
				// JPagination
				if (!class_exists('JPagination', false)) t3import ('joomla25/pagination');
			}
		} else {
		}
	}

	public static function getAdmin(){
		t3import ('core/admin');
		return new T3Admin();
	}

	public static function getSite($tpl){
		//when on site, the JDocumentHTML parameter must be pass
		if(empty($tpl)){
			return false;
		}

		$type = 'Template'. JFactory::getApplication()->input->getCmd ('t3tp', '');
		t3import ('core/'.$type);

		// create global t3 template object 
		$class = 'T3'.$type;
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
}

T3::init();
?>