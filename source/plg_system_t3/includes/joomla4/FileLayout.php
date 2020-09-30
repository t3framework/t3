<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Layout;

use Joomla\CMS\Factory;

defined('JPATH_PLATFORM') or die;

// Make alias of original FileLayout
\T3::makeAlias(JPATH_LIBRARIES . '/src/Layout/FileLayout.php', 'FileLayout', '_FileLayout');

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @link   https://docs.joomla.org/Special:MyLanguage/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since  3.0
 */
class FileLayout extends _FileLayout
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
		// Get the template
		$template = Factory::getApplication()->getTemplate(true);

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
			$paths[] = JPATH_THEMES . '/' . $template->template . '/html/layouts/' . $component;

			if (!empty($template->parent))
			{
				// (2.a) Component template overrides path for an inherited template using the parent
				$paths[] = JPATH_THEMES . '/' . $template->parent . '/html/layouts/' . $component;
			}

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

		// (4) Standard Joomla! layouts overridden
		$paths[] = JPATH_THEMES . '/' . $template->template . '/html/layouts';

		if (!empty($template->parent))
		{
			// (4.a) Component template overrides path for an inherited template using the parent
			$paths[] = JPATH_THEMES . '/' . $template->parent . '/html/layouts';
		}

		// T3 - (5.1) - T3 base layout overridden
		$paths[] = T3_PATH . '/html/layouts';

		// (5 - lower priority) Frontend base layouts
		$paths[] = JPATH_ROOT . '/layouts';

		return $paths;
	}

}
