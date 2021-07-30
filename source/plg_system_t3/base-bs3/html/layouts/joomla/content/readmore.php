<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
$params = $displayData['params'];
$item = $displayData['item'];
$direction = Factory::getLanguage()->isRtl() ? 'left' : 'right';
?>

<section class="readmore">
	<?php if (!$params->get('access-view')) : ?>
		<a class="btn btn-default" href="<?php echo $displayData['link']; ?>" itemprop="url" aria-label="<?php echo Text::_('COM_CONTENT_REGISTER_TO_READ_MORE') . ' ' . $this->escape($item->title); ?>">
			<span>
				<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
				<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?>
				<?php echo Text::_('COM_CONTENT_REGISTER_TO_READ_MORE'); ?>
			</span>
		</a>
	<?php elseif ($readmore = $item->alternative_readmore) : ?>
		<a class="btn btn-default" href="<?php echo $displayData['link']; ?>" itemprop="url" aria-label="<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>">
			<span>
				<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?> 
				<?php echo $readmore; ?>
				<?php if ($params->get('show_readmore_title', 0) != 0) : ?>
					<?php echo HTMLHelper::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
				<?php endif; ?>
			</span>
		</a>
	<?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
		<a class="btn btn-default" href="<?php echo $displayData['link']; ?>" itemprop="url" aria-label="<?php echo Text::sprintf('COM_CONTENT_READ_MORE', $this->escape($item->title)); ?>">
			<span>
				<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?>
				<?php echo Text::_('COM_CONTENT_READ_MORE'); ?>

			</span>
		</a>
	<?php else : ?>
		<a class="btn btn-default" href="<?php echo $displayData['link']; ?>" itemprop="url" aria-label="<?php echo Text::sprintf('COM_CONTENT_READ_MORE', $this->escape($item->title)); ?>">
		<span>
			<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?> 
			<?php echo Text::sprintf('COM_CONTENT_READ_MORE_TITLE', HTMLHelper::_('string.truncate', $item->title, $params->get('readmore_limit'))); ?>
		</span>
		</a>
	<?php endif; ?>
</section>
