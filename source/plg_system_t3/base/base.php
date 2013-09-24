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
 * @package      T3
 * @description  This file should contains information of itself
 */


class T3Base
{

	/**
	 * @var    T3Base  The application instance.
	 * @since  11.3
	 */
	protected static $instance;


	/**
	 * Constructor.
	 * You can pass a URI string to the constructor to initialise a specific URI.
	 *
	 * @param   array/mixed  $options  The options
	 *
	 */
	public function __construct($options = null)
	{

	}


	/**
	 * Returns a reference to the global T3Base object, only creating it if it doesn't already exist.
	 *
	 * @param   string $name  The name (optional) of the JApplicationWeb class to instantiate.
	 *
	 * @return  T3Base
	 *
	 * @since   11.3
	 */
	public static function getInstance($name = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance)) {
			if (class_exists($name) && (is_subclass_of($name, 'T3Base'))) {
				self::$instance = new $name;
			} else {
				self::$instance = new T3Base;
			}
		}

		return self::$instance;
	}


	/**
	 * Define constants for T3
	 * You should overwrite this method on child class
	 */
	public function define()
	{
		//already defined?
		if (defined('T3')) {
			return;
		}

		define('T3', T3_CORE_BASE);
		define('T3_URL', T3_CORE_BASE_URL);
		define('T3_PATH', T3_CORE_BASE_PATH);
		define('T3_REL', T3_CORE_BASE_REL);

		define('T3_BASE_MAX_GRID',            12);
		define('T3_BASE_WIDTH_PREFIX',        'span');
		define('T3_BASE_NONRSP_WIDTH_PREFIX', 'span');
		define('T3_BASE_WIDTH_PATTERN',       'span{width}');
		define('T3_BASE_WIDTH_REGEX',         '/(\s*)span(\d+)(\s*)/');
		define('T3_BASE_HIDDEN_PATTERN',      'hidden');
		define('T3_BASE_FIRST_PATTERN',       'spanfirst');
		define('T3_BASE_RSP_IN_CLASS',        false);
		define('T3_BASE_ROW_FLUID_PREFIX',    'row-fluid');
		define('T3_BASE_DEFAULT_DEVICE',      'default');
		define('T3_BASE_DEVICES',             json_encode(array('default', 'wide', 'normal', 'xtablet', 'tablet', 'mobile')));
		define('T3_BASE_DV_MAXCOL',           json_encode(array('default' => 6, 'wide' => 6, 'normal' => 6, 'xtablet' => 4, 'tablet' => 3, 'mobile' => 2)));
		define('T3_BASE_DV_MINWIDTH',         json_encode(array('default' => 2, 'wide' => 2, 'normal' => 2, 'xtablet' => 3, 'tablet' => 4, 'mobile' => 6)));
		define('T3_BASE_DV_UNITSPAN',         json_encode(array('default' => 1, 'wide' => 1, 'normal' => 1, 'xtablet' => 1, 'tablet' => 1, 'mobile' => 6)));
	}


	/**
	 * Define constants for T3
	 * You should overwrite this method on child class
	 */
	public function getInfo()
	{
		return array(
			'maxgrid'      => T3_BASE_MAX_GRID,
			'widthprefix'  => T3_BASE_WIDTH_PREFIX,
			'nonrspprefix' => T3_BASE_NONRSP_WIDTH_PREFIX,
			'spancls'      => T3_BASE_WIDTH_REGEX,
			'responcls'    => T3_BASE_RSP_IN_CLASS,
			'rowfluidcls'  => T3_BASE_ROW_FLUID_PREFIX,
			'defdv'        => T3_BASE_DEFAULT_DEVICE,
			'devices'      => json_decode(T3_BASE_DEVICES, true),
			'maxcol'       => json_decode(T3_BASE_DV_MAXCOL, true),
			'minspan'      => json_decode(T3_BASE_DV_MINWIDTH, true),
		);
	}
}