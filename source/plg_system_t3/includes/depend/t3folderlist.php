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

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('folderlist');

/**
 * Supports an HTML select list of files
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldT3FolderList extends JFormFieldFolderList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'T3FolderList';

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
		$table = JTable::getInstance('Style', 'TemplatesTable', array());
		$table->load((int) JFactory::getApplication()->input->getInt('id'));
		// update path to this template 
		$path = (string) $this->element['directory'];
		// process path in template
		$options = array();
		$vals = array();
		// get all path in template
		$paths = T3Path::getAllPath ($path);
		foreach ($paths as $path) {
			$this->directory = $this->element['directory'] = JPath::clean($path);
			$tmps = parent::getOptions();
			foreach ($tmps as $tmp) {
				if (in_array($tmp->value, $vals)) continue;
				$vals[] = $tmp->value;
				$options[] = $tmp;
			}
		}
		return $options;
	}
}
