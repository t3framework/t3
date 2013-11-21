<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php
	if (!$this->getParam('navigation_offcanvas_enable')) return ;
?>
<button class="off-canvas-btn" type="button" data-nav="#off-canvas" data-effect="<?php echo $this->getParam('navigation_collapse_offcanvas_effect', 'off-canvas-effect-4') ?>">
	<i class="fa fa-bars"></i>
</button>

<!-- OFF-CANVAS NAVIGATION -->
<nav id="off-canvas" class="off-canvas">
	<h3>Sidebar</h3>
	<jdoc:include type="modules" name="<?php $this->_p('mainnav') ?>" style="raw" />
</nav>
<!-- //OFF-CANVAS NAVIGATION -->
