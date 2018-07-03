<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.getElementById('mailtoForm');

		// do field validation
		if (form.mailto.value == "" || form.from.value == "")
		{
			alert('<?php echo JText::_('COM_MAILTO_EMAIL_ERR_NOINFO'); ?>');
			return false;
		}
		form.submit();
	}
</script>
<?php
$data	= $this->get('data');
?>

<div id="mailto-window">

	<h2><?php echo JText::_('COM_MAILTO_EMAIL_TO_A_FRIEND'); ?> <a class="mailto-close close" href="javascript: void window.close()" title="<?php echo JText::_('COM_MAILTO_CLOSE_WINDOW'); ?>">&times;</a></h2>

	<form action="<?php echo JUri::base() ?>index.php" id="mailtoForm" method="post" class="form-horizontal">

		<div class="form-group">
			<label for="mailto_field" class="col-sm-2 control-label"><?php echo JText::_('COM_MAILTO_EMAIL_TO'); ?></label>
			<div class="col-sm-10">
				<input type="text" id="mailto_field" name="mailto" class="form-control" size="25" value="<?php echo $this->escape($data->mailto); ?>"/>
			</div>
		</div>
		<div class="form-group">
			<label for="sender_field" class="col-sm-2 control-label">
			<?php echo JText::_('COM_MAILTO_SENDER'); ?></label>
			<div class="col-sm-10">
				<input type="text" id="sender_field" name="sender" class="form-control" value="<?php echo $this->escape($data->sender); ?>" size="25" />
			</div>
		</div>
		<div class="form-group">
			<label for="from_field" class="col-sm-2 control-label">
			<?php echo JText::_('COM_MAILTO_YOUR_EMAIL'); ?></label>
			<div class="col-sm-10">
				<input type="text" id="from_field" name="from" class="form-control" value="<?php echo $this->escape($data->from); ?>" size="25" />
			</div>
		</div>
		<div class="form-group">
			<label for="subject_field" class="col-sm-2 control-label">
			<?php echo JText::_('COM_MAILTO_SUBJECT'); ?></label>
			<div class="col-sm-10">
			<input type="text" id="subject_field" name="subject" class="form-control" value="<?php echo $this->escape($data->subject); ?>" size="25" /></div>
		</div>
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<button type="submit" class="btn btn-primary" onclick="return Joomla.submitbutton('send');">
					<?php echo JText::_('COM_MAILTO_SEND'); ?>
				</button>
				<button type="button" class="btn btn-default" onclick="window.close();return false;">
					<?php echo JText::_('COM_MAILTO_CANCEL'); ?>
				</button>
		</div>
			</div>

		<input type="hidden" name="layout" value="<?php echo htmlspecialchars($this->getLayout(), ENT_COMPAT, 'UTF-8'); ?>" />
		<input type="hidden" name="option" value="com_mailto" />
		<input type="hidden" name="task" value="send" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="link" value="<?php echo $this->link; ?>" />
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
