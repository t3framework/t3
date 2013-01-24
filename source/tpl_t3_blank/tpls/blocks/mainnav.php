<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<!-- MAIN NAVIGATION -->
<nav id="t3-mainnav" class="wrap t3-mainnav">
  <div class="container navbar">
    <div class="navbar-inner">

      <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
    		<span class="icon-bar"></span>
    		<span class="icon-bar"></span>
    		<span class="icon-bar"></span>
  	  </button>

  	  <div class="nav-collapse collapse">
      <?php if ($this->getParam('navigation_type') == 'megamenu') : ?>
        <?php $this->megamenu($this->getParam('mm_type', 'mainmenu')) ?>
      <?php else : ?>
        <div class="mainnav-wrap <?php $this->_c('navhelper') ?>">
          <jdoc:include type="modules" name="mainnav" style="raw" />
        </div>
      <?php endif ?>
  		</div>
    </div>
  </div>
</nav>

<?php if ($this->getParam ('navigation_trigger') == 'hover') :?>
  <script type="text/javascript">
    jQuery(document).ready(function($){
      $('.nav > li').hover(function(event) {
          var $this = $(this);
          clearTimeout ($this.data('hoverTimeout'));
          $this.addClass ('open');
      },
      function(event) {
          var $this = $(this);
          $this.data('hoverTimeout', 
              setTimeout(function(){$this.removeClass ('open')}, 100));
      });
    });
  </script>
<?php endif ?>
<!-- //MAIN NAVIGATION -->