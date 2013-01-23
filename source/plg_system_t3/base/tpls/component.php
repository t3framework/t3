<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if(!defined('T3V3_TPL_COMPONENT')){
  define('T3V3_TPL_COMPONENT', 1);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

  <head>
    <jdoc:include type="head" />
    <?php $this->loadBlock ('head') ?>  
  </head>

  <body>
    <section id="ja-mainbody" class="container ja-mainbody">
      <div class="row">
        <div id="ja-content" class="ja-content span12">
          <jdoc:include type="component" />    
        </div>
      </div>
    </section>
  </body>

</html>