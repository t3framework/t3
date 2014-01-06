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

				$('<style type="text/css">' +
						'.t3-megamenu.animate .animating > .mega-dropdown-menu,' +
						'.t3-megamenu.animate.slide .animating > .mega-dropdown-menu > div {' +
							'transition-duration: ' + mm_duration + 'ms !important;' +
							'-webkit-transition-duration: ' + mm_duration + 'ms !important;' +
						'}' +
					'</style>').appendTo ('head');
			}

			var mm_timeout = mm_duration ? 100 + mm_duration : 500,
				mm_rtl = $('html').attr('dir') == 'rtl',
				sb_width = (function () { 
				var parent = $('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo('body'),
					child = parent.children(),
					width = child.innerWidth() - child.height(100).innerWidth();

				parent.remove();

				return width;
			})();

			//lt IE 10
			if(!$.support.transition){
				//it is not support animate
				$('.t3-megamenu').removeClass('animate');
				
				mm_timeout = 100;
			}

			function position_menu(item){

				var sub = item.children('.mega-dropdown-menu'),
					is_show = sub.is(':visible');

				if(!is_show){
					sub.show();
				}

				var offset = item.offset(),
					width = item.outerWidth(),
					screen_width = $(window).width() - sb_width,
					sub_width = sub.outerWidth(),
					level = item.data('level');

				if(!is_show){
					sub.css('display', '');
				}

				//reset custom align
				sub.css({left : '', right : ''});

				if(level == 1){

					var align = item.data('alignsub'),
						align_offset = 0,
						align_delta = 0,
						align_trans = 0;

					if(align == 'justify'){
						return;	//do nothing
					}

					if(!align){
						align = 'left';
					}

					if(align == 'center'){
						align_offset = offset.left + (width /2);

						if(!$.support.t3transform){
							align_trans = -sub_width /2;
							sub.css(mm_rtl ? 'right' : 'left', align_trans + width /2);
						}

					} else {
						align_offset = offset.left + ((align == 'left' && mm_rtl || align == 'right' && !mm_rtl) ? width : 0);
					}
			
					if (mm_rtl) {

						if(align == 'right'){
							if(align_offset + sub_width > screen_width){
								align_delta = screen_width - align_offset - sub_width;
								sub.css('left', align_delta);

								if(screen_width < sub_width){
									sub.css('left', align_delta + sub_width - screen_width);
								}
							}
						} else {
							if(align_offset < (align == 'center' ? sub_width /2 : sub_width)){
								align_delta = align_offset - (align == 'center' ? sub_width /2 : sub_width);
								sub.css('right', align_delta + align_trans);
							}

							if(align_offset + (align == 'center' ? sub_width /2 : 0) - align_delta > screen_width){
								sub.css('right', align_offset + (align == 'center' ? (sub_width + width) /2 : 0) + align_trans - screen_width);
							}
						}

					} else {

						if(align == 'right'){
							if(align_offset < sub_width){
								align_delta = align_offset - sub_width;
								sub.css('right', align_delta);

								if(sub_width > screen_width){
									sub.css('right', sub_width - screen_width + align_delta);
								}
							}
						} else {

							if(align_offset + (align == 'center' ? sub_width /2 : sub_width) > screen_width){
								align_delta = screen_width - align_offset -(align == 'center' ? sub_width /2 : sub_width);
								sub.css('left', align_delta + align_trans);
							}

							if(align_offset - (align == 'center' ? sub_width /2 : 0) + align_delta < 0){
								sub.css('left', (align == 'center' ? (sub_width + width) /2 : 0) + align_trans - align_offset);
							}
						}
					}
				} else {

					if (mm_rtl) {
						if (item.closest('.mega-dropdown-menu').parent().hasClass('mega-align-right')) {

							//should be align to the right as parent
							item.removeClass('mega-align-left').addClass('mega-align-right');

							// check if not able => revert the direction
							if (offset.left + width + sub_width > screen_width) {
								item.removeClass('mega-align-right'); //should we add align left ? it is th default now

								if(offset.left - sub_width < 0){
									sub.css('right', offset.left + width - sub_width);
								}
							}
						} else {
							if (offset.left - sub_width < 0) {
								item.removeClass('mega-align-left').addClass('mega-align-right');

								if(offset.left + width + sub_width > screen_width){
									sub.css('left', screen_width - offset.left - sub_width);
								}
							}
						}
					} else {

						if (item.closest('.mega-dropdown-menu').parent().hasClass('mega-align-right')) {
							//should be align to the right as parent
							item.removeClass('mega-align-left').addClass('mega-align-right');

							// check if not able => revert the direction
							if (offset.left - sub_width < 0) {
								item.removeClass('mega-align-right'); //should we add align left ? it is th default now

								if(offset.left + width + sub_width > screen_width){
									sub.css('left', screen_width - offset.left - sub_width);
								}
							}
						} else {

							if (offset.left + width + sub_width > screen_width) {
								item.removeClass('mega-align-left').addClass('mega-align-right');

								if(offset.left - sub_width < 0){
									sub.css('right', offset.left + width - sub_width);
								}
							}
						}
					}
				}
			}


			// only work with dropdown and mega
			$('.nav').has('.dropdown-menu').children('li').add('li.mega').hover(function(event) {
				var $this = $(this);
				if ($this.hasClass ('mega')) {

					//place menu
					position_menu($this);

					// add class animate
					setTimeout(function(){$this.addClass ('animating')}, 10);

					clearTimeout ($this.data('animatingTimeout'));
					$this.data('animatingTimeout', 
						setTimeout(function(){$this.removeClass ('animating')}, mm_timeout + 50));

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