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

if(!class_exists('T3MenuMegamenuTpl', false)){
	T3::import('menu/megamenu.tpl');
}
if (is_file(T3_TEMPLATE_PATH.'/html/megamenu.php')) {
	require_once T3_TEMPLATE_PATH.'/html/megamenu.php';
}

class T3MenuMegamenu {

	/**
	 * Internal variables
	 */
	protected $_items = array();
	protected $children = array();
	protected $settings = null;
	protected $params = null;
	protected $menu = '';
	protected $active_id = 0;
	protected $active_tree = array();
	protected $top_level_caption = false;

	/**
	 * @param  string  $menutype  menu type to render
	 * @param  array   $settings  settings information
	 * @param  null    $params    other parameters
	 */
	function __construct($menutype = 'mainmenu', $settings = array(), $params = null) {
		$app   = JFactory::getApplication();
		$menu  = $app->getMenu('site');

		$attributes = array('menutype');
		$values     = array($menutype);

		if(isset($settings['access'])){
			$attributes[] = 'access';
			$values[]     = $settings['access'];
		} else {
			$settings['access'] = array(1);
		}
		
		if(isset($settings['language'])){
			$attributes[] = 'language';
			$values[]     = $settings['language'];
		}

		$items = $menu->getItems($attributes, $values);
		
		$active            = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
		$this->active_id   = $active ? $active->id : 0;
		$this->active_tree = $active->tree;
		
		$this->settings = $settings;
		$this->params   = $params;
		$this->editmode = isset($settings['editmode']);
		foreach ($items as &$item) {
			//remove all non-parent item (the parent has access higher access level)
			if($item->level >= 2 && !isset($this->_items[$item->parent_id])){
				continue;
			}
			
			//intergration with new params joomla 3.6.x (menu_show)
			$menu_show = $item->getParams()->get('menu_show');
			if (empty($menu_show) && $menu_show!==null)
				continue;

			$parent                           = isset($this->children[$item->parent_id]) ? $this->children[$item->parent_id] : array();
			$parent[]                         = $item;
			$this->children[$item->parent_id] = $parent;
			$this->_items[$item->id]          = $item;
		}
		foreach ($items as &$item) {
			// bind setting for this item
			$key     = 'item-' . $item->id;
			$setting = isset($this->settings[$key]) ? $this->settings[$key] : array();
			
			// decode html tag
			if (isset($setting['caption']) && $setting['caption'])
				$setting['caption'] = str_replace(array('[lt]', '[gt]'), array('<', '>'), $setting['caption']);
			if ($item->level == 1 && isset($setting['caption']) && $setting['caption'])
				$this->top_level_caption = true;
			
			// active - current
			$class = '';
			if ($item->id == $this->active_id) {
				$class .= ' current';
			}
			if (in_array($item->id, $this->active_tree)) {
				$class .= ' active';
			} elseif ($item->type == 'alias') {
				$aliasToId = $item->getParams()->get('aliasoptions');
				if (count($this->active_tree) > 0 && $aliasToId == $this->active_tree[count($this->active_tree) - 1]) {
					$class .= ' active';
				} elseif (in_array($aliasToId, $this->active_tree)) {
					$class .= ' alias-parent-active';
				}
			}
			
			$item->class    = $class;
			$item->mega     = 0;
			$item->group    = 0;
			$item->dropdown = 0;
			if (isset($setting['group']) && $item->level > 1) {
				$item->group = 1;
			} else {
				if ((isset($this->children[$item->id]) && ($this->editmode || !isset($setting['hidesub']))) || isset($setting['sub'])) {
					$item->dropdown = 1;
				}
			}
			$item->mega = $item->group || $item->dropdown;
			// set default sub if not exists
			if ($item->mega) {
			 	if (!isset($setting['sub'])) $setting['sub'] = array();
			 	if (isset($this->children[$item->id]) && (!isset($setting['sub']['rows']) || !count($setting['sub']['rows']))) {
					$c = $this->children[$item->id][0]->id;
					$setting['sub'] = array('rows'=>array(array(array('width'=>12, 'item'=>$c))));
				}
			}
			$item->setting = $setting;
			
			$item->flink = $item->link;
			
			// Reverted back for CMS version 2.5.6
			switch ($item->type) {
				case 'separator':
				case 'heading':
					// No further action needed.
					break;
				
				case 'url':
					if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
						// If this is an internal Joomla link, ensure the Itemid is set.
						$item->flink = $item->link . '&Itemid=' . $item->id;
					}
					break;
				
				case 'alias':
					// If this is an alias use the item id stored in the parameters to make the link.
					$item->flink = 'index.php?Itemid=' . $item->getParams()->get('aliasoptions');
					break;
				
				default:
					//$router = JSite::getRouter();
					$item->flink = 'index.php?Itemid=' . $item->id;
					break;
			}
			
			if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
				$item->flink = JRoute::_($item->flink, true, $item->getParams()->get('secure'));
			} else {
				$item->flink = JRoute::_($item->flink);
			}
			
			// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
			// when the cause of that is found the argument should be removed
			$item->title        = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
			$item->anchor_css   = htmlspecialchars($item->getParams()->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
			$item->anchor_title = htmlspecialchars($item->getParams()->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
			$item->anchor_rel = htmlspecialchars($item->getParams()->get('menu-anchor_rel', ''), ENT_COMPAT, 'UTF-8', false);
			$item->menu_image   = $item->getParams()->get('menu_image', '') ? htmlspecialchars($item->getParams()->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
			$item->menu_image_css = htmlspecialchars($item->getParams()->get('menu_image_css', ''), ENT_COMPAT, 'UTF-8', false);
		}
	}
	
	function render($return = false) {
		$this->menu = '';
		
		$this->_('beginmenu');
		$keys = array_keys($this->_items);
		if (count($keys)) { //in case the keys is empty array
			$this->nav(null, $keys[0]);
		}
		$this->_('endmenu');
		
		if ($return) {
			return $this->menu;
		} else {
			echo $this->menu;
		}
	}
	
	function nav($pitem, $start = 0, $end = 0) {
		if ($start > 0) {
			if (!isset($this->_items[$start]))
				return;
			$pid     = $this->_items[$start]->parent_id;
			$items   = array();
			$started = false;
			foreach ($this->children[$pid] as $item) {
				if ($started) {
					if ($item->id == $end)
						break;
					$items[] = $item;
				} else {
					if ($item->id == $start) {
						$started = true;
						$items[] = $item;
					}
				}
			}
			if (!count($items))
				return;
		} else if ($start === 0) {
			$pid = $pitem->id;
			if (!isset($this->children[$pid]))
				return;
			$items = $this->children[$pid];
		} else {
			//empty menu
			return;
		}
		
		$this->_('beginnav', array(
			'item' => $pitem
		));
		
		foreach ($items as $item) {
			$this->item($item);
		}
		
		$this->_('endnav', array(
			'item' => $pitem
		));
	}
	
	function item($item) {
		// item content
		$setting = $item->setting;
		
		$this->_('beginitem', array(
			'item' => $item,
			'setting' => $setting,
			'menu' => $this
		));
		
		$this->menu .= $this->_('item', array(
			'item' => $item,
			'setting' => $setting,
			'menu' => $this
		));
		
		if ($item->mega) {
			$this->mega($item);
		}
		$this->_('enditem', array(
			'item' => $item
		));
	}
	
	function mega($item) {
		$setting   = $item->setting;
		$sub       = $setting['sub'];
		$items     = isset($this->children[$item->id]) ? $this->children[$item->id] : array();
		$firstitem = count($items) ? $items[0]->id : 0;
		
		$this->_('beginmega', array(
			'item' => $item
		));
		$endItems = array();
		$k1       = $k2 = 0;
		foreach ($sub['rows'] as $row) {
			foreach ($row as $col) {
				if (!isset($col['position'])) {
					if ($k1) {
						$k2 = $col['item'];
						if (!isset($this->_items[$k2]) || $this->_items[$k2]->parent_id != $item->id)
							break;
						$endItems[$k1] = $k2;
					}
					$k1 = $col['item'];
				}
			}
		}
		$endItems[$k1] = 0;
		
		$firstitemscol = true;
		foreach ($sub['rows'] as $row) {
			$this->_('beginrow', array(
				'menu' => $this
			));

			foreach ($row as $col) {
				$this->_('begincol', array(
					'setting' => $col,
					'menu' => $this
				));
				if (isset($col['position'])) {
					$this->module($col['position']);
				} else {
					if (!isset($endItems[$col['item']]))
						continue;
					$toitem    = $endItems[$col['item']];
					$startitem = $firstitemscol ? $firstitem : $col['item'];
					$this->nav($item, $startitem, $toitem);
					$firstitemscol = false;
				}
				$this->_('endcol');
			}
			$this->_('endrow');
		}
		$this->_('endmega');
	}
	
	function module($module) {
		// load module
		$id    = intval($module);
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params')
			->from('#__modules AS m')
			->where('m.id = ' . $id)
			->where('m.published = 1')
			->where('m.access IN ('.implode(',', $this->settings['access']).')');
		$db->setQuery($query);
		$module = $db->loadObject();
		
		//check in case the module is unpublish or deleted
		if ($module && $module->id) {
			$style   = 'T3Xhtml';
			$content = JModuleHelper::renderModule($module, array(
				'style' => $style
			));

			$app = JFactory::getApplication();
			$frontediting = $app->get('frontediting', 1);
			$user = JFactory::getUser();

			$canEdit = $user->id && $frontediting && !(T3::isAdmin() && $frontediting < 2) && $user->authorise('core.edit', 'com_modules');
			$menusEditing = ($frontediting == 2) && $user->authorise('core.edit', 'com_menus');

			if ($app->isClient('site') && $canEdit && trim($content) != '' && $user->authorise('core.edit', 'com_modules.module.' . $module->id))
			{
				$displayData = array('moduleHtml' => &$content, 'module' => $module, 'position' => $module->position, 'menusediting' => $menusEditing);
				JLayoutHelper::render('joomla.edit.frontediting_modules', $displayData);
			}

			$this->menu .= $content . "\n";
		}
	}
	
	function _($tmpl, $vars = array()) {
		$vars['menu'] = $this;
		$this->menu .= T3MenuMegamenuTpl::_($tmpl, $vars);
	}
	
	function get($prop) {
		if (isset($this->$prop))
			return $this->$prop;
		return null;
	}
	
	function getParam($name, $default = null) {
		if (!$this->params)
			return $default;
		return $this->params->get($name, $default);
	}
}
