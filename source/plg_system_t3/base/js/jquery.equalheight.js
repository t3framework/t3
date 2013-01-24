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

;(function ($) {
	$.fn.equalHeight = function (options){
		var tallest = 0;
		$(this).each(function() {
			$(this).css({height:"", "min-height":""});
			var thisHeight = $(this).height();
			if(thisHeight > tallest) {
				tallest = thisHeight;
			}
		});

		$(this).each(function() {
			$(this).css( "min-height", tallest );
		});
	}
})(window.$T3 || window.jQuery);