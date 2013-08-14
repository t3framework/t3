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

/**
 * Radio List Element
 *
 * @package  T3.Core.Element
 */
class JFormFieldT3Modules extends JFormField
{
	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	protected $type = 'T3Modules';

	/**
	 * Check and load assets file if needed
	 */
	function loadAsset(){
		if (!defined ('_T3_DEPEND_ASSET_')) {
			define ('_T3_DEPEND_ASSET_', 1);
			
			if(!defined('T3')){
				$t3url = str_replace(DIRECTORY_SEPARATOR, '/', JURI::base(true) . '/' . substr(dirname(__FILE__), strlen(JPATH_SITE)));
				$t3url = str_replace('/administrator/', '/', $uri);
				$t3url = str_replace('//', '/', $uri);
			} else {
				$t3url = T3_ADMIN_URL;
			}

			$jdoc = JFactory::getDocument();

			if(!defined('T3_TEMPLATE')){
				JFactory::getLanguage()->load(T3_PLUGIN, JPATH_ADMINISTRATOR);

				if(version_compare(JVERSION, '3.0', 'ge')){
					JHtml::_('jquery.framework');
				} else {
					$jdoc->addScript(T3_ADMIN_URL . '/admin/js/jquery-1.8.3.min.js');
					$jdoc->addScript(T3_ADMIN_URL . '/admin/js/jquery.noconflict.js');
				}

				$jdoc->addStyleSheet(T3_ADMIN_URL . '/includes/depend/css/depend.css');
				$jdoc->addScript(T3_ADMIN_URL . '/includes/depend/js/depend.js');
			}

			JFactory::getDocument()->addScriptDeclaration ( '
				jQuery.extend(T3Depend, {
					adminurl: \'' . JFactory::getURI()->toString() . '\',
					rooturl: \'' . JURI::root() . '\'
				});
			');
		}
	}
	
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	function getInput()
	{
		$this->loadAsset();

		$show_default = $this->toBoolean((string) $this->element['show_default']);
		$show_none    = $this->toBoolean((string) $this->element['show_none']);
		$multiple     = $this->toBoolean((string) $this->element['multiple']);
		$disabled     = $this->toBoolean((string) $this->element['disabled']);

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('id, title, module, position')
			->from('#__modules')
			->where('published = 1')
			->where('client_id = 0')
			->order('title');
		$db->setQuery($query);
		
		$modules = $db->loadObjectList();
		$moduleopts = array();

		if($show_default){
			$moduleopts[] = JHTML::_('select.option', 'default', JText::_('JDEFAULT'));
		}

		if($show_none){
			$moduleopts[] = JHTML::_('select.option', 'none', JText::_('JNONE'));
		} 

		if (is_array($modules)) {
			foreach ($modules as $module) {
				$moduleopts[] = JHTML::_('select.option', $module->id, $module->title);
			}
		}

		return JHTML::_('select.genericlist', $moduleopts, $this->name . ($multiple ? '[]' : ''), ($multiple ? 'multiple="multiple" size="10" ' : '') . ($disabled ? 'disabled="disabled"' : ''), 'value', 'text', $this->value);
	}


	/**
	 * Helper function, check the field attribute and return boolean value
	 *
	 * @return  boolean the check result
	 */
	function toBoolean($str){
		return !in_array($str, array('false', '', '0', 'no', 'off'));
	}
}