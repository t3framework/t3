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

if (version_compare(JVERSION, '4', 'ge')) {
	/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
	$wa = $this->document->getWebAssetManager();
	$wa->useScript('keepalive')
		->useScript('form.validate');
	if (version_compare(JVERSION, '4.2', 'lt')) {
		HTMLHelper::_('script', 'com_users/two-factor-switcher.min.js', array('version' => 'auto', 'relative' => true));
	}
}
else {
	HTMLHelper::_('behavior.keepalive');
	HTMLHelper::_('behavior.formvalidator');
	HTMLHelper::_('formbehavior.chosen', 'select');
}
// Load user_profile plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_user_profile', JPATH_ADMINISTRATOR);

?>
<div class="profile-edit<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<div class="page-header">
			<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
	<?php endif; ?>

	<script type="text/javascript">
		Joomla.twoFactorMethodChange = function(e)
		{
			var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

			jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function(i, el) {
				if (el.id != selectedPane)
				{
					jQuery('#' + el.id).hide(0);
				}
				else
				{
					jQuery('#' + el.id).show(0);
				}
			});
		}
	</script>

	<form id="member-profile" action="<?php echo JRoute::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
	<?php // Iterate through the form fieldsets and display each one. ?>
	<?php foreach ($this->form->getFieldsets() as $group => $fieldset) : ?>
		<?php $fields = $this->form->getFieldset($group); ?>
		<?php if (count($fields)) : ?>
		<fieldset>
			<?php // If the fieldset has a label set, display it as the legend. ?>
			<?php if (isset($fieldset->label)) : ?>
			<legend>
				<?php echo Text::_($fieldset->label); ?>
			</legend>
			<?php endif; ?>
			<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
				<?php echo '<p>' . $this->escape(Text::_($fieldset->description)) . '</p>'; ?>
			<?php endif; ?>
			<?php // Iterate through the fields in the set and display them. ?>
			<?php foreach ($fields as $field) : ?>
			<?php // If the field is hidden, just display the input. ?>
				<?php if ($field->hidden) : ?>
					<?php echo $field->input; ?>
				<?php else : ?>
					<div class="form-group">
						<div class="col-sm-3 control-label">
							<?php echo $field->label; ?>
							<?php if (!$field->required && $field->type !== 'Spacer') : ?>
								<?php if(version_compare(JVERSION, '4', 'lt')) : ?>
								<span class="optional"><?php echo Text::_('COM_USERS_OPTIONAL'); ?></span>
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<div class="col-sm-9 controls">
							<?php if ($field->fieldname === 'password1') : ?>
								<?php // Disables autocomplete ?> <input type="password" style="display:none">
							<?php endif; ?>
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</fieldset>
		<?php endif; ?>
	<?php endforeach; ?>
	<?php if(version_compare(JVERSION,'4.2','ge')):?>
		<?php if ($this->mfaConfigurationUI) : ?>
				<fieldset class="com-users-profile__multifactor">
						<legend><?php echo Text::_('COM_USERS_PROFILE_MULTIFACTOR_AUTH'); ?></legend>
						<?php echo $this->mfaConfigurationUI ?>
				</fieldset>
		<?php endif; ?>
	<?php else:?>
		<?php if (is_array($this->twofactormethods) && count($this->twofactormethods) > 1): ?>
			<fieldset>
				<legend><?php echo Text::_('COM_USERS_PROFILE_TWO_FACTOR_AUTH'); ?></legend>

				<div class="form-group">
					<div class="col-sm-3 control-label">
						<label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="hasTooltip"
								title="<?php echo '<strong>' . Text::_('COM_USERS_PROFILE_TWOFACTOR_LABEL') . '</strong><br />' . Text::_('COM_USERS_PROFILE_TWOFACTOR_DESC'); ?>">
							<?php echo Text::_('COM_USERS_PROFILE_TWOFACTOR_LABEL'); ?>
						</label>
					</div>
					<div class="col-sm-9 controls">
						<?php echo JHtml::_('select.genericlist', $this->twofactormethods, 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $this->otpConfig->method, 'jform_twofactor_method', false); ?>
					</div>
				</div>
				<div id="com_users_twofactor_forms_container">
					<?php foreach ($this->twofactorform as $form) : ?>
					<?php $style = $form['method'] == $this->otpConfig->method ? 'display: block' : 'display: none'; ?>
					<div id="com_users_twofactor_<?php echo $form['method']; ?>" style="<?php echo $style; ?>">
						<?php echo $form['form']; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</fieldset>

			<fieldset>
				<legend>
					<?php echo Text::_('COM_USERS_PROFILE_OTEPS'); ?>
				</legend>
				<div class="alert alert-info">
					<?php echo Text::_('COM_USERS_PROFILE_OTEPS_DESC'); ?>
				</div>
				<?php if (empty($this->otpConfig->otep)) : ?>
				<div class="alert alert-warning">
					<?php echo Text::_('COM_USERS_PROFILE_OTEPS_WAIT_DESC'); ?>
				</div>
				<?php else : ?>
				<?php foreach ($this->otpConfig->otep as $otep) : ?>
				<span class="span3">
					<?php echo substr($otep, 0, 4); ?>-<?php echo substr($otep, 4, 4); ?>-<?php echo substr($otep, 8, 4); ?>-<?php echo substr($otep, 12, 4); ?>
				</span>
				<?php endforeach; ?>
				<div class="clearfix"></div>
				<?php endif; ?>
			</fieldset>
		<?php endif; ?>
	<?php endif; ?>

		<div class="form-group form-actions">
			<div class="col-sm-offset-3 col-sm-9">
				<button type="submit" class="btn btn-primary validate"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
				<a class="btn" href="<?php echo JRoute::_('index.php?option=com_users&view=profile'); ?>" title="<?php echo Text::_('JCANCEL'); ?>"><?php echo Text::_('JCANCEL'); ?></a>
				<input type="hidden" name="option" value="com_users" />
				<input type="hidden" name="task" value="profile.save" />
			</div>
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
