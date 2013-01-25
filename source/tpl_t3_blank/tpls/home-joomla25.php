<?php
/** 
 *------------------------------------------------------------------------------
 * @package	  T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license	  GNU General Public License; http://www.gnu.org/licenses/gpl.html
 * @author		JoomlArt, JoomlaBamboo 
 * 			      If you want to be come co-authors of this project, please follow 
 * 			      our guidelines at http://t3-framework.org/contribute
 *------------------------------------------------------------------------------
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

    <?php $this->loadBlock ('header-joomla') ?>
    
    <?php $this->loadBlock ('mainnav-joomla') ?>
	
	<?php $this->loadBlock ('navhelper-joomla25') ?>
    
    <?php $this->loadBlock ('mainbody-joomla') ?>
    
    <?php $this->loadBlock ('footer-joomla25') ?>

  </body>
</html>