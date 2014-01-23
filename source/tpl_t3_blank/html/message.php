<?php
/**
 * @version		$Id: pagination.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die;


/**
	 * Render the system message if no message template file found
	 *
	 * @param   array  $msgList  An array contains system message
	 *
	 * @return  string  System message markup
	 *
	 * @since   12.2
	 */

function renderMessage($msgList)
{
	// Build the return string
	$buffer = '';
	$buffer .= "\n<div id=\"system-message-container\">";

	// If messages exist render them
	if (is_array($msgList))
	{
		$buffer .= "\n<div id=\"system-message\">";
		foreach ($msgList as $type => $msgs)
		{
			$buffer .= "\n<div class=\"alert alert-" . $type . "\">";

			// This requires JS so we should add it trough JS. Progressive enhancement and stuff.
			$buffer .= "<a class=\"close\" data-dismiss=\"alert\" href=\"#\">×</a>";

			if (count($msgs))
			{
				$buffer .= "\n<h4 class=\"alert-heading\">" . JText::_($type) . "</h4>";
				$buffer .= "\n<div>";
				foreach ($msgs as $msg)
				{
					$buffer .= "\n\t\t<p>" . $msg . "</p>";
				}
				$buffer .= "\n</div>";
			}
			$buffer .= "\n</div>";
		}
		$buffer .= "\n</div>";
	}

	$buffer .= "\n</div>";

	return $buffer;
}