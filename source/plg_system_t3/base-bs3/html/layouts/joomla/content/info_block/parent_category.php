<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$item = $displayData['item'];
$params = $displayData['params'];
$title = $this->escape($item->parent_title);
if(version_compare(JVERSION, '4', 'ge')) {
	class ContentHelperRoute extends \Joomla\Component\Content\Site\Helper\RouteHelper{};
}
?>
<dd class="parent-category-name hasTooltip" title="<?php echo JText::sprintf('COM_CONTENT_PARENT', ''); ?>">
	<i class="fa fa-folder"></i>
	<?php if ($params->get('link_parent_category') && !empty($item->parent_slug)) : ?>
		<?php echo JHtml::_('link', JRoute::_(ContentHelperRoute::getCategoryRoute($item->parent_slug)), '<span itemprop="genre">'.$title.'</span>'); ?>
	<?php else : ?>
		<span itemprop="genre"><?php echo $title ?></span>
	<?php endif; ?>
</dd>