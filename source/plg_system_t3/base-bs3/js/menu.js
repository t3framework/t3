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
	var has_touch = 'ontouchstart' in window;

	if(!has_touch){
		$(document).ready(function($){
			// detect animation duration
			var mm_duration = $('.t3-megamenu').data('duration') || 0;
			if (mm_duration) {

				var style = '';
				style += '.t3-megamenu.animate .mega > .mega-dropdown-menu, .t3-megamenu.animate.slide .mega > .mega-dropdown-menu > div {'
				style += 'transition-duration: ' + mm_duration + 'ms;';
				style += '-webkit-transition-duration: ' + mm_duration + 'ms;';
				style += '-ms-transition-duration: ' + mm_duration + 'ms;';
				style += '-o-transition-duration: ' + mm_duration + 'ms;';
				style += '}';

				$('<style type="text/css">'+style+'</style>').appendTo ('head');
			}

			var mm_timeout = mm_duration ? 100 + mm_duration : 500;
			var mm_rtl = $('html').attr('dir') == 'rtl';

			function position_menu(item){

				var sub = item.children('.mega-dropdown-menu'),
					is_show = sub.is(':visible');

				if(!is_show){
					sub.show();
				}

				var offset = item.offset(),
					width = item.outerWidth(),
					screen_width = $(window).width(),
					sub_width = sub.outerWidth(),
					level = item.data('level');

				if(!is_show){
					sub.css('display', '');
				}

				if(level == 1){

					var align = item.data('alignsub'),
						align_offset = 0;

					if(!align){
						align = mm_rtl ? 'right' : 'left';
					}

					if(align == 'left'){
						align_offset = offset.left;

					} else if(align == 'center'){
						align_offset = offset.left + (width - sub_width) / 2;
					} else if(align == 'right'){
						align_offset = offset.left + width - sub_width;
					}
				}

				if (level == 1) {
					if ((mm_rtl && align != 'right') || (!mm_rtl && align == 'right')) {

						if(align_offset < 0){
							align_offset = align_offset + (align == 'center' ? width / 2 : 0);
							sub.css('right', align_offset);
						}

						if(align_offset + sub_width > screen_width){
							sub.css('right', align_offset + sub_width - screen_width + (align == 'center' ? width / 2 : 0));
						}
					} else {

						if(align_offset + sub_width > screen_width){
							align_offset = screen_width - align_offset - sub_width + (align == 'center' ? width / 2 : 0);
							sub.css('left', align_offset);
						}

						if(align_offset < 0){
							sub.css('left', -align_offset + (align == 'center' ? width / 2 : 0));
						}
					}
				} else {

					//reset custom align
					sub.css({left : '', right : ''});

					if (mm_rtl) {
						if (item.closest('.mega-dropdown-menu').parent().hasClass('mega-align-left')) {

							//should be align to the right as parent
							item.removeClass('mega-align-right').addClass('mega-align-left');

							// check if not able => revert the direction
							if (offset.left + width + sub_width > screen_width) {
								item.removeClass('mega-align-left'); //should we add align left ? it is th default now

								if(offset.left + width - sub_width > 0){
									sub.css('right', sub_width - offset.left - width);
								}
							}
						} else {
							if (offset.left + width - sub_width < 0) {
								item.removeClass('mega-align-right').addClass('mega-align-left');

								if(offset.left + sub_width > screen_width){
									sub.css('left', screen_width - offset.left - sub_width);
								}
							}
						}
					} else {

						if (item.closest('.mega-dropdown-menu').parent().hasClass('mega-align-right')) {

							//should be align to the right as parent
							item.removeClass('mega-align-left').addClass('mega-align-right');

							// check if not able => revert the direction
							if (offset.left + width - sub_width < 0) {
								item.removeClass('mega-align-right'); //should we add align left ? it is th default now

								if(offset.left + sub_width > screen_width){
									sub.css('left', screen_width - sub_width - offset.left);
								}
							}
						} else {

							if (offset.left + sub_width > screen_width) {
								item.removeClass('mega-align-left').addClass('mega-align-right');

								if(offset.left + width - sub_width < 0){
									sub.css('right', sub_width - offset.left - width);
								}
							}
						}
					}
				}

			}

			$('.nav > li, li.mega').hover(function(event) {
				var $this = $(this);
				if ($this.hasClass ('mega')) {

					//place menu
					position_menu($this);

					// add class animate
					setTimeout(function(){$this.addClass ('animating');})

					clearTimeout ($this.data('animatingTimeout'));
					$this.data('animatingTimeout', 
						setTimeout(function(){$this.removeClass ('animating')}, mm_timeout));

					clearTimeout ($this.data('hoverTimeout'));
					$this.data('hoverTimeout', 
						setTimeout(function(){$this.addClass ('open')}, 100));
				} else {
					clearTimeout ($this.data('hoverTimeout'));
					$this.data('hoverTimeout', 
						setTimeout(function(){$this.addClass ('open')}, 100));
				}
			},
			function(event) {
				var $this = $(this);
				if ($this.hasClass ('mega')) {
					$this.addClass ('animating');
					clearTimeout ($this.data('animatingTimeout'));
					$this.data('animatingTimeout', 
						setTimeout(function(){$this.removeClass ('animating')}, mm_timeout));
					clearTimeout ($this.data('hoverTimeout'));
					$this.data('hoverTimeout', 
						setTimeout(function(){$this.removeClass ('open')}, 100));
				} else {
					clearTimeout ($this.data('hoverTimeout'));
					$this.data('hoverTimeout', 
						setTimeout(function(){$this.removeClass ('open')}, 100));
				}
			});
		});

	}
	
}(jQuery);