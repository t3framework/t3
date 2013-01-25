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

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('filelist');

/**
 * Supports an HTML select list of files
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldT3FileList extends JFormFieldFileList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'T3FileList';

	/**
	 * The initialised state of the document object.
	 *
	 * @var    boolean
	 * @since  1.6
	 */
	protected static $initialised = false;

	/**
	 * Method to get the list of files for the field options.
	 * Specify the target directory with a directory attribute
	 * Attributes allow an exclude mask and stripping of extensions from file name.
	 * Default attribute may optionally be set to null (no file) or -1 (use a default).
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// update path to this template 
		$path = (string) $this->element['directory'];
		if (!is_dir($path)) {
			$this->element['directory'] = T3_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $path;
		}

 		return parent::getOptions();
	}
}
?>