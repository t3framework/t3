<?php
/** 
 *-------------------------------------------------------------------------
 * T3 Framework for Joomla!
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2013 JoomlArt.com, Ltd. All Rights Reserved.
 * License - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Authors:  JoomlArt, JoomlaBamboo 
 * If you want to be come co-authors of this project, please follow our 
 * guidelines at http://t3-framework.org/contribute
 * ------------------------------------------------------------------------
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