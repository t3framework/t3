<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument Megamenu renderer
 */
class JDocumentRendererMegamenuRender extends JDocumentRenderer
{
	/**
	 * Render megamenu block, then push the output into megamenu renderer to display
	 *
	 * @param   string  $position  The position of the modules to render
	 * @param   array   $params    Associative array of values
	 * @param   string  $content   Module content
	 *
	 * @return  string  The output of the script
	 *
	 * @since   11.1
	 */
	public function render($info = null, $params = array(), $content = null)
	{
		T3::import('menu/megamenu');

		$t3app = T3::getApp();

		//we will check from params
    $menutype      = empty($params['menutype']) ? (empty($params['name']) ? $t3app->getParam('mm_type', 'mainmenu') : $params['name']) : $params['menutype'];
		$currentconfig = json_decode($t3app->getParam('mm_config', ''), true);

		//force to array
		if (!is_array($currentconfig)) {
			$currentconfig = (array)$currentconfig;
		}

		//get user access levels
		$viewLevels = JFactory::getUser()->getAuthorisedViewLevels();
		$mmkey = $menutype;
		$mmconfig = array();
		if (!empty($currentconfig)) {

			//find best fit configuration based on view level
			$vlevels = array_merge($viewLevels);
			if (is_array($vlevels) && in_array(3, $vlevels)) { //we assume, if a user is special, they should be registered also
				$vlevels[] = 2;
			}
			$vlevels = array_unique($vlevels);
			rsort($vlevels);

			if (is_array($vlevels) && count($vlevels)) {
				//should check for special view level first
				if (in_array(3, $vlevels)) {
					array_unshift($vlevels, 3);
				}

				$found = false;
				foreach ($vlevels as $vlevel) {
					$mmkey = $menutype . '-' . $vlevel;
					if (isset($currentconfig[$mmkey])) {
						$found = true;
						break;
					}
				}

				//fallback
				if (!$found) {
					$mmkey = $menutype;
				}
			}

			// check if available configuration for language override
			$langcode = substr(JFactory::getDocument()->language, 0, 2);
			$langtype = $menutype . '-' . $langcode;
			$langkey  = $langtype . str_replace($menutype, '', $mmkey);
			
			if(isset($currentconfig[$langkey])) {
				$mmkey    = $langkey;
				$menutype = $langtype;
			} else if (isset($currentconfig[$langtype])){
				$mmkey    = $menutype = $langtype;
			}

			if (isset($currentconfig[$mmkey])) {
				$mmconfig = $currentconfig[$mmkey];
				if(!is_array($mmconfig)){
					$mmconfig = array();
				}
			}
		}

		JDispatcher::getInstance()->trigger('onT3Megamenu', array(&$menutype, &$mmconfig, &$viewLevels));

		$mmconfig['access'] = $viewLevels;
		$menu = new T3MenuMegamenu ($menutype, $mmconfig, $t3app->_tpl->params);
		
		$t3app->setBuffer($menu->render(true), 'megamenu', empty($params['name']) ? null : $params['name'], null);
		return '';
	}
}
