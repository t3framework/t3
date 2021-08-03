<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

// Create a shortcut for params.
$item = $displayData['item'];
$params = $displayData['params'];
$title_tag = $displayData['title-tag'];
$canEdit = $params->get('access-edit');
if (empty ($item->catslug)) {
  $item->catslug = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
}
$url = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
$uri = JUri::getInstance();
$prefix = $uri->toString(array('scheme', 'host', 'port'));
?>

<header class="article-header clearfix">
	<<?php echo $title_tag; ?> class="article-title" itemprop="headline">
		<?php if ($params->get('link_titles')) : ?>
			<a href="<?php echo $url ?>" itemprop="url" title="<?php echo $this->escape($item->title); ?>">
				<?php echo $this->escape($item->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($item->title); ?>
			<meta itemprop="url" content="<?php echo $prefix.$url ?>" />
		<?php endif; ?>
	</<?php echo $title_tag; ?>>

	<?php if ($item->state == 0) : ?>
		<span class="label label-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
	<?php endif; ?>
	<?php if (strtotime($item->publish_up) > strtotime(JFactory::getDate())) : ?>
		<span class="label label-warning"><?php echo Text::_('JNOTPUBLISHEDYET'); ?></span>
	<?php endif; ?>
	<?php if ((strtotime($item->publish_down) < strtotime(JFactory::getDate())) && !in_array($item->publish_down, array('',JFactory::getDbo()->getNullDate()))) : ?>
		<span class="label label-warning"><?php echo Text::_('JEXPIRED'); ?></span>
	<?php endif; ?>
</header>
