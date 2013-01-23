<?php
/**
 * $JA#COPYRIGHT$
 */

// No direct access
defined('_JEXEC') or die();

/**
 * T3V3Path class
 *
 * @package T3V3
 */
class T3v3Path extends JObject
{

	/**
	 * Get path in tpls folder. If found in template, use the path, else try in plugin t3v3
	 */
	public static function getPath ($file, $default = '', $relative = false) {
		$return = '';
		if (is_file (T3V3_TEMPLATE_PATH . '/' . $file)) $return = ($relative ? T3V3_TEMPLATE_REL : T3V3_TEMPLATE_PATH) . '/' . $file;
		if (!$return && is_file (T3V3_PATH . '/' . $file)) $return = ($relative ? T3V3_REL : T3V3_PATH) . '/' . $file;
		if (!$return && $default) $return = self::getPath ($default);
		return $return;
	}
 
	/**
	 * Get path in tpls folder. If found in template, use the path, else try in plugin t3v3
	 */
	public static function getUrl ($file, $default = '', $relative = false) {
		$return = '';
		if (is_file (T3V3_TEMPLATE_PATH . '/' . $file)) $return =  ($relative ? T3V3_TEMPLATE_REL : T3V3_TEMPLATE_URL) . '/' . $file;
		if (!$return && is_file (T3V3_PATH . '/' . $file)) $return =  ($relative ? T3V3_REL : T3V3_URL) . '/' . $file;
		if (!$return && $default) $return =  self::getUrl ($default);
		return $return;
	}
}