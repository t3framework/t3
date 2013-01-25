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
 * @Link:         https://github.com/t3framework/ 
 *------------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$user = JFactory::getUser();
$canDo = TemplatesHelper::getActions();
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
					<label id="t3-styles-list-lbl" for="t3-styles-list" class="hasTip" title="<?php echo JText::_('T3_SELECT_STYLE_DESC'); ?>"><?php echo JText::_('T3_SELECT_STYLE_LABEL'); ?></label>
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
					<input type="text" size="35" value="<?php echo $form->getValue('client_id') == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	" class="inputbox readonly" readonly="readonly" />
				</div>
			</div>
			<div class="control-group t3-control-group">
				<div class="control-label t3-control-label">
					<?php echo $form->getLabel('home'); ?>
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
			<ul class="nav nav-tabs">
				<li<?php echo $t3lock == 'overview_params' ? ' class="active"' : ''?>><a href="#overview_params" data-toggle="tab"><?php echo JText::_('T3_OVERVIEW_LABEL');?></a></li>
				<?php
				$fieldSets = $form->getFieldsets('params');
				foreach ($fieldSets as $name => $fieldSet) :
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_TEMPLATES_'.$name.'_FIELDSET_LABEL';
				?>
					<li<?php echo $t3lock == preg_replace( '/\s+/', ' ', $name) ? ' class="active"' : ''?>><a href="#<?php echo preg_replace( '/\s+/', ' ', $name);?>" data-toggle="tab"><?php echo JText::_($label) ?></a></li>
				<?php
				endforeach;
				?>
				<?php if ($user->authorise('core.edit', 'com_menu') && ($form->getValue('client_id') == 0)):?>
					<?php if ($canDo->get('core.edit.state')) : ?>
							<li<?php echo $t3lock == 'assignment' ? ' class="active"' : ''?>><a href="#assignment_params" data-toggle="tab"><?php echo JText::_('T3_MENUS_ASSIGNMENT_LABEL');?></a></li>
					<?php endif; ?>
				<?php endif;?>
			</ul>
		</div>
		<div class="t3-admin-tabcontent tab-content clearfix">
			<div class="tab-pane tab-overview clearfix<?php echo $t3lock == 'overview_params' ? ' active' : ''?>" id="overview_params">
				<?php
				$default_overview_override = T3_TEMPLATE_PATH . '/admin/default_overview.php';
				if(file_exists($default_overview_override)) {
					include $default_overview_override;
				} else {
					include T3_ADMIN_PATH . '/admin/tpls/default_overview.php';
				}
				?>
			</div>
			<?php
			foreach ($fieldSets as $name => $fieldSet) :
				
				?>
				<div class="tab-pane<?php echo $t3lock == preg_replace( '/\s+/', ' ', $name) ? ' active' : ''?>" id="<?php echo preg_replace( '/\s+/', ' ', $name); ?>">
					<?php

					if (isset($fieldSet->description) && trim($fieldSet->description)) :
						echo '<div class="t3-admin-fieldset-desc">'.(JText::_($fieldSet->description)).'</div>';
					endif;

					foreach ($form->getFieldset($name) as $field) :
					$hide = ($field->type === 'T3Depend' && $form->getFieldAttribute($field->fieldname, 'function', '', $field->group) == '@group');
					if ($field->type == 'Text') {
						// add placeholder to Text input
						$textinput = str_replace ('/>', ' placeholder="'.$form->getFieldAttribute($field->fieldname, 'default', '', $field->group).'"/>', $field->input);
					}
					?>
					<?php if ($field->hidden || ($field->type == 'T3Depend' && !$field->label)) : ?>
						<?php echo $field->input; ?>
					<?php else : ?>
					<div class="control-group t3-control-group<?php echo $hide ? ' hide' : ''?>">
						<div class="control-label t3-control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls t3-controls">
							<?php echo $field->type=='Text'?$textinput:$field->input ?>
						</div>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
				</div>
			<?php endforeach;  ?>

			<?php if ($user->authorise('core.edit', 'com_menu') && $form->getValue('client_id') == 0):?>
				<?php if ($canDo->get('core.edit.state')) : ?>
					<div class="tab-pane clearfix<?php echo $t3lock == 'assignment' ? ' active' : ''?>" id="assignment_params">
						<?php include T3_ADMIN_PATH . '/admin/tpls/default_assignment.php'; ?>
					</div>
				<?php endif; ?>
			<?php endif;?>
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

	if (is_file(T3_ADMIN_PATH . '/admin/megamenu/megamenu.tpl.php')){
		include_once T3_ADMIN_PATH . '/admin/megamenu/megamenu.tpl.php';
	}

	if (is_file(T3_ADMIN_PATH . '/admin/layout/layout.tpl.php')){
		include_once T3_ADMIN_PATH . '/admin/layout/layout.tpl.php';
	}
?>
