<?php
/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         http://t3-framework.org 
 *------------------------------------------------------------------------------
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * T3 Google Service utility class
 *
 * @package     T3
 * @subpackage  Google service
 */

class T3GService
{
	
	protected static $gfontcache = 'gfont.dat';

	/**
	 * Get font properties by name
	 *
	 * @return void
	 */
	public static function getFontProperties()
	{
		$input = JFactory::getApplication()->input;

		$template = $input->getCmd('template');
		$fontname = $input->getCmd('fontname');
		
		// Get gfont path
		$fontpath = self::getFontPath($template);
		// Get font data
		if ($fontpath !== false) {
			$data = @file_get_contents($fontpath);
			// Check to update font
			$idx  = strpos($data, '#');
			if ($idx !== false) {
				// Seperate time & json font list
				$time = (int) substr($data, 0, $idx);
				$data = substr($data, $idx + 1);
				// Check if not update 3 days => update
				if (time() - $time > 3 * 86400) {
					// Get local font path
					$fontpath = self::getFontPath($template, self::$gfontcache, true);
					// Update font list
					$status   = self::updateFontList($fontpath);
					// If success, re-get font list
					if ($status) {
						$data = @file_get_contents($fontpath);
						$idx  = strpos($data, '#');
						if ($idx !== false) {
							$data = substr($data, $idx + 1);
						}
					}
				}
			}

			$result = '';

			if($data){
				// Parse fonts information
				$font   = json_decode($data);
				$items  = $font->items;
				// Find suitable font by fontname
				foreach ($items as $item) {
					if (strcasecmp($fontname, $item->family) == 0) {
						$result = $item;
						break;
					}
				}
			}

		} else {
			$result = '';
		}
		
		echo json_encode($result);
		exit;
	}
	
	
	/**
	 * Get font list
	 *
	 * @return void
	 */
	function getFontList()
	{
		$input = JFactory::getApplication()->input;

		$template = $input->getCmd('template');
		$fontname = $input->getCmd('fontname');

		// Get gfont path
		$fontpath = self::getFontPath($template);
		if ($fontpath !== false) {
			// Get font list
			$data = @file_get_contents($fontpath);
			// Remove time before json data
			$idx  = strpos($data, '#');
			if ($idx !== false) {
				$data = substr($data, $idx + 1);
			}
			// Parse data
			$font    = json_decode($data);
			$items   = $font->items;
			$result  = array();
			$pattern = '/^' . $fontname . '.*/i';
			// Find suitable font by fontname
			foreach ($items as $item) {
				if (preg_match($pattern, $item->family)) {
					$result[] = $item->family;
				}
			}
		} else {
			$result = array();
		}

		echo json_encode($result);
		exit;
	}
	
	/**
	 * Get gfont path
	 *
	 * @param string $template  Template name
	 * @param string $filename  Filename include extension
	 * @param bool   $local     Indicate get local path or not
	 *
	 * @return mixed  Gfont file path if found, otherwise FALSE
	 */
	function getFontPath($template, $filename = false)
	{
		if(!$filename){
			$filename = self::$gfontcache;
		}

		// Check to sure that template is using new folder structure
		// If etc folder exists, considered as template is using new folder structure
		$filepath = JPATH_SITE . '/templates/' . $template . '/etc';
		if (@is_dir($filepath)) {
			$filepath .= '/' . $filename;
		}

		// Check file exists or not
		if (@is_file($filepath)) {
			return $filepath;
		}

		// Check file in base
		$filepath = T3_PATH . '/etc/' . $filename;
		if (@is_file($filepath)) {
			return $filepath;
		}
		
		// Can not find google font file
		return false;
	}
	
	/**
	 * Update font list from google web font page
	 *
	 * @param string $path  File path store font list
	 *
	 * @return bool  TRUE if update success, otherwise FALSE
	 */
	function updateFontList($path)
	{
		$key     = 'AIzaSyA6_mK8ERGaR4_dhK6tJVEdvJPQEdwULWg';
		$url     = 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . $key;
		$content = @file_get_contents($url);
		if (!empty($content)) {
			$content = time() . '#' . $content;
			$result  = file_put_contents($path, $content);
			return ($result !== false);
		}
		return false;
	}
}