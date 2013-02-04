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
	if (JError::$legacy) {
		JError::setErrorHandling(E_ERROR, 'die');
		JError::raiseError(500, JText::_('T3_MISSING_T3_PLUGIN'));
		exit;
	} else {
		throw new Exception(JText::_('T3_MISSING_T3_PLUGIN'), 500);
	}
}

$t3app = T3::getApp($this);

// get configured layout
$layout = $t3app->getLayout();

$t3app->loadLayout ($layout);