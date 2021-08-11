<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

if(!class_exists('ContentHelperRoute')){
	if(version_compare(JVERSION, '4', 'ge')){
		abstract class ContentHelperRoute extends \Joomla\Component\content\Site\Helper\RouteHelper{};
	}else{
		JLoader::register('ContentHelperRoute', $com_path . '/helpers/route.php');
	}
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::addIncludePath(T3_PATH.'/html/com_content');
JHtml::addIncludePath(dirname(dirname(__FILE__)));
if (version_compare(JVERSION, '4', 'lt')) {
	JHtml::_('behavior.caption');
}
$this->columns = !empty($this->columns) ? $this->columns : $this->params->get('num_columns');

// If the page class is defined, add to class as suffix.
// It will be a separate class if the user starts it with a space
?>
<div class="blog-featured<?php echo $this->pageclass_sfx;?>" itemscope itemtype="https://schema.org/Blog">
<?php if ($this->params->get('show_page_heading') != 0) : ?>
<div class="page-header">
	<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
</div>
<?php endif; ?>

<?php $leadingcount = 0; ?>
<?php if (!empty($this->lead_items)) : ?>
<div class="blog-items items-leading clearfix <?php echo $this->params->get('blog_class_leading'); ?>">
	<?php foreach ($this->lead_items as &$item) : ?>
		<div class="leading leading-<?php echo $leadingcount; ?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?>" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
		</div>
		<?php
			$leadingcount++;
		?>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<?php
	$introcount = (count($this->intro_items));
	$counter = 0;
?>
<?php if (!empty($this->intro_items)) : ?>
	<div class="row row-flex">
	<?php foreach ($this->intro_items as $key => &$item) : ?>
		<?php
		$key = ($key - $leadingcount) + 1;
		$rowcount = (((int) $key - 1) % (int) $this->columns) + 1;
		$row = $counter / $this->columns;
		?>
			<div class="item col-12 column-<?php echo $rowcount;?><?php echo $item->state == 0 ? ' system-unpublished' : null; ?> <?php echo ((int)$this->columns >= 2) ? ' col-sm-6':''; ?> col-md-<?php echo round((12 / $this->columns));?>" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
			<?php
				$this->item = &$item;
				echo $this->loadTemplate('item');
			?>
			</div>
			<?php $counter++; ?>
	<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if (!empty($this->link_items)) : ?>
	<section class="items-more">
		<h3><?php echo Text::_('COM_CONTENT_MORE_ARTICLES'); ?></h3>
		<?php echo $this->loadTemplate('links'); ?>
	</section>
<?php endif; ?>

<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
	<nav class="pagination-wrap clearfix">

		<?php 
    $pagesTotal = isset($this->pagination->pagesTotal) ? $this->pagination->pagesTotal : $this->pagination->get('pages.total');
    if ($this->params->def('show_pagination_results', 1) && $pagesTotal > 1) : ?>
			<div class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</div>
		<?php  endif; ?>
				<?php echo $this->pagination->getPagesLinks(); ?>
	</nav>
<?php endif; ?>

</div>
