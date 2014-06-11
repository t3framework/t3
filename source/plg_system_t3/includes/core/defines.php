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

// No direct access
defined('_JEXEC') or die;

define ('T3_PLUGIN', 'plg_system_t3');

//T3 base folder
define ('T3_ADMIN', 't3');
define ('T3_ADMIN_PATH', JPATH_ROOT . '/plugins/system/' . T3_ADMIN);
define ('T3_ADMIN_URL', JURI::root(true) . '/plugins/system/' . T3_ADMIN);
define ('T3_ADMIN_REL', 'plugins/system/' . T3_ADMIN);

//T3 secondary base theme folder
define ('T3_EX_BASE_PATH', JPATH_ROOT . '/media/t3/themes');
define ('T3_EX_BASE_URL', JURI::root(true) . '/media/t3/themes');
define ('T3_EX_BASE_REL', 'media/t3/themes');

//T3 core base theme
define ('T3_CORE_BASE', 'base');
define ('T3_CORE_BASE_PATH', T3_ADMIN_PATH . '/' . T3_CORE_BASE);
define ('T3_CORE_BASE_URL', T3_ADMIN_URL . '/' . T3_CORE_BASE);
define ('T3_CORE_BASE_REL', T3_ADMIN_REL . '/' . T3_CORE_BASE);

// T3 User dir
define ('T3_LOCAL_DIR', 'local');