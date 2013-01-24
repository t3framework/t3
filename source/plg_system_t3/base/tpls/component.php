<?php
/** 
 *------------------------------------------------------------------------------
 * @package   T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license   GNU General Public License; http://www.gnu.org/licenses/gpl.html
 * @author    JoomlArt, JoomlaBamboo 
 *            If you want to be come co-authors of this project, please follow 
 *            our guidelines at http://t3-framework.org/contribute
 *------------------------------------------------------------------------------
 */

defined('_JEXEC') or die;

if(!defined('T3_TPL_COMPONENT')){
  define('T3_TPL_COMPONENT', 1);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

  <head>
    <jdoc:include type="head" />
    <?php $this->loadBlock ('head') ?>  
  </head>

  <body>
    <section id="t3-mainbody" class="container t3-mainbody">
      <div class="row">
        <div id="t3-content" class="t3-content span12">
          <jdoc:include type="component" />    
        </div>
      </div>
    </section>
  </body>

</html>