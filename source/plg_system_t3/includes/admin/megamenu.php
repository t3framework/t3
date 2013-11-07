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

class T3AdminMegamenu
{
	public static function display()
	{
		T3::import('menu/megamenu');
		$input = JFactory::getApplication()->input;
		
		//params
		$tplparams = T3::getTemplateParams();
		
		//menu type
		$menutype = $input->get('t3menu', 'mainmenu');
		
		//accessLevel
		$accessLevel = array();
		$t3acl       = (int) $input->get('t3acl', 1);
		for ($i = 1; $i <= $t3acl; $i++) { 
			$accessLevel[] = $i;
		}
		$accessLevel  = array_unique($accessLevel);

		//languages
		$languages = array(trim($input->get('t3lang', '*')));
		if($languages[0] != '*'){
			$languages[] = '*';
		}

		//check config
		$currentconfig = $tplparams instanceof JRegistry ? json_decode($tplparams->get('mm_config', ''), true) : null;
		$mmkey         = $menutype . (($t3acl == 1) ? '' : '-' . $t3acl);
		$mmconfig      = array();

		if($currentconfig){
			for ($i = $t3acl; $i >= 1; $i--) {
				$tmmkey = $menutype . (($i == 1) ? '' : '-' . $i);
				if(isset($currentconfig[$tmmkey])){
					$mmconfig = $currentconfig[$tmmkey];
					break;
				}
			}
		}

		if(!is_array($mmconfig)){
			$mmconfig = array();
		}

		$mmconfig['editmode'] = true;
		$mmconfig['access']   = $accessLevel;
		$mmconfig['language'] = $languages;
		
		//build the menu
		$menu   = new T3MenuMegamenu($menutype, $mmconfig);
		$buffer = $menu->render(true);
		
		// replace image path
		$base      = JURI::base(true) . '/';
		$protocols = '[a-zA-Z0-9]+:'; //To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
		$regex     = '#(src)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$buffer    = preg_replace($regex, "$1=\"$base\$2\"", $buffer);
		
		//remove invisibile content	
		$buffer = preg_replace(array(
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu'
		), array(
			'',
			''
		), $buffer);

		//output the megamenu key to save
		echo $buffer . '<input id="megamenu-key" type="hidden" name="mmkey" value="' . $mmkey . '"/>';
	}

	public static function delete(){
		$input         = JFactory::getApplication()->input;
		$template      = $input->get('template');
		$mmkey         = $input->get('mmkey', $input->get('menutype', 'mainmenu'));
		$tplparams     = T3::getTemplateParams();
		
		$currentconfig = $tplparams instanceof JRegistry ? json_decode($tplparams->get('mm_config', ''), true) : null;

		if (!is_array($currentconfig)) {
			$currentconfig = array();
		}

		//delete it
		if(isset($currentconfig[$mmkey])){
			unset($currentconfig[$mmkey]);
		}
		$currentconfig = json_encode($currentconfig);
		
		//get all other styles that have the same template
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__template_styles')
			->where('template=' . $db->quote($template));

		$db->setQuery($query);
		$themes = $db->loadObjectList();
		$return = true;
		
		foreach($themes as $theme){
			$registry = new JRegistry;
			$registry->loadString($theme->params);

			//overwrite with new value
			$registry->set('mm_config', $currentconfig);

			$query = $db->getQuery(true);
			$query
				->update('#__template_styles')
				->set('params =' . $db->quote($registry->toString()))
				->where('id =' . (int)$theme->id);

			$db->setQuery($query);
			$return = $db->execute() && $return;
		}

		die(json_encode(array(
					'status' => $return,
					'message' => JText::_($return ? 'T3_NAVIGATION_DELETE_SUCCESSFULLY' : 'T3_NAVIGATION_DELETE_FAILED')
				)
			)
		);
	}
	
