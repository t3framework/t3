/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         http://t3-framework.org 
 *------------------------------------------------------------------------------
 */

;(function ($) {
	$.fn.equalHeight = function (options){

		//only set min-height if we have more than 1 element
		if(this.length > 1 || (options && options.force)){
			
			var tallest = 0;
			this.each(function() {

				var height = $(this).css({height: '', 'min-height': ''}).height();

				if(height > tallest) {
					tallest = height;
				}
			});

			this.each(function() {
				$(this).css('min-height', tallest);
			});
		}

		return this;
	}
})(jQuery);