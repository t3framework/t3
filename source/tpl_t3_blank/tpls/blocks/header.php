<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$sitename  = $this->params->get('sitename') ? $this->params->get('sitename') : JFactory::getConfig()->get('sitename');
$slogan    = $this->params->get('slogan');
$logotype  = $this->params->get('logotype', 'text');
$logoimage = $logotype == 'image' ? $this->params->get('logoimage', T3Path::getUrl('images/logo.png', '', true)) : '';
$logoimgsm = ($logotype == 'image' && $this->params->get('enable_logoimage_sm', 0)) ? $this->params->get('logoimage_sm', T3Path::getUrl('images/logo-sm.png', '', true)) : false;
?>

<!-- HEADER -->
<header id="t3-header" class="container t3-header">
  <div class="row">

    <!-- LOGO -->
    <div class="span8 logo">
      <div class="logo-<?php echo $logotype, ($logoimgsm ? ' logo-control' : '') ?>">
        <a href="<?php echo JUri::base() ?>" title="<?php echo strip_tags($sitename) ?>">
          <?php if($logotype == 'image'): ?>
            <img class="logo-img" src="<?php echo JUri::base(true) . '/' . $logoimage ?>" alt="<?php echo strip_tags($sitename) ?>" />
          <?php endif ?>
          <?php if($logoimgsm) : ?>
            <img class="logo-img-sm" src="<?php echo JUri::base(true) . '/' . $logoimgsm ?>" alt="<?php echo strip_tags($sitename) ?>" />
          <?php endif ?>
          <span><?php echo $sitename ?></span>
        </a>
        <small class="site-slogan hidden-phone"><?php echo $slogan ?></small>
      </div>
    </div>
    <!-- //LOGO -->

    <?php if($this->countModules('head-search or languageswitcherload')): ?>
    <div class="span4 clearfix">  
      <?php if ($this->countModules('head-search')) : ?>
      <!-- HEAD SEARCH -->
      <div class="head-search<?php $this->_c('head-search')?>">     
        <jdoc:include type="modules" name="<?php $this->_p('head-search') ?>" style="raw" />
      </div>
      <!-- //HEAD SEARCH -->
      <?php endif ?>
  
      <?php if ($this->countModules('languageswitcherload')) : ?>
      <!-- LANGUAGE SWITCHER -->
      <div class="languageswitcherload">
          <jdoc:include type="modules" name="<?php $this->_p('languageswitcherload') ?>" style="raw" />
      </div>
      <!-- //LANGUAGE SWITCHER -->
      <?php endif ?>
    </div>
    <?php endif ?>

  </div>
</header>
<!-- //HEADER -->
