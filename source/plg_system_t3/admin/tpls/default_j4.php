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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$user = JFactory::getUser();
$canDo = method_exists('TemplatesHelper', 'getActions') ? TemplatesHelper::getActions() : JHelperContent::getActions('com_templates');
$iswritable = is_writable('t3test.txt');
?>
<?php if($iswritable): ?>
<div id="t3-admin-writable-message" class="alert warning">
	<button type="button" class="close" data-dismiss="alert">×</button>
	<strong><?php echo JText::_('T3_MSG_WARNING'); ?></strong> <?php echo JText::_('T3_MSG_FILE_NOT_WRITABLE'); ?>
</div>
<?php endif;?>
<div class="t3-admin-form clearfix">
<form action="<?php echo JRoute::_('index.php?option=com_templates&layout=edit&id='.$input->getInt('id')); ?>" method="post" name="adminForm" id="style-form" class="form-validate form-horizontal">
	<div class="t3-admin-header clearfix">
		<div class="controls-row">
			<div class="control-group t3-control-group">
				<div class="control-label t3-control-label">
					<label id="t3-styles-list-lbl" for="t3-styles-list" class="hasTooltip" title="<?php echo JText::_('T3_SELECT_STYLE_DESC'); ?>"><?php echo JText::_('T3_SELECT_STYLE_LABEL'); ?></label>
				</div>
				<div class="controls t3-controls">
					<?php echo JHTML::_('select.genericlist', $styles, 't3-styles-list', 'autocomplete="off"', 'id', 'title', $input->get('id')); ?>
				</div>
			</div>
			<div class="control-group t3-control-group">
				<div class="control-label t3-control-label">
					<?php echo $form->getLabel('title'); ?>
				</div>
				<div class="controls t3-controls">
					<?php echo $form->getInput('title'); ?>
				</div>
			</div>
			<div class="control-group t3-control-group hide">
				<div class="control-label t3-control-label">
					<?php echo $form->getLabel('template'); ?>
				</div>
				<div class="controls t3-controls">
					<?php echo $form->getInput('template'); ?>
				</div>
			</div>
			<div class="control-group t3-control-group hide">
				<div class="control-label t3-control-label">
					<?php echo $form->getLabel('client_id'); ?>
				</div>
				<div class="controls t3-controls">
					<?php echo $form->getInput('client_id'); ?>
					<input type="text" size="35" value="<?php echo $form->getValue('client_id') == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	" class="input readonly" readonly="readonly" />
				</div>
			</div>
			<div class="control-group t3-control-group">
				<div class="control-label t3-control-label">
					<?php echo str_replace('<label', '<label data-placement="bottom" ', $form->getLabel('home')); ?>
				</div>
				<div class="controls t3-controls">
					<?php echo $form->getInput('home'); ?>
				</div>
			</div>
		</div>
	</div>
	<fieldset>
		<div class="t3-admin clearfix">
			<div class="t3-admin-nav">
				<?php echo HTMLHelper::_('uitab.startTabSet', 't3-admin-tabs', array('startOffset' => 0)); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 't3-admin-tabs', 'overview_params', Text::_('T3_OVERVIEW_LABEL')) ?>
					<?php
						$default_overview_override = T3_TEMPLATE_PATH . '/admin/default_overview.php';
						if(file_exists($default_overview_override)) {
							include $default_overview_override;
						} else {
							include T3_ADMIN_PATH . '/admin/tpls/default_overview.php';
						}
					?>
				<?php echo HTMLHelper::_('uitab.endTab') ?>
			<?php
			$fieldSets = $form->getFieldsets('params');
			foreach ($fieldSets as $name => $fieldSet) : ?>
				<?php echo HTMLHelper::_('uitab.addTab', 't3-admin-tabs', $name, Text::_("T3_".strtoupper(str_replace("_params", "", $name))."_LABEL")) ?>
					<?php
					if (isset($fieldSet->description) && trim($fieldSet->description)) : 
						echo '<div class="t3-admin-fieldset-desc">'.(JText::_($fieldSet->description)).'</div>';
					endif;

					foreach ($form->getFieldset($name) as $field) :
						$hide = ($field->type === 'T3Depend' && $form->getFieldAttribute($field->fieldname, 'function', '', $field->group) == '@group');
						$fieldinput = $field->input;

						// add placeholder to Text input
						if ($field->type == 'Text') {
							$placeholder = $form->getFieldAttribute($field->fieldname, 'placeholder', '', $field->group);
							if(empty($placeholder)){
								$placeholder = $form->getFieldAttribute($field->fieldname, 'default', '', $field->group);
							} else {
								$placeholder = JText::_($placeholder);
							}

							if(!empty($placeholder)){
								$fieldinput = str_replace ('/>', ' placeholder="' . $placeholder . '"/>', $fieldinput);
							}
						}

						$global = $form->getFieldAttribute($field->fieldname, 'global', 0, $field->group);
					?>
					<?php if ($field->hidden || ($field->type == 'T3Depend' && !$field->label)) : ?>
						<?php echo $fieldinput; ?>
					<?php else : ?>
					<div class="control-group t3-control-group<?php echo $hide ? ' hide' : '' ?>">
						<div class="control-label t3-control-label<?php echo $global ? ' t3-admin-global' : '' ?>">
							<?php echo $field->label; ?>
						</div>
						<div class="controls t3-controls">
							<?php echo $fieldinput ?>
						</div>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php echo HTMLHelper::_('uitab.endTab') ?>
			<?php endforeach;  ?>
			<?php if ($user->authorise('core.edit', 'com_menu') && $form->getValue('client_id') == 0):?>
			<?php echo HTMLHelper::_('uitab.addTab', 't3-admin-tabs', 'assignment_params', Text::_('T3_MENUS_ASSIGNMENT_LABEL')) ?>
				<?php if ($canDo->get('core.edit.state')) : ?>
					<?php include T3_ADMIN_PATH . '/admin/tpls/default_assignment.php'; ?>
				<?php endif; ?>
			<?php echo HTMLHelper::_('uitab.endTab') ?>
			<?php endif;?>
			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

			</div>
		</div>

	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>

<?php
	if (is_file(T3_ADMIN_PATH . '/admin/tour/tour.tpl.php')){
		include_once T3_ADMIN_PATH . '/admin/tour/tour.tpl.php';
	}

	//if (is_file(T3_ADMIN_PATH . '/admin/megamenu/megamenu.tpl.php')){
	//	include_once T3_ADMIN_PATH . '/admin/megamenu/megamenu.tpl.php';
	//}

	if (is_file(T3_ADMIN_PATH . '/admin/layout/layout.tpl.php')){
		include_once T3_ADMIN_PATH . '/admin/layout/layout.tpl.php';
	}
?>
