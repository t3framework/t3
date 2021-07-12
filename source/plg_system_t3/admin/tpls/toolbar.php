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

defined('_JEXEC') or die;
jimport('joomla.language.help');
$input = JFactory::getApplication()->input;
$params  = T3::getTplParams();
if(version_compare(JVERSION, '4','ge')){
	$dropdown = 'data-bs-toggle="dropdown"';
}else{
	$dropdown = 'data-toggle="dropdown"';
}
?>
<div id="t3-admin-toolbar" class="btn-toolbar">

	<?php if($input->getCmd('view') == 'style'): ?>
	<div id="t3-admin-tb-save" class="btn-group">
		<button id="t3-admin-tb-style-save-save" class="btn btn-success"><i class="icon-save"></i>  <?php echo JText::_('T3_TOOLBAR_SAVE') ?></button>
		<button class="btn btn-success dropdown-toggle" <?php echo $dropdown;?>>
			<span class="caret"></span>&nbsp;
		</button>
		<ul class="dropdown-menu">
			<li id="t3-admin-tb-style-save-close"><a href="#"><?php echo JText::_('T3_TOOLBAR_SAVECLOSE') ?></a></li>
			<li id="t3-admin-tb-style-save-clone"><a href="#"><?php echo JText::_('T3_TOOLBAR_SAVE_AS_CLONE') ?></a></li>
		</ul>
	</div>
	<?php endif; ?>

	<div id="t3-admin-tb-recompile" class="btn-group">
		<button id="t3-admin-tb-compile-all" class="btn hasTip" title="<?php echo JText::_('T3_TOOLBAR_COMPILE_LESS_CSS') ?>::<?php echo JText::_('T3_TOOLBAR_COMPILE_LESS_CSS_DESC') ?>"><i class="icon-code"></i>  <i class="icon-loading"></i>  <?php echo JText::_('T3_TOOLBAR_COMPILE_LESS_CSS') ?></button>
		<?php if($input->getCmd('view') == 'style') : ?>
		<button class="btn dropdown-toggle" <?php echo $dropdown;?>>
			<span class="caret"></span>&nbsp;
		</button>
		<ul class="dropdown-menu">
			<li id="t3-admin-tb-compile-this" data-default="<?php echo JText::_('JDEFAULT') ?>" data-msg="<?php echo JText::_('T3_TOOLBAR_COMPILE_THIS') ?>"><a href="#"><?php echo JText::sprintf('T3_TOOLBAR_COMPILE_THIS', $params->get('theme', JText::_('JDEFAULT'))) ?></a></li>
		</ul>
		<?php endif ?>
	</div>

	<div id="t3-admin-tb-themer" 
		class="btn-group">
		<button 
			data-title="<?php echo JText::_('T3_TM_THEME_MAGIC') ?>"
			data-content="<?php echo JText::_('T3_MSG_ENABLE_THEMEMAGIC') ?>"
			class="btn hasTip" 
			title="<?php echo JText::_('T3_TOOLBAR_THEMER') ?>::<?php echo JText::_('T3_TOOLBAR_THEMER_DESC') ?>">
			
			<i class="icon-magic"></i>  <?php echo JText::_('T3_TOOLBAR_THEMER') ?>
		</button>
	</div>

	<div id="t3-admin-tb-megamenu" 
		class="btn-group" >
		<button 
			data-title="<?php echo JText::_('T3_NAVIGATION_MM_TITLE') ?>"
			data-content="<?php echo JText::_('T3_MSG_MEGAMENU_NOT_USED') ?>"
			class="btn hasTip" 
			title="<?php echo JText::_('T3_TOOLBAR_MEGAMENU') ?>::<?php echo JText::_('T3_TOOLBAR_MEGAMENU_DESC') ?>">
				<i class="icon-sitemap"></i>  <?php echo JText::_('T3_TOOLBAR_MEGAMENU') ?>
		</button>
	</div>
	
	<?php if(version_compare(JVERSION, '3.0', 'ge') && $input->getCmd('view') == 'template'): ?>
	<div id="t3-admin-tb-copy" class="btn-group <?php echo $input->getCmd('view') ?>" data-toggle="modal" data-target="#collapseModal">
		<button class="btn"><i class="icon-copy"></i>  <?php echo JText::_('T3_TOOLBAR_COPY') ?></button>
	</div>
	<?php endif; ?>

	<div id="t3-admin-tb-close" class="btn-group <?php echo $input->getCmd('view') ?>">
		<button class="btn"><i class="icon-remove"></i>  <?php echo JText::_('T3_TOOLBAR_CLOSE') ?></button>
	</div>
	<div id="t3-admin-tb-help" class="btn-group <?php echo $input->getCmd('view') ?>">
		<button class="btn"><i class="icon-question-sign"></i>  <?php echo JText::_('T3_TOOLBAR_HELP') ?></button>
	</div>

</div>