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
 * Mainbody 3 columns, content in left, mast-col on top of 2 sidebars: content - sidebar1 - sidebar2
 */

// Layout configuration
$layout_config = json_decode('{
    "two_sidebars": {
      "lg": [],
      "md": [ "col-md-6", "col-md-6", "col-md-6", "col-md-6" ],
      "sm": [ "col-sm-8", "col-sm-4", "col-sm-12", "col-sm-12" ],
      "xs": [ "col-xs-12", "col-xs-12", "col-xs-6", "col-xs-6" ]
    },
    "one_sidebar": {
      "lg": [],
      "md": [ "col-md-9", "col-md-3", "col-md-12" ],
      "sm": [ "col-sm-8", "col-sm-4", "col-sm-12" ],
      "xs": [ "col-xs-12", "col-xs-12", "col-xs-12" ]
    },
    "no_sidebar": {
      "xs" : [ "col-xs-12" ]
    }
  }');

// positions configuration
$mastcol  = 'mast-col';
$sidebar1 = 'sidebar-1';
$sidebar2 = 'sidebar-2';

// Detect layout
if ($this->countModules($mastcol) or $this->countModules("$sidebar1 and $sidebar2")) {
	$layout = "two_sidebars";
} elseif ($this->countModules("$sidebar1 or $sidebar2")) {
	$layout = "one_sidebar";
} else {
	$layout = "no_sidebar";
}

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

		<?php if ($this->countModules("$sidebar1 or $sidebar2 or $mastcol")) : ?>
			<div
				class="t3-sidebar <?php echo $this->getClass($layout, $col++) ?>">
				<?php if ($this->countModules($mastcol)) : ?>
					<!-- MASSCOL 1 -->
					<div class="t3-mastcol t3-mastcol-1 <?php $this->_c($mastcol) ?>">
						<jdoc:include type="modules" name="<?php $this->_p($mastcol) ?>" style="T3Xhtml" />
					</div>
					<!-- //MASSCOL 1 -->
				<?php endif ?>

				<?php if ($this->countModules("$sidebar1 or $sidebar2")) : ?>
					<div class="row">
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
				<?php endif ?>
			</div>
		<?php endif ?>
	</div>
</div> 