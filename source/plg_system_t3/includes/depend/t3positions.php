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
defined('_JEXEC') or die;

/**
 * Radio List Element
 *
 * @package  T3.Core.Element
 */
class JFormFieldT3Positions extends JFormField
{
	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	protected $type = 'T3Positions';

	/**
	 * Check and load assets file if needed
	 */
	function loadAsset(){
		if (!defined ('_T3_DEPEND_ASSET_')) {
			define ('_T3_DEPEND_ASSET_', 1);

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
					adminurl: \'' . JUri::getInstance()->toString() . '\',
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

		T3::import('admin/layout');
		
		return $this->getPositions();
	}
	
	function getPositions()
	{
		$path     = JPATH_SITE;
		$lang     = JFactory::getLanguage();
		$clientId = 0;
		$state    = 1;
		
		$templates      = array_keys(T3AdminLayout::getTemplates($clientId, $state));
		$templateGroups = array();
		
		// Add positions from templates
		foreach ($templates as $template) {
			$options = array();
			
			$positions = T3AdminLayout::getTplPositions($clientId, $template);
			if (is_array($positions))
				foreach ($positions as $position) {
					$text      = T3AdminLayout::getTranslatedModulePosition($clientId, $template, $position) . ' [' . $position . ']';
					$options[] = T3AdminLayout::createOption($position, $text);
				}
			
			$templateGroups[$template] = T3AdminLayout::createOptionGroup(ucfirst($template), $options);
		}
		
		// Add custom position to options
		$customGroupText                  = JText::_('T3_LAYOUT_CUSTOM_POSITION');
		$customPositions                  = T3AdminLayout::getDbPositions($clientId);
		$templateGroups[$customGroupText] = T3AdminLayout::createOptionGroup($customGroupText, $customPositions);


		$multiple = $this->toBoolean((string) $this->element['multiple']);
		$disabled = $this->toBoolean((string) $this->element['disabled']);
		
		
		return JHtml::_('select.groupedlist', $templateGroups, $this->name, array(
			'list.attr' => ($multiple ? ' multiple="multiple" size="10"' : '') . ($disabled ? 'disabled="disabled"' : '')
		));
	}


	/**
	 * Helper function, check the field attribute and return boolean value
	 *
	 * @return  boolean the check result
	 */
	function toBoolean($attr){
		return !in_array($attr, array('false', '', '0', 'no', 'off'));
	}
}