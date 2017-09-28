<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (version_compare(JVERSION, '3.0', 'ge')) {
	JHtml::_('bootstrap.tooltip');
}

$lang        = JFactory::getLanguage();
$upper_limit = $lang->getUpperLimitSearchWord();
?>
<form id="searchForm" action="<?php echo JRoute::_('index.php?option=com_search'); ?>" method="post">

	<input type="hidden" name="task" value="search"/>

	<div class="input-group form-group">
		<input type="text" name="searchword" placeholder="<?php echo JText::_('COM_SEARCH_SEARCH_KEYWORD'); ?>"
			   id="search-searchword" size="30" maxlength="<?php echo $upper_limit; ?>"
			   value="<?php echo $this->escape($this->origkeyword); ?>" class="form-control"/>
		<span class="input-group-btn">
			<button name="Search" onclick="this.form.submit()" class="btn btn-default"
					title="<?php echo JText::_('COM_SEARCH_SEARCH'); ?>"><span class="fa fa-search"></span></button>
		</span>
	</div>

	<div class="searchintro<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php if (!empty($this->searchword)): ?>
			<p><?php echo JText::plural('COM_SEARCH_SEARCH_KEYWORD_N_RESULTS', '<span class="badge badge-info">' . $this->total . '</span>'); ?></p>
		<?php endif; ?>
	</div>

	<fieldset class="phrases">
		<legend><?php echo JText::_('COM_SEARCH_FOR'); ?></legend>
		<div class="phrases-box form-group">
			<?php echo str_replace('class="radio"', 'class="radio-inline"', $this->lists['searchphrase']); ?>
		</div>
		<div class="ordering-box form-group">
			<label for="ordering" class="control-label">
				<?php echo JText::_('COM_SEARCH_ORDERING'); ?>
			</label>
			<?php echo $this->lists['ordering']; ?>
		</div>
	</fieldset>

	<?php if ($this->params->get('search_areas', 1)) : ?>
		<fieldset class="only">
			<legend><?php echo JText::_('COM_SEARCH_SEARCH_ONLY'); ?></legend>
			<?php foreach ($this->searchareas['search'] as $val => $txt) :
				$checked = is_array($this->searchareas['active']) && in_array($val, $this->searchareas['active']) ? 'checked="checked"' : '';
				?>
				<label for="area-<?php echo $val; ?>" class="checkbox-inline">
					<input type="checkbox" name="areas[]" value="<?php echo $val; ?>"
						   id="area-<?php echo $val; ?>" <?php echo $checked; ?> >
					<?php echo JText::_($txt); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
	<?php endif; ?>

	<?php if ($this->total > 0) : ?>
		<div class="form-limit">
			<label for="limit">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
			</label>
			<?php echo $this->pagination->getLimitBox(); ?>
			
			<?php if($this->pagination->getPagesCounter() > 0) : ?>
			<p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</form>
