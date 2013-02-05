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
 * @Link:         https://github.com/t3framework/ 
 *------------------------------------------------------------------------------
 */

	t3import('admin/layout');
?>

<!-- LAYOUT CONFIGURATION PANEL -->
<div id="t3-admin-layout" class="t3-admin-layout hide">
	<div class="t3-admin-inline-nav clearfix">
		<div class="t3-admin-layout-row-mode clearfix">
			<ul class="t3-admin-layout-modes nav nav-tabs">
				<li class="t3-admin-layout-mode-structure active"><a href="" title="<?php echo JTexT::_('T3_LAYOUT_MODE_STRUCTURE') ?>"><?php echo JTexT::_('T3_LAYOUT_MODE_STRUCTURE') ?></a></li>
				<li class="t3-admin-layout-mode-layout"><a href="" title="<?php echo JTexT::_('T3_LAYOUT_MODE_LAYOUT') ?>"><?php echo JTexT::_('T3_LAYOUT_MODE_LAYOUT') ?></a></li>
			</ul>
			<button class="t3-admin-layout-reset-all btn pull-right"><i class="icon-undo"></i><?php echo JTexT::_('T3_LAYOUT_RESET_ALL') ?></button>
		</div>
		<div class="t3-admin-layout-row-device clearfix">
			<div class="t3-admin-layout-devices btn-group hide">
				<button class="btn t3-admin-dv-wide" data-device="wide" title="<?php echo JTexT::_('T3_LAYOUT_DVI_WIDE') ?>"><i class="icon-desktop"></i><?php echo JTexT::_('T3_LAYOUT_DVI_WIDE') ?></button>
				<button class="btn t3-admin-dv-normal" data-device="normal" title="<?php echo JTexT::_('T3_LAYOUT_DVI_NORMAL') ?>"><i class="icon-laptop"></i><?php echo JTexT::_('T3_LAYOUT_DVI_NORMAL') ?></button>
				<button class="btn t3-admin-dv-xtablet" data-device="xtablet" title="<?php echo JTexT::_('T3_LAYOUT_DVI_XTABLET') ?>"><i class="icon-laptop"></i><?php echo JTexT::_('T3_LAYOUT_DVI_XTABLET') ?></button>
				<button class="btn t3-admin-dv-tablet" data-device="tablet" title="<?php echo JTexT::_('T3_LAYOUT_DVI_TABLET') ?>"><i class="icon-tablet"></i><?php echo JTexT::_('T3_LAYOUT_DVI_TABLET') ?></button>
				<button class="btn t3-admin-dv-mobile" data-device="mobile" title="<?php echo JTexT::_('T3_LAYOUT_DVI_MOBILE') ?>"><i class="icon-mobile-phone"></i><?php echo JTexT::_('T3_LAYOUT_DVI_MOBILE') ?></button>
			</div>
			<button class="btn t3-admin-layout-reset-device pull-right hide"><?php echo JTexT::_('T3_LAYOUT_RESET_PER_DEVICE') ?></button>
			<button class="btn t3-admin-layout-reset-position pull-right"><?php echo JTexT::_('T3_LAYOUT_RESET_POSITION') ?></button>
			<button class="t3-admin-tog-fullscreen" title="<?php echo JTexT::_('T3_LAYOUT_TOGG_FULLSCREEN') ?>"><i class="icon-resize-full"></i></button>
		</div>
	</div>
	<div id="t3-admin-layout-container" class="t3-admin-layout-container t3-admin-layout-preview t3-admin-layout-mode-m"></div>
</div>

<!-- POPOVER POSITIONS -->
<div id="t3-admin-layout-tpl-positions" class="popover right hide">
	<div class="arrow"></div>
	<h3 class="popover-title"><?php echo JTexT::_('T3_LAYOUT_POPOVER_TITLE') ?></h3>
	<div class="popover-content">
		<?php echo T3AdminLayout::getTplPositions() ?>
		<button class="t3-admin-layout-rmvbtn btn btn-small"><i class="icon-remove"></i><?php echo JTexT::_('T3_LAYOUT_EMPTY_POSITION') ?></button>
		<button class="t3-admin-layout-defbtn btn btn-small btn-success"><i class="icon-ok-circle"></i><?php echo JTexT::_('JDEFAULT') ?></button>
	</div>
</div>

<!-- CLONE BUTTONS -->
<div id="t3-admin-layout-clone-btns">
	<button id="t3-admin-layout-clone-copy" class="btn btn-success"><i class="icon-save"></i><?php echo JTexT::_('T3_LAYOUT_LABEL_SAVE_AS_COPY') ?></button>
	<button id="t3-admin-layout-clone-delete" class="btn"><i class="icon-remove"></i><?php echo JTexT::_('T3_LAYOUT_LABEL_DELETE') ?></button>
</div>

<!-- MODAL CLONE LAYOUT -->
<div id="t3-admin-layout-clone-dlg" class="modal fade hide">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><?php echo JTexT::_('T3_LAYOUT_ASK_ADD_LAYOUT') ?></h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal prompt-block">
			<p><?php echo JTexT::_('T3_LAYOUT_ASK_ADD_LAYOUT_DESC') ?></p>
      <div class="input-prepend">
        <span class="add-on"><i class="icon-info-sign"></i></span>
        <input type="text" class="input-xlarge" id="t3-admin-layout-cloned-name" />
      </div>
		</form>
		<div class="message-block">
			<p class="msg"><?php echo JTexT::_('T3_LAYOUT_ASK_DEL_LAYOUT_DESC') ?></p>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn cancel" data-dismiss="modal"><?php echo JTexT::_('JCANCEL') ?></button>
		<button class="btn btn-danger yes hide"><?php echo JTexT::_('T3_LAYOUT_LABEL_DELETEIT') ?></button>
		<button class="btn btn-success yes"><?php echo JTexT::_('T3_LAYOUT_LABEL_CLONEIT') ?></button>
	</div>
</div>