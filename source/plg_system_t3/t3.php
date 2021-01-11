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
 * T3 plugin class
 *
 * @package        T3
 */

class plgSystemT3 extends JPlugin
{
	/**
	 * Switch template for thememagic
	 */
	function onAfterInitialise()
	{
		include_once dirname(__FILE__) . '/includes/core/defines.php';
		include_once dirname(__FILE__) . '/includes/core/t3.php';
		include_once dirname(__FILE__) . '/includes/core/bot.php';

		//must be in frontend
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return;
		}

		$input = $app->input;

		if($input->getCmd('themer', 0) && ($t3tmid = $input->getCmd('t3tmid', 0))){
			$user = JFactory::getUser();

			if($t3tmid > 0 && ($user->authorise('core.manage', 'com_templates') ||
					(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], JUri::base()) !== false))){

				$current = T3::getDefaultTemplate();
				if(!$current || ($current->id != $t3tmid)){

					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query
						->select('home, template, params')
						->from('#__template_styles')
						->where('client_id = 0 AND id= ' . (int)$t3tmid)
						->order('id ASC');
					$db->setQuery($query);
					$tm = $db->loadObject();

					if (is_object($tm) && file_exists(JPATH_THEMES . '/' . $tm->template)) {

						$app->setTemplate($tm->template, (new JRegistry($tm->params)));
						// setTemplate is buggy, we need to update more info
						// update the template
						$template = $app->getTemplate(true);
						$template->id = $t3tmid;
						$template->home = $tm->template;
					}
				}
			}
		}
	}

	function onAfterRoute()
	{
		if(defined('T3_PLUGIN')){

			T3Bot::preload();
			$template = T3::detect();

			if ($template) {

				// load the language
				$this->loadLanguage();

				T3Bot::beforeInit();
				T3::init($template);
				T3Bot::afterInit();

				//load T3 plugins
				JPluginHelper::importPlugin('t3');

				if (is_file(T3_TEMPLATE_PATH . '/templateHook.php')) {
					include_once T3_TEMPLATE_PATH . '/templateHook.php';
				}

				$tplHookCls = preg_replace('/(^[^A-Z_]+|[^A-Z0-9_])/i', '', T3_TEMPLATE . 'Hook');
				$dispatcher = JDispatcher::getInstance();

				if (class_exists($tplHookCls)) {
					new $tplHookCls($dispatcher, array());
				}

				$dispatcher->trigger('onT3Init');

				//check and execute the t3action
				T3::checkAction();

				//check and change template for ajax
				T3::checkAjax();
			}
		}
	}

	function onBeforeRender()
	{
		if (defined('T3_PLUGIN') && T3::detect()) {
			$japp = JFactory::getApplication();

			JDispatcher::getInstance()->trigger('onT3BeforeRender');

			if ($japp->isAdmin()) {

				$t3app = T3::getApp();
				$t3app->addAssets();
			} else {
				$params = $japp->getTemplate(true)->params;
				if (defined('T3_THEMER') && $params->get('themermode', 1)) {
					T3::import('admin/theme');
					T3AdminTheme::addAssets();
				}

				//check for ajax action and render t3ajax type to before head type
				if (class_exists('T3Ajax')) {
					T3Ajax::render();
				}
				
				// allow load module/modules in component using jdoc:include
				$doc = JFactory::getDocument();
				$main_content = $doc->getBuffer('component');
				if ($main_content) {
					// parse jdoc
					if (preg_match_all('#<jdoc:include\ type="([^"]+)"(.*)\/>#iU', $main_content, $matches))
					{
						$replace = array();
						$with = array();
				
						// Step through the jdocs in reverse order.
						for ($i = 0; $i < count($matches[0]); $i++)
						{
						$type = $matches[1][$i];
						$attribs = empty($matches[2][$i]) ? array() : JUtility::parseAttributes($matches[2][$i]);
						$name = isset($attribs['name']) ? $attribs['name'] : null;
								$replace[] = $matches[0][$i];
								$with[] = $doc->getBuffer($type, $name, $attribs);
						}
				
						$main_content = str_replace($replace, $with, $main_content);
				
						// update buffer
						$doc->setBuffer($main_content, 'component');
					}
				}				
			}
		}
	}

	function onBeforeCompileHead()
	{
		if (defined('T3_PLUGIN') && T3::detect() && !JFactory::getApplication()->isAdmin()) {
			// call update head for replace css to less if in devmode
			$t3app = T3::getApp();
			if ($t3app) {

				JDispatcher::getInstance()->trigger('onT3BeforeCompileHead');

				$t3app->updateHead();

				JDispatcher::getInstance()->trigger('onT3AfterCompileHead');
			}
		}
	}

	function onAfterRender()
	{
		if (defined('T3_PLUGIN') && T3::detect()) {
			$t3app = T3::getApp();

			if ($t3app) {

				if (JFactory::getApplication()->isAdmin()) {
					$t3app->render();
				} else {
					$t3app->snippet();
				}

				JDispatcher::getInstance()->trigger('onT3AfterRender');
			}
		}
	}

	/**
	 * Add JA Extended menu parameter in administrator
	 *
	 * @param   JForm $form   The form to be altered.
	 * @param   array $data   The associated data for the form
	 *
	 * @return  null
	 */
	function onContentPrepareForm($form, $data)
	{

		if(defined('T3_PLUGIN')){
			$form_name = $form->getName();
			// make it compatible with AMM
			if ($form_name == 'com_advancedmodules.module') $form_name = 'com_modules.module';

			if (T3::detect() && (
				$form_name == 'com_templates.style'
				|| $form_name == 'com_config.templates'
			)) {

				$_form = clone $form;
				$_form->loadFile(T3_PATH . '/params/template.xml', false);
				//custom config in custom/etc/assets.xml
				$cusXml = T3Path::getPath ('etc/assets.xml');
				if ($cusXml && file_exists($cusXml))
					$_form->loadFile($cusXml, true, '//config');

				// extend parameters
				T3Bot::prepareForm($form);

				//search for global parameters and store in user state
				$app      = JFactory::getApplication();
				$gparams = array();
				foreach($_form->getGroup('params') as $param){
					if($_form->getFieldAttribute($param->fieldname, 'global', 0, 'params')){
						$gparams[] = $param->fieldname;
					}
				}
				$this->gparams = $gparams;
			}

			$tmpl = T3::detect() ? T3::detect() : (T3::getDefaultTemplate(true) ? T3::getDefaultTemplate(true) : false);

			if ($tmpl) {
				$tplpath  = JPATH_ROOT . '/templates/' . (is_object($tmpl) && !empty($tmpl->tplname) ? $tmpl->tplname : $tmpl);
				$formpath = $tplpath . '/etc/form/';
				JForm::addFormPath($formpath);

				$extended = $formpath . $form_name . '.xml';
				if (is_file($extended)) {
					JFactory::getLanguage()->load('tpl_' . $tmpl, JPATH_SITE);
					$form->loadFile($form_name, false);
				}

				// load extra fields for specified module in format com_modules.module.module_name.xml
				if ($form_name == 'com_modules.module') {
					$module = isset($data->module) ? $data->module : '';
					if (!$module) {
						$jform = JFactory::getApplication()->input->get ("jform", null, 'array');
						$module = $jform['module'];
					}
					$extended = $formpath . $module . '.xml';
					if (is_file($extended)) {
						JFactory::getLanguage()->load('tpl_' . $tmpl, JPATH_SITE);
						$form->loadFile($module, false);
					}
				}

				//extend extra fields
				T3Bot::extraFields($form, $data, $tplpath);
			}

			// Extended by T3
			$extended = T3_ADMIN_PATH . '/admin/form/' . $form_name . '.xml';
			if (is_file($extended)) {
				$form->loadFile($extended, false);
			}

		}
	}

	function onContentBeforeSave($context, $data, $isNew)
	{
		// Check we are handling the frontend edit form.
		if ($context == 'com_content.form')
		{
			// $this->t4->onContentBeforeSave($context, $data, $isNew);
			//extend extra fields update value
			T3Bot::onContentBeforeSave($context, $data, $isNew);
		}
		return true;
	}
	
	function onExtensionAfterSave($option, $data)
	{
		if (defined('T3_PLUGIN') && T3::detect() && $option == 'com_templates.style' && !empty($data->id)) {
			//get new params value
			$japp = JFactory::getApplication();
			$params = new JRegistry;
			$params->loadString($data->params);
			//if we have any changed, we will update to global
			if (isset($this->gparams) && count($this->gparams)) {

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
				foreach ($themes as $theme) {
					$registry = new JRegistry;
					$registry->loadString($theme->params);

					foreach ($this->gparams as $pname) {
						$registry->set($pname, $params->get($pname)); //overwrite with new value
					}

					$query = $db->getQuery(true);
					$query
						->update('#__template_styles')
						->set('params =' . $db->quote($registry->toString()))
						->where('id =' . (int)$theme->id)
						->where('id <>' . (int)$data->id);

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	/**
	 * Implement event onRenderModule to include the module chrome provide by T3
	 * This event is fired by overriding ModuleHelper class
	 * Return false for continueing render module
	 *
	 * @param   object &$module   A module object.
	 * @param   array $attribs   An array of attributes for the module (probably from the XML).
	 *
	 * @return  bool
	 */
	function onRenderModule(&$module, $attribs)
	{
		static $chromed = false;
		// Detect layout path in T3 themes
		if (defined('T3_PLUGIN') && T3::detect()) {

			// fix JA Backlink
			if($module->module == 'mod_footer'){
				$module->content = T3::fixJALink($module->content);
			}

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
	 * @param   string $module  The name of the module
	 * @param   string $layout  The name of the module layout. If alternative
	 *                           layout, in the form template:filename.
	 *
	 * @return  null
	 */
	function onGetLayoutPath($module, $layout)
	{
		// Detect layout path in T3 themes
		if (defined('T3_PLUGIN') && T3::detect()) {

			T3::import('core/path');

			$tPath = T3Path::getPath('html/' . $module . '/' . $layout . '.php');
			if ($tPath) {
				return $tPath;
			}
		}

		return false;
	}

	/**
	 * Update params before rendering content
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$article  The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer  $page      The 'page' number
	 *
	 * @return  mixed   true if there is an error. Void otherwise.
	 *
	 * @since   1.6
	 */
	public function onContentPrepare ($context, &$article, &$params, $page = 0) {
		// update params for Article View
		if ($context == 'com_content.article') {
			$app = JFactory::getApplication();
			$tmpl = $app->getTemplate(true);
			if ($tmpl->params->get('link_titles') !== NULL) {
				if (isset($article->params) && is_object($article->params)) $article->params->set('link_titles', $tmpl->params->get('link_titles'));
			}
		}
	}
}
