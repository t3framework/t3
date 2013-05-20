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

/**
 *
 * Admin helper module class
 * @author JoomlArt
 *
 */
class T3Ajax {

	protected static $signature;
	protected static $modesef;

	public static function render() {
		// excute action by T3
		$input = JFactory::getApplication()->input;

		if ($input->getCmd ('t3ajax')) {
			JFactory::getDocument()->getBuffer('t3ajax');
		}
	}

	public static function processAjaxRule () {
		$app = JFactory::getApplication();
		$router = $app->getRouter();
		
		if ($app->isSite()) {
			//self::$signature = 't3ajax';
			//self::$modesef = ($router->getMode() == JROUTER_MODE_SEF) ? true : false;
			
			$router->attachBuildRule(array('T3Ajax', 'buildRule'));
			//$router->attachParseRule(array('T3Ajax', 'parseRule'));
		}
	}

	public static function buildRule (&$router, &$uri) {
		$uri->delVar('t3ajax');
	}

	public static function parseRule (&$router, &$uri) {

	}
}