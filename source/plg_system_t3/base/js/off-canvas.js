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
	if (!$.browser.msie || $.browser.version >= 9) {
		$(document).ready(function(){
			var $btn = $('.btn-navbar'),
				$nav = null,
				$fixeditems = null;

			if (!$btn.length) return;

			$nav = $('<div class="t3-mainnav" />').appendTo($('<div id="off-canvas-nav"></div>').appendTo(document.body));
			$($btn.data('target')).clone().appendTo($nav);
			
			$btn.click (function(e){
				if ($(this).data('off-canvas') == 'show') {
					hideNav();
				} else {
					showNav();
				}

				return false;
			});

			posNav = function () {
				var t = $(window).scrollTop();
				if (t < $nav.position().top) $nav.css('top', t);
			};

			bdHideNav = function (e) {
				e.preventDefault();
				hideNav();
				return false;
			};

			showNav = function () {
				$('html').addClass ('off-canvas');

				$nav.css('top', $(window).scrollTop());
				wpfix(1);
				
				setTimeout (function(){
					$btn.data('off-canvas', 'show');
					$('html').addClass ('off-canvas-enabled');
					$(window).bind('scroll touchmove', posNav);

					// hide when click on off-canvas-nav
					$('#off-canvas-nav').bind ('click', function (e) {
						e.stopPropagation();
					});
					
					$('#off-canvas-nav a').bind ('click', hideNav);
					$('body').bind ('click', bdHideNav);
				}, 50);

				setTimeout (function(){
					wpfix(2);
				}, 1000);
			};

			hideNav = function () {				
				$(window).unbind('scroll touchmove', posNav);
				$('#off-canvas-nav').unbind ('click');
				$('#off-canvas-nav a').unbind ('click', hideNav);
				$('body').unbind ('click', bdHideNav);
				
				$('html').removeClass ('off-canvas-enabled');
				$btn.data('off-canvas', 'hide');

				setTimeout (function(){
					$('html').removeClass ('off-canvas');
				}, 600);
			};

			wpfix = function (step) {
				// check if need fixed
				if ($fixeditems == -1) return ;// no need to fix
				if (!$fixeditems) {
					$fixeditems = $('body').children().filter(function(){ return $(this).css('position') === 'fixed' });
					if (!$fixeditems.length) {
						$fixeditems = -1;
						return ;
					}
				}

				if (step==1) {
					$fixeditems.css({'position': 'absolute', 'top': $(window).scrollTop()+'px'});
				} else {
					$fixeditems.css({'position': '', 'top': ''});
				}
			}

		})
	}
}(jQuery);