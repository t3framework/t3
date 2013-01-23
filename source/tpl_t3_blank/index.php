<?php
// no direct access
defined('_JEXEC') or die;

//check if t3 plugin is existed
if(!defined('T3V3')){
	throw new Exception(JText::_('T3V3_MISSING_T3V3_PLUGIN'));
}

$t3v3 = T3V3::getApp($this);

// get configured layout
$layout = $t3v3->getLayout();

$t3v3->loadLayout ($layout);
