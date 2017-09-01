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
 
$cls = array('t3-admin-layout-pos', 'block-' . $vars['name']);
$attr = '';

if(isset($vars['data-original'])) {
	$attr = ' data-original="'. $vars['data-original'] . '"';
	if (!empty($vars['data-optgroup'])) {
		$attr .= ' data-optgroup="'. $vars['data-optgroup'] . '"';
	}
} else {
	$cls[] = 't3-admin-layout-uneditable'; 
}
?>
<div class="<?php echo implode(' ', $cls) ?>"<?php echo $attr ?>>
	<h3><?php echo $vars['name'] ?></h3>
</div>