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

defined('_JEXEC') or die;
?>

<div id="t3-admin-toolbar" class="btn-toolbar">

	<?php
	if($input->getCmd('view') == 'style'):
	?>
  <div id="t3-admin-tb-save" class="btn-group">
    <button id="t3-admin-tb-style-save-save" class="btn btn-success"><i class="icon-save"></i>Save</button>
    <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
      <span class="caret"></span>&nbsp;
    </button>
    <ul class="dropdown-menu">
      <li id="t3-admin-tb-style-save-close"><a href="#"><?php echo JText::_('T3_TOOLBAR_SAVECLOSE') ?></a></li>
      <li id="t3-admin-tb-style-save-clone"><a href="#"><?php echo JText::_('T3_TOOLBAR_SAVE_AS_CLONE') ?></a></li>
    </ul>
  </div>
  <?php 
  endif;
  ?>

	<div id="t3-admin-tb-recompile" class="btn-group">
		<button class="btn"><i class="icon-check"></i><i class="icon-loading"></i><?php echo JText::_('T3_TOOLBAR_COMPILE_LESS_CSS') ?></button>
	</div>

	<div id="t3-admin-tb-themer" class="btn-group">
		<button class="btn"><i class="icon-magic"></i><?php echo JText::_('T3_TOOLBAR_THEMER') ?></button>
	</div>

	<div id="t3-admin-tb-close" class="btn-group <?php echo $input->getCmd('view') ?>">
		<button class="btn"><i class="icon-remove"></i><?php echo JText::_('T3_TOOLBAR_CLOSE') ?></button>
	</div>
	<div id="t3-admin-tb-help" class="btn-group <?php echo $input->getCmd('view') ?>">
		<button class="btn"><i class="icon-question-sign"></i><?php echo JText::_('T3_TOOLBAR_HELP') ?></button>
	</div>

</div>