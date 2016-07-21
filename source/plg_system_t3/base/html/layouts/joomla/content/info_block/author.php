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
$author = '<span itemprop="name">' . $author . '</span>';
?>

<dd class="createdby hasTooltip" itemprop="author" itemscope itemtype="http://schema.org/Person" title="<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', ''); ?>">
	<i class="icon-user"></i>
	<?php if (!empty($displayData['item']->contact_link ) && $displayData['params']->get('link_author') == true) : ?>
		<?php echo JHtml::_('link', $displayData['item']->contact_link, $author, array('itemprop' => 'url')); ?>
	<?php else :?>
		<?php echo $author; ?>
	<?php endif; ?>
</dd>
