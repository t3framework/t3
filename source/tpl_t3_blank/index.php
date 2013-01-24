<?php
// no direct access
defined('_JEXEC') or die;

//check if t3 plugin is existed
if(!defined('T3')){
	throw new Exception(JText::_('T3_MISSING_T3_PLUGIN'));
}

$t3app = T3::getApp($this);

// get configured layout
$layout = $t3app->getLayout();

$t3app->loadLayout ($layout);
