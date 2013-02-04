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

t3import ('menu/megamenu.tpl');

class T3MenuMegamenu {
	protected $children = array();
	protected $_items = array();
	protected $settings = null;
	protected $menu = '';
	protected $active_id = 0;
	protected $active_tree = array();

	function __construct ($menutype='mainmenu', $settings=array()) {
		$app = JFactory::getApplication();
		$menu = $app->getMenu('site');
		$items = $menu->getItems('menutype', $menutype);

		$active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
		$this->active_id = $active ? $active->id : 0;
		$this->active_tree = $active->tree;

		$this->settings = $settings;
		$this->editmode = isset ($settings['editmode']);
		foreach ($items as &$item) {
			$parent = isset($this->children[$item->parent_id]) ? $this->children[$item->parent_id] : array();
			$parent[] = $item;
			$this->children[$item->parent_id] = $parent;
			$this->_items[$item->id] = $item;
		}
		foreach ($items as &$item) {
			// bind setting for this item
			$key = 'item-'.$item->id;
			$setting = isset($this->settings[$key]) ? $this->settings[$key] : array();

			// active - current
			$class = '';
			if ($item->id == $this->active_id) {
				$class .= ' current';
			}
			if (in_array($item->id, $this->active_tree)) {
				$class .= ' active';
			}
			elseif ($item->type == 'alias') {
				$aliasToId = $item->params->get('aliasoptions');
				if (count($this->active_tree) > 0 && $aliasToId == $this->active_tree[count($this->active_tree)-1]) {
					$class .= ' active';
				}
				elseif (in_array($aliasToId, $this->active_tree)) {
					$class .= ' alias-parent-active';
				}
			}

			$item->class = $class;
			$item->mega = 0;
			$item->group = 0;
			$item->dropdown = 0;
			if (isset($setting['group'])) {
				$item->group = 1;
			} else {
				if ((isset($this->children[$item->id]) && ($this->editmode || !isset($setting['hidesub']))) || isset($setting['sub'])) {
					$item->dropdown = 1;
				}
			}
			$item->mega = $item->group || $item->dropdown;
			// set default sub if not exists
			if ($item->mega && !isset($setting['sub'])) {
				$c = $this->children[$item->id][0]->id;
				$setting['sub'] = array('rows'=>array(array(array('width'=>12, 'item'=>$c))));
			}
			$item->setting = $setting;

			$item->flink  = $item->link;

			// Reverted back for CMS version 2.5.6
			switch ($item->type)
			{
				case 'separator':
				case 'heading':
					// No further action needed.
					continue;

				case 'url':
					if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
					{
						// If this is an internal Joomla link, ensure the Itemid is set.
						$item->flink = $item->link . '&Itemid=' . $item->id;
					}
					break;

				case 'alias':
					// If this is an alias use the item id stored in the parameters to make the link.
					$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
					break;

				default:
					$router = JSite::getRouter();
					if ($router->getMode() == JROUTER_MODE_SEF)
					{
						$item->flink = 'index.php?Itemid=' . $item->id;
					}
					else
					{
						$item->flink .= '&Itemid=' . $item->id;
					}
					break;
			}

			if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false))
			{
				$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
			}
			else
			{
				$item->flink = JRoute::_($item->flink);
			}

			// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
			// when the cause of that is found the argument should be removed
			$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
			$item->anchor_css   = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
			$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
			$item->menu_image   = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';

		}
	}

	function render ($return = false) {
		$this->menu = '';

		$this->_('beginmenu');
		$keys = array_keys($this->_items);
		if(count($keys)){	//in case the keys is empty array
			$this->nav(null, $keys[0]);
		}
		$this->_('endmenu');

		if ($return) {
			return $this->menu;
		} else {
			echo $this->menu;			
		}
	}

	function nav ($pitem, $start = 0, $end = 0) {
		if ($start > 0) {		
			if (!isset ($this->_items[$start])) return ;
			$pid = $this->_items[$start]->parent_id;
			$items = array();
			$started = false;
			foreach ($this->children[$pid] as $item) {
				if ($started) {
					if ($item->id == $end) break;
					$items[] = $item;
				} else {
					if ($item->id == $start) {
						$started = true;
						$items[] = $item;
					}
				}
			}
			if (!count($items)) return;
		} else if ($start == 0){
			$pid = $pitem->id;
			if (!isset($this->children[$pid])) return ;
			$items = $this->children[$pid];			
		} else {
			//empty menu
			return;
		}

		$this->_('beginnav', array ('item'=>$pitem));

		foreach ($items as $item) {
			$this->item ($item);
		}

		$this->_('endnav', array ('item'=>$pitem));
	}

	function item ($item) {
		// item content
		$setting = $item->setting;
		
		$this->_('beginitem', array ('item'=>$item, 'setting'=>$setting));

		$this->menu .= $this->_('item', array ('item'=>$item, 'setting'=>$setting));

		if ($item->mega) {
			$this->mega($item);
		}
		$this->_('enditem', array ('item'=>$item));
	}

	function mega ($item) {
		$key = 'item-'.$item->id;
		$setting = $item->setting;
		$sub = $setting['sub'];

		$this->_('beginmega', array ('item'=>$item));
		$endItems = array();
		$k = 0;
		foreach ($sub['rows'] as $row) {
			foreach ($row as $col) {
				if (!isset($col['position'])) {
					if ($k) $endItems[$k] = $col['item'];
					$k = $col['item'];
				}
			}
		}
		$endItems[$k] = 0;

		foreach ($sub['rows'] as $row) {
			$this->_('beginrow');
			foreach ($row as $col) {
				$this->_('begincol', array('setting'=>$col));
				if (isset($col['position'])) {
					$this->module ($col['position']);
				} else {
					$toitem = $endItems[$col['item']];
					$this->nav ($item, $col['item'], $toitem);
				}
				$this->_('endcol');
			}
			$this->_('endrow');
		}
		$this->_('endmega');
	}

	function module ($module) {
			// load module
		$id = intval($module);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params');
		$query->from('#__modules AS m');
		$query->where('m.id = '.$id);
		$query->where('m.published = 1');
		$db->setQuery($query);
		$module = $db->loadObject ();

		//check in case the module is unpublish or deleted
		if($module && $module->id){
			$style = 'T3Xhtml';
			$content = JModuleHelper::renderModule($module, array('style'=>$style));

			$this->menu .= $content."\n";
		}
	}

	function _ ($tmpl, $vars = array()) {
		if (method_exists('T3MenuMegamenuTpl', $tmpl)) {			
			$this->menu .= T3MenuMegamenuTpl::$tmpl($vars)."\n";
		} else {
			$this->menu .= "$tmpl\n";			
		}
	}
}