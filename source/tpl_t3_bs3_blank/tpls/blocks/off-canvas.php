<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php
	if (!$this->getParam('addon_offcanvas_enable')) return ;
?>

<button class="btn btn-primary off-canvas-toggle <?php $this->_c('off-canvas') ?>" type="button" data-pos="left" data-nav="#t3-off-canvas" data-effect="<?php echo $this->getParam('addon_offcanvas_effect', 'off-canvas-effect-4') ?>">
  <span class="bar-first"><span class="hidden">bar</span></span>
  <span class="bar-mid"><span class="hidden">bar</span></span>
  <span class="bar-last"><span class="hidden">bar</span></span>
</button>

<!-- OFF-CANVAS SIDEBAR -->
<div id="t3-off-canvas" class="t3-off-canvas <?php $this->_c('off-canvas') ?>">

  <div class="t3-off-canvas-header">
    <h2 class="t3-off-canvas-header-title"><?php echo JText::_('TPL_SIDE_BAR') ?></h2>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  </div>

  <div class="t3-off-canvas-body">
    <jdoc:include type="modules" name="<?php $this->_p('off-canvas') ?>" style="T3Xhtml" />
  </div>

</div>
<!-- //OFF-CANVAS SIDEBAR -->
