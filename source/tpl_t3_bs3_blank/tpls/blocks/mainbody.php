<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (is_array($this->getParam('skip_component_content')) && 
  in_array(JFactory::getApplication()->input->getInt('Itemid'), $this->getParam('skip_component_content'))) 
return;
?>

<?php

/**
 * Mainbody 3 columns, content in center: sidebar1 - content - sidebar2
 */

// positions configuration
$sidebar1 = 'sidebar-1';
$sidebar2 = 'sidebar-2';

$sidebar1 = $this->countModules($sidebar1) ? $sidebar1 : false;
$sidebar2 = $this->countModules($sidebar2) ? $sidebar2 : false;

// detect layout
if ($sidebar1 && $sidebar2) {
	$this->loadBlock('mainbody/two-sidebar', array('sidebar1' => $sidebar1, 'sidebar2' => $sidebar2));
} elseif ($sidebar1) {
	$this->loadBlock('mainbody/one-sidebar-left', array('sidebar' => $sidebar1));
} elseif ($sidebar2) {
	$this->loadBlock('mainbody/one-sidebar-right', array('sidebar' => $sidebar2));
} else {
	$this->loadBlock('mainbody/no-sidebar');
}
