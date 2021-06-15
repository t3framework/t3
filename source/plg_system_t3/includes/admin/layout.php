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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Layout helper module class
 */
class T3AdminLayout
{
	public static function response($result = array())
	{
		die(json_encode($result));
	}
	
	public static function error($msg = '')
	{
		return self::response(array(
			'error' => $msg
		));
	}
	
	public static function display()
	{
		
		$app   = JFactory::getApplication();
		$input = $app->input;
		
		if (!T3::isAdmin()) {
			
			$tpl = $app->getTemplate(true);
			
			// get template name
			if ($input->getCmd('t3action') && ($styleid = $input->getInt('styleid', '')) && $tpl->id != $styleid) {
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('template, params');
				$query->from('#__template_styles');
				$query->where('client_id = 0');
				$query->where('id = ' . $styleid);
				
				$db->setQuery($query);
				$tpl = $db->loadObject();
				
				if ($tpl) {
					$registry = new JRegistry;
					$registry->loadString($tpl->params);
					$tpl->params = $registry;
				}
				
				if (!$tpl) {
					die(json_encode(array(
						'error' => JText::_('T3_MSG_UNKNOW_ACTION')
					)));
				}
			}
			
		} else {
			
			$tplid = $input->getCmd('view') == 'style' ? $input->getCmd('id', 0) : false;
			if (!$tplid) {
				die(json_encode(array(
					'error' => JText::_('T3_MSG_UNKNOW_ACTION')
				)));
			}
			
			$cache = JFactory::getCache('com_templates', '');
			if (!$templates = $cache->get('t3tpl')) {
				// Load styles
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('id, home, template, s.params');
				$query->from('#__template_styles as s');
				$query->where('s.client_id = 0');
				$query->where('e.enabled = 1');
				$query->leftJoin('#__extensions as e ON e.element=s.template AND e.type=' . $db->quote('template') . ' AND e.client_id=s.client_id');
				
				$db->setQuery($query);
				$templates = $db->loadObjectList('id');
				foreach ($templates as &$template) {
					$registry = new JRegistry;
					$registry->loadString($template->params);
					$template->params = $registry;
				}
				$cache->store($templates, 't3tpl');
			}
			
			if (isset($templates[$tplid])) {
				$tpl = $templates[$tplid];
			} else {
				$tpl = $templates[0];
			}
		}
		
		//load language for template
		JFactory::getLanguage()->load('tpl_' . T3_TEMPLATE, JPATH_SITE);
		
		//clean all unnecessary datas
		if(ob_get_length()){
			@ob_end_clean();
		}
		$t3app  = T3::getSite($tpl);
		$layout = $t3app->getLayout();
		$t3app->loadLayout($layout);
		$lbuffer = ob_get_clean();
		die($lbuffer);
	}
	
	public static function save()
	{
		// Initialize some variables
		$input    = JFactory::getApplication()->input;
		$template = $input->getCmd('template');
		$layout   = $input->getCmd('layout');
		if (!$template || !$layout) {
			return self::error(JText::_('T3_LAYOUT_INVALID_DATA_TO_SAVE'));
		}

		// store layout configuration into custom directory
    $file = T3Path::getLocalPath ('etc/layout/' . $layout . '.ini');

		if (!is_dir(dirname($file))) {
			JFolder::create(dirname($file));
		}
		
		$params = new JRegistry();
		$params->loadObject($_POST);
		
		$data = $params->toString('INI');
		if (!@JFile::write($file, $data)) {
			return self::error(JText::_('T3_LAYOUT_OPERATION_FAILED'));
		}
		
		return self::response(array(
			'successful' => JText::sprintf('T3_LAYOUT_SAVE_SUCCESSFULLY', $layout),
			'layout' => $layout,
			'type' => 'new'
		));
	}
	
	public static function copy()
	{
		// Initialize some variables
		$input    = JFactory::getApplication()->input;
		$template = $input->getCmd('template');
		$original = $input->getCmd('original');
		$layout   = $input->getCmd('layout');
		
		//safe name
		$layout = JApplication::stringURLSafe($layout);
		
		if (!$template || !$original || !$layout) {
			return self::error(JText::_('T3_LAYOUT_INVALID_DATA_TO_SAVE'));
		}


		// clone to CUSTOM dir
		$source = T3Path::getPath('tpls/' . $original . '.php');
    $dest   = T3Path::getLocalPath('tpls/' . $layout . '.php');
		$confsource = T3Path::getPath('etc/layout/'. $layout . '.ini');
    $confdest   = T3Path::getLocalPath('etc/layout/'. $layout . '.ini');

		$params = new JRegistry();
		$params->loadObject($_POST);
		
		$data = $params->toString('INI');
		
		if (!is_dir(dirname($confdest))) {
			JFolder::create(dirname($confdest));
		}

		if (!is_dir(dirname($dest))) {
			JFolder::create(dirname($dest));
		}
		
		if ($data && !@JFile::write($confdest, $data)) {
			return self::error(JText::_('T3_LAYOUT_OPERATION_FAILED'));
		}
		
		// Check if original file exists
		if (JFile::exists($source)) {
			// Check if the desired file already exists
			if (!JFile::exists($dest)) {
				if (!JFile::copy($source, $dest)) {
					return self::error(JText::_('T3_LAYOUT_OPERATION_FAILED'));
				}
				//clone configuration file, we only copy if the target file does not exist
				if (!JFile::exists($confdest) && JFile::exists($confsource)) {
					JFile::copy($confsource, $confdest);
				}
			} else {
				return self::error(JText::_('T3_LAYOUT_EXISTED'));
			}
		} else {
			return self::error(JText::_('T3_LAYOUT_NOT_FOUND'));
		}
		
		return self::response(array(
			'successful' => JText::_('T3_LAYOUT_SAVE_SUCCESSFULLY'),
			'original' => $original,
			'layout' => $layout,
			'type' => 'clone'
		));
	}
	
