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

defined('JPATH_PLATFORM') or die;

/**
 * INI format handler for JRegistry.
 *
 * @package     Joomla.Platform
 * @subpackage  Registry
 * @since       11.1
 */
class JRegistryFormatLESS
{
	/**
	 * Converts an object into an INI formatted string
	 * -	Unfortunately, there is no way to have ini values nested further than two
	 * levels deep.  Therefore we will only go through the first two levels of
	 * the object.
	 *
	 * @param   object  $object   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string  INI formatted string.
	 *
	 * @since   11.1
	 */
	public function objectToString($object, $options = array())
	{
		// Initialize variables.
		$result = array();
		$import_urls = '';

		// Iterate over the object to set the properties.
		foreach (get_object_vars($object) as $key => $value)
		{
			// If the value is an object then we need to put it in a local section.
			if ($key == 'import-external-urls') {
				$import_urls = explode ("\n", $value);
			} else {
				$result[] = $this->getKey($key) . ': ' . $this->getValue($value);
			}
		}

		$output = '';
		if (is_array ($import_urls)) {
			foreach ($import_urls as $url) {
				$output .= "@import url({$url});\n";
			}
		}

		$output .= "\n" . implode("\n", $result);

		return $output;
	}

	/**
	 * Parse an INI formatted string and convert it into an object.
	 *
	 * @param   string  $data     INI formatted string to convert.
	 * @param   mixed   $options  An array of options used by the formatter, or a boolean setting to process sections.
	 *
	 * @return  object   Data object.
	 *
	 * @since   11.1
	 */
	public function stringToObject($data, $options = array())
	{
		// If no lines present just return the object.
		if (empty($data))
		{
			return new stdClass;
		}

		// Initialize variables.
		$obj = new stdClass;
		$lines = explode("\n", $data);
		$import_urls = array();

		// Process the lines.
		foreach ($lines as $line)
		{
			// Trim any unnecessary whitespace.
			$line = trim($line);

			// Ignore empty lines and comments.
			if (empty($line) || (substr($line, 0, 1) == '/') || (substr($line, 0, 1) == '*'))
			{
				continue;
			}

			// if url import
			if (preg_match ('/@import\s+url\((.+)\);/', $line, $match)) {
				$import_urls[] = $match[1];
				continue;
			}

			// Check that an equal sign exists and is not the first character of the line.
			if (!strpos($line, ':'))
			{
				// Maybe throw exception?
				continue;
			}

			// Get the key and value for the line.
			list ($key, $value) = explode(':', $line, 2);

			// Validate the key.
			if (preg_match('/@[^A-Z0-9_]/i', $key))
			{
				// Maybe throw exception?
				continue;
			}

			// Validate the value.
			//if (preg_match('/[^\(\)A-Z0-9_-];$/i', $value))
			//{
				// Maybe throw exception?
			//	continue;
			//}
			
			// If the value is quoted then we assume it is a string.
			
			$key = str_replace('@', '', $key);
			$value = str_replace(';', '', $value);
			$value = preg_replace('/\/\/(.*)/', '', $value);
			$value = trim($value);
			$obj->$key = $value;
		}

		// update font import
		$key = 'import-external-urls';
		$obj->$key = implode ("\n", $import_urls);

		// Cache the string to save cpu cycles -- thus the world :)
		
		return $obj;
	}

	/**
	 * Method to get a value in an INI format.
	 *
	 * @param   mixed  $value  The value to convert to INI format.
	 *
	 * @return  string  The value in INI format.
	 *
	 * @since   11.1
	 */
	protected function getValue($value)
	{
		return $value . ';';
	}
	
	/**
	 * Method to get a value in an INI format.
	 *
	 * @param   mixed  $key
	 *
	 * @return  string  The value in INI format.
	 *
	 * @since   11.1
	 */
	protected function getKey($key)
	{
		return '@' . $key;
	}
}
