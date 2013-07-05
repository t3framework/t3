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
	
	if(isTouch){

		$.fn.touchmenu = function(){
			
			if(!$(document).data('touchmenu')){
				$(document).data('touchmenu', 1).data('touchitems', $()).on('click hidesub', function(){
					$(document).removeClass('hoverable')
						.data('touchitems').data('noclick', 0).removeClass('open');
				});

				if (navigator.userAgent.match(/(iPad|iPhone);.*CPU.*OS 6_\d/i)){ 
					$(document.body).children(':not(.nav)').on('click', function(){
						$(document).trigger('hidesub');
					});
				}
			}

			return this.each(function(){	
				var	itemsel = $(this).has('.mega').length ? 'li.mega' : 'li.parent',
					jitems = $(this).find(itemsel),
					reset = function(){
						$(this).data('noclick', 0);
					},
					onTouch = function(e){
						e.stopPropagation();
						
						$(document.body).addClass('hoverable');

						var jitem = $(this),
							val = !jitem.data('noclick');

						if(val){
							var jchild = jitem.children('.dropdown-menu'),
								hasopen = jitem.hasClass('open'),
								style = jchild.prop('style'),
								display = style ? style['display'] : '';

							if(jchild.css('display', 'none').css('display') == 'none'){ //normal or hide when collapse
								jchild.css('display', display);

								//at initial state, test if it is display: none !important, 
								//if true, we will open this link (val = 0)
								if(!hasopen){	
									//add open class, 
									//iphone seem have buggy when we modify display property
									//it does not trigger hover CSS
									$(document).data('touchitems').removeClass('open');
									jitem.addClass('open').parentsUntil('.nav').filter(itemsel).addClass('open');

									val = jchild.css('display') != 'none';
								}

							} else { //always show
								val = 0;
							}

							jchild.css('display', display);
						}

						// reset all
						jitems.data('noclick', 0);
						jitem.data('noclick', val);

						if(val){
							$(this) //reset, sometime the mouseenter does not refire, so we reset to enable click
								.data('rsid', setTimeout($.proxy(reset, this), 500))
								.parent().parentsUntil('.nav').filter(itemsel).addClass('open');							
						}
					},
					onClick = function(e){
						e.stopPropagation();

						if($(this).data('noclick')){
							e.preventDefault();
							jitems.removeClass('open');
							$(this).addClass('open').parentsUntil('.nav').filter(itemsel).addClass('open');
						} else {
							var href = $(this).children('a').attr('href');
							if(href){
								window.location.href = href;
							}
						}
					};
				
				jitems.on('mouseenter', onTouch).data('noclick', 0);
				$(this).find('li').on('click', onClick);

				$(document).data('touchitems', $(document).data('touchitems').add(jitems));
			});
		};
	}

	$('html').addClass(isTouch ? 'touch' : 'no-touch');

	$(document).ready(function(){
		//remove conflict of mootools more show/hide function of element
		if(window.MooTools && window.MooTools.More && Element && Element.implement){
			$('.collapse, .hasTooltip').each(function(){this.show = null; this.hide = null});
		}

		if(isTouch){
			$('ul.nav').has('.dropdown-menu').touchmenu();
		} else {
			$(document.body).on('click', '[data-toggle="dropdown"]' ,function(){
				//if this link has 'open' (second click) class or when we are in collapsed menu and have always-show
				if($(this).parent().hasClass('open') && this.href && this.href != '#' || 
					($('.btn-navbar').is(':visible') && $(this).closest('.always-show').length)){
					window.location.href = this.href;
				}
			});
		}
	});

	//fix animation for navbar-collapse-fixed-top||bottom
	$(window).load(function(){
		if ($.support.transition) {
			if(!$(document.documentElement).hasClass('off-canvas-ready') &&
				($('.navbar-collapse-fixed-top').length ||
					$('.navbar-collapse-fixed-bottom').length)){

				var btn = $('.btn-navbar');
				if (!btn.length){
					return;
				}

				if(btn.data('target')){
					var nav = $(btn.data('target'));
					if(!nav.length){
						return;
					}

					var fixedtop = nav.closest('.navbar-collapse-fixed-top').length;

					btn.on('click', function(){
						nav
							.addClass('animate')
							.css('max-height', (window.innerHeight || $(window).height()) -
								(fixedtop ? (parseFloat(nav.css('top')) || 0) : (parseFloat(nav.css('bottom')) || 0)));
					});
					nav.on('shown hidden', function(){
						nav.removeClass('animate');
					});
				}
			}
		}
	});


}(jQuery);