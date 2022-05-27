<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Registry\Registry;

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
	 * Render the module.
	 *
	 * @param   object  $module   A module object.
	 * @param   array   $attribs  An array of attributes for the module (probably from the XML).
	 *
	 * @return  string  The HTML content of the module output.
	 *
	 * @since   1.5
	 */
	public static function renderModule($module, $attribs = array())
	{
		$app = Factory::getApplication();

		// Check that $module is a valid module object
		if (!\is_object($module) || !isset($module->module) || !isset($module->params))
		{
			if (JDEBUG)
			{
				Log::addLogger(array('text_file' => 'jmodulehelper.log.php'), Log::ALL, array('modulehelper'));
				$app->getLogger()->debug(
					__METHOD__ . '() - The $module parameter should be a module object.',
					array('category' => 'modulehelper')
				);
			}

			return '';
		}

		// Get module parameters
		$params = new Registry($module->params);

		// Render the module content
		static::renderRawModule($module, $params, $attribs);

		// Return early if only the content is required
		if (!empty($attribs['contentOnly']))
		{
			return $module->content;
		}

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('beforeRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		// Record the scope.
		$scope = $app->scope;

		// Set scope to component name
		$app->scope = $module->module;

		// Get the template
		$template = $app->getTemplate();

		// Check if the current module has a style param to override template module style
		$paramsChromeStyle = $params->get('style');
		$basePath          = '';

		if ($paramsChromeStyle)
		{
			$paramsChromeStyle   = explode('-', $paramsChromeStyle, 2);
			$ChromeStyleTemplate = strtolower($paramsChromeStyle[0]);
			$attribs['style']    = $paramsChromeStyle[1];

			// Only set $basePath if the specified template isn't the current or system one.
			if ($ChromeStyleTemplate !== $template && $ChromeStyleTemplate !== 'system')
			{
				$basePath = JPATH_THEMES . '/' . $ChromeStyleTemplate . '/html/layouts';
			}
		}
		if(version_compare(JVERSION, '4.0', 'lt')){
			include_once JPATH_THEMES . '/system/html/modules.php';
		}

		$chromePath = JPATH_THEMES . '/' . $template . '/html/modules.php';

		if (!isset($chrome[$chromePath]))
		{
			if (file_exists($chromePath))
			{
				// load module style on template
				include_once $chromePath;
			}else{
				if($app->isClient('site')){
					// load module style function for use on some module of JA
					$chromePath = \T3Path::getPath('html/modules.php');
					include_once $chromePath;
				}
			}

			$chrome[$chromePath] = true;
		}
		// Make sure a style is set
		if (!isset($attribs['style']))
		{
			$attribs['style'] = 'none';
		}

		// Dynamically add outline style
		if ($app->input->getBool('tp') && ComponentHelper::getParams('com_templates')->get('template_positions_display'))
		{
			$attribs['style'] .= ' outline';
		}

		$module->style = $attribs['style'];

		// If the $module is nulled it will return an empty content, otherwise it will render the module normally.
		$app->triggerEvent('onRenderModule', array(&$module, &$attribs));

		if ($module === null || !isset($module->content))
		{
			return '';
		}

		$displayData = array(
			'module'  => $module,
			'params'  => $params,
			'attribs' => $attribs,
		);

		foreach (explode(' ', $attribs['style']) as $style)
		{
			if ($moduleContent = LayoutHelper::render('chromes.' . $style, $displayData, $basePath))
			{
				$module->content = $moduleContent;
			}
		}
		foreach (explode(' ', $attribs['style']) as $style)
		{
			$chromeMethod = 'modChrome_' . $style;
			$chromeStylePath = \T3Path::getPath('html/layouts/chromes/'.$style.'.php');
			// Apply chrome and render module
			if (function_exists($chromeMethod) && !file_exists($chromeStylePath))
			{
				$module->style = $attribs['style'];

				ob_start();
				$chromeMethod($module, $params, $attribs);
				$module->content = ob_get_contents();
				ob_end_clean();
			}
		}
		// Revert the scope
		$app->scope = $scope;

		$app->triggerEvent('onAfterRenderModule', array(&$module, &$attribs));

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('afterRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		return $module->content;
	}
	
	/**
	 * Render the module content.
	 *
	 * @param   object    $module   A module object
	 * @param   Registry  $params   A module parameters
	 * @param   array     $attribs  An array of attributes for the module (probably from the XML).
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public static function renderRawModule($module, Registry $params, $attribs = array())
	{
		if (!empty($module->contentRendered))
		{
			return $module->content;
		}

		if (JDEBUG)
		{
			\JProfiler::getInstance('Application')->mark('beforeRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		$app = \JFactory::getApplication();

		// Record the scope.
		$scope = $app->scope;

		// Set scope to component name
		$app->scope = $module->module;
		
		if(version_compare(JVERSION, '4.0', 'ge')){

			$dispatcher = $app->bootModule($module->module, $app->getName())->getDispatcher($module, $app);

			// Check if we have a dispatcher
			if ($dispatcher)
			{
				ob_start();
				$dispatcher->dispatch();
				$module->content = ob_get_clean();
			}

		}else{
			// Get module path
			$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
			$path = JPATH_BASE . '/modules/' . $module->module . '/' . $module->module . '.php';

			// Load the module
			if (file_exists($path))
			{
				$lang = \JFactory::getLanguage();

				$coreLanguageDirectory      = JPATH_BASE;
				$extensionLanguageDirectory = dirname($path);

				$langPaths = $lang->getPaths();

				// Only load the module's language file if it hasn't been already
				if (!$langPaths || (!isset($langPaths[$coreLanguageDirectory]) && !isset($langPaths[$extensionLanguageDirectory])))
				{
					// 1.5 or Core then 1.6 3PD
					$lang->load($module->module, $coreLanguageDirectory, null, false, true) ||
						$lang->load($module->module, $extensionLanguageDirectory, null, false, true);
				}

				$content = '';
				ob_start();
				include $path;
				$module->content = ob_get_contents() . $content;
				ob_end_clean();
			}
		}
		// Add the flag that the module content has been rendered
		$module->contentRendered = true;

		// Revert the scope
		$app->scope = $scope;

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('afterRenderRawModule ' . $module->module . ' (' . $module->title . ')');
		}

		return $module->content;
	}
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
