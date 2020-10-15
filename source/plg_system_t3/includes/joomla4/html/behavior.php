<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for JavaScript behaviors
 *
 * @since  1.5
 */
abstract class T3HtmlBehavior extends JHtmlBehavior
{

	/**
	 * Add unobtrusive JavaScript support for a hover tooltips.
	 *
	 * Add a title attribute to any element in the form
	 * title="title::text"
	 *
	 * Uses the core Tips class in MooTools.
	 *
	 * @param   string  $selector  The class selector for the tooltip.
	 * @param   array   $params    An array of options for the tooltip.
	 *                             Options for the tooltip can be:
	 *                             - maxTitleChars  integer   The maximum number of characters in the tooltip title (defaults to 50).
	 *                             - offsets        object    The distance of your tooltip from the mouse (defaults to {'x': 16, 'y': 16}).
	 *                             - showDelay      integer   The millisecond delay the show event is fired (defaults to 100).
	 *                             - hideDelay      integer   The millisecond delay the hide hide is fired (defaults to 100).
	 *                             - className      string    The className your tooltip container will get.
	 *                             - fixed          boolean   If set to true, the toolTip will not follow the mouse.
	 *                             - onShow         function  The default function for the show event, passes the tip element
	 *                               and the currently hovered element.
	 *                             - onHide         function  The default function for the hide event, passes the currently
	 *                               hovered element.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function tooltip($selector = '.hasTip', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		// Include MooTools framework
		static::framework(true);

		// Setup options object
		$opt['maxTitleChars'] = isset($params['maxTitleChars']) && $params['maxTitleChars'] ? (int) $params['maxTitleChars'] : 50;

		// Offsets needs an array in the format: array('x'=>20, 'y'=>30)
		$opt['offset']    = isset($params['offset']) && is_array($params['offset']) ? $params['offset'] : null;
		$opt['showDelay'] = isset($params['showDelay']) ? (int) $params['showDelay'] : null;
		$opt['hideDelay'] = isset($params['hideDelay']) ? (int) $params['hideDelay'] : null;
		$opt['className'] = isset($params['className']) ? $params['className'] : null;
		$opt['fixed']     = isset($params['fixed']) && $params['fixed'];
		$opt['onShow']    = isset($params['onShow']) ? '\\' . $params['onShow'] : null;
		$opt['onHide']    = isset($params['onHide']) ? '\\' . $params['onHide'] : null;

		$options = json_encode($opt);

		// Include jQuery
		JHtml::_('jquery.framework');

		// Attach tooltips to document
		JFactory::getDocument()->addScriptDeclaration(
			"jQuery(function($) {
			 $('$selector').each(function() {
				var title = $(this).attr('title');
				if (title) {
					var parts = title.split('::', 2);
					var mtelement = document.getElementById(this);
					mtelement.store('tip:title', parts[0]);
					mtelement.store('tip:text', parts[1]);
				}
			});
			var JTooltips = new Tips($('$selector').get(), $options);
		});"
		);

		// Set static array
		static::$loaded[__METHOD__][$sig] = true;

		return;
	}

	/**
	 * Add unobtrusive JavaScript support for form validation.
	 *
	 * To enable form validation the form tag must have class="form-validate".
	 * Each field that needs to be validated needs to have class="validate".
	 * Additional handlers can be added to the handler for username, password,
	 * numeric and email. To use these add class="validate-email" and so on.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @Deprecated 3.4 Use formvalidator instead
	 */
	public static function formvalidation()
	{
		// Include MooTools framework
		static::framework();

		// Load the new jQuery code
		static::formvalidator();
	}


	/**
	 * Method to load the MooTools framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of MooTools is included for easier debugging.
	 *
	 * @param   boolean  $extras  Flag to determine whether to load MooTools More in addition to Core
	 * @param   mixed    $debug   Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @deprecated 4.0 Update scripts to jquery
	 */
	public static function framework($extras = false, $debug = null)
	{
		$type = $extras ? 'more' : 'core';

		// Only load once
		if (!empty(static::$loaded[__METHOD__][$type]))
		{
			return;
		}

		JLog::add('JHtmlBehavior::framework is deprecated. Update to jquery scripts.', JLog::WARNING, 'deprecated');

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$debug = JDEBUG;
		}

		if ($type !== 'core' && empty(static::$loaded[__METHOD__]['core']))
		{
			static::framework(false, $debug);
		}

		JHtml::_('script', 'system/mootools-' . $type . '.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));

		// Keep loading core.js for BC reasons
		static::core();

		static::$loaded[__METHOD__][$type] = true;

		return;
	}


	/**
	 * Add unobtrusive JavaScript support for image captions.
	 *
	 * @param   string  $selector  The selector for which a caption behaviour is to be applied.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 *
	 * @Deprecated 4.0 Use native HTML figure tags.
	 */
	public static function caption($selector = 'img.caption')
	{
		JLog::add('JHtmlBehavior::caption is deprecated. Use native HTML figure tags.', JLog::WARNING, 'deprecated');

		// Only load once
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		JHtml::_('script', 'system/caption.js', array('version' => 'auto', 'relative' => true));

		// Attach caption to document
		JFactory::getDocument()->addScriptDeclaration(
			"jQuery(window).on('load',  function() {
				new JCaption('" . $selector . "');
			});"
		);

		// Set static array
		static::$loaded[__METHOD__][$selector] = true;
	}

}
