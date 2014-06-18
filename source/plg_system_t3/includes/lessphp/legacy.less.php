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

T3::import('lessphp/lessc.inc');

/**
 * T3LessCompiler class compile less
 *
 * @package T3
 */
class T3LessCompiler
{
	public static function compile ($source, $path, $todir, $importdirs) {
		// call Less to compile
		$parser = new lessc();
		$parser->setImportDir(array_keys($importdirs));
		$parser->setPreserveComments(true);
		$output = $parser->compile($source);
		// update url
		$arr    = preg_split(T3Less::$rsplitbegin . T3Less::$kfilepath . T3Less::$rsplitend, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
		$output = '';
		$file   = $relpath = '';
		$isfile = false;

		foreach ($arr as $s) {
			if ($isfile) {
				$isfile  = false;
				$file    = $s;
				$relpath = T3Less::relativePath($todir, dirname($file));
				$output .= "\n#".T3Less::$kfilepath."{content: \"{$file}\";}\n";
			} else {
				$output .= ($file ? T3Path::updateUrl($s, $relpath) : $s) . "\n\n";
				$isfile = true;
			}
		}

		return $output;
	}
}
