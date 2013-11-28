<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<!-- MAIN NAVIGATION -->
<nav id="t3-mainnav" class="wrap navbar navbar-default t3-mainnav">
	<div class="container">

		<?php if ($this->getParam('navigation_collapse_enable')) :
			$this->addScript(T3_URL.'/js/nav-collapse.js');
		?>
		<div class="navbar-header">
			<button class="btn btn-primary off-canvas-toggle" type="button" data-nav="#t3-off-canvas" data-pos="left" data-effect="<?php echo $this->getParam('addon_offcanvas_effect', 'off-canvas-effect-4') ?>">
			  <i class="fa fa-bars"></i>
			</button>
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".t3-navbar-collapse">
				<i class="fa fa-bars"></i>
			</button>
		</div>
		<div class="t3-navbar-collapse navbar-collapse collapse"></div>
		<?php endif ?>

		<div class="t3-navbar navbar-collapse collapse">
			<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu') ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu') ?>" />
		</div>

	</div>
</nav>
<!-- //MAIN NAVIGATION -->
