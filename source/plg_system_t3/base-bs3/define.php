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


define('T3', 'base-bs3');
define('T3_URL',  T3_ADMIN_URL  . '/' . T3);
define('T3_PATH', T3_ADMIN_PATH . '/' . T3);
define('T3_REL',  T3_ADMIN_REL  . '/' . T3);

define('T3_BASE_MAX_GRID',            12);
define('T3_BASE_WIDTH_PREFIX',        'col-md-');
define('T3_BASE_NONRSP_WIDTH_PREFIX', 'col-xs-');
define('T3_BASE_WIDTH_PATTERN',       'col-{device}-{width}');
define('T3_BASE_WIDTH_REGEX',         '@(\s*)col-(lg|md|sm|xs)-(\d+)(\s*)@');
define('T3_BASE_HIDDEN_PATTERN',      'hidden');
define('T3_BASE_FIRST_PATTERN',       '');
define('T3_BASE_ROW_FLUID_PREFIX',    'row');
define('T3_BASE_RSP_IN_CLASS',        true);
define('T3_BASE_DEFAULT_DEVICE',      'md');
define('T3_BASE_DEVICES',             json_encode(array('lg', 'md', 'sm', 'xs')));
define('T3_BASE_DV_MAXCOL',           json_encode(array('lg' => 6, 'md' => 6, 'sm' => 4, 'xs' => 2)));
define('T3_BASE_DV_MINWIDTH',         json_encode(array('lg' => 2, 'md' => 2, 'sm' => 3, 'xs' => 6)));
define('T3_BASE_DV_UNITSPAN',         json_encode(array('lg' => 1, 'md' => 1, 'sm' => 1, 'xs' => 1)));
define('T3_BASE_DV_PREFIX',           json_encode(array('col-md-', 'col-lg-', 'col-sm-', 'col-xs-')));	/* priority order */
define('T3_BASE_LESS_COMPILER',      'less');
