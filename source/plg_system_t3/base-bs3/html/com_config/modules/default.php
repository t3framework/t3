<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
if(version_compare(JVERSION, '4', 'ge')){
	HTMLHelper::_('behavior.combobox');

	/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
	$wa = $this->document->getWebAssetManager();
	$wa->useScript('keepalive')
		->useScript('form.validate')
		->useScript('com_config.modules');

	$hasContent  = false;
	$moduleXml   = JPATH_SITE . '/modules/' . $this->item['module'] . '/' . $this->item['module'] . '.xml';

	if (File::exists($moduleXml))
	{
		$xml = simplexml_load_file($moduleXml);

		if (isset($xml->customContent))
		{
			$hasContent = true;
		}
	}

	// If multi-language site, make language read-only
	if (Multilanguage::isEnabled())
	{
		$this->form->setFieldAttribute('language', 'readonly', 'true');
	}
}else{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.formvalidator');
	JHtml::_('behavior.keepalive');
	JHtml::_('behavior.framework', true);
	JHtml::_('behavior.combobox');
	JHtml::_('formbehavior.chosen', 'select');

	$hasContent = empty($this->item['module']) || $this->item['module'] === 'custom' || $this->item['module'] === 'mod_custom';

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
		}
	");
}
?>

<form
	action="<?php echo JRoute::_('index.php?option=com_config'); ?>"
	method="post" name="adminForm" id="modules-form"
	class="form-validate">

	<div class="row-fluid">

		<!-- Begin Content -->
		<div class="span12">
			<?php if(version_compare(JVERSION, '4.0', 'ge')): ?>
				<div class="mb-2">
			<button type="button" class="btn btn-primary" data-submit-task="modules.apply">
				<span class="icon-check" aria-hidden="true"></span>
				<?php echo Text::_('JAPPLY'); ?>
			</button>
			<button type="button" class="btn btn-primary" data-submit-task="modules.save">
				<span class="icon-check" aria-hidden="true"></span>
				<?php echo Text::_('JSAVE'); ?>
			</button>
			<button type="button" class="btn btn-danger" data-submit-task="modules.cancel">
				<span class="icon-times" aria-hidden="true"></span>
				<?php echo Text::_('JCANCEL'); ?>
			</button>
			</div>
			<?php else: ?>
			<div class="btn-toolbar" role="toolbar" aria-label="<?php echo Text::_('JTOOLBAR'); ?>">
				<div class="btn-group">
					<button type="button" class="btn btn-default btn-primary"
						onclick="Joomla.submitbutton('config.save.modules.apply')">
						<span class="icon-apply" aria-hidden="true"></span>
						<?php echo Text::_('JAPPLY'); ?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-default"
						onclick="Joomla.submitbutton('config.save.modules.save')">
						<span class="icon-save" aria-hidden="true"></span>
						<?php echo Text::_('JSAVE'); ?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-default"
						onclick="Joomla.submitbutton('config.cancel.modules')">
						<span class="icon-cancel" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</button>
				</div>
			</div>
			<?php endif; ?>
			<hr class="hr-condensed" />
			
			<legend><?php echo Text::_('COM_CONFIG_MODULES_SETTINGS_TITLE'); ?></legend>

			<div>
				<?php echo Text::_('COM_CONFIG_MODULES_MODULE_NAME'); ?>
				<span class="label label-default"><?php echo $this->item['title']; ?></span>
				&nbsp;&nbsp;
				<?php echo Text::_('COM_CONFIG_MODULES_MODULE_TYPE'); ?>
				<span class="label label-default"><?php echo $this->item['module']; ?></span>
			</div>

			<br />

			<div class="row-fluid">
				<div class="span12">
					<fieldset class="form-horizontal">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('showtitle'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('showtitle'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('position'); ?>
							</div>
							<div class="controls">
								<?php echo $this->loadTemplate('positions'); ?>
							</div>
						</div>

						<hr />

						<?php
						if (JFactory::getUser()->authorise('core.edit.state', 'com_modules.module.' . $this->item['id'])) : ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('published'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('published'); ?>
							</div>
						</div>
						<?php endif ?>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('publish_up'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('publish_up'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('publish_down'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('publish_down'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('access'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('access'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('ordering'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('ordering'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('language'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('language'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('note'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('note'); ?>
							</div>
						</div>

						<hr />

						<div id="options">
							<?php echo $this->loadTemplate('options'); ?>
						</div>

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