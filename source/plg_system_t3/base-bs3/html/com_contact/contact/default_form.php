<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');

$regex = '@class="([^"]*)"@';
$label = 'class="$1 col-sm-2 control-label"';
$input = 'class="$1 form-control"';

if (isset($this->error)) : ?>
	<div class="contact-error">
		<?php echo $this->error; ?>
	</div>
<?php endif; ?>

<div class="contact-form">
	<form id="contact-form" action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-validate form-horizontal">
		<fieldset>
			<legend><?php echo JText::_('COM_CONTACT_FORM_LABEL'); ?></legend>
			<div class="form-group">
				<?php echo preg_replace($regex, $label, $this->form->getLabel('contact_name'), 1); ?>
				<div class="col-sm-10">
					<?php echo preg_replace($regex, $input, $this->form->getInput('contact_name')); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo preg_replace($regex, $label, $this->form->getLabel('contact_email'), 1); ?>
				<div class="col-sm-10">
					<?php echo preg_replace($regex, $input, $this->form->getInput('contact_email')); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo preg_replace($regex, $label, $this->form->getLabel('contact_subject'), 1); ?>
				<div class="col-sm-10">
					<?php echo preg_replace($regex, $input, $this->form->getInput('contact_subject')); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo preg_replace($regex, $label, $this->form->getLabel('contact_message'), 1); ?>
				<div class="col-sm-10">
					<?php echo preg_replace($regex, $input, $this->form->getInput('contact_message')); ?>
				</div>
			</div>
			<?php if ($this->params->get('show_email_copy')) { ?>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<div class="checkbox">
							<?php echo $this->form->getInput('contact_email_copy'); ?>
							<?php echo $this->form->getLabel('contact_email_copy'); ?>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php //Dynamically load any additional fields from plugins. ?>
			<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
				<?php if ($fieldset->name != 'contact'):?>
					<?php $fields = $this->form->getFieldset($fieldset->name);?>
					<?php foreach ($fields as $field) : ?>
						<div class="form-group">
							<?php if ($field->hidden) : ?>
								<?php echo $field->input;?>
							<?php else:?>
								<?php echo $field->label; ?>
								<?php if (!$field->required && $field->type != "Spacer") : ?>
									<span class="optional"><?php echo JText::_('COM_CONTACT_OPTIONAL');?></span>
								<?php endif; ?>
								<?php echo $field->input;?>
							<?php endif;?>
						</div>
					<?php endforeach;?>
				<?php endif ?>
			<?php endforeach;?>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button class="btn btn-primary validate" type="submit"><?php echo JText::_('COM_CONTACT_CONTACT_SEND'); ?></button>
				</div>
				
				<input type="hidden" name="option" value="com_contact" />
				<input type="hidden" name="task" value="contact.submit" />
				<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
				<input type="hidden" name="id" value="<?php echo $this->contact->slug; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</fieldset>
	</form>
</div>
