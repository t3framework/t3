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
	var isTouch = 'ontouchstart' in window && !(/hp-tablet/gi).test(navigator.appVersion);

	if(!isTouch){
		$(document).ready(function($){
			// detect animation duration
			var mm_duration = $('.t3-megamenu').data('duration') || 0;
			if (mm_duration) {
				var style = '.t3-megamenu.animate .mega > .mega-dropdown-menu, .t3-megamenu.animate.slide .mega > .mega-dropdown-menu > div {';
				style += 'transition-duration: ' + mm_duration + 'ms;';
				style += '-webkit-transition-duration: ' + mm_duration + 'ms;';
				style += '-ms-transition-duration: ' + mm_duration + 'ms;';
				style += '-o-transition-duration: ' + mm_duration + 'ms;';
				style += '}';
				$('<style type="text/css">'+style+'</style>').appendTo ('head');
			}

			var mm_timeout = mm_duration ? 100 + mm_duration : 500;

			$('.nav > li, li.mega').hover(function(event) {
				var $this = $(this);
				if ($this.hasClass ('mega')) {
					// add class animate
					$this.addClass ('animating');
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