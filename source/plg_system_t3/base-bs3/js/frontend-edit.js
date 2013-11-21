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
		});

		$('fieldset.radio')
			.removeClass('btn-group')
			.find('label').removeClass('btn btn-success btn-danger btn-primary');


		$('.radio input[checked=checked]').each(function(){
			$('label[for=' + $(this).attr('id') + ']').addClass('active');
		});

		$('.t3-admin-form').on('update', 'input[type=radio]', function(){
			if(this.checked){
				$(this)
					.closest('.radio')
					.find('label').removeClass('active')
					.filter('[for="' + this.id + '"]')
						.addClass('active');
			}
		});

		
	});
	
}(jQuery);