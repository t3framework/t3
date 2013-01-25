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
 * @Link:         https://github.com/t3framework/ 
 *------------------------------------------------------------------------------
 */

// No direct access
defined('_JEXEC') or die();

/**
 * T3Path class
 *
 * @package T3
 */
class T3Path extends JObject
{

	/**
	 * Get path in tpls folder. If found in template, use the path, else try in plugin t3
	 */
	public static function getPath ($file, $default = '', $relative = false) {
		$return = '';
		if (is_file (T3_TEMPLATE_PATH . '/' . $file)) $return = ($relative ? T3_TEMPLATE_REL : T3_TEMPLATE_PATH) . '/' . $file;
		if (!$return && is_file (T3_PATH . '/' . $file)) $return = ($relative ? T3_REL : T3_PATH) . '/' . $file;
		if (!$return && $default) $return = self::getPath ($default);
		return $return;
	}
 
	/**
	 * Get path in tpls folder. If found in template, use the path, else try in plugin t3
	 */
	public static function getUrl ($file, $default = '', $relative = false) {
		$return = '';
		if (is_file (T3_TEMPLATE_PATH . '/' . $file)) $return =  ($relative ? T3_TEMPLATE_REL : T3_TEMPLATE_URL) . '/' . $file;
		if (!$return && is_file (T3_PATH . '/' . $file)) $return =  ($relative ? T3_REL : T3_URL) . '/' . $file;
		if (!$return && $default) $return =  self::getUrl ($default);
		return $return;
	}
}