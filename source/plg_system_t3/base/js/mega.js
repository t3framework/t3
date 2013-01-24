/**
 *$JA#COPYRIGHT$
 */
 

;(function($, undefined) {
	'use strict';
	
	// blank image data-uri bypasses webkit log warning (thx doug jones)
	var blank = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

	$.fn.t3imgload = function(option){
		var opts = $.extend({onload: false}, $.isFunction(option) ? {onload: option} : option),
			jimgs = this.find('img').add(this.filter('img')),
			total = jimgs.length,
			loaded = [],
			onload = function(){
				if(this.src === blank || $.inArray(this, loaded) !== -1){
					return;
				}

				loaded.push(this);

				$.data(this, 't3iload', {src: this.src});
				if (total === loaded.length){
					$.isFunction(opts.onload) && setTimeout(opts.onload);
					jimgs.unbind('.t3iload');
				}
			};

		if (!total){
			$.isFunction(opts.onload) && opts.onload();
		} else {
			jimgs.on('load.t3iload error.t3iload', onload).each(function(i, el){
				var src = el.src,
					cached = $.data(el, 't3iload');

				if(cached && cached.src === src){
					onload.call(el);
					return;
				}

				if(el.complete && el.naturalWidth !== undefined){
					onload.call(el);
					return;
				}

				if(el.readyState || el.complete){
					el.src = blank;
					el.src = src;
				}
			});
		}

		return this;
	};
})(window.$T3 || window.jQuery);

