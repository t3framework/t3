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
 * Joomla! P3P Header Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	System.p3p
 */

class plgSystemT3 extends JPlugin
{
	//function onAfterInitialise(){
	function onAfterRoute(){
		include_once dirname(__FILE__) . '/includes/core/defines.php';
		include_once dirname(__FILE__) . '/includes/core/t3.php';
		include_once dirname(__FILE__) . '/includes/core/bot.php';
		T3Bot::preload();
		$template = T3::detect();
		if($template){			
			T3Bot::beforeInit();
			T3::init($template);
			T3Bot::afterInit();
			T3::checkAction();
		}
	}
	
	function onBeforeRender(){
		if(T3::detect()){
			$japp = JFactory::getApplication();
			if($japp->isAdmin()){

				$t3app = T3::getApp();
				$t3app->addAssets();
			} else {
				$params = $japp->getTemplate(true)->params;
				if(defined('T3_THEMER') && $params->get('themermode', 1)){
					T3::import('admin/theme');
					T3AdminTheme::addAssets();
				}
			}
		}
	}
	
	function onBeforeCompileHead () {
		$app = JFactory::getApplication();
		if(T3::detect() && !$app->isAdmin()){
			// call update head for replace css to less if in devmode
			$t3app = T3::getApp();
			if($t3app){
				$t3app->updateHead();
			}
		}
	}

	function onAfterRender ()
	{
		$japp = JFactory::getApplication();
		if($japp->isAdmin()){
			if(T3::detect()){
				$t3app = T3::getApp();
				$t3app->render();
			}
		}
	}
	
	/**
	 * Add JA Extended menu parameter in administrator
	 *
	 * @param   JForm   $form   The form to be altered.
	 * @param   array   $data   The associated data for the form
	 *
	 * @return  null
	 */
	function onContentPrepareForm($form, $data)
	{
		// extra option for menu item
		/*if ($form->getName() == 'com_menus.item') {
			$this->loadLanguage();
			JForm::addFormPath(T3_PATH . DIRECTORY_SEPARATOR . 'params');
			$form->loadFile('megaitem', false);

			$jversion = new JVersion;
			if(!$jversion->isCompatible('3.0')){
				$jdoc = JFactory::getDocument();
				$jdoc->addScript(T3_ADMIN_URL . '/admin/js/jquery-1.8.0.min.js');
				$jdoc->addScript(T3_ADMIN_URL . '/admin/js/jquery.noconflict.js');
			}

		} else 
		*/
		if(T3::detect() && $form->getName() == 'com_templates.style'){
			$this->loadLanguage();
			JForm::addFormPath(T3_PATH . DIRECTORY_SEPARATOR . 'params');
			$form->loadFile('template', false);
		}
	}
	
	function onExtensionAfterSave($option, $data){
		if(T3::detect() && $option == 'com_templates.style' && !empty($data->id)){
			//get new params value
			$japp = JFactory::getApplication();
			$params = new JRegistry;
			$params->loadString($data->params);
			$oparams = $japp->getUserState('oparams');

			//check for changed params
			$pchanged = array();
			foreach($oparams as $oparam){
				if($params->get($oparam['name']) != $oparam['value']){
					$pchanged[] = $oparam['name'];
				}
			}

			//if we have any changed, we will update to global
			if(count($pchanged)){

				//get all other styles that have the same template
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query
					->select('*')
					->from('#__template_styles')
					->where('template=' . $db->quote($data->template));

				$db->setQuery($query);
				$themes = $db->loadObjectList();
				
				//update all global parameters
				foreach($themes as $theme){
					$registry = new JRegistry;
					$registry->loadString($theme->params);

					foreach($pchanged as $pname){
						$registry->set($pname, $params->get($pname)); //overwrite with new value
					}

					$query = $db->getQuery(true);
					$query
						->update('#__template_styles')
						->set('params =' . $db->quote($registry->toString()))
						->where('id =' . (int)$theme->id)
						->where('id <>' . (int)$data->id);

					$db->setQuery($query);
					$db->query();
				}
			}
		}
	}

	/**
	 * Implement event onRenderModule to include the module chrome provide by T3
	 * This event is fired by overriding ModuleHelper class
	 * Return false for continueing render module
	 *
	 * @param   object  &$module   A module object.
	 * @param   array   $attribs   An array of attributes for the module (probably from the XML).
	 *
	 * @return  bool
	 */
	function onRenderModule (&$module, $attribs)
	{
		static $chromed = false;
		// Detect layout path in T3 themes
		if (T3::detect()) {
			// Chrome for module
			if (!$chromed) {
				$chromed = true;
				// We don't need chrome multi times
				$chromePath = T3Path::getPath('html/modules.php');
				if (file_exists($chromePath)) {
					include_once $chromePath;
				}
			}
		}
		return false;
	}

	/**
	 * Implement event onGetLayoutPath to return the layout which override by T3 & T3 templates
	 * This event is fired by overriding ModuleHelper class
	 * Return path to layout if found, false if not
	 *
	 * @param   string  $module  The name of the module
	 * @param   string  $layout  The name of the module layout. If alternative
	 *                           layout, in the form template:filename.
	 *
	 * @return  null
	 */
	function onGetLayoutPath($module, $layout)
	{
		// Detect layout path in T3 themes
		if (T3::detect()) {
			$tPath = T3Path::getPath('html/' . $module . '/' . $layout . '.php');
			if ($tPath)
				return $tPath;
		}
		return false;
	}	
}
