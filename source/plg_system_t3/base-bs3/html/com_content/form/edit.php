<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');

if(version_compare(JVERSION, '3.0', 'ge')){
	JHtml::_('formbehavior.chosen', 'select');
	JHtml::_('behavior.modal', 'a.modal_jform_contenthistory');
}

// Create shortcut to parameters.
$params = $this->state->get('params');
//$images = json_decode($this->item->images);
//$urls = json_decode($this->item->urls);

// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
if (!$editoroptions)
{
	$params->show_urls_images_frontend = '0';
}

//T3: customize
$fieldsets   = $this->form->getFieldsets('attribs');
$extrafields = array();

foreach ($fieldsets as $fieldset) {
	if(isset($fieldset->group) && $fieldset->group == 'extrafields'){
		$extrafields[] = $fieldset;
	}
}

if(count($extrafields)){
	if(is_string($this->item->attribs)){
		$this->item->attribs = json_decode($this->item->attribs);
	}
	$tmp = new stdClass;
	$tmp->attribs = $this->item->attribs;
	$this->form->bind($tmp);
}
//T3: customize
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'article.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			<?php echo $this->form->getField('articletext')->save(); ?>
			Joomla.submitform(task);
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_content&a_id='.(int) $this->item->id); ?>" role="form" method="post" name="adminForm" id="adminForm" class="form-validate">
		<fieldset>

			<ul class="nav nav-tabs">
				<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('JEDITOR') ?></a></li>
				<?php if(count($extrafields)) : ?>
				<li><a href="#extrafields" data-toggle="tab"><?php echo JText::_('T3_EXTRA_FIELDS_GROUP_LABEL') ?></a></li>
				<?php endif; ?>
				<?php if ($params->get('show_urls_images_frontend') ) : ?>
				<li><a href="#images" data-toggle="tab"><?php echo JText::_('COM_CONTENT_IMAGES_AND_URLS') ?></a></li>
				<?php endif; ?>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_CONTENT_PUBLISHING') ?></a></li>
				<li><a href="#language" data-toggle="tab"><?php echo JText::_('JFIELD_LANGUAGE_LABEL') ?></a></li>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_CONTENT_METADATA') ?></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="editor">

					<div class="form-group">
						<?php echo $this->form->getLabel('title'); ?>
						<?php echo $this->form->getInput('title'); ?>
					</div>

					<?php if (is_null($this->item->id)) : ?>
					<div class="form-group">
						<?php echo $this->form->getLabel('alias'); ?>
						<?php echo $this->form->getInput('alias'); ?>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<?php echo $this->form->getInput('articletext'); ?>
					</div>
				</div>

				<?php if(count($extrafields)) : ?>
				<div class="tab-pane" id="extrafields">
					<?php foreach ($extrafields as $extraset) : ?>
						<?php foreach ($this->form->getFieldset($extraset->name) as $field) : ?>
							<div class="form-group">
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endforeach ?>
					<?php endforeach ?>
				</div>
				<?php endif; ?>

				<?php if ($params->get('show_urls_images_frontend')): ?>
				<div class="tab-pane" id="images">

					<div class="form-group">
						<?php echo $this->form->getLabel('image_intro', 'images'); ?>
						<?php echo $this->form->getInput('image_intro', 'images'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('image_intro_alt', 'images'); ?>
						<?php echo $this->form->getInput('image_intro_alt', 'images'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('image_intro_caption', 'images'); ?>
						<?php echo $this->form->getInput('image_intro_caption', 'images'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('float_intro', 'images'); ?>
						<?php echo $this->form->getInput('float_intro', 'images'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('image_fulltext', 'images'); ?>
						<?php echo $this->form->getInput('image_fulltext', 'images'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('image_fulltext_alt', 'images'); ?>
						<?php echo $this->form->getInput('image_fulltext_alt', 'images'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('image_fulltext_caption', 'images'); ?>
						<?php echo $this->form->getInput('image_fulltext_caption', 'images'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('float_fulltext', 'images'); ?>
						<?php echo $this->form->getInput('float_fulltext', 'images'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('urla', 'urls'); ?>
						<?php echo $this->form->getInput('urla', 'urls'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('urlatext', 'urls'); ?>
						<?php echo $this->form->getInput('urlatext', 'urls'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getInput('targeta', 'urls'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('urlb', 'urls'); ?>
						<?php echo $this->form->getInput('urlb', 'urls'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('urlbtext', 'urls'); ?>
						<?php echo $this->form->getInput('urlbtext', 'urls'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getInput('targetb', 'urls'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('urlc', 'urls'); ?>
						<?php echo $this->form->getInput('urlc', 'urls'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('urlctext', 'urls'); ?>
						<?php echo $this->form->getInput('urlctext', 'urls'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getInput('targetc', 'urls'); ?>
					</div>

				</div>
				<?php endif; ?>

				<div class="tab-pane" id="publishing">
					<div class="form-group">
						<?php echo $this->form->getLabel('catid'); ?>
						<?php echo $this->form->getInput('catid'); ?>
					</div>

					<div class="form-group">
						<?php echo $this->form->getLabel('tags'); ?>
						<?php echo str_replace('span12', '', $this->form->getInput('tags')); ?>
					</div>

					<?php if ($params->get('save_history', 0)) : ?>
					<div class="form-group">
						<?php echo $this->form->getLabel('version_note'); ?>
						<?php echo $this->form->getInput('version_note'); ?>
					</div>
					<?php endif; ?>

					<div class="form-group">
						<?php echo $this->form->getLabel('created_by_alias'); ?>
						<?php echo $this->form->getInput('created_by_alias'); ?>
					</div>

					<?php if ($this->item->params->get('access-change')) : ?>
						<div class="form-group">
							<?php echo $this->form->getLabel('state'); ?>
							<?php echo $this->form->getInput('state'); ?>
						</div>

						<div class="form-group">
							<?php echo $this->form->getLabel('featured'); ?>
							<?php echo $this->form->getInput('featured'); ?>
						</div>

						<div class="form-group">
							<?php echo $this->form->getLabel('publish_up'); ?>
							<?php echo str_replace('class="btn"', 'class="btn btn-default"', $this->form->getInput('publish_up')); ?>
						</div>

						<div class="form-group">
							<?php echo $this->form->getLabel('publish_down'); ?>
							<?php echo str_replace('class="btn"', 'class="btn btn-default"', $this->form->getInput('publish_down')); ?>
						</div>
					<?php endif; ?>

					<div class="form-group">
						<?php echo $this->form->getLabel('access'); ?>
						<?php echo $this->form->getInput('access'); ?>
					</div>

					<?php if (is_null($this->item->id)):?>
						<div class="form-group">
							<?php echo JText::_('COM_CONTENT_ORDERING'); ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="tab-pane" id="language">
					<div class="form-group">
						<?php echo $this->form->getLabel('language'); ?>
						<?php echo $this->form->getInput('language'); ?>
					</div>
				</div>

				<div class="tab-pane" id="metadata">
					<div class="form-group">
						<?php echo $this->form->getLabel('metadesc'); ?>
						<?php echo $this->form->getInput('metadesc'); ?>
					</div>

					<div class="form-group">
							<?php echo $this->form->getLabel('metakey'); ?>
							<?php echo $this->form->getInput('metakey'); ?>
					</div>

					<input type="hidden" name="task" value="" />
					<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
					<?php if ($this->params->get('enable_category', 0) == 1) :?>
					<input type="hidden" name="jform[catid]" value="<?php echo $this->params->get('catid', 1); ?>" />
					<?php endif; ?>
				</div>
			</div>
			<div class="btn-toolbar">
				<div class="btn-group">
					<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('article.save')">
						<span class="fa fa-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-default" onclick="Joomla.submitbutton('article.cancel')">
						<span class="fa fa-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
					</button>
				</div>
				<?php if ($params->get('save_history', 0)) : ?>
				<div class="btn-group">
					<?php echo $this->form->getInput('contenthistory'); ?>
				</div>
				<?php endif; ?>
			</div>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
