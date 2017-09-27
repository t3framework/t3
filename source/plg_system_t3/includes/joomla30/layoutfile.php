<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;


// Read core JLayoutFile from library
$filepath = JPATH_LIBRARIES . '/cms/layout/file.php';
$filecontent = file_get_contents($filepath);
// remove open php
$filecontent = str_replace(array('<?php', '?>', ' JLayoutFile '), array('', '', ' JLayoutFileCore '), $filecontent);
// define class JLayoutFileCore
eval ($filecontent);


// now define JLayoutFile class override method getDefaultIncludePaths
/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @see    https://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since  3.0
 */
class JLayoutFile extends JLayoutFileCore
{

	/**
	 * Get the default array of include paths
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getDefaultIncludePaths()
	{
		// Reset includePaths
		$paths = array();

		// (1 - highest priority) Received a custom high priority path
		if ($this->basePath !== null)
		{
			$paths[] = rtrim($this->basePath, DIRECTORY_SEPARATOR);
		}

		// Component layouts & overrides if exist
		$component = $this->options->get('component', null);

		if (!empty($component))
		{
			// (2) Component template overrides path
			$paths[] = JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts/' . $component;

			// (3) Component path
			if ($this->options->get('client') == 0)
			{
				$paths[] = JPATH_SITE . '/components/' . $component . '/layouts';
			}
			else
			{
				$paths[] = JPATH_ADMINISTRATOR . '/components/' . $component . '/layouts';
			}
		}

		// T3 - (4.1) - user custom layout overridden
		if (!defined('T3_LOCAL_DISABLED')) $paths[] = T3_LOCAL_PATH . '/html/layouts';

		// (4) Standard Joomla! layouts overriden
		$paths[] = JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts';

		// T3 - (5.1) - T3 base layout overridden
		$paths[] = T3_PATH . '/html/layouts';

		// (5 - lower priority) Frontend base layouts
		$paths[] = JPATH_ROOT . '/layouts';

		return $paths;
	}

}
