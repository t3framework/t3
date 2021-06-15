<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */
$displayHeader = $this->params->get('displayHeader', '1');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/admin/bootstrap/css/bootstrap.css" />
	<jdoc:include type="head" />
</head>
<body class="admin" data-basepath="<?php echo JURI::root(true); ?>">
<header class="header<?php echo $header_is_light ? ' header-inverse' : ''; ?>">
	<!-- <div class="container-logo">
		<img src="<?php echo $logo; ?>" class="logo" alt="<?php echo $sitename;?>" />
	</div> -->
	<div class="container-title">
		<jdoc:include type="modules" name="title" />		
	</div>
</header>

	<!-- Subheader -->	
<div class="row-fluid">
	<div class="span12">
		[[TOOLBAR]]
		<!--jdoc:include type="modules" name="toolbar" style="no" /-->
	</div>
</div>

<!-- container-fluid -->
<div class="container-fluid container-main">
	<section id="content">
		<!-- Begin Content -->
		<div class="row-fluid">
			<div class="span12">
				<jdoc:include type="message" />
				<jdoc:include type="component" />
			</div>
		</div>
			<!-- End Content -->
	</section>
</div>
<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>
