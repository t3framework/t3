<?php 
/** 
 *-------------------------------------------------------------------------
 * T3 Framework for Joomla!
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2013 JoomlArt.com, Ltd. All Rights Reserved.
 * License - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Authors:  JoomlArt, JoomlaBamboo 
 * If you want to be come co-authors of this project, please follow our 
 * guidelines at http://t3-framework.org/contribute
 * ------------------------------------------------------------------------
 */
 
$cls = array('t3-layout-pos', 'block-' . $vars['name']);
$attr = '';

if(isset($vars['data-original'])){
	$attr = ' data-original="'. $vars['data-original'] . '"';
} else {
	$cls[] = 't3-layout-uneditable'; 
}
?>
<div class="<?php echo implode(' ', $cls) ?>"<?php echo $attr ?>>
	<h3><?php echo $vars['name'] ?></h3>
</div>