;(function($){
	'use strict';

	var T3MenuI = window.T3MenuI = window.T3MenuI || {};
	T3MenuI.megamenu = [];

	var MegaMenu = function(elm, options){

		this.options = $.extend({}, $.fn.megamenu.defaults, options);
		this.menu = $(elm)[0];

		if (!this.menu){
			return;
		}

		//ignore hidedelay if no animation
		if (!this.options.slide && !this.options.fade){
			this.options.hidedelay = 10;
		}

		$(this.menu).addClass('mm-enable');
		this.childopen = [];
		this.loaded = false;
		this.imgloaded = false;
		
		$(this.menu).t3imgload($.proxy(this.update, this, true));
	};

	MegaMenu.prototype = {
		constructor: MegaMenu,

		detect: function(){
			var jmega = $(this.menu).find('.mega-dropdown-menu:first'),
				rs = true;

			if(jmega){
				rs = jmega.css('position') != 'static';
				if(rs != $(this.menu).hasClass('mm-enable')){
					$(this.menu)[rs ? 'addClass' : 'removeClass']('mm-enable');
				}
			}
			
			return rs;
		},

		update: function (force) {
			if(force){
				this.imgloaded = true;
			}

			if (!this.detect() || !this.imgloaded || this.loaded){
				return;
			}

			this.start();
		},
		
		start: function () {
			//init once
			if (this.loaded){
				return;
			}

			var self = this,
				options = this.options,
				jmenu = $(this.menu),
				jpw = jmenu.parent(),
				jchilds = jmenu.find('.mega-dropdown-menu'),
				odisplay = jchilds.eq(0).css('display');
			
			this.loaded = true;
			this.zindex = 1000;
			this.jitems = jmenu.find('li');

			while (jpw.length) {
				if (jpw.hasClass('navbar')) {
					this.jwrapper = jpw;
					break;
				}
				jpw = jpw.parent();
			}

			jchilds.css('display', 'block');
					
			this.jitems.each(function (idx, li) {
				//link item
				var link = $(li).children('a')[0],
					child = $(li).children('.mega-dropdown-menu')[0],
					level0 = $(li).parent().hasClass('nav'),
					parent = self.getParent(li),
					item = { stimer: null, direction: ((level0 && options.direction == 'up') ? 0 : 1)},
					effect = false,
					stylesOn = {};

				//child content
				if (child) {
					var childwrap = $(child).find('.anim-menu')[0],
						width = $(child).innerWidth(),
						height = $(child).innerWidth(),
						padding = $(child).innerWidth - $(child).width();
				
					//show direction
					if (options.direction == 'up') {
						if (level0) {
							$(child).css('top', -$(child).innerHeight()); //ajust top position
						} else {
							$(child).css('bottom', 0);
						}
					}
				}

				if (child && (options.slide || options.fade)) {

					effect = true;

					if (options.slide) {
						if (level0) {
							stylesOn[item.direction == 1 ? 'margin-top' : 'bottom'] = 0;
						} else {
							stylesOn[window.isRTL ? 'margin-right' : 'margin-left'] = 0;
						}
					}
					if (options.fade) {
						stylesOn['opacity'] = 1;
					}
				}

				if (child && options.action == 'click') {
					$(li).on('click', function (e) {
						e.stopPropagation();

						if ($(this).hasClass('group')) {
							return;
						}

						if (item.status == 'open') {
							if (self.cursorIn(this, e)) {
								self.itemHide(item);
							} else {
								self.hideOthers(this);
							}
						} else {
							self.itemShow(item);
						}
					});
				}

				if (options.action == 'mouseover' || options.action == 'mouseenter') {
					$(li).on('mouseover', function (e) {
						if ($(this).hasClass('group')) {
							return;
						}

						e.stopPropagation();

						clearTimeout(item.stimer);
						clearTimeout(self.atimer);

						self.intent(item, 'open');
						self.itemShow(item);

					}).on('mouseleave', function (e) {
						if ($(this).hasClass('group')) {
							return;
						}

						clearTimeout(item.stimer);

						self.intent(item, 'close');
						if (item.child) {
							item.stimer = setTimeout(function(){self.itemHide(item)}, options.hidedelay);
						} else {
							self.itemHide(item);
						}
					});

					//if has childcontent, don't goto link before open childcontent - fix for touch screen
					if (link && child) {
						$(link).on('click', function (e) {
							return item.clickable;
						});
					}

					//stop if click on menu item - prevent raise event to container => hide all open submenu
					$(li).on('click', function (e) {
						e.stopPropagation()
					});

					if (child) {
						$(child).on('mouseover', function () {
							clearTimeout(item.stimer);
							clearTimeout(self.atimer);

							self.intent(item, 'open');
							self.itemShow(item);
						}).on('mouseleave', function (e) {
							e.stopPropagation();
							e.preventDefault();

							self.intent(item, 'close');
							clearTimeout(item.stimer);

							if (!self.cursorIn(item.el, e)) {
								self.atimer = setTimeout($.proxy(self.hideAlls, self), options.hidedelay);
							}
						})
					}
				}

				//when click on a link - close all open childcontent
				if (link && !child) {
					$(link).on('click', function (e) {
						e.stopPropagation(); //prevent to raise event up
						
						self.hideOthers(null);
						//Remove current class
						$(self.menu).find('.active').removeClass('active');

						//Add current class
						var p = $(li);
						while (p.length) {
							var idata = p.data('item');

							p.addClass('active');
							$(idata.link).addClass('active');
							p = $(idata.parent);
						}
					});
				}

				$.extend(item, {
					el: li,
					parent: parent,
					link: link,
					child: child,
					childwrap: childwrap,
					width: width,
					height: height,
					padding: padding,
					level0: level0,
					effect: effect,
					stylesOn: stylesOn,
					clickable: !(link && child)
				});

				$(li).data('item', item);
			});

			//click on windows will close all submenus
			var container = $('#t3-wrapper');
			if (!container) {
				container = document.body;
			}

			container.on('click', $.proxy(self.hideAlls, self));
			jchilds.css('display', odisplay);
		},

		getParent: function (el) {
			var p = el;
			while ((p = p.parentNode)) {
				if ($.inArray(p, this.jitems) !== -1 && !$(p).hasClass('group')) {
					return p;
				}

				if (!p || p == this.menu) {
					return null;
				}
			}
		},

		intent: function (item, action) {
			item.intent = action;

			while (item.parent && (item = $(item.parent).data('item'))) {
				item.intent = action;
			}
		},

		cursorIn: function (el, event) {
			if (!el || !event) {
				return false;
			}

			var pos = $(el).offset(),
				right = pos.left + $(el).innerWidth(),
				bottom = pos.top + $(el).innerHeight();

			return (event.pageX > pos.left && event.pageX < right && event.pageY > pos.top && event.pageY < bottom);
		},

		itemOver: function (item) {
			var jel = $(item.el);
			jel.addClass('over');

			if (jel.hasClass('haschild')) {
				jel.removeClass('haschild').addClass('haschild-over');
			}

			if (item.link) {
				$(item.link).addClass('over');
			}
		},

		itemOut: function (item) {
			var jel = $(item.el);
			jel.removeClass('over');

			if (jel.hasClass('haschild-over')) {
				jel.removeClass('haschild-over').addClass('haschild');
			}

			if (item.link) {
				$(item.link).removeClass('over');
			}
		},

		itemShow: function (item) {
			if(!$(this.menu).hasClass('mm-enable')){
				return false;
			}
			
			if($.inArray(item, this.childopen) < this.childopen.length -1){
				this.hideOthers(item.el);
			}
			
			if (item.status == 'open') {
				return; //don't need do anything
			}

			//Setup the class
			this.itemOver(item);

			//push to show queue
			if (item.level0) {
				this.childopen.length = 0;
			}

			if (item.child) {
				this.childopen.push(item);
			}

			item.intent = 'open';
			item.status = 'open';

			setTimeout($.proxy(this.enableclick, this, item), 100);

			if (item.child) {
				//reposition the submenu
				this.positionSubmenu(item);

				if (item.effect && !item.stylesOff) {
					item.stylesOff = {};
					if (this.options.slide) {
						if (item.level0) {
							item.stylesOff[item.direction == 1 ? 'margin-top' : 'bottom'] = -item.height;
						} else {
							item.stylesOff[window.isRTL ? 'margin-right' : 'margin-left'] = (item.direction == 1 ? -item.width : item.width);
						}
					}
					if (this.options.fade) {
						item.stylesOff['opacity'] = 0;
					}
					$(item.childwrap).stop(true).css(item.stylesOff);
				}

				$(item.child).css({
					display: 'block',
					zIndex: this.zindex++
				});
			}

			if (!item.effect || !item.child) {
				return;
			}

			$(item.child).css('overflow', 'hidden');

			$(item.childwrap).stop(true).animate(item.stylesOn, {
				duration: this.options.duration,
				complete: $.proxy(this.itemAnimDone, this, item)
			});
		},

		itemHide: function (item) {
			if(!$(this.menu).hasClass('mm-enable')){
				return false;
			}
			
			clearTimeout(item.stimer);
			item.status = 'close';
			item.intent = 'close';
			this.itemOut(item);
			for (var i = this.childopen.length; i--;){
				if (this.childopen[i] === item){
					this.childopen.splice(i, 1);
				}
			}
			
			if (!item.effect && item.child) {
				clearTimeout(item.sid);
				item.sid = setTimeout(function(){
					$(item.child).css('display', 'none');
				}, this.options.hidedelay);
			}

			if (!item.effect || !item.child || $(item.child).css('opacity') == '0') {
				return;
			}

			$(item.child).css('overflow', 'hidden');
			$(item.childwrap).stop(true).animate( 
				this.options.hidestyle == 'fastwhenshow' ? $.extend(item.stylesOff, {
				'opacity': 0
			}) : item.stylesOff, {
				duration: this.options.hidestyle == 'fast' ? 100 : this.options.duration,
				complete: $.proxy(this.itemAnimDone, this, item)
			});
		},

		itemAnimDone: function (item) {
			//hide done
			if (item.status == 'close') {
				//reset duration and enable opacity if not fade
				if (this.options.hidestyle.indexOf('fast') != -1 && !this.options.fade) {
					$(item.childwrap).css('opacity', 1);
				}
				//hide
				$(item.child).css('display', 'none');
				setTimeout($.proxy(this.disableclick, this, item), 100);

				var pitem = item.parent ? $(item.parent).data('item') : null;
				if (pitem && pitem.intent == 'close') {
					this.itemHide(pitem);
				}
			}

			//show done
			if (item.status == 'open') {
				$(item.child).css('overflow', '');
				$(item.childwrap).css('opacity', 1);
				$(item.child).css('display', 'block');
			}
		},

		hideOthers: function (el) {
			$.each(this.childopen, function (idx, item) {
				if (!el || (item.el != el && !item.el.contains(el))) {
					item.intent = 'close';
				}
			});

			if (this.options.slide || this.options.fade) {
				var self = this;
				$.each(this.childopen, function (idx, item) {
					if(item && item.intent == 'close'){
						self.itemHide(item);
					}
				});
			} else {
				var last = this.childopen[this.childopen.length -1];
				if (last && last.intent == 'close') {
					this.itemHide(last);
				}
			}
		},

		hideAlls: function (el) {
			var self = this;
			$.each(this.childopen, function (idx, item) {
				if (!item.effect) {
					self.itemHide(item);
				} else {
					item.intent = 'close';
				}
			});

			if (this.options.slide || this.options.fade) {
				var last = this.childopen[this.childopen.length -1];
				if (last && last.intent == 'close') {
					this.itemHide(last);
				}
			}
		},

		enableclick: function (item) {
			if (item.link && item.child) {
				item.clickable = true;
			}
		},

		disableclick: function (item) {
			item.clickable = false;
		},

		positionSubmenu: function (item) {
			var options = this.options, offsleft, offstop, left, top, stylesOff = {},
				jwnd = $(window),
				jel = $(item.el),
				ioffset = jel.offset(),
				icoord = {
					top: ioffset.top,
					left: ioffset.left,
					right: ioffset.left + jel.innerWidth(),
					bottom: ioffset.top + jel.innerHeight()
				},
				wcoord = {
					top: jwnd.scrollTop(),
					left: jwnd.scrollLeft(),
					width: jwnd.width(),
					height: jwnd.height()
				},
				wpcoord = {
					top: 0,
					left: 0,
					width: wcoord.width,
					height: wcoord.height
				};

			if(this.jwrapper){
				var wpoffset = this.jwrapper.offset(),
					wpcoord = {
						top: wpoffset.top,
						left: wpoffset.left,
						right: wpoffset.left + this.jwrapper.innerWidth(),
						bottom: wpoffset.top + this.jwrapper.innerHeight()
					}
			}
			
			wcoord.top = Math.max(wcoord.top, wpcoord.top);
			wcoord.left = Math.max(wcoord.left, wpcoord.left);
			wcoord.width = Math.min(wcoord.width, wpcoord.width);
			wcoord.height = Math.min(wcoord.height, $(document.body).height());
			wcoord.right = wcoord.left + wcoord.width;
			wcoord.bottom = wcoord.top + wcoord.height;

			if (item.level0) {
				if (window.isRTL) {
					offsleft = Math.max(wcoord.left, icoord.right - item.width - 20);
					left = Math.max(0, offsleft - wcoord.left);
				} else {
					offsleft = Math.max(wcoord.left, Math.min(wcoord.right - item.width, icoord.left));
					left = Math.max(0, Math.min(wcoord.right - item.width, icoord.left) - wcoord.left);
				}
			} else {
				if (window.isRTL) {
					if (item.direction == 1) {
						offsleft = icoord.left - item.width - 20 + options.offset;
						left = -icoord.width - 20;

						if (offsleft < wcoord.left) {
							item.direction = 0;
							offsleft = Math.min(wcoord.right, Math.max(wcoord.left, icoord.right + item.padding - 20 - options.offset));
							left = icoord.width - 20;
							stylesOff['margin-right'] = item.width;
						}
					} else {
						offsleft = icoord.right + item.padding - 20;
						left = icoord.width - 20;

						if (offsleft + item.width > wcoord.right) {
							item.direction = 1;
							offsleft = Math.max(wcoord.left, icoord.left - item.width - 20);
							left = -icoord.width - 20;
							stylesOff['margin-right'] = -item.width;
						}
					}
				} else {

					if (item.direction == 1) {
						offsleft = icoord.right - options.offset;
						left = icoord.width;

						if (offsleft + item.width > wcoord.right) {
							item.direction = 0;
							offsleft = Math.max(wcoord.left, icoord.left - item.width - item.padding + options.offset);
							left = -icoord.width;
							stylesOff['margin-left'] = item.width;
						}
					} else {
						offsleft = icoord.left - item.width - item.padding + options.offset;
						left = -icoord.width;

						if (offsleft < wcoord.left) {
							item.direction = 1;
							offsleft = Math.max(wcoord.left, Math.min(wcoord.right - item.width, icoord.right - options.offset));
							left = icoord.width;
							stylesOff['margin-left'] = -item.width;
						}
					}
				}
			}

			if (options.slide && item.effect && !$.isEmptyObject(stylesOff)) {
				$(item.childwrap).css(stylesOff);
			}

			var oldp = $(item.child).css('display');
			$(item.child).css('display', 'block');
			if ($(item.child).offsetParent().length) {
				left = offsleft - $(item.child).offsetParent().offset().left;
			}

			$(item.child).css({
				'margin-left': 0,
				'left': left,
				'display': oldp
			});
		}
	};

	$.fn.megamenu = function (option) {
		return this.each(function () {
			var jelm = $(this),
				data = jelm.data('megamenu'),
				options = typeof option == 'object' && option;
			
			if (!data) {
				jelm.data('megamenu', (data = new MegaMenu(this, options)));
				T3MenuI.megamenu.push(data);
			} else {
				if (typeof option == 'string' && data[option]){
					data[option]()
				}
			}
		})
	};

	$.fn.megamenu.defaults = {
		slide: 1, 				//enable slide
		duration: 300, 			//slide speed. lower for slower, bigger for faster
		fade: 1,				//Enable fade
		hidedelay: 500,
		direction: 'down',
		action: 'mouseenter', 	//mouseenter or click
		hidestyle: 'normal',
		offset: 5,				
		fixArrow: false			//internal setting, fix for theme has arrow
	};

	$(document).ready(function(){
		
		if(T3MenuI.megamenu || T3MenuI.megamenu.length){
			T3MenuI.mmid = null;
			$(window).on('resize.mm', function(){
				clearTimeout(T3MenuI.mmid);
				T3MenuI.mmid = setTimeout(function(){
					for(var i = 0, il = T3MenuI.megamenu.length; i < il; i++){
						T3MenuI.megamenu[i].update();
					}

				}, /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ? 500 : 100);
			});
		}
	});
})(window.$T3 || window.jQuery);

