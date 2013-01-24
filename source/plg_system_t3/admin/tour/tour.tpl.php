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

<link rel="stylesheet" href="<?php echo T3_ADMIN_URL ?>/admin/tour/css/tour.css" type="text/css" />
<script type="text/javascript" src="<?php echo T3_URL ?>/js/jquery.ckie.js"></script>
<script type="text/javascript" src="<?php echo T3_URL ?>/bootstrap/js/bootstrap-popover.js"></script>
<script type="text/javascript" src="<?php echo T3_ADMIN_URL ?>/admin/tour/js/tour.js"></script>


<div id="t3-tour-overlay" class="hide">
	<div class="t3-tour-overlay"></div>
	<div class="t3-tour-intro">
		<div class="t3-tour-intro-msg">
		    <h1>Welcome to T3!</h1>
		    <p>Are you ready to discover the best framework for Joomla! yet? Click the buttons below to start your travel and having fun!</p>
		</div>
		<div class="t3-tour-intro-action clearfix">
			<button class="t3-tour-starttour btn btn-large btn-primary pull-left"><i class="icon-signin"></i>Start the tour!</button>	
			<button class="t3-tour-endtour btn btn-large pull-right"><i class="icon-ok"></i>End</button>	
		</div>
	</div>	

	<div id="t3-tour-controls" class="t3-tour-controls clearfix">
		<div class="btn-group  pull-left">
			<button class="t3-tour-prevtourstep btn btn-primary"><i class="icon-caret-left"></i>Prev</button>	
			<button class="t3-tour-nexttourstep btn btn-primary">Next<i class="icon-caret-right" style="margin-left: 5px; margin-right: 0;"></i></button>
		</div>
		<button class="t3-tour-endtour btn pull-right"><i class="icon-ok"></i>End</button>	
		<div class="t3-tour-count"><span class="t3-tour-idx"></span>/<span class="t3-tour-total"></span></div>
	</div>
</div>

<div id="t3-tour-quickhelp" class="t3-tour-quickhelp hide">
	<button type="button" class="close" aria-hidden="true">&times;</button>
	<div><?php echo JTexT::_('T3_TOUR_QUICK_HELP') ?></div>
</div>

