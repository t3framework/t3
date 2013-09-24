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
