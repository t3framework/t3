/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         https://github.com/t3framework/ 
 *------------------------------------------------------------------------------
 */

!function($){
	$(document).ready(function(){
		//remove conflict of mootools more show/hide function of element
		if(window.MooTools && window.MooTools.More && Element && Element.implement){
			$('.collapse').each(function(){this.show = null; this.hide = null});
		}

		$(document.body).on('click', '[data-toggle="dropdown"]' ,function(){
			if(!$(this).parent().hasClass('open') && this.href && this.href != '#'){
				window.location.href = this.href;
			}
		});
	});
}(jQuery);