<script type="text/javascript">
	var T3Tours = {};

	T3Tours.tours = [
		{
			id		: '1',
			element : "#t3-toolbar-recompile",
			position: "bottom",
			highlighter: "", 
			monitor	: "",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_1_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_1_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>
		},
		{
			id		: '2',
			element : "#t3-toolbar-themer",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_2_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_2_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_2')) ?>
		},
		{
			id		: '3',
			element : "#t3_styles_list_chzn",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_3_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_3_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_3')) ?>
		},
		{
			id		: '4',
			element : "#jform_home_chzn",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_4_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_4_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_4')) ?>,
		},
		{
			id		: '5',
			element : "#template-home .updater",
			position: "left",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_5_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_5_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_4')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(0) a').tab ('show')}
		},
				{
			id		: '6',
			element : "#framework-home .updater",
			position: "left",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_6_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_6_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_4')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(0) a').tab ('show')}
		},
		{
			id		: '7',
			element : ".t3-admin-nav ul li:eq(1)",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_7_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_7_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(1) a').tab ('show')}
		},
		{
			id		: '8',
			element : "#jform_params_devmode label:first",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_8_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_8_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '9',
			element : "#jform_params_themermode label:first",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_9_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_9_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '10',
			element : "#jform_params_responsive label:first",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_10_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_10_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '11',
			element : ".t3-admin-nav ul li:eq(2)",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_11_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_11_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(2) a').tab ('show')}
		},
		{
			id		: '12',
			element : "#jform_params_theme_chzn",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_12_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_12_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '13',
			element : "#jform_params_logotype_chzn",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_13_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_13_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '14',
			element : ".t3-admin-nav ul li:eq(3)",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_14_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_14_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(3) a').tab ('show')}
		},
		{
			id		: '15',
			element : "#jform_params_mainlayout_chzn",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_15_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_15_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '16',
			element : ".mode-structure",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_21_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_21_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.mode-structure').trigger ('click')}
		},
				{
			id		: '17',
			element : ".t3-layout-mode-m",
			position: "right",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_20_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_20_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.t3-layout-mode-m').trigger ('click')}
		},
				{
			id		: '18',
			element : ".mode-layout",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_22_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_22_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.mode-layout').trigger ('click')}
		},
		{
			id		: '19',
			element : ".t3-layout-mode-r",
			position: "right",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_23_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_23_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '20',
			element : ".head-search .t3-layout-edit",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_16_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_16_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '21',
			element : ".t3-admin-nav ul li:eq(4)",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_17_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_17_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(4) a').tab ('show')}
		},
		{
			id		: '22',
			element : "#jform_params_mm_enable label:first",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_18_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_18_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '23',
			element : ".t3-admin-nav ul li:eq(5)",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_19_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_19_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(5) a').tab ('show')}
		},
		{
			id		: '24',
			element : "#jform_params_mm_enable label:last",
			position: "right",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_24_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_24_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
		},
		{
			id		: '25',
			element : ".t3-admin-nav ul li:eq(4)",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_25_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_25_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(4) a').tab ('show')}
		},
		{
			id		: '26',
			element : "#jform_params_navigation_trigger_chzn",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_26_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_26_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow	: function() {jQuery('.t3-admin-nav ul li:eq(4) a').tab ('show')}
		},
		{
			id		: '27',
			element : "#jform_params_mm_type_chzn",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_27_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_27_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow : function() {jQuery('#jform_params_mm_enable1').prop('checked', true).trigger('update').trigger('change')}
		},
		{
			id		: '28',
			element : "#megamenu-intro",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_28_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_28_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow : function() {jQuery('#jform_params_mm_enable1').prop('checked', true).trigger('update').trigger('change')}
		},
		{
			id		: '29',
			element : "#jform_params_navigation_type_chzn",
			position: "bottom",
			highlighter: "", 
			monitor	: "mouseover",
			title	: <?php echo json_encode(JText::_('T3_TOUR_GUIDE_29_TITLE')) ?>,
			text    : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_29_CONTENT')) ?>,
			dismiss : <?php echo json_encode(JText::_('T3_TOUR_GUIDE_DISMISS_1')) ?>,
			beforeShow : function() {jQuery('#jform_params_mm_enable1').prop('checked', true).trigger('update').trigger('change')}
		},
	];


	T3Tours.first = {
		tour: ["1", "2", "3", "4", "5", "6", "7", "11", "14", "25", "23"],
		intro: <?php echo json_encode(JText::_('T3_TOUR_INTRO_FIRST')) ?>
	}

	T3Tours.plays = [
		{
			when: function() {return jQuery('.t3-admin-nav ul li:eq(1)').hasClass('active');},
			tour: ["8", "9", "10"],
			/*intro	: <?php echo json_encode(JText::_('T3_TOUR_INTRO_TOUR1')) ?>*/
		},
		{
			when: function() {return jQuery('.t3-admin-nav ul li:eq(2)').hasClass('active');},
			tour: ["12", "13"],
			/*intro	: <?php echo json_encode(JText::_('T3_TOUR_INTRO_TOUR2')) ?>*/
		},
		{
			when: function() {return jQuery('.t3-admin-nav ul li:eq(3)').hasClass('active');},
			tour: ["15", "16", "17", "18", "19"],
			/*intro	: <?php echo json_encode(JText::_('T3_TOUR_INTRO_TOUR3')) ?>*/
		},
		{
			when: function() {return jQuery('.t3-admin-nav ul li:eq(4)').hasClass('active');},
			tour: ["26", "29", "27", "28"],
			/*intro	: <?php echo json_encode(JText::_('T3_TOUR_INTRO_TOUR4')) ?>*/
		},
	];


	// init tours
	jQuery(document).ready(function($) {
		if(!T3Tours.init){
			T3Tours.onShow = function(){
				var fullscreen = $('.t3-fullscreen-full');
				if(fullscreen.length){
					fullscreen.trigger('click');
				}
			};

			$.each(T3Tours.tours, function(idx, tour){
				tour.title = tour.title.replace(/T3_ADMIN_URL/g, T3Admin.t3adminurl);
				tour.text = tour.text.replace(/T3_ADMIN_URL/g, T3Admin.t3adminurl);
			});
			$(document.body).t3tour(T3Tours);
			T3Tours.init = true;
		}

		// integrate with help button
		$('#t3-toolbar-help').on('click', function(){
			if(typeof T3Tours != 'undefined'){
				$(document.body).t3tour('defaultTour');
			}
		});
	});
</script>