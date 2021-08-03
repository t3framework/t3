<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');

?>
<div id="mailto-window">
	<h2>
		<?php echo Text::_('COM_MAILTO_EMAIL_TO_A_FRIEND'); ?>
		
		<a class="mailto-close" href="javascript: void window.close()" title="<?php echo Text::_('COM_MAILTO_CLOSE_WINDOW'); ?>">
			<span class="fa fa-close"></span>
		</a>
	</h2>

	<form id="mailtoForm" action="<?php echo JRoute::_('index.php?option=com_mailto&task=send'); ?>" method="post" class="form-validate form-horizontal">
		<fieldset>
			<?php foreach ($this->form->getFieldset('') as $field) : ?>
				<?php if (!$field->hidden) : ?>
					<?php echo $field->renderField(); ?>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary validate">
						<?php echo Text::_('COM_MAILTO_SEND'); ?>
					</button>
					<button type="button" class="btn btn-default button" onclick="window.close();return false;">
						<?php echo Text::_('COM_MAILTO_CANCEL'); ?>
					</button>
				</div>
			</div>
		</fieldset>
		<input type="hidden" name="layout" value="<?php echo htmlspecialchars($this->getLayout(), ENT_COMPAT, 'UTF-8'); ?>" />
		<input type="hidden" name="option" value="com_mailto" />
		<input type="hidden" name="task" value="send" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="link" value="<?php echo $this->link; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
