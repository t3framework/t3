<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument Modules renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererT3Ajax extends JDocumentRenderer
{
	/**
	 * Renders multiple modules script and returns the results as a string
	 *
	 * @param   string  $position  The position of the modules to render
	 * @param   array   $params    Associative array of values
	 * @param   string  $content   Module content
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 */
	public function render($info, $params = array(), $content = null)
	{
		$input = JFactory::getApplication()->input;
		$task = $input->getCmd('t3ajax', 'position');
		$format = $input->getCmd('f', 'html');

		if($task == 'position'){
			if($format == 'html'){
				return $this->htmlPosition($info, $params, $content);
			} else if($format == 'json'){
				return $this->jsonPosition($info, $params, $content);
			}
		} else if($task == 'module'){
			if($format == 'html'){
				return $this->htmlModule($info, $params, $content);
			} else if($format == 'json'){
				return $this->jsonModule($info, $params, $content);
			}
		}

		return null;
	}

	protected function htmlPosition($info, $params = array(), $content = null)
	{		
		$renderer = $this->_doc->loadRenderer('module');
		
		$input = JFactory::getApplication()->input;
		$position = $input->getCmd('p');

		$buffer = '';

		foreach (JModuleHelper::getModules($position) as $mod)
		{
			$buffer .= $renderer->render($mod, $params, $content);
		}

		return $buffer;
	}

	protected function jsonPosition($info, $params = array(), $content = null)
	{		
		$result = array();
		
		$result['markup'] = $this->htmlPosition($info, $params = array(), $content = null);
		
		$result['stylesheets'] = $this->_doc->_styleSheets;
		$result['styles'] = $this->_doc->_style;

		$result['scripts'] = $this->_doc->_scripts;
		$result['scriptinlines'] = $this->_doc->_script;

		return json_encode($result);
	}

	protected function htmlModule($info, $params = array(), $content = null)
	{		
		$input = JFactory::getApplication()->input;
		$mid = $input->getCmd('m');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params');
		$query->from('#__modules AS m');
		$query->where('m.id = '. $mid);
		$query->where('m.published = 1');
		$db->setQuery($query);
		$module = $db->loadObject();
		
		$buffer = '';
		//check in case the module is unpublish or deleted
		if($module && $module->id){
			$buffer = JModuleHelper::renderModule($module, array('style'=> $input->getCmd('style', 'T3Xhtml')));
		}

		return $buffer;
	}

	protected function jsonModule($info, $params = array(), $content = null)
	{		
		$result = array();
		
		$result['markup'] = $this->htmlModule($info, $params = array(), $content = null);
		
		$result['stylesheets'] = $this->_doc->_styleSheets;
		$result['styles'] = $this->_doc->_style;

		$result['scripts'] = $this->_doc->_scripts;
		$result['scriptinlines'] = $this->_doc->_script;

		return json_encode($result);
	}
}
