<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.combobox');
JHtml::_('formbehavior.chosen', 'select');

$hasContent = empty($this->item['module']) || $this->item['module'] == 'custom' || $this->item['module'] == 'mod_custom';

// If multi-language site, make language read-only
if (JLanguageMultilang::isEnabled())
{
	$this->form->setFieldAttribute('language', 'readonly', 'true');
}

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'config.cancel.modules' || document.formvalidator.isValid(document.getElementById('modules-form')))
		{
			Joomla.submitform(task, document.getElementById('modules-form'));
		}
	};
");

?>

<form
	action="<?php echo JRoute::_('index.php?option=com_config'); ?>"
	method="post" name="adminForm" id="modules-form"
	class="form-validate">

	<div class="row-fluid">

		<!-- Begin Content -->
		<div class="span12">

			<div class="btn-toolbar">
				<div class="btn-group">
					<button type="button" class="btn btn-default btn-primary"
						onclick="Joomla.submitbutton('config.save.modules.apply')">
						<i class="icon-apply"></i>
						<?php echo JText::_('JAPPLY') ?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-default"
						onclick="Joomla.submitbutton('config.save.modules.save')">
						<i class="icon-save"></i>
						<?php echo JText::_('JSAVE') ?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-default"
						onclick="Joomla.submitbutton('config.cancel.modules')">
						<i class="icon-cancel"></i>
						<?php echo JText::_('JCANCEL') ?>
					</button>
				</div>
			</div>

			<hr class="hr-condensed" />

			<div>
				<?php echo JText::_('COM_CONFIG_MODULES_MODULE_NAME') ?>
				<span class="label label-default"><?php echo $this->item['title'] ?></span>
				&nbsp;&nbsp;
				<?php echo JText::_('COM_CONFIG_MODULES_MODULE_TYPE') ?>
				<span class="label label-default"><?php echo $this->item['module'] ?></span>
			</div>

			<br />

			<div class="row-fluid">
				<div class="span12">
					<fieldset class="form-horizontal">
						<?php $activepane = JFactory::getApplication()->input->cookie->get('configModulePane');
							if (!$activepane) $activepane = 'collapse0'; ?>
						<?php echo JHtml::_('bootstrap.startAccordion', 'collapseTypes', array('active'=>$activepane)); ?>

							<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', JText::_('COM_CONFIG_MODULES_SETTINGS_TITLE'), 'collapse0'); ?>
							<?php echo $this->loadTemplate('details'); ?>
							<?php echo JHtml::_('bootstrap.endSlide'); ?>

							<?php echo $this->loadTemplate('options'); ?>

						<?php echo JHtml::_('bootstrap.endAccordion'); ?>

						<?php if ($hasContent): ?>
							<div class="tab-pane" id="custom">
								<?php echo $this->form->getInput('content'); ?>
							</div>
						<?php endif; ?>
					</fieldset>
				</div>

				<input type="hidden" name="id" value="<?php echo $this->item['id'];?>" />
				<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->get('return', null, 'base64');?>" />
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>

			</div>

		</div>
		<!-- End Content -->
	</div>

</form>

<?php
// fix collapse
JFactory::getDocument()->addScriptDeclaration("
jQuery(function($){
	$('#collapseTypes').on('show.bs.collapse', function () {
		$('#collapseTypes .in').collapse('hide');
	});

	$('#collapseTypes').on('shown.bs.collapse', function () {
		var active = $('#collapseTypes .in').attr('id');
		console.log(active);
		document.cookie = 'configModulePane=' + active;
	});

});
");
?>