	public static function delete()
	{
		// Initialize some variables
		$input    = JFactory::getApplication()->input;
		$layout   = $input->getCmd('layout');
		$template = $input->getCmd('template');
		
		if (!$layout) {
			return self::error(JText::_('T3_LAYOUT_UNKNOW_ACTION'));
		}
		
		// delete custom layout    
		$layoutfile = T3Path::getLocalPath('tpls/' . $layout . '.php');
		$initfile   = T3Path::getLocalPath('etc/layout/' . $layout . '.ini');

		if (!@JFile::delete($layoutfile) || !@JFile::delete($initfile)) {
			return self::error(JText::_('T3_LAYOUT_DELETE_FAIL'));
		} else {
			return self::response(array(
				'successful' => JText::_('T3_LAYOUT_DELETE_SUCCESSFULLY'),
				'layout' => $layout,
				'type' => 'delete'
			));
		}
	}

	public static function purge()
	{
		// Initialize some variables
		$input    = JFactory::getApplication()->input;
		$layout   = $input->getCmd('layout');
		$template = $input->getCmd('template');

		if (!$layout) {
			return self::error(JText::_('T3_LAYOUT_UNKNOW_ACTION'));
		}

		// delete custom layout
		$layoutfile = T3Path::getLocalPath('tpls/' . $layout . '.php');
		$initfile   = T3Path::getLocalPath('etc/layout/' . $layout . '.ini');

		// delete default layout
		$defaultlayoutfile = T3_TEMPLATE_PATH . '/tpls/' . $layout . '.php';
		$defaultinitfile   = T3_TEMPLATE_PATH . '/etc/layout/' . $layout . '.ini';

		if (!@JFile::delete($layoutfile) || !@JFile::delete($defaultlayoutfile)
        || !@JFile::delete($initfile) || !@JFile::delete($defaultinitfile)
      ) {
			return self::error(JText::_('T3_LAYOUT_DELETE_FAIL'));
		} else {
			return self::response(array(
				'successful' => JText::_('T3_LAYOUT_DELETE_SUCCESSFULLY'),
				'layout' => $layout,
				'type' => 'delete'
			));
		}
	}
	
	public static function getTplPositions($clientId = 0, $template = '')
	{
		$positions = array();
		
		$templateBaseDir = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;
		$filePath        = JPath::clean($templateBaseDir . '/templates/' . $template . '/templateDetails.xml');
		
		if (is_file($filePath)) {
			// Read the file to see if it's a valid component XML file
			$xml = simplexml_load_file($filePath);
			if (!$xml) {
				return false;
			}
			
			// Check for a valid XML root tag.
			
			// Extensions use 'extension' as the root tag.  Languages use 'metafile' instead
			
			if ($xml->getName() != 'extension' && $xml->getName() != 'metafile') {
				unset($xml);
				return false;
			}
			
			$positions = (array) $xml->positions;
			
			if (isset($positions['position'])) {
				$positions = $positions['position'];
			} else {
				$positions = array();
			}
		}
		
		return $positions;
	}
	
	public static function getPositions()
	{
		
		$template = T3_TEMPLATE;
		$path     = JPATH_SITE;
		$lang     = JFactory::getLanguage();
		
		$clientId = 0;
		$state    = 1;
		
		$templates      = array_keys(self::getTemplates($clientId, $state));
		$templateGroups = array();
		
		// Add positions from templates
		foreach ($templates as $template) {
			$options = array();
			
			$positions = self::getTplPositions($clientId, $template);
			if (is_array($positions))
				foreach ($positions as $position) {
					$text      = self::getTranslatedModulePosition($clientId, $template, $position) . ' [' . $position . ']';
					$options[] = self::createOption($position, $text);
				}
			
			$templateGroups[$template] = self::createOptionGroup(ucfirst($template), $options);
		}
		
		// Add custom position to options
		$customGroupText                  = JText::_('T3_LAYOUT_CUSTOM_POSITION');
		$customPositions                  = self::getDbPositions($clientId);
		$templateGroups[$customGroupText] = self::createOptionGroup($customGroupText, $customPositions);
		
		return JHtml::_('select.groupedlist', $templateGroups, '', array(
			'id' => 'tpl-positions-list',
			'list.select' => ''
		));
		
	}
	
