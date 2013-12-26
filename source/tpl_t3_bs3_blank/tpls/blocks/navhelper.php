<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php if ($this->countModules('navhelper')) : ?>
	<!-- NAV HELPER -->
	<nav class="wrap t3-navhelper <?php $this->_c('navhelper') ?>">
		<div class="container">
			<jdoc:include type="modules" name="<?php $this->_p('navhelper') ?>" />
		</div>
	</nav>
	<!-- //NAV HELPER -->
<?php endif ?>
