<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pagination;

defined('JPATH_PLATFORM') or die;


// Make alias of original FileLayout
\T3::makeAlias(JPATH_LIBRARIES . '/src/Pagination/Pagination.php', 'Pagination', '_Pagination');

/**
 * Pagination Class. Provides a common interface for content pagination for the Joomla! CMS.
 *
 * @since  1.5
 */
class Pagination extends _Pagination
{

	/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x.
	 *
	 * @return  string  Pagination page list string.
	 *
	 * @since   1.5
	 */
	public function getPagesLinks()
	{
		// Build the page navigation list.
		$data = $this->_buildDataObject();

		$list           = array();
		$list['prefix'] = $this->prefix;

		$itemOverride = false;
		$listOverride = false;

		// $chromePath = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/pagination.php';
		// T3: detect if chrome pagination.php in template or in plugin
		$chromePath = \T3Path::getPath ('html/pagination.php');

		if (file_exists($chromePath))
		{
			include_once $chromePath;

			/*
			 * @deprecated 4.0 Item rendering should use a layout
			 */
			if (function_exists('pagination_item_active') && function_exists('pagination_item_inactive'))
			{
				\JLog::add(
					'pagination_item_active and pagination_item_inactive are deprecated. Use the layout joomla.pagination.link instead.',
					\JLog::WARNING,
					'deprecated'
				);

				$itemOverride = true;
			}

			/*
			 * @deprecated 4.0 The list rendering is now a layout.
			 * @see Pagination::_list_render()
			 */
			if (function_exists('pagination_list_render'))
			{
				\JLog::add('pagination_list_render is deprecated. Use the layout joomla.pagination.list instead.', \JLog::WARNING, 'deprecated');
				$listOverride = true;
			}
		}

		// Build the select list
		if ($data->all->base !== null)
		{
			$list['all']['active'] = true;
			$list['all']['data']   = $itemOverride ? pagination_item_active($data->all) : $this->_item_active($data->all);
		}
		else
		{
			$list['all']['active'] = false;
			$list['all']['data']   = $itemOverride ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}

		if ($data->start->base !== null)
		{
			$list['start']['active'] = true;
			$list['start']['data']   = $itemOverride ? pagination_item_active($data->start) : $this->_item_active($data->start);
		}
		else
		{
			$list['start']['active'] = false;
			$list['start']['data']   = $itemOverride ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}

		if ($data->previous->base !== null)
		{
			$list['previous']['active'] = true;
			$list['previous']['data']   = $itemOverride ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		}
		else
		{
			$list['previous']['active'] = false;
			$list['previous']['data']   = $itemOverride ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		// Make sure it exists
		$list['pages'] = array();

		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null)
			{
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data']   = $itemOverride ? pagination_item_active($page) : $this->_item_active($page);
			}
			else
			{
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data']   = $itemOverride ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}

		if ($data->next->base !== null)
		{
			$list['next']['active'] = true;
			$list['next']['data']   = $itemOverride ? pagination_item_active($data->next) : $this->_item_active($data->next);
		}
		else
		{
			$list['next']['active'] = false;
			$list['next']['data']   = $itemOverride ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}

		if ($data->end->base !== null)
		{
			$list['end']['active'] = true;
			$list['end']['data']   = $itemOverride ? pagination_item_active($data->end) : $this->_item_active($data->end);
		}
		else
		{
			$list['end']['active'] = false;
			$list['end']['data']   = $itemOverride ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if ($this->total > $this->limit)
		{
			return $listOverride ? pagination_list_render($list) : $this->_list_render($list);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Return the pagination footer.
	 *
	 * @return  string  Pagination footer.
	 *
	 * @since   1.5
	 */
	public function getListFooter()
	{
		// Keep B/C for overrides done with chromes
		// $chromePath = JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/pagination.php';
		// T3: detect if chrome pagination.php in template or in plugin
		$chromePath = \T3Path::getPath ('html/pagination.php');

		if (file_exists($chromePath))
		{
			include_once $chromePath;

			if (function_exists('pagination_list_footer'))
			{
				\JLog::add('pagination_list_footer is deprecated. Use the layout joomla.pagination.links instead.', \JLog::WARNING, 'deprecated');

				$list = array(
					'prefix'       => $this->prefix,
					'limit'        => $this->limit,
					'limitstart'   => $this->limitstart,
					'total'        => $this->total,
					'limitfield'   => $this->getLimitBox(),
					'pagescounter' => $this->getPagesCounter(),
					'pageslinks'   => $this->getPagesLinks(),
				);

				return pagination_list_footer($list);
			}
		}

		return $this->getPaginationLinks();
	}



	/**
	 * Compatible with J3
	 */
	public function get($property, $default = null)
	{
		\JLog::add('Pagination::get() is deprecated. Access the properties directly.', \JLog::WARNING, 'deprecated');

		if (strpos($property, '.'))
		{
			$prop     = explode('.', $property);
			$prop[1]  = ucfirst($prop[1]);
			$property = implode($prop);
		}

		if (isset($this->$property))
		{
			return $this->$property;
		}

		return $default;
	}
}
