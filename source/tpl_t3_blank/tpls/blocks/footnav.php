<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php if ($this->checkSpotlight('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6')) : ?>
<!-- FOOT NAVIGATION -->
<nav class="t3-footnav">
  <?php 
  	$this->spotlight ('footnav', 'footer-1, footer-2, footer-3, footer-4, footer-5, footer-6')
  ?>
</nav>
<!-- //FOOT NAVIGATION -->
<?php endif ?>