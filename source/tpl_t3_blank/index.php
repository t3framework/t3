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
 * @Link:         https://github.com/t3framework/ 
 *------------------------------------------------------------------------------
 */
 
// no direct access
defined('_JEXEC') or die;

//check if t3 plugin is existed
if(!defined('T3')){
	T3::error(JText::_('T3_MISSING_T3_PLUGIN'));
}

$t3app = T3::getApp($this);

// get configured layout
$layout = $t3app->getLayout();

$t3app->loadLayout ($layout);