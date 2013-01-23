<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$sitename = $this->params->get('sitename') ? $this->params->get('sitename') : JFactory::getConfig()->get('sitename');
$slogan = $this->params->get('slogan');
$logotype = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', '') : '';
if ($logoimage) {
  $logoimage = ' style="background-image:url('.JURI::base(true).'/'.$logoimage.');"';
}
?>

<!-- HEADER -->
<header id="ja-header" class="container ja-header">
  <div class="row">

    <!-- LOGO -->
    <div class="span8">
      <div class="logo logo-<?php echo $logotype ?>">
        <h1>
          <a href="<?php echo JURI::base(true) ?>" title="<?php echo strip_tags($sitename) ?>"<?php echo $logoimage ?>>
            <span><?php echo $sitename ?></span>
          </a>
          <small class="site-slogan hidden-phone"><?php echo $slogan ?></small>
        </h1>
      </div>
    </div>
    <!-- //LOGO -->

    <?php if ($this->countModules('head-search')) : ?>
    <!-- HEAD SEARCH -->
    <div class="span4<?php $this->_c('head-search')?>">     
      <div class="head-search">
        <jdoc:include type="modules" name="<?php $this->_p('head-search') ?>" style="raw" />
      </div>
    </div>
    <!-- //HEAD SEARCH -->
    <?php endif ?>

  </div>
</header>
<!-- //HEADER -->
