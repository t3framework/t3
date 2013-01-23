<?php


defined('_JEXEC') or die('Restricted access');

/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * eg.  To render a module mod_test in the sliders style, you would use the following include:
 * <jdoc:include type="module" name="test" style="slider" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * three arguments.
 */


/*
 * Default Module Chrome that has sematic markup and has best SEO support
 */
function modChrome_JAxhtml($module, &$params, &$attribs)
{ 
	$badge = preg_match ('/badge/', $params->get('moduleclass_sfx'))?"<span class=\"badge\">&nbsp;</span>\n":"";
?>
	<div class="ja-module module<?php echo $params->get('moduleclass_sfx'); ?>" id="Mod<?php echo $module->id; ?>">
    <div class="module-inner">
      <?php echo $badge; ?>
      <?php if ($module->showtitle != 0) : ?>
      <h3 class="module-title"><span><?php echo $module->title; ?></span></h3>
      <?php endif; ?>
      <div class="module-ct">
      <?php echo $module->content; ?>
      </div>
    </div>
  </div>
	<?php
}
