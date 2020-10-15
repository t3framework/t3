<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Filesystem\Path;

defined('JPATH_PLATFORM') or die;


// Make alias of original FileLayout
\T3::makeAlias(JPATH_LIBRARIES . '/src/MVC/View/HtmlView.php', 'HtmlView', '_JHtmlView');


/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  2.5.5
 */
class HtmlView extends _JHtmlView
{

	/**
	 * Sets an entire array of search paths for templates or resources.
	 *
	 * @param   string  $type  The type of path to set, typically 'template'.
	 * @param   mixed   $path  The new search path, or an array of search paths.  If null or false, resets to the current directory only.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function _setPath($type, $path)
	{
		$component = \JApplicationHelper::getComponentName();
		$app = \JFactory::getApplication();

		// Clear out the prior search dirs
		$this->_path[$type] = array();

		// Actually add the user-specified directories
		$this->_addPath($type, $path);

		// Always add the fallback directories as last resort
		switch (strtolower($type))
		{
			case 'template':
				// Set the alternative template search dir
				if (isset($app))
				{
					$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $component);

					//if it is T3 template, update search path for template
					$this->_addPath('template', T3_PATH . '/html/' . $component . '/' . $this->getName());
					if (\T3::isAdmin()) $this->_addPath('template', T3_ADMIN_PATH . '/admin/html/' . $component . '/' . $this->getName());

					$fallback = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . $this->getName();
					$this->_addPath('template', $fallback);

					//search path for user custom folder
					if (!defined('T3_LOCAL_DISABLED')) $this->_addPath('template', T3_LOCAL_PATH . '/html/' . $component . '/' . $this->getName());
				}
				break;
		}
	}


}
