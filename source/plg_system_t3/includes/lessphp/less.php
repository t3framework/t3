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
defined('_JEXEC') or die();

T3::import('lessphp/less/less');

/**
 * T3LessCompiler class compile less
 *
 * @package T3
 */
// prevent over max_nesting config in some case
@ini_set('xdebug.max_nesting_level', 120);
 
class T3LessCompiler
{
	public static function compile ($source, $importdirs) {
		$parser = new Less_Parser();
		$parser->SetImportDirs($importdirs);
		$parser->parse($source);
		$output = $parser->getCss();
		return $output;
	}
}
