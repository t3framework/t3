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

	T3::import('admin/layout');
?>

<!-- LAYOUT CONFIGURATION PANEL -->
<div id="t3-admin-layout" class="t3-admin-layout hide">
	<div class="t3-admin-inline-nav clearfix">
		<div class="t3-admin-layout-row-mode clearfix">
			<ul class="t3-admin-layout-modes nav nav-tabs">
				<li class="t3-admin-layout-mode-structure active"><a href="" title="<?php echo JText::_('T3_LAYOUT_MODE_STRUCTURE') ?>"><?php echo JText::_('T3_LAYOUT_MODE_STRUCTURE') ?></a></li>
				<li class="t3-admin-layout-mode-layout"><a href="" title="<?php echo JText::_('T3_LAYOUT_MODE_LAYOUT') ?>"><?php echo JText::_('T3_LAYOUT_MODE_LAYOUT') ?></a></li>
			</ul>
			<button class="t3-admin-layout-reset-all btn pull-right"><i class="icon-undo"></i>  <?php echo JText::_('T3_LAYOUT_RESET_ALL') ?></button>
		</div>
		<div class="t3-admin-layout-row-device clearfix">
			<div class="t3-admin-layout-devices btn-group hide">
				<?php $t3devices = json_decode(T3_BASE_DEVICES, true); ?>
				<?php foreach($t3devices as $device) : ?>
					<button class="btn t3-admin-dv-<?php echo $device ?>" data-device="<?php echo $device ?>" title="<?php echo JText::_('T3_LAYOUT_DVI_' . strtoupper($device)) ?>"><i class="icon-device"></i>  <?php echo JText::_('T3_LAYOUT_DVI_' . strtoupper($device)) ?></button>
				<?php endforeach; ?>
			</div>
			<button class="btn t3-admin-layout-reset-device pull-right hide"><?php echo JText::_('T3_LAYOUT_RESET_PER_DEVICE') ?></button>
			<button class="btn t3-admin-layout-reset-position pull-right"><?php echo JText::_('T3_LAYOUT_RESET_POSITION') ?></button>
			<button class="t3-admin-tog-fullscreen" title="<?php echo JText::_('T3_LAYOUT_TOGG_FULLSCREEN') ?>"><i class="icon-resize-full"></i></button>
		</div>
	</div>
	<div id="t3-admin-layout-container" class="t3-admin-layout-container t3-admin-layout-preview t3-admin-layout-mode-m"></div>
</div>

<!-- POPOVER POSITIONS -->
<div id="t3-admin-layout-tpl-positions" class="popover right hide">
	<div class="arrow"></div>
	<h3 class="popover-title"><?php echo JText::_('T3_LAYOUT_POPOVER_TITLE') ?></h3>
	<div class="popover-content">
		<?php echo T3AdminLayout::getPositions() ?>
		<button class="t3-admin-layout-rmvbtn btn btn-small"><i class="icon-remove"></i>  <?php echo JText::_('T3_LAYOUT_EMPTY_POSITION') ?></button>
		<button class="t3-admin-layout-defbtn btn btn-small btn-success"><i class="icon-ok-circle"></i>  <?php echo JText::_('JDEFAULT') ?></button>
	</div>
</div>

<!-- CLONE BUTTONS -->
<div id="t3-admin-layout-clone-btns">
	<button id="t3-admin-layout-clone-copy" class="btn btn-success"><i class="icon-save"></i>  <?php echo JText::_('T3_LAYOUT_LABEL_SAVE_AS_COPY') ?></button>
	<button id="t3-admin-layout-clone-delete" class="btn"><i class="icon-remove"></i>  <?php echo JText::_('T3_LAYOUT_LABEL_DELETE') ?></button>
</div>

<!-- MODAL CLONE LAYOUT -->
<div id="t3-admin-layout-clone-dlg" class="layout-modal modal fade hide">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><?php echo JText::_('T3_LAYOUT_ASK_ADD_LAYOUT') ?></h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal prompt-block">
			<p><?php echo JText::_('T3_LAYOUT_ASK_ADD_LAYOUT_DESC') ?></p>
      <div class="input-prepend">
        <span class="add-on"><i class="icon-info-sign"></i></span>
        <input type="text" class="input-xlarge" id="t3-admin-layout-cloned-name" />
      </div>
		</form>
		<div class="message-block">
			<p class="msg"><?php echo JText::_('T3_LAYOUT_ASK_DEL_LAYOUT_DESC') ?></p>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn cancel" data-dismiss="modal"><?php echo JText::_('JCANCEL') ?></button>
		<button class="btn btn-danger yes hide"><?php echo JText::_('T3_LAYOUT_LABEL_DELETEIT') ?></button>
		<button class="btn btn-success yes"><?php echo JText::_('T3_LAYOUT_LABEL_CLONEIT') ?></button>
	</div>
</div>
<script type="text/javascript">
	T3AdminLayout = window.T3AdminLayout || {};
	T3AdminLayout.layout = T3AdminLayout.layout || {};
	T3AdminLayout.layout.devices     = <?php echo T3_BASE_DEVICES ?>;
	T3AdminLayout.layout.maxcol      = <?php echo T3_BASE_DV_MAXCOL ?>;
	T3AdminLayout.layout.minspan     = <?php echo T3_BASE_DV_MINWIDTH ?>;
	T3AdminLayout.layout.unitspan    = <?php echo T3_BASE_DV_UNITSPAN ?>;
	T3AdminLayout.layout.dlayout     = '<?php echo T3_BASE_DEFAULT_DEVICE ?>';
	T3AdminLayout.layout.clayout     = '<?php echo T3_BASE_DEFAULT_DEVICE ?>';
	T3AdminLayout.layout.nlayout     = '<?php echo T3_BASE_DEFAULT_DEVICE ?>';
	T3AdminLayout.layout.maxgrid     = <?php echo T3_BASE_MAX_GRID ?>;
	T3AdminLayout.layout.maxcols     = <?php echo T3_BASE_MAX_GRID ?>;
	T3AdminLayout.layout.widthprefix = '<?php echo T3_BASE_WIDTH_PREFIX ?>';
	T3AdminLayout.layout.spanptrn    = '<?php echo T3_BASE_WIDTH_PATTERN ?>';
	T3AdminLayout.layout.hiddenptrn  = '<?php echo T3_BASE_HIDDEN_PATTERN ?>';
	T3AdminLayout.layout.firstptrn   = '<?php echo T3_BASE_FIRST_PATTERN ?>';
	T3AdminLayout.layout.spancls     = new RegExp('<?php echo trim(preg_quote(T3_BASE_WIDTH_REGEX), '/') ?>', 'g');
	T3AdminLayout.layout.responcls   = <?php echo (bool)T3_BASE_RSP_IN_CLASS ? 'true' : 'false' ?>;
</script>