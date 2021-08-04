<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

$params             = $this->item->params;
$presentation_style = $params->get('presentation_style');

$displayGroups      = $params->get('show_user_custom_fields');
$userFieldGroups    = array();
?>

<?php if (!$displayGroups || !$this->contactUser) : ?>
	<?php return; ?>
<?php endif; ?>

<?php foreach ($this->contactUser->jcfields as $field) : ?>
	<?php if (!in_array('-1', $displayGroups) && (!$field->group_id || !in_array($field->group_id, $displayGroups))) : ?>
		<?php continue; ?>
	<?php endif; ?>
	<?php if (!key_exists($field->group_title, $userFieldGroups)) : ?>
		<?php $userFieldGroups[$field->group_title] = array(); ?>
	<?php endif; ?>
	<?php $userFieldGroups[$field->group_title][] = $field; ?>
<?php endforeach; ?>

<?php foreach ($userFieldGroups as $groupTitle => $fields) : ?>
	<?php $id = JApplicationHelper::stringURLSafe($groupTitle); ?>
	
	<!-- Slider -->
	<?php if ($presentation_style == 'sliders') : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
			<h4 class="panel-title">
				<a class="accordion-toggle" data-toggle="collapse" data-parent="#slide-contact" href="#<?php echo 'display-' . $id; ?>">
				<?php echo Text::_('COM_CONTACT_USER_FIELDS');?>
				</a>
			</h4>
			</div>
			<div id="<?php echo 'display-' . $id; ?>" class="panel-collapse collapse">
				<div class="panel-body">
					<div class="contact-profile" id="user-custom-fields-<?php echo $id; ?>">
						<dl class="dl-horizontal">
						<?php foreach ($fields as $field) : ?>
							<?php if (!$field->value) : ?>
								<?php continue; ?>
							<?php endif; ?>

							<?php if ($field->params->get('showlabel')) : ?>
								<?php echo '<dt>' . Text::_($field->label) . '</dt>'; ?>
							<?php endif; ?>

							<?php echo '<dd>' . $field->value . '</dd>'; ?>
						<?php endforeach; ?>
						</dl>
					</div>

				</div>
			</div>
		</div>
	<?php endif; ?>
	<!-- // Slider -->

	<!-- Tabs -->
	<?php if ($presentation_style == 'tabs') : ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'display-profile', $groupTitle ?: Text::_('COM_CONTACT_USER_FIELDS')); ?>
			<div class="contact-profile" id="user-custom-fields-<?php echo $id; ?>">
				<dl class="dl-horizontal">
				<?php foreach ($fields as $field) : ?>
					<?php if (!$field->value) : ?>
						<?php continue; ?>
					<?php endif; ?>

					<?php if ($field->params->get('showlabel')) : ?>
						<?php echo '<dt>' . Text::_($field->label) . '</dt>'; ?>
					<?php endif; ?>

					<?php echo '<dd>' . $field->value . '</dd>'; ?>
				<?php endforeach; ?>
				</dl>
			</div>

		<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif; ?>
	<!-- // Tabs -->

	<!-- Plain -->
	<?php if ($presentation_style == 'plain') : ?>
		<?php echo '<h3>' . ($groupTitle ?: Text::_('COM_CONTACT_USER_FIELDS')) . '</h3>'; ?>
		<div class="contact-profile" id="user-custom-fields-<?php echo $id; ?>">
			<dl class="dl-horizontal">
			<?php foreach ($fields as $field) : ?>
				<?php if (!$field->value) : ?>
					<?php continue; ?>
				<?php endif; ?>

			<?php if ($field->params->get('showlabel')) : ?>
				<?php echo '<dt>' . Text::_($field->label) . '</dt>'; ?>
			<?php endif; ?>
				<?php echo '<dd>' . $field->value . '</dd>'; ?>
			<?php endforeach; ?>
			</dl>
		</div>
	<?php endif; ?>
	<!-- // Plain -->

<?php endforeach; ?>
