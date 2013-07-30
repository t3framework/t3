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

		$db = JFactory::getDBO();
		$query = "SELECT e.extension_id, a.id, a.title, a.note, a.position, a.module, a.language,a.checked_out,
		a.checked_out_time, a.published, a.access, a.ordering, a.publish_up, a.publish_down,
		l.title AS language_title,uc.name AS editor,ag.title AS access_level,
		MIN(mm.menuid) AS pages,e.name AS name
		FROM `#__modules` AS a
		LEFT JOIN `#__languages` AS l ON l.lang_code = a.language
		LEFT JOIN #__users AS uc ON uc.id=a.checked_out
		LEFT JOIN #__viewlevels AS ag ON ag.id = a.access
		LEFT JOIN #__modules_menu AS mm ON mm.moduleid = a.id
		LEFT JOIN #__extensions AS e ON e.element = a.module
		WHERE (a.published IN (0, 1)) AND a.client_id = 0
		GROUP BY a.id";
		
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$groupHTML = array();

		if($this->element['show_default']){
			$groupHTML[] = JHTML::_('select.option', 'default', JText::_('JDEFAULT'));
		}

		if($this->element['show_none']){
			$groupHTML[] = JHTML::_('select.option', 'none', JText::_('JNONE'));
		} 

		if ($groups && count($groups)) {
			foreach ($groups as $v => $t) {
				$groupHTML[] = JHTML::_('select.option', $t->id, $t->title);
			}
		}

		$lists = JHTML::_('select.genericlist', $groupHTML, "{$this->name}" . ($this->element['multiple'] == 1 ? '[]' : ''), ($this->element['multiple'] == 1 ? 'multiple="multiple" size="10" ' : '') . ($this->element['disabled'] ? 'disabled="disabled"' : ''), 'value', 'text', $this->value);

		return $lists;
	}
}