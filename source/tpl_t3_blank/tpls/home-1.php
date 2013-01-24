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
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

  <head>
    <jdoc:include type="head" />
    <?php $this->loadBlock ('head') ?>  
    <?php $this->addCss('home') ?>
    <?php $this->addCss('home-responsive') ?>
  </head>

  <body>

    <?php $this->loadBlock ('header') ?>
    
    <?php $this->loadBlock ('mainnav') ?>
    
    <?php $this->loadBlock ('mainbody-home-1') ?>
    
    <?php $this->loadBlock ('footer') ?>

  </body>
</html>