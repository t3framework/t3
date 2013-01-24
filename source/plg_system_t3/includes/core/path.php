<?php
/** 
 *------------------------------------------------------------------------------
 * @package   T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license   GNU General Public License; http://www.gnu.org/licenses/gpl.html
 * @author    JoomlArt, JoomlaBamboo 
 *            If you want to be come co-authors of this project, please follow 
 *            our guidelines at http://t3-framework.org/contribute
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