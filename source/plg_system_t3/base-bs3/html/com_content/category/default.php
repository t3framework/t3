<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if(!class_exists('ContentHelperRoute')){
	if(version_compare(JVERSION, '4', 'ge')){
		abstract class ContentHelperRoute extends \Joomla\Component\content\Site\Helper\RouteHelper{};
	}else{
		JLoader::register('ContentHelperRoute', $com_path . '/helpers/route.php');
	}
}

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
if(version_compare(JVERSION, '4','lt')){
  JHtml::_('behavior.caption'); 
}
?>
<div class="category-list<?php echo $this->pageclass_sfx;?>">

  <?php
    $this->subtemplatename = 'articles';
    echo JLayoutHelper::render('joomla.content.category_default', $this);
  ?>

</div>
