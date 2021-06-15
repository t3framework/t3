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
 * JDocument Bodyclass renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererPageClass extends JDocumentRenderer
{
	/**
	 * Render body class of current page
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
		$t3tpl = T3::getApp();
		$pageclass = array();
		if($input->getCmd('option', '')){
			$pageclass[] = $input->getCmd('option', '');
		}
		if($input->getCmd('view', '')){
			$pageclass[] = 'view-' . $input->getCmd('view', '');
		}
		if($input->getCmd('layout', '')){
			$pageclass[] = 'layout-' . $input->getCmd('layout', '');
		}
		if($input->getCmd('task', '')){
			$pageclass[] = 'task-' . $input->getCmd('task', '');
		}
		if($input->getCmd('Itemid', '')){
			$pageclass[] = 'itemid-' . $input->getCmd('Itemid', '');
		}

		$menu = JFactory::getApplication()->getMenu();
		if($menu){
			$active = $menu->getActive();
			$default = $menu->getDefault();

			if ($active) {
				if($default && $active->id == $default->id){
					$pageclass[] = 'home';
				}

				if ($active->getParams() && $active->getParams()->get('pageclass_sfx')) {
					$pageclass[] = $active->getParams()->get('pageclass_sfx');
				}
			}
		}

		$pageclass[] = 'j'.str_replace('.', '', (number_format((float)JVERSION, 1, '.', '')));
		$pageclass = array_unique(array_merge($pageclass, $t3tpl->getPageclass()));

		JFactory::getApplication()->triggerEvent('onT3BodyClass', array(&$pageclass));

		return implode(' ', $pageclass);
	}

}
