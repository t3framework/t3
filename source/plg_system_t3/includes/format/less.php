<?php
/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License; http://www.gnu.org/licenses/gpl.html
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         https://github.com/t3framework/ 
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

		// Iterate over the object to set the properties.
		foreach (get_object_vars($object) as $key => $value)
		{
			// If the value is an object then we need to put it in a local section.
			$result[] = $this->getKey($key) . ': ' . $this->getValue($value);
		}

		return implode("\n", $result);
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

		// Process the lines.
		foreach ($lines as $line)
		{
			// Trim any unnecessary whitespace.
			$line = trim($line);

			// Ignore empty lines and comments.
			if (empty($line) || (substr($line, 0, 2) == '//'))
			{
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
			if (preg_match('/[^A-Z0-9_-];$/i', $value))
			{
				// Maybe throw exception?
				continue;
			}
			
			// If the value is quoted then we assume it is a string.
			
			$key = str_replace('@', '', $key);
			$value = str_replace(';', '', $value);
			$value = preg_replace('/\/\/(.*)/', '', $value);
			$value = trim($value);
			$obj->$key = $value;
		}

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
