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
 * @credits       Mary Lou - http://tympanus.net/codrops/2013/08/28/transitions-for-off-canvas-navigations/
 *------------------------------------------------------------------------------
 */


defined('_JEXEC') or die;
?>

<?php
  if (!$this->getParam('addon_offcanvas_enable')) return ;
?>
<button class="btn btn-default off-canvas-toggle" type="button" data-nav="#t3-off-canvas" data-effect="<?php echo $this->getParam('addon_offcanvas_effect', 'off-canvas-effect-4') ?>">
  <i class="fa fa-bars"></i>
</button>

<!-- OFF-CANVAS SIDEBAR -->
<div id="t3-off-canvas" class="t3-off-canvas">

  <div class="t3-off-canvas-header">
    <h2 class="t3-off-canvas-header-title">Sidebar</h2>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
  </div>

  <div class="t3-off-canvas-body">
    <jdoc:include type="modules" name="<?php $this->_p('off-canvas') ?>" style="T3Xhtml" />
  </div>

</div>
<!-- //OFF-CANVAS SIDEBAR -->
