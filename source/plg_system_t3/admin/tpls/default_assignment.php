<?php
/** 
 *-------------------------------------------------------------------------
 * T3 Framework for Joomla!
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2013 JoomlArt.com, Ltd. All Rights Reserved.
 * License - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Authors:  JoomlArt, JoomlaBamboo 
 * If you want to be come co-authors of this project, please follow our 
 * guidelines at http://t3-framework.org/contribute
 * ------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

// Initiasile related data.
require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();
$user = JFactory::getUser();
?>

<div class="t3-assignment clearfix">

<div class="t3-fieldset-desc">
  <?php echo JText::_('T3_MENUS_ASSIGNMENT_DESC'); ?>
</div>

<div class="control-group t3-control-group">

  <div class="control-label t3-control-label">
    <label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>
  </div>

  <div class="controls t3-controls">
    <div class="btn-toolbar">
      <button type="button" class="btn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
        <i class="icon-checkbox-partial"></i> <?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
      </button>
    </div>
    <div id="menu-assignment">
      <ul class="menu-links thumbnails">
        <?php foreach ($menuTypes as &$type) : ?>
            <li class="span3">
              <div class="thumbnail">
              <h5><?php echo $type->title ? $type->title : $type->menutype; ?></h5>
                <?php foreach ($type->links as $link) :?>
                  <label class="checkbox small" for="link<?php echo (int) $link->value;?>" >
                  <input type="checkbox" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php if ($link->template_style_id == $form->getValue('id')):?> checked="checked"<?php endif;?><?php if ($link->checked_out && $link->checked_out != $user->id):?> disabled="disabled"<?php else:?> class="chk-menulink "<?php endif;?> />
                    <?php echo $link->text; ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

</div>

</div>

