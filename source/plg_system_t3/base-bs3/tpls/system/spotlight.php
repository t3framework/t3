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

// No direct access
defined('_JEXEC') or die;
?>
<?php
	$style = 'T3Xhtml';
	$name = $vars['name'];
	$poss = $vars['poss'];
	$spldata = $vars['spldata'];
	$default = $vars['default'];
	$optgroup = $vars['optgroup'];
?>
	<!-- SPOTLIGHT -->
	<div class="t3-spotlight t3-<?php echo $name, ' ', T3_BASE_ROW_FLUID_PREFIX ?>" <?php echo $spldata ?>>
		<?php foreach ($poss as $i => $pos): ?>
		<div class="<?php echo T3_BASE_WIDTH_PREFIX, $default[$i] ?>">
			<?php if ($this->countModules($pos)) : ?>
				<jdoc:include type="modules" name="<?php echo $pos ?>" data-original="" data-optgroup="<?php echo $optgroup[$i]; ?>" style="<?php echo $style ?>" />
				<?php else: ?>
				&nbsp;
			<?php endif ?>
		</div>
		<?php endforeach ?>
	</div>
	<!-- SPOTLIGHT -->