	public static function getDbPositions($clientId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT(position)')
			->from('#__modules')
			->where($db->quoteName('client_id') . ' = ' . (int) $clientId)->order('position');
		
		$db->setQuery($query);
		
		try {
			$positions = $db->loadColumn();
			$positions = is_array($positions) ? $positions : array();
		}
		catch (RuntimeException $e) {
			JError::raiseWarning(500, $e->getMessage());
			return;
		}
		
		// Build the list
		$options = array();
		foreach ($positions as $position) {
			if ($position) {
				$options[] = JHtml::_('select.option', $position, $position);
			}
		}
		return $options;
	}
	
	/**
	 * Create and return a new Option
	 *
	 * @param   string  $value  The option value [optional]
	 * @param   string  $text   The option text [optional]
	 *
	 * @return  object  The option as an object (stdClass instance)
	 *
	 * @since   3.0
	 */
	public static function createOption($value = '', $text = '')
	{
		if (empty($text)) {
			$text = $value;
		}
		
		$option        = new stdClass;
		$option->value = $value;
		$option->text  = $text;
		
		return $option;
	}
	
	/**
	 * Create and return a new Option Group
	 *
	 * @param   string  $label    Value and label for group [optional]
	 * @param   array   $options  Array of options to insert into group [optional]
	 *
	 * @return  array  Return the new group as an array
	 *
	 * @since   3.0
	 */
	public static function createOptionGroup($label = '', $options = array())
	{
		$group          = array();
		$group['value'] = $label;
		$group['text']  = $label;
		$group['items'] = $options;
		
		return $group;
	}
	
	/**
	 * Check if the string was translated
	 *
	 * @param   string  $langKey  Language file text key
	 * @param   string  $text     The "translated" text to be checked
	 *
	 * @return  boolean  Return true for translated text
	 *
	 * @since   3.0
	 */
	public static function isTranslatedText($langKey, $text)
	{
		return $text !== $langKey;
	}
	
	/**
	 * Return a translated module position name
	 *
	 * @param   string  $template  Template name
	 * @param   string  $position  Position name
	 *
	 * @return  string  Return a translated position name
	 *
	 * @since   3.0
	 */
	public static function getTranslatedModulePosition($clientId, $template, $position)
	{
		// Template translation
		$lang = JFactory::getLanguage();
		$path = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;
		
		$lang->load('tpl_' . $template . '.sys', $path, null, false, false) 
			|| $lang->load('tpl_' . $template . '.sys', $path . '/templates/' . $template, null, false, false) 
			|| $lang->load('tpl_' . $template . '.sys', $path, $lang->getDefault(), false, false) 
			|| $lang->load('tpl_' . $template . '.sys', $path . '/templates/' . $template, $lang->getDefault(), false, false);
		
		$langKey = strtoupper('TPL_' . $template . '_POSITION_' . $position);
		$text    = JText::_($langKey);
		
		// Avoid untranslated strings
		if (!self::isTranslatedText($langKey, $text)) {
			// Modules component translation
			$langKey = strtoupper('COM_MODULES_POSITION_' . $position);
			$text    = JText::_($langKey);
			
			// Avoid untranslated strings
			if (!self::isTranslatedText($langKey, $text)) {
				// Try to humanize the position name
				$text = ucfirst(preg_replace('/^' . $template . '\-/', '', $position));
				$text = ucwords(str_replace(array(
					'-',
					'_'
				), ' ', $text));
			}
		}
		
		return $text;
	}
	
	/**
	 * Return a list of templates
	 *
	 * @param   integer  $clientId  Client ID
	 * @param   string   $state     State
	 * @param   string   $template  Template name
	 *
	 * @return  array  List of templates
	 */
	public static function getTemplates($clientId = 0, $state = '', $template = '')
	{
		$db = JFactory::getDbo();
		
		// Get the database object and a new query object.
		$query = $db->getQuery(true);
		
		// Build the query.
		$query
			->select('element, name, enabled')
			->from('#__extensions')
			->where('client_id = ' . (int) $clientId)
			->where('type = ' . $db->quote('template'));

		if ($state != '') {
			$query->where('enabled = ' . $db->quote($state));
		}
		
		if ($template != '') {
			$query->where('element = ' . $db->quote($template));
		}
		
		// Set the query and load the templates.
		$db->setQuery($query);
		$templates = $db->loadObjectList('element');
		return $templates;
	}
}