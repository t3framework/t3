<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

JHtml::_('behavior.keepalive');
if(version_compare(JVERSION, '3.0', 'lt')){
	JHtml::_('behavior.tooltip');
}
JHtml::_('behavior.formvalidation');
?>
<div class="registration<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1 class="page-title"><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	</div>
<?php endif; ?>

	<form id="member-registration" action="<?php echo Route::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-validate form-horizontal">
	<?php  // Iterate through the form fieldsets and display each one. ?>
	<?php foreach ($this->form->getFieldsets() as $fieldset): ?>
		<?php $fields = $this->form->getFieldset($fieldset->name);?>
		<?php if (count($fields)):?>
			<fieldset>
			<?php // If the fieldset has a label set, display it as the legend. ?>
			<?php if (isset($fieldset->label)):
			?>
				<legend><?php echo Text::_($fieldset->label);?></legend>
			<?php endif;?>
			<?php // Iterate through the fields in the set and display them. ?>
			<?php echo $this->form->renderFieldset($fieldset->name); ?>
			</fieldset>
		<?php endif;?>
	<?php endforeach;?>
		<div class="form-group form-actions">
			<div class="col-sm-offset-3 col-sm-9">
				<button type="submit" class="btn btn-primary validate"><?php echo Text::_('JREGISTER');?></button>
				<a class="btn cancel" href="<?php echo JRoute::_('');?>" title="<?php echo Text::_('JCANCEL');?>"><?php echo Text::_('JCANCEL');?></a>
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="registration.register" />
				<?php echo JHtml::_('form.token');?>
			</div>
		</div>
	</form>
</div>
