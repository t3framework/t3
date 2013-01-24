<?php
	t3import('admin/layout');
?>

<!-- LAYOUT CONFIGURATION PANEL -->
<div id="t3-layout-admin" class="t3-layout-admin hide">
	<div class="t3-inline-nav clearfix">
		<div class="t3-row-mode clearfix">
			<ul class="nav nav-tabs t3-layout-modes">
				<li class="active mode-structure"><a href="" title="<?php echo JTexT::_('T3_LAYOUT_MODE_STRUCTURE') ?>"><?php echo JTexT::_('T3_LAYOUT_MODE_STRUCTURE') ?></a></li>
				<li class="mode-layout"><a href="" title="<?php echo JTexT::_('T3_LAYOUT_MODE_LAYOUT') ?>"><?php echo JTexT::_('T3_LAYOUT_MODE_LAYOUT') ?></a></li>
			</ul>
			<button class="btn t3-reset-all pull-right"><i class="icon-undo"></i><?php echo JTexT::_('T3_LAYOUT_RESET_ALL') ?></button>
		</div>
		<div class="t3-row-device clearfix">
			<div class="btn-group t3-layout-devices hide">
				<button class="btn t3-dv-wide" data-device="wide" title="<?php echo JTexT::_('T3_LAYOUT_DVI_WIDE') ?>"><i class="icon-desktop"></i><?php echo JTexT::_('T3_LAYOUT_DVI_WIDE') ?></button>
				<button class="btn t3-dv-normal" data-device="normal" title="<?php echo JTexT::_('T3_LAYOUT_DVI_NORMAL') ?>"><i class="icon-laptop"></i><?php echo JTexT::_('T3_LAYOUT_DVI_NORMAL') ?></button>
				<button class="btn t3-dv-xtablet" data-device="xtablet" title="<?php echo JTexT::_('T3_LAYOUT_DVI_XTABLET') ?>"><i class="icon-laptop"></i><?php echo JTexT::_('T3_LAYOUT_DVI_XTABLET') ?></button>
				<button class="btn t3-dv-tablet" data-device="tablet" title="<?php echo JTexT::_('T3_LAYOUT_DVI_TABLET') ?>"><i class="icon-tablet"></i><?php echo JTexT::_('T3_LAYOUT_DVI_TABLET') ?></button>
				<button class="btn t3-dv-mobile" data-device="mobile" title="<?php echo JTexT::_('T3_LAYOUT_DVI_MOBILE') ?>"><i class="icon-mobile-phone"></i><?php echo JTexT::_('T3_LAYOUT_DVI_MOBILE') ?></button>
			</div>
			<button class="btn t3-reset-device pull-right hide"><?php echo JTexT::_('T3_LAYOUT_RESET_PER_DEVICE') ?></button>
			<button class="btn t3-reset-position pull-right"><?php echo JTexT::_('T3_LAYOUT_RESET_POSITION') ?></button>
			<button class="t3-tog-fullscreen" title="<?php echo JTexT::_('T3_LAYOUT_TOGG_FULLSCREEN') ?>"><i class="icon-resize-full"></i></button>
		</div>
	</div>
	<div id="t3-layout-cont" class="t3-layout-cont layout-custom t3-layout-mode-m"></div>
</div>

<!-- POPOVER POSITIONS -->
<div id="t3-layout-tpl-positions" class="popover right hide">
	<div class="arrow"></div>
	<h3 class="popover-title"><?php echo JTexT::_('T3_LAYOUT_POPOVER_TITLE') ?></h3>
	<div class="popover-content">
		<?php echo T3AdminLayout::getTplPositions() ?>
		<button class="btn btn-small t3-chzn-empty"><i class="icon-remove"></i><?php echo JTexT::_('T3_LAYOUT_EMPTY_POSITION') ?></button>
		<button class="btn btn-small btn-success t3-chzn-default"><i class="icon-ok-circle"></i><?php echo JTexT::_('JDEFAULT') ?></button>
	</div>
</div>

<!-- CLONE BUTTONS -->
<div id="t3-layout-clone-btns">
	<button id="t3-layout-clone-copy" class="btn btn-success"><i class="icon-save"></i><?php echo JTexT::_('T3_LAYOUT_LABEL_SAVE_AS_COPY') ?></button>
	<button id="t3-layout-clone-delete" class="btn"><i class="icon-remove"></i><?php echo JTexT::_('T3_LAYOUT_LABEL_DELETE') ?></button>
</div>

<!-- MODAL CLONE LAYOUT -->
<div id="t3-layout-clone-dlg" class="modal fade hide">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3><?php echo JTexT::_('T3_LAYOUT_ASK_ADD_LAYOUT') ?></h3>
	</div>
	<div class="modal-body">
		<form class="form-horizontal prompt-block">
			<p><?php echo JTexT::_('T3_LAYOUT_ASK_ADD_LAYOUT_DESC') ?></p>
      <div class="input-prepend">
        <span class="add-on"><i class="icon-info-sign"></i></span>
        <input type="text" class="input-xlarge" id="t3-layout-cloned-name" />
      </div>
		</form>
		<div class="message-block">
			<p class="msg"><?php echo JTexT::_('T3_LAYOUT_ASK_DEL_LAYOUT_DESC') ?></p>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn cancel" data-dismiss="modal"><?php echo JTexT::_('JCANCEL') ?></button>
		<button class="btn yes btn-danger hide" id="t3-layout-clone-btn"><?php echo JTexT::_('T3_LAYOUT_LABEL_DELETEIT') ?></button>
		<button class="btn yes btn-success" id="t3-layout-clone-btn"><?php echo JTexT::_('T3_LAYOUT_LABEL_CLONEIT') ?></button>
	</div>
</div>