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
 * JDocument Megamenu renderer
 */
class JDocumentRendererT3Bootstrap extends JDocumentRenderer
{
	/**
	 * Render megamenu block
	 *
	 * @param   string  $position  The position of the modules to render
	 * @param   array   $params    Associative array of values
	 * @param   string  $content   Module content
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 */
	public function render($info = null, $params = array(), $content = null)
	{
		T3::import('menu/t3bootstrap');

		// import the renderer
		$t3app    = T3::getApp();
		$menutype = empty($params['menutype']) ? $t3app->getParam('mm_type', 'mainmenu') : $params['menutype'];

		JDispatcher::getInstance()->trigger('onT3BSMenu', array(&$menutype));
		$menu = new T3Bootstrap($menutype);
		
		return $menu->render(true);
	}
}
