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
	if (!$.browser.msie || $.browser.version >= 10) {
		$(document).ready(function(){
			$('#t3-mainnav .nav-collapse').clone().appendTo ($('<div class="t3-mainnav" />').appendTo($('<div id="off-canvas-nav"></div>').appendTo($('body'))));
			$('html').addClass ('off-canvas');
			$('.btn-navbar').click (function(e){
				var $this = $(this);
				if ($this.data('off-canvas') == 'show') {
					$this.data('off-canvas', 'hide');
					$('html').removeClass ('off-canvas-enabled');
				} else {
					$this.data('off-canvas', 'show');
					$('html').addClass ('off-canvas-enabled');
				}
				return false;
			});

			// hide when click on off-canvas-nav
			$('#off-canvas-nav').bind ('touchstart click', function (e) {
				if (e.target == this) {
					var btn = $('.btn-navbar');
					if (btn.data('off-canvas') == 'show') {
						btn.data('off-canvas', 'hide');
						$('html').removeClass ('off-canvas-enabled');
					}
					return false;
				}
			});

		})
	}
}(jQuery);