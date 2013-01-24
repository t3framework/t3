<?php
/** 
 *------------------------------------------------------------------------------
 * @package   T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license   GNU General Public License; http://www.gnu.org/licenses/gpl.html
 * @author    JoomlArt, JoomlaBamboo 
 *            If you want to be come co-authors of this project, please follow 
 *            our guidelines at http://t3-framework.org/contribute
 *------------------------------------------------------------------------------
 */


/**
 *
 * Layout helper module class
 * @author JoomlArt
 *
 */
class T3AdminLayout
{
	public static function response($result = array()){
		die(json_encode($result));
	}

	public static function error($msg = ''){
		return self::response(array(
			'error' => $msg
			));
	}

	public static function display(){
		
		$japp = JFactory::getApplication();
		if(!$japp->isAdmin()){
			$tpl = $japp->getTemplate(true);
		} else {

			$tplid = JFactory::getApplication()->input->getCmd('view') == 'style' ? JFactory::getApplication()->input->getCmd('id', 0) : false;
			if(!$tplid){
				die(json_encode(array(
					'error' => JText::_('T3_MSG_UNKNOW_ACTION')
					)));
			}

			$cache = JFactory::getCache('com_templates', '');
			if (!$templates = $cache->get('t3tpl')) {
				// Load styles
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('id, home, template, s.params');
				$query->from('#__template_styles as s');
				$query->where('s.client_id = 0');
				$query->where('e.enabled = 1');
				$query->leftJoin('#__extensions as e ON e.element=s.template AND e.type='.$db->quote('template').' AND e.client_id=s.client_id');

				$db->setQuery($query);
				$templates = $db->loadObjectList('id');
				foreach($templates as &$template) {
					$registry = new JRegistry;
					$registry->loadString($template->params);
					$template->params = $registry;
				}
				$cache->store($templates, 't3tpl');
			}

			if (isset($templates[$tplid])) {
				$tpl = $templates[$tplid];
			}
			else {
				$tpl = $templates[0];
			}
		}

		ob_clean();
		$t3app = T3::getSite($tpl);
		$layout = $t3app->getLayout();
		$t3app->loadLayout($layout);
		$lbuffer = ob_get_clean();
		die($lbuffer);
	}

