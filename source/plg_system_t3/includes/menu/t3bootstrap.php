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

if (!class_exists('T3BootstrapTpl', false)) {
	T3::import('menu/t3bootstrap.tpl');
}

class T3Bootstrap
{

	/**
	 * Internal variables
	 */
	protected $menutype;
	protected $menu;

	/**
	 * @param string $menutype
	 */
	function __construct($menutype = 'mainmenu')
	{
		$this->menutype = $menutype;
		$this->menu = '';
	}

	/**
	 * @return string
	 */
	function render()
	{
		if(!$this->menu){
			ob_start();
			T3BootstrapTpl::render($this->getList());
			$this->menu = ob_get_contents();
			ob_end_clean();
		}

		return $this->menu;
	}

	/**
	 * @return mixed
	 */
	function getList()
	{
		$app   = JFactory::getApplication();
		$menu  = $app->getMenu();

		// Get active menu item
		$items = $menu->getItems('menutype', $this->menutype);
		$hidden_parents = array();
		$lastitem = 0;

		if ($items) {
			foreach ($items as $i => $item) {

				// Exclude item with menu item option 'Display in Menu' set to 'No' - #522
				if (($item->params->get('menu_show', 1) == 0) || in_array($item->parent_id, $hidden_parents))
				{
					$hidden_parents[] = $item->id;
					unset($items[$i]);
					continue;
				}

				$item->deeper = false;
				$item->shallower = false;
				$item->level_diff = 0;

				if (isset($items[$lastitem])) {
					$items[$lastitem]->deeper = ($item->level > $items[$lastitem]->level);
					$items[$lastitem]->shallower = ($item->level < $items[$lastitem]->level);
					$items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
				}

				$item->parent = (boolean)$menu->getItems('parent_id', (int)$item->id, true);

				$lastitem = $i;
				$item->active = false;
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
						$item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
						break;

					default:
						$router = $app::getRouter();
						if ($router->getMode() == JROUTER_MODE_SEF) {
							$item->flink = 'index.php?Itemid=' . $item->id;
						} else {
							$item->flink .= '&Itemid=' . $item->id;
						}
						break;
				}

				if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
					$item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
				} else {
					$item->flink = JRoute::_($item->flink);
				}

				// We prevent the double encoding because for some reason the $item is shared for menu modules and we get double encoding
				// when the cause of that is found the argument should be removed
				$item->title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
				$item->anchor_css = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
				$item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
				$item->menu_image = $item->params->get('menu_image', '') ? htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
			}

			if (isset($items[$lastitem])) {
				$items[$lastitem]->deeper = (1 > $items[$lastitem]->level);
				$items[$lastitem]->shallower = (1 < $items[$lastitem]->level);
				$items[$lastitem]->level_diff = ($items[$lastitem]->level - 1);
			}
		}

		return $items;
	}

	/**
	 * Get base menu item.
	 *
	 * @return   object
	 */
	public static function getBase()
	{
		return self::getActive();
	}

	/**
	 * Get active menu item.
	 *
	 * @return  object
	 */
	public static function getActive()
	{
		$menu = JFactory::getApplication()->getMenu();
		return $menu->getActive() ? $menu->getActive() : $menu->getDefault();
	}
}
