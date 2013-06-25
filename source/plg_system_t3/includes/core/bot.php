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
 * T3Less class
 * Auto trigger
 *
 * @package T3
 */
class T3Bot extends JObject
{
	// call before checking & loading T3
	public static function preload () {
		// check if menu is alter, then turn a flag to reupdate megamenu configuration
		$input = JFactory::getApplication()->input;
		if ($input->get('option') == 'com_menus' && 
			preg_match('#save|apply|trash|remove|delete|publish|order#i', $input->get('task'))) {
			// get all template styles
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query
				->select('*')
				->from('#__template_styles')
				->where('client_id=0');

			$db->setQuery($query);
			$themes = $db->loadObjectList();
			
			//update all global parameters
			foreach($themes as $theme){
				$registry = new JRegistry;
				$registry->loadString($theme->params);
				$mm_config = $registry->get('mm_config');
				if (!$mm_config) continue;

				// turn on flag
				$registry->set('mm_config_needupdate', 1); //overwrite with new value

				$query = $db->getQuery(true);
				$query
					->update('#__template_styles')
					->set('params =' . $db->quote($registry->toString()))
					->where('id =' . (int)$theme->id);

				$db->setQuery($query);
				$db->execute();
			}
			// force reload cache template
			$cache = JFactory::getCache('com_templates', '');
			$cache->clean();
		}
	}

	// call before call T3::init
	public static function beforeInit () {
	}

	// call after call T3::init
	public static function afterInit () {
		$app = JFactory::getApplication();
		$input = $app->input;
		if (!$app->isAdmin()) {
			// check if need update megamenu configuration
			if ($app->getTemplate(true)->params->get ('mm_config_needupdate')) {
				T3::import('menu/megamenu');
				$currentconfig = json_decode($app->getTemplate(true)->params->get ('mm_config'), true);
				if (!is_array($currentconfig)) $currentconfig = array();
				
				foreach ($currentconfig as $menutype => $mmconfig) {
					if (!is_array($mmconfig)) continue;
					$menu = new T3MenuMegamenu ($menutype, $mmconfig);
					$children = $menu->get ('children');
					foreach ($mmconfig as $item => $setting) {
						if (isset ($setting['sub'])) {
							$sub = &$setting['sub'];
							$id = (int) substr($item, 5); // remove item-
							$modify = false;

							if (!isset($children[$id]) || !count ($children[$id])){
								//check and remove any empty row
								for ($j=0; $j < count($sub['rows']); $j++) {
									$remove = true;
									for ($k=0; $k < count($sub['rows'][$j]); $k++) {
										if (isset($sub['rows'][$j][$k]['position'])) {
											$remove = false;
											break;
										}
									}

									if($remove){
										$modify = true;
										unset($sub['rows'][$j]);
									}
								}

								if($modify){
									$sub['rows'] = array_values($sub['rows']); //re-index
									$mmconfig[$item]['sub'] = $sub;
								}

								continue;
							}

							$items = array();
							foreach ($sub['rows'] as $row) {
								foreach ($row as $col) {
									if (!isset($col['position'])) {
										$items[] = $col['item'];
									}
								}
							}
							// update the order of items
							$_items = array();
							$_itemsids = array();
							$firstitem = 0;
							foreach ($children[$id] as $child) {
								$_itemsids[] = (int)$child->id;

								if (!$firstitem) $firstitem = (int)$child->id;
								if (in_array($child->id, $items)) {
									$_items [] = (int)$child->id;
								}
							}

							// $_items[0] = $firstitem;
							if (empty($_items) || $_items[0] != $firstitem) {
								if (count ($_items) == count($items)) {
									$_items[0] = $firstitem;
								} else {
									array_splice($_items, 0, 0, $firstitem);
								}
							}

							// no need update config for this item
							if ($items == $_items) continue;
							// update back to setting

							$i = 0;
							$c = count ($_items);
							for ($j=0; $j < count($sub['rows']); $j++) {
							// foreach ($sub['rows'] as $row) {
								for ($k=0; $k < count($sub['rows'][$j]); $k++) {
								// foreach ($row as $col) {
									if (!isset($sub['rows'][$j][$k]['position'])) {
										$sub['rows'][$j][$k]['item'] = $i < $c ? $_items[$i++] : "";
									}
								}
							}

							//update - add new rows for new items - at the first rows
							if(!empty($_items) && count($items) == 0){
								$modify = true;
								array_unshift($sub['rows'], array(array('item' => $_items[0], 'width' => 12)));
							}

							//check and remove any empty row
							for ($j=0; $j < count($sub['rows']); $j++) {
								$remove = true;
								for ($k=0; $k < count($sub['rows'][$j]); $k++) {
									if (isset($sub['rows'][$j][$k]['position']) || in_array($sub['rows'][$j][$k]['item'], $_itemsids)) {
										$remove = false;
										break;
									}
								}

								if($remove){
									$modify = true;
									unset($sub['rows'][$j]);
								}
							}

							if($modify){
								$sub['rows'] = array_values($sub['rows']); //re-index
							}

							$mmconfig[$item]['sub'] = $sub;
						}
					}

					$currentconfig[$menutype] = $mmconfig;
				}

				// update  megamenu back to other template styles parameter
				$mm_config = json_encode($currentconfig);

				// update megamenu back to current template style parameter
				$params = $app->getTemplate(true)->params;
				$params->set ('mm_config', $mm_config);
				$app->setTemplate (T3_TEMPLATE, $params);
				if ($input->get('tplparams')) {
					$input->set('tplparams', $app->getTemplate(true)->params);
				}

				//get all other styles that have the same template
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query
					->select('*')
					->from('#__template_styles')
					->where('template=' . $db->quote(T3_TEMPLATE))
					->where('client_id=0');

				$db->setQuery($query);
				$themes = $db->loadObjectList();
				
				//update all global parameters
				foreach($themes as $theme){
					$registry = new JRegistry;
					$registry->loadString($theme->params);
					$registry->set('mm_config', $mm_config); //overwrite with new value
					$registry->set('mm_config_needupdate', ""); //overwrite with new value

					$query = $db->getQuery(true);
					$query
						->update('#__template_styles')
						->set('params =' . $db->quote($registry->toString()))
						->where('id =' . (int)$theme->id);

					$db->setQuery($query);
					$db->execute();
				}
				// force reload cache template
				$cache = JFactory::getCache('com_templates', '');
				$cache->clean();
			}
		}		

	}
}