	public static function save()
	{
		$input         = JFactory::getApplication()->input;
		$template      = $input->get('template');
		$mmconfig      = $input->getString('config');
		$mmkey         = $input->get('mmkey', $input->get('menutype', 'mainmenu'));
		$tplparams     = T3::getTemplateParams();

		if(get_magic_quotes_gpc() && !is_null($mmconfig)){
			$mmconfig  = stripslashes($mmconfig);
		} 
		
		$currentconfig = $tplparams instanceof JRegistry ? json_decode($tplparams->get('mm_config', ''), true) : null;

		if (!is_array($currentconfig)) {
			$currentconfig = array();
		}

		$currentconfig[$mmkey] = json_decode($mmconfig, true);
		$currentconfig         = json_encode($currentconfig);
		
		//get all other styles that have the same template
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('*')
			->from('#__template_styles')
			->where('template=' . $db->quote($template));

		$db->setQuery($query);
		$themes = $db->loadObjectList();
		$return = true;
		
		foreach($themes as $theme){
			$registry = new JRegistry;
			$registry->loadString($theme->params);

			//overwrite with new value
			$registry->set('mm_config', $currentconfig);

			$query = $db->getQuery(true);
			$query
				->update('#__template_styles')
				->set('params =' . $db->quote($registry->toString()))
				->where('id =' . (int)$theme->id);

			$db->setQuery($query);
			$return = $db->execute() && $return;
		}

		die(json_encode(array(
					'status' => $return,
					'message' => JText::_($return ? 'T3_NAVIGATION_SAVE_SUCCESSFULLY' : 'T3_NAVIGATION_SAVE_FAILED')
				)
			)
		);
	}

	/**
	 *
	 * Ge all available modules
	 */
	public static function menus()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('menutype AS value, title AS text')
			->from($db->quoteName('#__menu_types'))
			->order('title');
		$db->setQuery($query);
		$menus = $db->loadObjectList();

		$query = $db->getQuery(true)
			->select('menutype, language')
			->from($db->quoteName('#__menu'))
			->group('menutype');
		$db->setQuery($query);
		$menulangs = $db->loadAssocList('menutype');

		$query = $db->getQuery(true)
			->select('menutype, language')
			->from($db->quoteName('#__menu'))
			->where('home = 1');
		$db->setQuery($query);
		$homelangs = $db->loadAssocList('menutype');

		if(is_array($menulangs) && is_array($homelangs)){
			$menulangs = array_merge($menulangs, $homelangs);
		}

		if(is_array($menus) && is_array($menulangs)){
			foreach ($menus as $menu) {
				$menu->text = $menu->text . ' [' . $menu->value . ']';
				$menu->language = isset($menulangs[$menu->value]) ? $menulangs[$menu->value]['language'] : '*';
			}
		}
		
		return is_array($menus) ? $menus : array();
	}

	/**
	 *
	 * Ge all support access levels
	 */
	public static function access()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text');
		$query->from('#__viewlevels AS a');
		$query->group('a.id, a.title, a.ordering');
		$query->order('a.ordering ASC');
		$query->where('a.id in (1,2,3) or a.title = ' . $db->quote('Guest')); //we only support Public, Registered, Special, Guest
		
		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		
		return is_array($options) ? $options : array();
	}

	/**
	 *
	 * Ge all available modules
	 */
	public static function modules()
	{
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
		
		return is_array($modules) ? $modules : array();
	}
	
	/**
	 *
	 * Show thememagic form
	 */
	public static function megamenu()
	{
		$tplparams = T3::getTemplateParams();
		
		$url = JFactory::getURI();
		$url->delVar('t3action');
		$url->delVar('t3task');
		$referer  = $url->toString();
		$template = T3_TEMPLATE;
		$styleid  = JFactory::getApplication()->input->getCmd('id');

		$mm_type  = ($tplparams && $tplparams instanceof JRegistry) ? $tplparams->get('mm_type', '') : null;

		//Keepalive
		$config      = JFactory::getConfig();
		$lifetime    = ($config->get('lifetime') * 60000);
		$refreshTime = ($lifetime <= 60000) ? 30000 : $lifetime - 60000;
		
		// Refresh time is 1 minute less than the liftime assined in the configuration.php file.
		// The longest refresh period is one hour to prevent integer overflow.
		if ($refreshTime > 3600000 || $refreshTime <= 0) {
			$refreshTime = 3600000;
		}

		//check config
		$currentconfig = ($tplparams && $tplparams instanceof JRegistry) ? $tplparams->get('mm_config', '') : null;
		if(!$currentconfig){
			$currentconfig = '"{}"';
		}
		
		include T3_ADMIN_PATH . '/admin/megamenu/megamenu.tpl.php';
		exit;
	}
}
