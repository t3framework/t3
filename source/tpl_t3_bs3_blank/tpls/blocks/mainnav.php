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

		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".t3-navbar">
				<i class="fa fa-bars"></i>
			</button>
		</div>

		<div class="t3-navbar collapse navbar-collapse <?php echo $this->getParam('navigation_collapse_showsub', 1) ? ' always-show' : '' ?>">
			<jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu') ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu') ?>" />
		</div>

	</div>
</nav>
<!-- //MAIN NAVIGATION -->
