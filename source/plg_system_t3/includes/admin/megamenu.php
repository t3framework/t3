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

class T3AdminMegamenu {
	public static function display () {
		T3::import('menu/megamenu');
		$input = JFactory::getApplication()->input;
		$menutype = $input->get ('t3menu', 'mainmenu');
		$tplparams = $input->get('tplparams', '', 'raw');
		$currentconfig = $tplparams instanceof JRegistry ? json_decode($tplparams->get('mm_config', ''), true) : null;
		$mmconfig = ($currentconfig && isset($currentconfig[$menutype])) ? $currentconfig[$menutype] : array();
		$mmconfig['editmode'] = true;
		$menu = new T3MenuMegamenu ($menutype, $mmconfig);
		$buffer = $menu->render(true);
		// replace image path
		$base   = JURI::base(true).'/';
		$protocols = '[a-zA-Z0-9]+:'; //To check for all unknown protocals (a protocol must contain at least one alpahnumeric fillowed by :
		$regex     = '#(src)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		$buffer    = preg_replace($regex, "$1=\"$base\$2\"", $buffer);
		
		//remove invisibile content	
		$buffer = preg_replace(array( '@<style[^>]*?>.*?</style>@siu', '@<script[^>]*?.*?</script>@siu'), array('', ''), $buffer);

		echo $buffer;
	}

	public static function save () {
		$input = JFactory::getApplication()->input;
		$mmconfig = $input->getString ('config');
		$menutype = $input->get ('menutype', 'mainmenu');
		$file = T3_TEMPLATE_PATH.'/etc/megamenu.ini';
		$currentconfig = json_decode(@file_get_contents($file), true);
		if (!$currentconfig) $currentconfig = array();
		$currentconfig[$menutype] = json_decode($mmconfig, true);
		JFile::write ($file, json_encode ($currentconfig));
	}
}
	