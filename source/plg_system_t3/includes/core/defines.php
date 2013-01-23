<?php
/**
 * $JA#COPYRIGHT$
 */

// No direct access
defined('_JEXEC') or die;

define ('T3V3_PLUGIN', 'plg_system_t3');

define ('T3V3_ADMIN', 't3');
define ('T3V3_ADMIN_PATH', dirname(dirname(dirname(__FILE__))));
define ('T3V3_ADMIN_URL', JURI::root(true).'/plugins/system/'.T3V3_ADMIN);
define ('T3V3_ADMIN_REL', 'plugins/system/'.T3V3_ADMIN);

define ('T3V3', 'base');
define ('T3V3_URL', T3V3_ADMIN_URL.'/'.T3V3);
define ('T3V3_PATH', T3V3_ADMIN_PATH . '/' . T3V3);
define ('T3V3_REL', T3V3_ADMIN_REL.'/'.T3V3);
