<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
$item = $displayData['item'];
$author = ($item->created_by_alias ? $item->created_by_alias : $item->author);
?>

<dd class="createdby hasTooltip" itemprop="author" title="<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', ''); ?>">
	<i class="fa fa-user"></i>
	<?php if (!empty($displayData['item']->contact_link ) && $displayData['params']->get('link_author') == true) : ?>
		<span itemprop="name"><?php echo JHtml::_('link', $displayData['item']->contact_link, $author, array('itemprop' => 'url')); ?></span>
	<?php else :?>
		<span itemprop="name"><?php echo $author; ?></span>
	<?php endif; ?>
  <span style="display: none;" itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
  <span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
    <img src="<?php echo JURI::base(); ?>/templates/<?php echo JFactory::getApplication()->getTemplate() ?>/images/logo.png" alt="logo" itemprop="url" />
    <meta itemprop="width" content="auto" />
    <meta itemprop="height" content="auto" />
  </span>
  <meta itemprop="name" content="<?php echo $author; ?>"/>
  </span>
</dd>
