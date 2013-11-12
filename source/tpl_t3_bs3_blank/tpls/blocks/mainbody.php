<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php

/**
 * Mainbody 3 columns, content in center: sidebar1 - content - sidebar2
 */

// layout configuration
$layout_config = json_decode('{
    "two_sidebars": {
      "lg": [],
      "md": [ "col-md-6 col-md-push-3", "col-md-3 col-md-pull-6", "col-md-3" ],
      "sm": [ "col-sm-12", "col-sm-6", "col-sm-6" ],
      "xs": [ "col-xs-12", "col-xs-6", "col-xs-6" ]
    },
    "one_sidebar1": {
      "lg": [],
      "md": [ "col-md-9 pull-right", "col-md-3" ],
      "sm": [ "col-sm-8 pull-right", "col-sm-4" ],
      "xs": [ "col-xs-12", "col-xs-12"]
    },
    "one_sidebar2": {
      "lg": [ ],
      "md": [ "col-md-9", "col-md-3" ],
      "sm": [ "col-sm-8", "col-sm-4" ],
      "xs": [ "col-xs-12", "col-xs-12" ]
    },
    "no_sidebar": {
      "xs" : [ "col-xs-12" ]
    }
  }');

// positions configuration
$sidebar1 = 'sidebar-1';
$sidebar2 = 'sidebar-2';

// detect layout
if ($this->countModules("$sidebar1 and $sidebar2")) {
	$layout = 'two_sidebars';
} elseif ($this->countModules($sidebar1)) {
	$layout = 'one_sidebar1';
} elseif ($this->countModules($sidebar2)) {
	$layout = 'one_sidebar2';
} else {
	$layout = 'no_sidebar';
}

// select the layout
$layout = $layout_config->$layout;
$col = 0;
?>

<div id="t3-mainbody" class="container t3-mainbody">
	<div class="row">

		<!-- MAIN CONTENT -->
		<div id="t3-content" class="t3-content <?php echo $this->getClass($layout, $col++) ?>">
			<?php if($this->hasMessage()) : ?>
			<jdoc:include type="message" />
			<?php endif ?>
			<jdoc:include type="component" />
		</div>
		<!-- //MAIN CONTENT -->

		<?php if ($this->countModules($sidebar1)) : ?>
			<!-- SIDEBAR 1 -->
			<div
				class="t3-sidebar t3-sidebar-1 <?php echo $this->getClass($layout, $col++) ?> <?php $this->_c($sidebar1) ?>">
				<jdoc:include type="modules" name="<?php $this->_p($sidebar1) ?>" style="T3Xhtml" />
			</div>
			<!-- //SIDEBAR 1 -->
		<?php endif ?>

		<?php if ($this->countModules($sidebar2)) : ?>
			<!-- SIDEBAR 2 -->
			<div
				class="t3-sidebar t3-sidebar-2 <?php echo $this->getClass($layout, $col++) ?> <?php $this->_c($sidebar2) ?>">
				<jdoc:include type="modules" name="<?php $this->_p($sidebar2) ?>" style="T3Xhtml" />
			</div>
			<!-- //SIDEBAR 2 -->
		<?php endif ?>

	</div>
</div> 