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
		
		//frontend edit radio on/off - auto convert on-off radio if applicable
		$('fieldset.radio').filter(function(){
			return $(this).find('input').length == 2 && $(this).find('input').filter(function(){
					return $.inArray(this.value + '', ['0', '1']) !== -1;
				}).length == 2;
		}).addClass('t3onoff').removeClass('btn-group');

		//add class on/off
		$('fieldset.t3onoff').find('label').addClass(function(){
			var $this = $(this), $input = $this.prev('input'),
			cls = $this.hasClass('off') || $input.val() == '0' ? 'off' : 'on';
			cls += $input.prop('checked') ? ' active' : '';
			return cls;
		});

		//listen to all
		$('fieldset.radio').find('label').unbind('click').click(function() {
			var label = $(this),
				input = $('#' + label.attr('for'));

			if (!input.prop('checked')){
				label.addClass('active').siblings().removeClass('active');

				input.prop('checked', true).trigger('change');
			}
			if (input.val() == '') {
				label.addClass('active btn-primary');
			} else if (input.val() == 0) {
				label.addClass('active btn-danger');
			} else {
				label.addClass('active btn-success');
			}
		});
		
		$(".btn-group input[checked=checked]").each(function()
		{
			if ($(this).val() == '') {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
			} else if ($(this).val() == 0) {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-danger');
			} else {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-success');
			}
		});

	});
	
}(jQuery);
