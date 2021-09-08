<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/*
* This segment of code sets up the autocompleter.
*/
if ($this->params->get('show_autosuggest', 1))
{
	if (version_compare(JVERSION, '4', 'ge')) {
		
	$this->document->getWebAssetManager()->usePreset('awesomplete');
	$this->document->addScriptOptions('finder-search', array('url' => Route::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component')));
	}else{
		$doc = JFactory::getDocument();

		JHtml::_('jquery.framework');
		$script = "
			jQuery(function() {";

				if ($this->params->get('show_advanced', 1))
				{
					/*
					* This segment of code disables select boxes that have no value when the
					* form is submitted so that the URL doesn't get blown up with null values.
					*/
					$script .= "
				jQuery('#finder-search').on('submit', function(e){
					e.stopPropagation();
					// Disable select boxes with no value selected.
					jQuery('#advancedSearch').find('select').each(function(index, el) {
						var el = jQuery(el);
						if(!el.val()){
							el.attr('disabled', 'disabled');
						}
					});
				});";
				}
		/*
		* This segment of code sets up the autocompleter.
		*/
		if ($this->params->get('show_autosuggest', 1))
			{
				JHtml::_('script', 'jui/jquery.autocomplete.min.js', array('version' => 'auto', 'relative' => true));
				$script .= "
				jQuery('.input-group-append a.btn').on('click',function(e){
					e.preventDefault();
					e.stopPropagation();
					var target = jQuery(this).data('target');jQuery(target).slideToggle();
				});
			var suggest = jQuery('#q').autocomplete({
				serviceUrl: '" . JRoute::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component') . "',
				paramName: 'q',
				minChars: 1,
				maxHeight: 400,
				width: 300,
				zIndex: 9999,
				deferRequestBy: 500
			});";
			}

			$script .= "
		});";

			$doc->addScriptDeclaration($script);
		}

}

?>

<form action="<?php echo Route::_($this->query->toUri()); ?>" method="get" class="js-finder-searchform">
	<?php echo $this->getFields(); ?>
	<fieldset class="com-finder__search word mb-3">
		<?php if(version_compare(JVERSION, '4', 'ge')): ?>
		<legend class="com-finder__search-legend visually-hidden">
			<?php echo Text::_('COM_FINDER_SEARCH_FORM_LEGEND'); ?>
		</legend>
	<?php endif; ?>
		<div class="form-inline">
			<label for="q" class="me-2">
				<?php echo Text::_('COM_FINDER_SEARCH_TERMS'); ?>
			</label>
			<div class="input-group">
				<input type="text" name="q" id="q" class="js-finder-search-query form-control" value="<?php echo $this->escape($this->query->input); ?>">
				<button type="submit" class="btn btn-primary">
					<span class="icon-search icon-white" aria-hidden="true"></span>
					<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>
				</button>
				<?php if ($this->params->get('show_advanced', 1)) : ?>
					<?php if(version_compare(JVERSION,'4','ge')): ?>
					<?php JHtml::_('bootstrap.collapse'); ?>
					<button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSearch" aria-expanded="<?php echo ($this->params->get('expand_advanced', 0) ? 'true' : 'false'); ?>">
						<span class="icon-search-plus" aria-hidden="true"></span>
						<?php echo Text::_('COM_FINDER_ADVANCED_SEARCH_TOGGLE'); ?></button>
					<?php else: ?>
						<a href="#advancedSearch" data-toggle="collapse" class="btn">
						<span class="icon-list" aria-hidden="true"></span>
							<?php echo Text::_('COM_FINDER_ADVANCED_SEARCH_TOGGLE'); ?>
						</a>
				<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</fieldset>

	<?php if ($this->params->get('show_advanced', 1)) : ?>
		<fieldset id="advancedSearch" class="com-finder__advanced js-finder-advanced collapse<?php if ($this->params->get('expand_advanced', 0)) echo ' show'; ?>">
			<?php if(version_compare(JVERSION, '4', 'ge')): ?>
			<legend class="com-finder__search-advanced visually-hidden">
				<?php echo Text::_('COM_FINDER_SEARCH_ADVANCED_LEGEND'); ?>
			</legend>
		<?php endif; ?>
			<?php if ($this->params->get('show_advanced_tips', 1)) : ?>
				<div class="com-finder__tips card card-outline-secondary mb-3">
					<div class="card-body">
						<?php if(version_compare(JVERSION, '4', 'ge')): ?>
						<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_INTRO'); ?>
						<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_AND'); ?>
						<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_NOT'); ?>
						<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_OR'); ?>
						<?php if ($this->params->get('tuplecount', 1) > 1) : ?>
						<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_PHRASE'); ?>
						<?php endif; ?>
						<?php echo Text::_('COM_FINDER_ADVANCED_TIPS_OUTRO'); ?>
					<?php else: ?>
						<?php echo Text::_('COM_FINDER_ADVANCED_TIPS'); ?>
					<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
			<div id="finder-filter-window" class="com-finder__filter">
				<?php echo JHtml::_('filter.select', $this->query, $this->params); ?>
			</div>
		</fieldset>
	<?php endif; ?>
</form>
