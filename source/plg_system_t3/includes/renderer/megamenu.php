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
 * JDocument Megamenu renderer - this is a placeholder for menumenurender
 */
T3::import('renderer/megamenurender');

class JDocumentRendererMegamenu extends JDocumentRendererMegamenuRender
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
		$params['return_result'] = true;
		return parent::render($info, $params, $content);
	}
}
