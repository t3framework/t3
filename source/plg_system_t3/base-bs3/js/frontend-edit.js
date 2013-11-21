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

!function($){
	
	$(document).ready(function(){
		
		//frontend edit radio on/off
		$('.radio label').unbind('click').click(function() {
			var label = $(this),
				input = $('#' + label.attr('for'));

			if (!input.prop('checked')){
				label.addClass('active').siblings().removeClass('active');

				input.prop('checked', true).trigger('change');
			}
		}).addClass(function(){
			return $(this).next().length ? 'off' : 'on'
		});

		//initial state
		$('.radio input[checked=checked]').each(function(){
			$('label[for=' + $(this).attr('id') + ']').addClass('active');
		});
		
	});
	
}(jQuery);