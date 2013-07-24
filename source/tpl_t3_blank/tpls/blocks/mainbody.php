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
    "two_sidebars": {
      "default" : [ "span6 offset3" , "span3 offset-9"    , "span3"             ],
      "wide"    : [],
      "xtablet" : [],
      "tablet"  : [ "span12"        , "span6 spanfirst"   , "span6"             ]
    },
    "one_sidebar1": {
      "default" : [ "span9 pull-right"         , "span3"             ],
      "wide"    : [],
      "xtablet" : [ "span8 pull-right"         , "span4"             ],
      "tablet"  : [ "span12"        , "span12 spanfirst"  ]
    },
    "one_sidebar2": {
      "default" : [ "span9"         , "span3"             ],
      "wide"    : [],
      "xtablet" : [ "span8"         , "span4"             ],
      "tablet"  : [ "span12"        , "span12 spanfirst"  ]
    },
    "no_sidebar": {
      "default" : [ "span12" ]
    }
  }');

  // positions configuration
  $sidebar1 = 'sidebar-1';
  $sidebar2 = 'sidebar-2';
  // Detect layout
  if ($this->countModules("$sidebar1 and $sidebar2")) {
    $layout = 'two_sidebars';
  } elseif ($this->countModules($sidebar1)) {
    $layout = 'one_sidebar1';
  } elseif ($this->countModules($sidebar2)) {
    $layout = 'one_sidebar2';
  } else {
    $layout = 'no_sidebar';
  }
  $layout = $layout_config->$layout;

  $col = 0;
?>

<div id="t3-mainbody" class="container t3-mainbody">
  <div class="row">
    
    <!-- MAIN CONTENT -->
    <div id="t3-content" class="t3-content <?php echo $this->getClass($layout, $col) ?>" <?php echo $this->getData ($layout, $col++) ?>>
      <jdoc:include type="message" />
      <jdoc:include type="component" />
    </div>
    <!-- //MAIN CONTENT -->

    <?php if ($this->countModules($sidebar1)) : ?>
    <!-- SIDEBAR 1 -->
    <div class="t3-sidebar t3-sidebar-1 <?php echo $this->getClass($layout, $col) ?><?php $this->_c($sidebar1)?>" <?php echo $this->getData ($layout, $col++) ?>>
      <jdoc:include type="modules" name="<?php $this->_p($sidebar1) ?>" style="T3Xhtml" />
    </div>
    <!-- //SIDEBAR 1 -->
    <?php endif ?>
    
    <?php if ($this->countModules($sidebar2)) : ?>
    <!-- SIDEBAR 2 -->
    <div class="t3-sidebar t3-sidebar-2 <?php echo $this->getClass($layout, $col) ?><?php $this->_c($sidebar2)?>" <?php echo $this->getData ($layout, $col++) ?>>
      <jdoc:include type="modules" name="<?php $this->_p($sidebar2) ?>" style="T3Xhtml" />
    </div>
    <!-- //SIDEBAR 2 -->
    <?php endif ?>

  </div>
</div> 