<?php
/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         http://t3-framework.org 
 *------------------------------------------------------------------------------
 */

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
function modChrome_T3Xhtml($module, &$params, &$attribs)
{ 
	$badge = preg_match ('/badge/', $params->get('moduleclass_sfx'))? '<span class="badge">&nbsp;</span>' : '';
?>
	<div class="t3-module module<?php echo $params->get('moduleclass_sfx'); ?>" id="Mod<?php echo $module->id; ?>">
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


function modChrome_t3tabs($module, $params, $attribs)
{
		$area = isset($attribs['id']) ? (int) $attribs['id'] :'1';
	$area = 'area-'.$area;


	static $modulecount;
	static $modules;



	if ($modulecount < 1) {
		$modulecount = count(JModuleHelper::getModules($attribs['name']));
		$modules = array();
	}


	if ($modulecount == 1) {
		$temp = new stdClass;
		$temp->content = $module->content;
		$temp->title = $module->title;
		$temp->params = $module->params;
		$temp->id = $module->id;
		$modules[] = $temp;

		// list of moduletitles
		echo '<ul class="nav nav-tabs" id="tab'.$temp->id .'">';

		foreach($modules as $rendermodule) {
			echo '<li><a data-toggle="tab" href="#module-'.$rendermodule->id.'" >'.$rendermodule->title.'</a></li>';
		}
		echo '</ul>';
		echo '<div class="tab-content">';
		$counter = 0;
		// modulecontent
		foreach($modules as $rendermodule) {
			$counter ++;

			echo '<div class="tab-pane  fade in" id="module-'.$rendermodule->id.'">';
			echo $rendermodule->content;
			
			echo '</div>';
		}
		echo '</div>';
		echo '<script type="text/javascript">';
  		echo 'jQuery(document).ready(function(){';
    		echo 'jQuery("#tab'.$temp->id.' a:first").tab("show")';
  		echo '});';
		echo '</script>';
		$modulecount--;
	} else {
		$temp = new stdClass;
		$temp->content = $module->content;
		$temp->params = $module->params;
		$temp->title = $module->title;
		$temp->id = $module->id;
		$modules[] = $temp;
		$modulecount--;
	}
}


function modChrome_t3slider($module, &$params, &$attribs)
{
	$badge = preg_match ('/badge/', $params->get('moduleclass_sfx'))?"<span class=\"badge\">&nbsp;</span>\n":"";
	$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
?>

<div class="moduleslide-<?php echo $module->id ?> collapse-trigger collapsed" data-toggle="collapse" data-target="#slidecontent-<?php echo $module->id ?>">
 	<h<?php echo $headerLevel; ?>><span><?php echo $module->title; ?></span></h<?php echo $headerLevel; ?>>
</div>
 
<div id="slidecontent-<?php echo $module->id ?>" class="collapse-<?php echo $module->id ?> in"><?php echo $module->content; ?></div>

	 <script type="text/javascript">;
			jQuery(document).ready(function(){;
				jQuery(".collapse-<?php echo $module->id ?>").collapse({toggle: 1});
			});
	</script>

<?php } 


function modChrome_t3modal($module, &$params, &$attribs)
{
	
	$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
     
	if (!empty ($module->content)) : ?>

		<div class="moduletable <?php echo $params->get('moduleclass_sfx'); ?> modalmodule">
			<div class="t3-module-title">
				<a href="#module<?php echo $module->id ?>" role="button" class="btn" data-toggle="modal">
					<h<?php echo $headerLevel; ?>><span><?php echo $module->title; ?></span></h<?php echo $headerLevel; ?>>
				</a>
			</div>
			<div id="module<?php echo $module->id ?>" class="modal hide fade" aria-hidden="true">
			  <div class="modal-header">
			 	 <h<?php echo $headerLevel; ?>><span><?php echo $module->title; ?></span></h<?php echo $headerLevel; ?>>
			    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				
			</div>
				<div class="t3-module-body">
					<?php echo $module->content; ?>
				</div>
			</div>
		</div>
		
	<?php endif;  
}


function modChrome_popover($module, &$params, &$attribs)
{
	$position = preg_match ('/left/', $params->get('moduleclass_sfx'))?"":"";
	$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
     
	if (!empty ($module->content)) : ?>
		<div class="moduletable <?php echo $params->get('moduleclass_sfx'); ?> popovermodule">
			<a id="popover<?php echo $module->id ?>" href="#" rel="popover" data-placement="right" class="btn">
					<h<?php echo $headerLevel; ?>><span><?php echo $module->title; ?></span></h<?php echo $headerLevel; ?>>
				</a>
				<div id="popover_content_wrapper-<?php echo $module->id ?>" style="display: none">
				  <div><?php echo $module->content; ?></div>
				</div>
			
				<script type="text/javascript">;
						jQuery(document).ready(function(){

							
							jQuery("#popover<?php echo $module->id ?>").popover({
								html: true,
								content: function() {
								      return jQuery('#popover_content_wrapper-<?php echo $module->id ?>').html();
								    }
							}).click(function(e) {
       								 e.preventDefault();
     							});
						});
				</script>
		</div>
	<?php endif;  
}