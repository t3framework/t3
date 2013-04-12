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
			$('.nav > li').hover(function(event) {
				var $this = $(this);

				// add class animate
				//$this.addClass ('animate');
				setTimeout(function(){$this.removeClass ('animate')}, 500);

				clearTimeout ($this.data('hoverTimeout'));
				$this.data('hoverTimeout', 
					setTimeout(function(){$this.addClass ('open')}, 100));
				//$this.addClass ('open');
			},
			function(event) {
				var $this = $(this);
				//$this.addClass ('animate');
				setTimeout(function(){$this.removeClass ('animate')}, 500);
				clearTimeout ($this.data('hoverTimeout'));
				$this.data('hoverTimeout', 
					setTimeout(function(){$this.removeClass ('open')}, 100));
			});
		});
	}
	
}(jQuery);