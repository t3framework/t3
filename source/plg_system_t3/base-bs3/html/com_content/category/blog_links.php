<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>


<section class="items-more">
<h3><?php echo JText::_('COM_CONTENT_MORE_ARTICLES'); ?></h3>
<ol class="nav">
<?php
	foreach ($this->link_items as &$item) :
?>
	<li>
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid)); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ol>
</section>
