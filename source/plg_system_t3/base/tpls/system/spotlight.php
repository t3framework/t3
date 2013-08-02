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
	$rowcls = isset($vars['row-fluid']) && $vars['row-fluid'] ? 'row-fluid':'row';
?>
	<!-- SPOTLIGHT -->
	<div class="<?php echo $rowcls ?> t3-spotlight t3-<?php echo $name ?>"<?php echo $spldata ?>>
		<?php foreach ($poss as $i => $pos): ?>
		<div class="span<?php echo $default[$i] ?>">
			<?php if ($this->countModules($pos)) : ?>
				<jdoc:include type="modules" name="<?php echo $pos ?>" data-original="" style="<?php echo $style ?>" />
				<?php else: ?>
				&nbsp;
			<?php endif ?>
		</div>
		<?php endforeach ?>
	</div>
	<!-- SPOTLIGHT -->