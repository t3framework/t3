<?php
/**
 * $JA#COPYRIGHT$
 */

if (!defined('_JEXEC')) {
    // no direct access
	define('_JEXEC', 1);
	defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
	$path = dirname(dirname(dirname(dirname(__FILE__))));
	define('JPATH_BASE', $path);

	if (strpos(php_sapi_name(), 'cgi') !== false && !empty($_SERVER['REQUEST_URI'])) {
        //Apache CGI
		$_SERVER['PHP_SELF'] = rtrim(dirname(dirname(dirname($_SERVER['PHP_SELF']))), '/\\');
	} else {
        //Others
		$_SERVER['SCRIPT_NAME'] = rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/\\');
	}

	require_once (JPATH_BASE . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'defines.php');
	require_once (JPATH_BASE . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'framework.php');
	JDEBUG ? $_PROFILER->mark('afterLoad') : null;

	/**
	 * CREATE THE APPLICATION
	 *
	 * NOTE :
	 */
	$japp = JFactory::getApplication('administrator');

	/**
	 * INITIALISE THE APPLICATION
	 *
	 * NOTE :
	 */
	$japp->initialise(array('language' => $japp->getUserState('application.lang', 'lang')));
}

$user = JFactory::getUser();

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


if(!$user->authorise('core.manage', 'com_templates')){
	die(json_encode(array(JText::_('NO_PERMISSION'))));
}


$helpcls = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fileconfig.php';
if(file_exists($helpcls))
include_once $helpcls;

$task = isset($_REQUEST['jaction']) ? $_REQUEST['jaction'] : '';
if ($task != '' && method_exists('JAFileConfig', $task)) {
	JAFileConfig::$task();
}