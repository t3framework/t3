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

	//detect transform (https://github.com/cubiq/)
	$.support.t3transform = (function () {
		var style = document.createElement('div').style,
		vendors = ['t', 'webkitT', 'MozT', 'msT', 'OT'],
		transform, i = 0, l = vendors.length;

		for ( ; i < l; i++ ) {
			transform = vendors[i] + 'ransform';
			if ( transform in style ) {
				return transform;
			}
		}

		return false;
	})();

	
	var isTouch = 'ontouchstart' in window && !(/hp-tablet/gi).test(navigator.appVersion);
	
	if(isTouch){

		$.fn.touchmenu = function(){
			
			if(!$(document).data('touchmenu')){
				$(document).data('touchmenu', 1).data('touchitems', $()).on('click hidesub', function(){
					$(document).data('touchitems').data('noclick', 0).removeClass('open');
				});

				if (navigator.userAgent.match(/(iPad|iPhone);.*CPU.*OS 6_\d/i)){ 
					$(document.body).children(':not(.nav)').on('click', function(){
						$(document).trigger('hidesub');
					});
				}
			}

			return this.each(function(){
				var	itemsel = $(this).has('.mega').length ? 'li.mega' : 'li.parent, li.dropdown',
					jitems = $(this).find(itemsel),
					reset = function(){
						$(this).data('noclick', 0);
					},
					onTouch = function(e){
						e.stopPropagation();
						
						var jitem = $(this),
							val = !jitem.data('noclick');

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
				
				jitems.on('mouseenter', onTouch).data('noclick', 0).find('[data-toggle="dropdown"]').removeAttr('data-toggle');
				$(this).find('li').on('click', onClick);

				$(document).data('touchitems', $(document).data('touchitems').add(jitems));
			});
		};
	}

	$('html').addClass(isTouch ? 'touch' : 'no-touch');

	$(document).ready(function(){

		//remove conflict of mootools more show/hide function of element
		(function(){
			if(window.MooTools && window.MooTools.More && Element && Element.implement){

				var mthide = Element.prototype.hide,
					mtshow = Element.prototype.show,
					mtslide = Element.prototype.slide;

				Element.implement({
					show: function(args){
						if(arguments.callee && 
							arguments.callee.caller && 
							arguments.callee.caller.toString().indexOf('isPropagationStopped') !== -1){	//jquery mark
							return this;
						}

						return $.isFunction(mtshow) && mtshow.apply(this, args);
					},

					hide: function(){
						if(arguments.callee && 
							arguments.callee.caller && 
							arguments.callee.caller.toString().indexOf('isPropagationStopped') !== -1){	//jquery mark
							return this;
						}

						return $.isFunction(mthide) && mthide.apply(this, arguments);
					},

					slide: function(args){
						if(arguments.callee && 
							arguments.callee.caller && 
							arguments.callee.caller.toString().indexOf('isPropagationStopped') !== -1){	//jquery mark
							return this;
						}

						return $.isFunction(mtslide) && mtslide.apply(this, args);
					}
				})
			}
		})();
		

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

		// overwrite default tooltip/popover behavior (same as Joomla 3.1.5)
		$.fn.tooltip.Constructor.DEFAULTS && ($.fn.tooltip.Constructor.DEFAULTS.html = true);
		$.fn.popover.Constructor.DEFAULTS && ($.fn.popover.Constructor.DEFAULTS.html = true);
		$.fn.tooltip.defaults && ($.fn.tooltip.defaults.html = true);
		$.fn.popover.defaults && ($.fn.popover.defaults.html = true);
	});

	//fix animation for navbar-collapse-fixed-top||bottom
	$(window).load(function(){
		
		if(!$(document.documentElement).hasClass('off-canvas-ready') &&
			($('.navbar-collapse-fixed-top').length ||
			$('.navbar-collapse-fixed-bottom').length)){

			var btn = $('.btn-navbar[data-toggle="collapse"]');
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

					var wheight = (window.innerHeight || $(window).height());

					if(!$.support.transition){
						nav.parent().css('height', !btn.hasClass('collapsed') && btn.data('t3-clicked') ? '' : wheight);
						btn.data('t3-clicked', 1);
					}

					nav
						.addClass('animate')
						.css('max-height', wheight -
							(fixedtop ? (parseFloat(nav.css('top')) || 0) : (parseFloat(nav.css('bottom')) || 0)));
				});
				nav.on('shown hidden', function(){
					nav.removeClass('animate');
				});
			}
		}

	});

}(jQuery);