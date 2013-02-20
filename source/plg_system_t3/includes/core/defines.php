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

define ('T3_ADMIN', 't3');
define ('T3_ADMIN_PATH', dirname(dirname(dirname(__FILE__))));
define ('T3_ADMIN_URL', JURI::root(true).'/plugins/system/'.T3_ADMIN);
define ('T3_ADMIN_REL', 'plugins/system/'.T3_ADMIN);

define ('T3', 'base');
define ('T3_URL', T3_ADMIN_URL.'/'.T3);
define ('T3_PATH', T3_ADMIN_PATH . '/' . T3);
define ('T3_REL', T3_ADMIN_REL.'/'.T3);