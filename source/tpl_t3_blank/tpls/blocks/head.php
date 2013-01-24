<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<!-- META FOR IOS & HANDHELD -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<meta name="HandheldFriendly" content="true" />
<meta name="apple-mobile-web-app-capable" content="YES" />
<!-- //META FOR IOS & HANDHELD -->

<!-- SYSTEM CSS -->
<link href="<?php echo JURI::base(true) ?>/templates/system/css/system.css" rel="stylesheet" />
<!-- //SYSTEM CSS -->

<?php
	// Add T3 Basic head
	$this->addHead();
?>

<!-- CUSTOM CSS -->
<link href="<?php echo T3_TEMPLATE_URL ?>/css/custom.css" rel="stylesheet" />
<!-- //CUSTOM CSS -->

<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<!-- for IE6-8 support of media query -->
<script type="text/javascript" src="<?php echo T3_URL ?>/js/respond.min.js"></script>

<!-- You can add Google Analytics here-->