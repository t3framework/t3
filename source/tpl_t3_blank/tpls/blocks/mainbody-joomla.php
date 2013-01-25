<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Mainbody 3 columns, content in center: sidebar1 - content - sidebar2
 */
defined('_JEXEC') or die;
?>
<?php

  // Layout configuration
  $layout_config = json_decode ('{
    "one_sidebar": {
      "default" : [ "span9"         , "span3"            ],
      "wide"    : [],
      "xtablet" : [ "span12"        , "span12 spanfirst" ],
      "tablet"  : [ "span12"        , "span12 spanfirst" ]
    },
    "no_sidebar": {
      "default" : [ "span12" ]
    }
  }');

  // positions configuration
  $sidebar = 'position-7';
 // Detect layout
  if ($this->countModules("$sidebar")) {
    $layout = "one_sidebar";
  } else {
    $layout = "no_sidebar";
  }

  $layout = $layout_config->$layout;

  //
  $col = 0;
?>

<section id="t3-mainbody" class="container t3-mainbody">
  <div class="row">
    
    <!-- MAIN CONTENT -->
    <div id="t3-content" class="t3-content <?php echo $this->getClass($layout, $col) ?>" <?php echo $this->getData ($layout, $col++) ?>>
      <jdoc:include type="message" />
      <jdoc:include type="component" />
    </div>
    <!-- //MAIN CONTENT -->

	<?php if ($this->countModules($sidebar)) : ?>
    <div class="t3-sidebar <?php echo $this->getClass($layout, $col) ?>" <?php echo $this->getData ($layout, $col++) ?>>
      <!-- SIDEBAR -->
      <jdoc:include type="modules" name="<?php $this->_p($sidebar) ?>" style="JAxhtml" />
      <!-- //SIDEBAR -->
    </div>
    <?php endif ?>

  </div>
</section> 