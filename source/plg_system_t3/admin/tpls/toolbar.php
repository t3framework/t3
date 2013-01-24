<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<div id="t3-toolbar" class="btn-toolbar">

	<?php
	if($input->getCmd('view') == 'style'):
	?>
  <div id="t3-toolbar-save" class="btn-group">
    <button id="t3-toolbar-style-save-save" class="btn btn-success"><i class="icon-save"></i>Save</button>
    <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
      <span class="caret"></span>&nbsp;
    </button>
    <ul class="dropdown-menu">
      <li id="t3-toolbar-style-save-close"><a href="#"><?php echo JText::_('T3_TOOLBAR_SAVECLOSE') ?></a></li>
      <li id="t3-toolbar-style-save-clone"><a href="#"><?php echo JText::_('T3_TOOLBAR_SAVE_AS_CLONE') ?></a></li>
    </ul>
  </div>
  <?php 
  endif;
  ?>

	<div id="t3-toolbar-recompile" class="btn-group">
		<button class="btn"><i class="icon-check"></i><i class="icon-loading"></i><?php echo JText::_('T3_TOOLBAR_COMPILE_LESS_CSS') ?></button>
	</div>

	<div id="t3-toolbar-themer" class="btn-group">
		<button class="btn"><i class="icon-magic"></i><?php echo JText::_('T3_TOOLBAR_THEMER') ?></button>
	</div>

	<div id="t3-toolbar-close" class="btn-group <?php echo $input->getCmd('view') ?>">
		<button class="btn"><i class="icon-remove"></i><?php echo JText::_('T3_TOOLBAR_CLOSE') ?></button>
	</div>
	<div id="t3-toolbar-help" class="btn-group <?php echo $input->getCmd('view') ?>">
		<button class="btn"><i class="icon-question-sign"></i><?php echo JText::_('T3_TOOLBAR_HELP') ?></button>
	</div>

</div>