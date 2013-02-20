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


$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->select('id, title, module, position');
$query->from('#__modules');
$query->where('published = 1');
$query->where('client_id = 0');
$query->order('title');
$db->setQuery($query);
$modules = $db->loadObjectList();
?>

<div id="t3-admin-megamenu" class="hidden t3-admin-megamenu">
  <div class="admin-inline-toolbox clearfix">
    <div class="t3-admin-mm-row clearfix">
      
      <div id="t3-admin-mm-intro" class="pull-left">
        <h3><?php echo JTexT::_('T3_NAVIGATION_MM_TOOLBOX') ?></h3>
        <p><?php echo JTexT::_('T3_NAVIGATION_MM_TOOLBOX_DESC') ?></p>
      </div>
      
      <div id="t3-admin-mm-tb">
        <div id="t3-admin-mm-toolitem" class="admin-toolbox">
          <h3><?php echo JTexT::_('T3_NAVIGATION_MM_ITEM_CONF') ?></h3>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_SUBMENU'), '::', JTexT::_('T3_NAVIGATION_MM_SUBMENU_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_SUBMENU') ?></label>
              <fieldset class="radio btn-group toolitem-sub">
                <input type="radio" id="toggleSub0" class="toolbox-toggle" data-action="toggleSub" name="toggleSub" value="0"/>
                <label for="toggleSub0"><?php echo JTexT::_('JNO') ?></label>
                <input type="radio" id="toggleSub1" class="toolbox-toggle" data-action="toggleSub" name="toggleSub" value="1" checked="checked"/>
                <label for="toggleSub1"><?php echo JTexT::_('JYES') ?></label>
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_GROUP'), '::', JTexT::_('T3_NAVIGATION_MM_GROUP_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_GROUP') ?></label>
              <fieldset class="radio btn-group toolitem-group">
                <input type="radio" id="toggleGroup0" class="toolbox-toggle" data-action="toggleGroup" name="toggleGroup" value="0"/>
                <label for="toggleGroup0"><?php echo JTexT::_('JNO') ?></label>
                <input type="radio" id="toggleGroup1" class="toolbox-toggle" data-action="toggleGroup" name="toggleGroup" value="1" checked="checked"/>
                <label for="toggleGroup1"><?php echo JTexT::_('JYES') ?></label>
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_POSITIONS'), '::', JTexT::_('T3_NAVIGATION_MM_POSITIONS_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_POSITIONS') ?></label>
              <fieldset class="btn-group">
                <a href="" class="btn toolitem-moveleft toolbox-action" data-action="moveItemsLeft" title="<?php echo JTexT::_('T3_NAVIGATION_MM_MOVE_LEFT') ?>"><i class="icon-arrow-left"></i></a>
                <a href="" class="btn toolitem-moveright toolbox-action" data-action="moveItemsRight" title="<?php echo JTexT::_('T3_NAVIGATION_MM_MOVE_RIGHT') ?>"><i class="icon-arrow-right"></i></a>
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_EX_CLASS'), '::', JTexT::_('T3_NAVIGATION_MM_EX_CLASS_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_EX_CLASS') ?></label>
              <fieldset class="">
                <input type="text" class="input-medium toolitem-exclass toolbox-input" name="toolitem-exclass" data-name="class" value="" />
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_ICON'), '::', JTexT::_('T3_NAVIGATION_MM_ICON_DESC') ?>">
                <a href="http://twitter.github.com/bootstrap/base-css.html#icons" target="_blank"><i class="icon-search"></i><?php echo JTexT::_('T3_NAVIGATION_MM_ICON') ?></a>
              </label>
              <fieldset class="">
                <input type="text" class="input-medium toolitem-xicon toolbox-input" name="toolitem-xicon" data-name="xicon" value="" />
              </fieldset>
            </li>
          </ul>
        </div>

        <div id="t3-admin-mm-toolsub" class="admin-toolbox">
          <h3><?php echo JTexT::_('T3_NAVIGATION_MM_SUBMNEU_CONF') ?></h3>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_SUBMNEU_GRID'), '::', JTexT::_('T3_NAVIGATION_MM_SUBMNEU_GRID_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_SUBMNEU_GRID') ?></label>
              <fieldset class="btn-group">
                <a href="" class="btn toolsub-addrow toolbox-action" data-action="addRow">Add Row</a>
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_HIDE_COLLAPSE'), '::', JTexT::_('T3_NAVIGATION_MM_HIDE_COLLAPSE_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_HIDE_COLLAPSE') ?></label>
              <fieldset class="radio btn-group toolsub-hidewhencollapse">
                <input type="radio" id="togglesubHideWhenCollapse0" class="toolbox-toggle" data-action="hideWhenCollapse" name="togglesubHideWhenCollapse" value="0" checked="checked"/>
                <label for="togglesubHideWhenCollapse0"><?php echo JTexT::_('JNO') ?></label>
                <input type="radio" id="togglesubHideWhenCollapse1" class="toolbox-toggle" data-action="hideWhenCollapse" name="togglesubHideWhenCollapse" value="1"/>
                <label for="togglesubHideWhenCollapse1"><?php echo JTexT::_('JYES') ?></label>
              </fieldset>
            </li>
          </ul>                    
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_SUBMNEU_WIDTH_PX'), '::', JTexT::_('T3_NAVIGATION_MM_SUBMNEU_WIDTH_PX_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_SUBMNEU_WIDTH_PX') ?></label>
              <fieldset class="">
                <input type="text" class="toolsub-width toolbox-input input-small" name="toolsub-width" data-name="width" value="" />
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_ALIGN'), '::', JTexT::_('T3_NAVIGATION_MM_ALIGN_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_ALIGN') ?></label>
              <fieldset class="toolsub-alignment">
                <div class="btn-group">
                <a class="btn toolsub-align-left toolbox-action" href="#" data-action="alignment" data-align="left" title="<?php echo JTexT::_('T3_NAVIGATION_MM_ALIGN_LEFT') ?>"><i class="icon-align-left"></i></a>
                <a class="btn toolsub-align-right toolbox-action" href="#" data-action="alignment" data-align="right" title="<?php echo JTexT::_('T3_NAVIGATION_MM_ALIGN_RIGHT') ?>"><i class="icon-align-right"></i></a>
                <a class="btn toolsub-align-center toolbox-action" href="#" data-action="alignment" data-align="center" title="<?php echo JTexT::_('T3_NAVIGATION_MM_ALIGN_CENTER') ?>"><i class="icon-align-center"></i></a>
                <a class="btn toolsub-align-justify toolbox-action" href="#" data-action="alignment" data-align="justify" title="<?php echo JTexT::_('T3_NAVIGATION_MM_ALIGN_JUSTIFY') ?>"><i class="icon-align-justify"></i></a>
                </div>
              </fieldset>
            </li>
          </ul>          
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_EX_CLASS'), '::', JTexT::_('T3_NAVIGATION_MM_EX_CLASS_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_EX_CLASS') ?></label>
              <fieldset class="">
                <input type="text" class="toolsub-exclass toolbox-input input-medium" name="toolsub-exclass" data-name="class" value="" />
              </fieldset>
            </li>
          </ul>
        </div>

        <div id="t3-admin-mm-toolcol" class="admin-toolbox">
          <h3><?php echo JTexT::_('T3_NAVIGATION_MM_COLUMN_CONF') ?></h3>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_SUBMNEU_GRID'), '::', JTexT::_('T3_NAVIGATION_MM_SUBMNEU_GRID_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_SUBMNEU_GRID') ?></label>
              <fieldset class="btn-group">
                <a href="" class="btn toolcol-addcol toolbox-action" data-action="addColumn">Add Column</a>
                <a href="" class="btn toolcol-removecol toolbox-action" data-action="removeColumn">Remove Column</a>
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_HIDE_COLLAPSE'), '::', JTexT::_('T3_NAVIGATION_MM_HIDE_COLLAPSE_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_HIDE_COLLAPSE') ?></label>
              <fieldset class="radio btn-group toolcol-hidewhencollapse">
                <input type="radio" id="toggleHideWhenCollapse0" class="toolbox-toggle" data-action="hideWhenCollapse" name="toggleHideWhenCollapse" value="0" checked="checked"/>
                <label for="toggleHideWhenCollapse0"><?php echo JTexT::_('JNO') ?></label>
                <input type="radio" id="toggleHideWhenCollapse1" class="toolbox-toggle" data-action="hideWhenCollapse" name="toggleHideWhenCollapse" value="1"/>
                <label for="toggleHideWhenCollapse1"><?php echo JTexT::_('JYES') ?></label>
              </fieldset>
            </li>
          </ul>          
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_WIDTH_SPAN'), '::', JTexT::_('T3_NAVIGATION_MM_WIDTH_SPAN_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_WIDTH_SPAN') ?></label>
              <fieldset class="">
                <select class="toolcol-width toolbox-input toolbox-select input-mini" name="toolcol-width" data-name="width">
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
                  <option value="6">6</option>
                  <option value="7">7</option>
                  <option value="8">8</option>
                  <option value="9">9</option>
                  <option value="10">10</option>
                  <option value="11">11</option>
                  <option value="12">12</option>
                </select>
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_MODULE'), '::', JTexT::_('T3_NAVIGATION_MM_MODULE_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_MODULE') ?></label>
              <fieldset class="">
                <select class="toolcol-position toolbox-input toolbox-select input-medium" name="toolcol-position" data-name="position" data-placeholder="<?php echo JTexT::_('T3_NAVIGATION_MM_SELECT_MODULE') ?>">
                  <option value=""></option>
                  <?php
                  foreach ($modules as $module) {
                    echo "<option value=\"{$module->id}\">{$module->title}</option>\n";
                  }
                  ?>
                </select>
              </fieldset>
            </li>
          </ul>
          <ul>
            <li>
              <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_EX_CLASS'), '::', JTexT::_('T3_NAVIGATION_MM_EX_CLASS_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_EX_CLASS') ?></label>
              <fieldset class="">
                <input type="text" class="input-medium toolcol-exclass toolbox-input" name="toolcol-exclass" data-name="class" value="" />
              </fieldset>
            </li>
          </ul>
        </div>    
      </div> 
      
      <div class="toolbox-actions-group">
        <button class="t3-admin-tog-fullscreen toolbox-action toolbox-togglescreen" data-action="toggleScreen" data-iconfull="icon-resize-full" data-iconsmall="icon-resize-small"><i class="icon-resize-full"></i></button>

        <button class="btn btn-success toolbox-action toolbox-saveConfig hide" data-action="saveConfig"><i class="icon-save"></i><?php echo JTexT::_('T3_NAVIGATION_MM_SAVE') ?></button>
        <!--button class="btn btn-danger toolbox-action toolbox-resetConfig"><i class="icon-undo"></i><?php echo JTexT::_('T3_NAVIGATION_MM_RESET') ?></button-->
      </div>

    </div>
  </div>

  <div id="t3-admin-mm-container" class="navbar clearfix"></div> 
</div>
<script type="text/javascript">
  jQuery('#t3-admin-megamenu select').chosen({
    allow_single_deselect: true
  });
</script>