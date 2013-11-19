<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::addIncludePath(T3_PATH . '/html/com_content');
JHtml::addIncludePath(dirname(dirname(__FILE__)));
$params = $this->params;
?>

<div id="archive-items">
	<?php foreach ($this->items as $i => $item) : ?>

		<div class="row<?php echo $i % 2; ?>">
			<!-- Article -->
			<article>
				<header>
					<h2 class="item-title">
						<?php if ($params->get('link_titles')): ?>
							<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug)); ?>"> <?php echo $this->escape($item->title); ?></a>
						<?php else: ?>
							<?php echo $this->escape($item->title); ?>
						<?php endif; ?>
					</h2>
				</header>

				<!-- Aside -->
				<aside class="clearfix">

					<?php if (($params->get('show_author') && !empty($item->author))
						or ($params->get('show_parent_category'))
						or ($params->get('show_category'))
						or ($params->get('show_create_date'))
						or ($params->get('show_publish_date'))
					) : ?>
						<dl class="article-info pull-left">

							<?php if ($params->get('show_author') && !empty($item->author)) : ?>
								<dd class="createdby">
									<?php $author = $item->author; ?>
									<?php $author = ($item->created_by_alias ? $item->created_by_alias : $author); ?>
									<?php if (!empty($item->contactid) && $params->get('link_author') == true): ?>
										<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', '<strong>' . JHtml::_('link', JRoute::_('index.php?option=com_contact&view=contact&id=' . $item->contactid), $author) . '</strong>'); ?>
									<?php else : ?>
										<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', '<strong>' . $author . '</strong>'); ?>
									<?php endif; ?>
								</dd>
							<?php endif; ?>

							<?php if ($params->get('show_publish_date')) : ?>
								<dd class="published">
									<span
										class="fa fa-calendar"></span> <?php echo JText::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', '<strong>' . JHtml::_('date', $item->publish_up, JText::_('DATE_FORMAT_LC3')) . '</strong>'); ?>
								</dd>
							<?php endif; ?>

							<?php if ($params->get('show_create_date')) : ?>
								<dd class="create">
									<?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', '<strong>' . JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC3')) . '</strong>'); ?>
								</dd>
							<?php endif; ?>

							<?php if ($params->get('show_parent_category')) : ?>
								<dd class="parent-category-name">
									<?php    $title = $this->escape($item->parent_title);
									$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($item->parent_slug)) . '">' . $title . '</a>';?>
									<?php if ($params->get('link_parent_category') && $item->parent_slug) : ?>
										<?php echo JText::sprintf('COM_CONTENT_PARENT', '<strong>' . $url . '</strong>'); ?>
									<?php else : ?>
										<?php echo JText::sprintf('COM_CONTENT_PARENT', '<strong>' . $title . '</strong>'); ?>
									<?php endif; ?>
								</dd>
							<?php endif; ?>

							<?php if ($params->get('show_category')) : ?>
								<dd class="category-name">
									<?php    $title = $this->escape($item->category_title);
									$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug)) . '">' . $title . '</a>'; ?>
									<?php if ($params->get('link_category') && $item->catslug) : ?>
										<?php echo JText::sprintf('COM_CONTENT_CATEGORY', '<strong>' . $url . '</strong>'); ?>
									<?php else : ?>
										<?php echo JText::sprintf('COM_CONTENT_CATEGORY', '<strong>' . $title . '</strong>'); ?>
									<?php endif; ?>
								</dd>
							<?php endif; ?>

						</dl>
					<?php endif; ?>

				</aside>
				<!-- //Aside -->

				<?php if ($params->get('show_intro')) : ?>
					<div class="intro"> <?php echo JHtml::_('string.truncate', $item->introtext, $params->get('introtext_limit')); ?> </div>
				<?php endif; ?>

				<?php if (($params->get('show_modify_date'))
						or ($params->get('show_hits'))) : ?>
					<footer>
						<div class="btn-toolbar">

							<?php if ($params->get('show_modify_date')) : ?>
								<div class="btn-group modified">
									<i class="fa fa-calendar"></i> <?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', '<strong>' . JHtml::_('date', $item->modified, JText::_('DATE_FORMAT_LC3')) . '</strong>'); ?>
								</div>
							<?php endif; ?>

							<?php if ($params->get('show_hits')) : ?>
								<div class="btn-group hits">
									<i class="fa fa-eye"></i> <?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', '<strong>' . $item->hits . '</strong>'); ?>
								</div>
							<?php endif; ?>

						</div>
					</footer>
				<?php endif; ?>

			</article>
			<!-- //Article -->
		</div>
	<?php endforeach; ?>
</div>
<div class="pagination-wrap">
	<p class="counter"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
