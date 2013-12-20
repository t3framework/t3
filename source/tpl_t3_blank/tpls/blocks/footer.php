<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<!-- FOOTER -->
<footer id="t3-footer" class="wrap t3-footer">

  <!-- FOOT NAVIGATION -->
  <div class="container">
    <?php $this->spotlight ('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6') ?>
  </div>
  <!-- //FOOT NAVIGATION -->

  <section class="t3-copyright">
    <div class="container">
      <div class="row">
        <div class="<?php echo $this->getParam('t3-rmvlogo', 1) ? 'span8' : 'span12' ?> copyright<?php $this->_c('footer')?>">
          <jdoc:include type="modules" name="<?php $this->_p('footer') ?>" />
        </div>
        <?php if($this->getParam('t3-rmvlogo', 1)): ?>
        <div class="span4 poweredby">
          <a class="t3-logo t3-logo-light" href="http://t3-framework.org" title="Powered By T3 Framework" target="_blank" <?php echo method_exists('T3', 'isHome') && T3::isHome() ? '' : 'rel="nofollow"' ?>>Powered by <strong>T3 Framework</strong></a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

</footer>
<!-- //FOOTER -->