<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;


// Make alias of original FileLayout
\T3::makeAlias(JPATH_LIBRARIES . '/src/Helper/ModuleHelper.php', 'ModuleHelper', '_ModuleHelper');


/**
 * Module helper class
 *
 * @since  1.5
 */
abstract class ModuleHelper extends _ModuleHelper
{
	/**
	 * Get the path to a layout for a module
	 *
	 * @param   string  $module  The name of the module
	 * @param   string  $layout  The name of the module layout. If alternative layout, in the form template:filename.
	 *
	 * @return  string  The path to the module layout
	 *
	 * @since   1.5
	 */
	public static function getLayoutPath($module, $layout = 'default')
	{
		$template = \JFactory::getApplication()->getTemplate();
		$defaultLayout = $layout;

		if (strpos($layout, ':') !== false)
		{
			// Get the template and file name from the string
			$temp = explode(':', $layout);
			$template = $temp[0] === '_' ? $template : $temp[0];
			$layout = $temp[1];
			$defaultLayout = $temp[1] ?: 'default';
		}

		// T3
		// Do 3rd party stuff to detect layout path for the module
		// onGetLayoutPath should return the path to the $layout of $module or false
		// $results holds an array of results returned from plugins, 1 from each plugin.
		// if a path to the $layout is found and it is a file, return that path
		$app	= \JFactory::getApplication();
		$result = $app->triggerEvent( 'onGetLayoutPath', array( $module, $layout ) );
		if (is_array($result))
		{
			foreach ($result as $path)
			{
				if ($path !== false && is_file ($path))
				{
					return $path;
				}
			}
		}
		// /T3

		// Build the template and base path for the layout
		$tPath = JPATH_THEMES . '/' . $template . '/html/' . $module . '/' . $layout . '.php';
		$bPath = JPATH_BASE . '/modules/' . $module . '/tmpl/' . $defaultLayout . '.php';
		$dPath = JPATH_BASE . '/modules/' . $module . '/tmpl/default.php';

		// If the template has a layout override use it
		if (file_exists($tPath))
		{
			return $tPath;
		}

		if (file_exists($bPath))
		{
			return $bPath;
		}

		return $dPath;
	}
}
