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

defined('_JEXEC') or die;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title><?php echo JText::_('T3_NAVIGATION_MM_TITLE'); ?></title>
  <link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/admin/bootstrap/css/bootstrap.css" />
  <link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/admin/plugins/chosen/chosen.css" />
  <link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/base/css/megamenu.css" />
  <link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/admin/megamenu/css/megamenu.css" />
  <link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/admin/css/admin.css" />

  <script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/js/jquery-1.8.0.min.js"></script>
  <script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/bootstrap/js/bootstrap.js"></script>
  <script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/js/json2.js"></script>
  <script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/plugins/chosen/chosen.jquery.min.js"></script>
  <script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/includes/depend/js/depend.js"></script>
  <script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/megamenu/js/megamenu.js"></script>

</head>
<body class="bd">
  <div id="wrapper" class="container-main">
    <div class="header">
      <h1><?php echo JText::_('T3_NAVIGATION_MM_TITLE'); ?></h1>
    </div>
    <div class="t3-admin-header clearfix">
      <div class="controls-row">
        <div class="control-group t3-control-group">
          <div class="control-label t3-control-label">
            <label id="menu-type-lbl" for="menu-type" class="hasTip" title="<?php echo JText::_('T3_NAVIGATION_MM_TYPE_LABEL'), '::', JTexT::_('T3_NAVIGATION_MM_TYPE_DESC') ?>"><?php echo JText::_('T3_NAVIGATION_MM_TYPE_LABEL'); ?></label>
          </div>
          <div class="controls t3-controls">
            <select id="menu-type" name="menu-type">
              <?php foreach (self::menus() as $menu) : ?>
                <option value="<?php echo $menu->value ?>" data-language="<?php echo $menu->language?>"><?php echo $menu->text ?></option>
              <?php endforeach ?>
            </select>
          </div>
        </div>
        <div class="control-group t3-control-group">
          <div class="control-label t3-control-label">
            <label id="access-level-lbl" for="access-level" class="hasTip" title="<?php echo JText::_('T3_NAVIGATION_ACL_LABEL'), '::', JTexT::_('T3_NAVIGATION_ACL_DESC') ?>"><?php echo JText::_('T3_NAVIGATION_ACL_LABEL'); ?></label>
          </div>
          <div class="controls t3-controls">
            <?php echo JHtml::_('access.level', 'access-level', '', 'multiple="multiple"', array(), 'access-level') ?>
          </div>
        </div>

        <div class="btn-toolbar">
          <div class="btn-group">
            <button id="t3-admin-mm-save" class="btn btn-success"><i class="icon-save"></i><?php echo JText::_('T3_TOOLBAR_SAVE') ?></button>
          </div>
          <div class="btn-group">
            <button id="t3-admin-mm-close" class="btn"><i class="icon-remove"></i><?php echo JText::_('T3_TOOLBAR_CLOSE') ?></button>
          </div> 
        </div>

      </div>
    </div>


    <div id="t3-admin-megamenu" class="t3-admin-megamenu">
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
                    <a href="http://fortawesome.github.io/Font-Awesome/#icons-web-app" target="_blank"><i class="icon-search"></i><?php echo JTexT::_('T3_NAVIGATION_MM_ICON') ?></a>
                  </label>
                  <fieldset class="">
                    <input type="text" class="input-medium toolitem-xicon toolbox-input" name="toolitem-xicon" data-name="xicon" value="" />
                  </fieldset>
                </li>
              </ul>
              <ul>
                <li>
                  <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_CAPTION'), '::', JTexT::_('T3_NAVIGATION_MM_CAPTION_DESC') ?>">
                    <?php echo JTexT::_('T3_NAVIGATION_MM_CAPTION') ?>
                  </label>
                  <fieldset class="">
                    <input type="text" class="input-large toolitem-caption toolbox-input" name="toolitem-caption" data-name="caption" value="" />
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
                    <a href="" class="btn toolsub-addrow toolbox-action" data-action="addRow"><i class="icon-plus"></i></a>
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
                  <label class="hasTip" title="<?php echo JTexT::_('T3_NAVIGATION_MM_ADD_REMOVE_COLUMN'), '::', JTexT::_('T3_NAVIGATION_MM_ADD_REMOVE_COLUMN_DESC') ?>"><?php echo JTexT::_('T3_NAVIGATION_MM_ADD_REMOVE_COLUMN') ?></label>
                  <fieldset class="btn-group">
                    <a href="" class="btn toolcol-addcol toolbox-action" data-action="addColumn"><i class="icon-plus"></i></a>
                    <a href="" class="btn toolcol-removecol toolbox-action" data-action="removeColumn"><i class="icon-minus"></i></a>
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
                      <?php foreach (T3AdminMegamenu::modules() as $module): ?>
                        <option value="<?php echo $module->id ?>"><?php echo $module->title ?></option>
                      <?php endforeach; ?>
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

          <div class="toolbox-actions-group hidden">
            <button class="t3-admin-tog-fullscreen toolbox-action toolbox-togglescreen" data-action="toggleScreen" data-iconfull="icon-resize-full" data-iconsmall="icon-resize-small"><i class="icon-resize-full"></i></button>

            <button class="btn btn-success toolbox-action toolbox-saveConfig hide" data-action="saveConfig"><i class="icon-save"></i><?php echo JTexT::_('T3_NAVIGATION_MM_SAVE') ?></button>
            <!--button class="btn btn-danger toolbox-action toolbox-resetConfig"><i class="icon-undo"></i><?php echo JTexT::_('T3_NAVIGATION_MM_RESET') ?></button-->
          </div>

        </div>
      </div>

      <div id="t3-admin-mm-container" class="navbar clearfix"></div>
      <div class="ajaxloader"></div>
    </div>
    <div id="ajax-message" class="ajax-message alert">
      <button type="button" class="close">&times;</button>
      <strong>Save success full</strong>
    </div>
  </div>
  <script type="text/javascript">
    //<![CDATA[

    var T3AdminMegamenu = window.T3AdminMegamenu || {};
    T3AdminMegamenu.referer = '<?php echo $referer; ?>';
    T3AdminMegamenu.config = <?php echo $currentconfig ?>;
    T3AdminMegamenu.template = '<?php echo T3_TEMPLATE ?>';

    //Keepalive
    setInterval(function(){
    $.get('index.php');
    }, <?php echo $refreshTime; ?>);

    //]]>
  </script>
</body>
</html>