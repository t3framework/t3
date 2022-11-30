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

// Create a shortcut for params.
$params  = & $this->item->params;
$images  = json_decode($this->item->images);
$canEdit = $this->item->params->get('access-edit');
$info    = $params->get('info_block_position', 2);

$aInfo1 = ($params->get('show_publish_date') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author'));
$aInfo2 = ($params->get('show_create_date') || $params->get('show_modify_date') || $params->get('show_hits'));
$topInfo = ($aInfo1 && $info != 1) || ($aInfo2 && $info == 0);
$botInfo = ($aInfo1 && $info == 1) || ($aInfo2 && $info != 0);
$icons = $params->get('access-edit') || $params->get('show_print_icon') || $params->get('show_email_icon');
	$timePublishDown = $this->item->publish_down != null ? $this->item->publish_down : '';
	$timePublishUp = $this->item->publish_up != null ? $this->item->publish_up : '';
?>

  <?php if ($this->item->state == 0 || strtotime($timePublishUp) > strtotime(JFactory::getDate())
	|| ((strtotime($timePublishDown) < strtotime(JFactory::getDate())) && $this->item->publish_down != JFactory::getDbo()->getNullDate() )) : ?>
<div class="system-unpublished">
	<?php endif; ?>

	<!-- Article -->
	<article>

		<?php if ($params->get('show_title')) : ?>
			<?php echo JLayoutHelper::render('joomla.content.item_title', array('item' => $this->item, 'params' => $params, 'title-tag'=>'h2')); ?>
		<?php endif; ?>

    <!-- Aside -->
    <?php if ($topInfo) : ?>
    <aside class="article-aside clearfix">
    	<?php if ($icons): ?>
      <?php echo JLayoutHelper::render('joomla.content.icons', array('item' => $this->item, 'params' => $params)); ?>
      <?php endif; ?>

      <?php if ($topInfo): ?>
      <?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
      <?php endif; ?>
    </aside>  
    <?php endif; ?>
    <!-- //Aside -->

		<section class="article-intro clearfix">

			<?php if (!$params->get('show_intro')) : ?>
				<?php echo $this->item->event->afterDisplayTitle; ?>
			<?php endif; ?>

			<?php echo $this->item->event->beforeDisplayContent; ?>

			<?php echo JLayoutHelper::render('joomla.content.intro_image', $this->item); ?>

			<?php echo $this->item->introtext; ?>
		</section>

    <!-- footer -->
    <?php if ($botInfo) : ?>
    <footer class="article-footer clearfix">
    	<?php if ($icons && $info == 1): ?>
      <?php echo JLayoutHelper::render('joomla.content.icons', array('item' => $this->item, 'params' => $params)); ?>
      <?php endif; ?>

      <?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
    </footer>
    <?php endif; ?>
    <!-- //footer -->

    <?php if ($params->get('show_tags', 1) && !empty($this->item->tags)) : ?>
      <?php echo JLayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
    <?php endif; ?>

		<?php if ($params->get('show_readmore')) :
			if ($params->get('access-view')) :
				$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
			else :
				$menu      = JFactory::getApplication()->getMenu();
				$active    = $menu->getActive();
				$itemId    = $active->id;
        $link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
        $link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)));
			endif; ?>

      <?php echo JLayoutHelper::render('joomla.content.readmore', array('item' => $this->item, 'params' => $params, 'link' => $link)); ?>
      
		<?php endif; ?>
	</article>
	<!-- //Article -->

  <?php if ($this->item->state == 0 || strtotime($timePublishUp) > strtotime(JFactory::getDate())
	|| ((strtotime($timePublishDown) < strtotime(JFactory::getDate())) && $this->item->publish_down != JFactory::getDbo()->getNullDate() )) : ?>
</div>
<?php endif; ?>
<?php echo $this->item->event->afterDisplayContent; ?>