	public static function save()
	{
		// Initialize some variables
		$input = JFactory::getApplication()->input;
		$template = $input->getCmd('template');
		$layout = $input->getCmd('layout');
		if (!$template || !$layout) {
			return self::error(JText::_('T3_LAYOUT_INVALID_DATA_TO_SAVE'));
		}
		
		$file = JPATH_ROOT . '/templates/' . $template . '/etc/layout/' . $layout . '.ini';
		if (JFile::exists($file)) {
			@chmod($file, 0777);
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
		$input = JFactory::getApplication()->input;
		$template = $input->getCmd('template');
		$original = $input->getCmd('original');
		$layout = $input->getCmd('layout');

		//safe name
		$layout = JApplication::stringURLSafe($layout);

		if (!$template || !$original || !$layout) {
			return self::error(JText::_('T3_LAYOUT_INVALID_DATA_TO_SAVE'));
		}

		$srcpath = JPATH_ROOT . '/templates/' . $template . '/tpls/';
		$source = $srcpath . $original . '.php';
		$dest = $srcpath . $layout . '.php';

		$confpath = JPATH_ROOT . '/templates/' . $template . '/etc/layout/';
		$confdest = $confpath . $layout . '.ini';
		if (JFile::exists($confdest)) {
			@chmod($confdest, 0777);
		}

		$params = new JRegistry();
		$params->loadObject($_POST);

		$data = $params->toString('INI');
		if ($data && !@JFile::write($confdest, $data)) {
			return self::error(JText::_('T3_LAYOUT_OPERATION_FAILED'));
		}

		// Check if original file exists
		if (JFile::exists($source)) {
			// Check if the desired file already exists
			if (!JFile::exists($dest)) {
				if (!JFile::copy($source, $dest)) {
					return self::error(JText::_('T3_LAYOUT_OPERATION_FAILED'));
				} else {
					//clone configuration file, we only copy if the target file does not exist
					if(!JFile::exists($confdest) && JFile::exists($confpath . $original . '.ini')){
						JFile::copy($confpath . $original . '.ini', $confdest);
					}
				}
			}
			else {
				return self::error(JText::_('T3_LAYOUT_EXISTED'));
			}
		}
		else {
			return self::error(JText::_('T3_LAYOUT_NOT_FOUND'));
		}

		return self::response(array(
			'successful' => JText::_('T3_LAYOUT_SAVE_SUCCESSFULLY'),
			'original' => $original,
			'layout' => $layout,
			'type' => 'clone'
			));
	}

	public static function delete(){
		// Initialize some variables
		$input = JFactory::getApplication()->input;
		$layout = $input->getCmd('layout');
		$template = $input->getCmd('template');

		if (!$layout) {
			return self::error(JText::_('T3_LAYOUT_UNKNOW_ACTION'));
		}

		$layoutfile = JPATH_ROOT . '/templates/' . $template . '/tpls/' . $layout . '.php';
		$initfile = JPATH_ROOT . '/templates/' . $template . '/etc/layout/' . $layout . '.ini';

		$return = false;
		if (!JFile::exists($layoutfile)) {
			return self::error(JText::sprintf('T3_LAYOUT_NOT_FOUND', $layout));
		}
		
		$return = @JFile::delete($layoutfile);
		
		if (!$return) {
			return self::error(JText::_('T3_LAYOUT_DELETE_FAIL'));
		} else {
			@JFile::delete($initfile);
			
			return self::response(array(
				'successful' => JText::_('T3_LAYOUT_DELETE_SUCCESSFULLY'),
				'layout' => $layout,
				'type' => 'delete'
			));
		}
	}

	public static function getTplPositions(){

		$template = T3_TEMPLATE;
		$path = JPATH_SITE;
		$lang = JFactory::getLanguage();
		$lang->load('tpl_'.$template.'.sys', $path, null, false, false)
			||  $lang->load('tpl_'.$template.'.sys', $path.'/templates/'.$template, null, false, false)
			||  $lang->load('tpl_'.$template.'.sys', $path, $lang->getDefault(), false, false)
			||  $lang->load('tpl_'.$template.'.sys', $path.'/templates/'.$template, $lang->getDefault(), false, false);
			
		$options = array();
		
		$positions = self::getPositions($template);
		foreach ($positions as $position)
		{
			// Template translation
			
			$langKey = strtoupper('TPL_' . $template . '_POSITION_' . $position);
			$text = JText::_($langKey);

			// Avoid untranslated strings
			if ($langKey === $text)
			{
				// Modules component translation
				$langKey = strtoupper('COM_MODULES_POSITION_' . $position);
				$text = JText::_($langKey);

				if ($langKey === $text)
				{
					// Try to humanize the position name
					$text = ucfirst(preg_replace('/^' . $template . '\-/', '', $position));
					$text = ucwords(str_replace(array('-', '_'), ' ', $text));
				}
			}

			$text = $text . ' [' . $position . ']';
			$options[] = JHTML::_('select.option', $position, $text);
		}
		
		$lists = JHTML::_('select.genericlist', $options, '', 'multiple="multiple" size="10"', 'value', 'text', '');
		
		return $lists;
	}

	public static function getPositions($template = '')
	{
		$positions = array();

		$templateBaseDir = JPATH_SITE;
		$filePath = JPath::clean($templateBaseDir . '/templates/' . $template . '/templateDetails.xml');

		if (is_file($filePath))
		{
			// Read the file to see if it's a valid component XML file
			$xml = simplexml_load_file($filePath);
			if (!$xml)
			{
				return false;
			}

			// Check for a valid XML root tag.

			// Extensions use 'extension' as the root tag.  Languages use 'metafile' instead

			if ($xml->getName() != 'extension' && $xml->getName() != 'metafile')
			{
				unset($xml);
				return false;
			}

			$positions = (array) $xml->positions;

			if (isset($positions['position']))
			{
				$positions = $positions['position'];
			}
			else
			{
				$positions = array();
			}
		}

		return $positions;
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
		if (empty($text))
		{
			$text = $value;
		}

		$option = new stdClass;
		$option->value = $value;
		$option->text  = $text;

		return $option;
	}
}