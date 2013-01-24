!function ($) {
	var Tour = function(option){
		// JOOM: all tour elements
		var options = $.extend({}, $.fn.t3tour.defaults, option);
		if(!options.tours.length){
			return false;
		}

		this.activeTour = null;
		this.activated = false;

		this.options = options;
		this.parse ();
		this.bind();

		this.firstShow();

		return this;
	};

	Tour.prototype = {
		parse: function () {
			this.tours = {};
			for (i=0; i < this.options.tours.length; i++) {
				var tip = this.options.tours[i];
				this.tours[tip.id] = tip;
				if (tip.monitor && 0) {
					// bind the context tip
					this.bindContextTip (tip);
				}
			}
		},		
		/*
		we can restart or stop the tour,
		and also navigate through the steps
		**/
		startTour: function(){
			// add class activated to control
			// $('#t3-tour-overlay').addClass ('t3-tour-activated');
			this.activated = true;
			$('.t3-tour-intro').hide();
			this.nextStep();
		},
		
		/* 
			find active tour for current context
		*/
		activateTour: function (firstTour) {
			var i = 0,
				activeTour = null,
				activeIntro = '';
			if (!firstTour) {	
				for (i=0; i < this.options.plays.length; i++) {
					if (this.options.plays[i].when()) {
						activeTour = this.options.plays[i].tour;
						if (this.options.plays[i].intro !== undefined) activeIntro = this.options.plays[i].intro;
						break;
					}
				}
			}
			if (!activeTour || !activeTour.length) {
				activeTour = this.options.first.tour;
				activeIntro = this.options.first.intro;
			}
			this.activeTour = [];
			var j = 0;
			for (i=0; i < activeTour.length; i++) {
				if (this.tours[activeTour[i]] !== undefined) this.activeTour[j++] = this.tours[activeTour[i]];
			}
			this.total_steps = this.activeTour.length;
			if (!this.total_steps) return false;

			this.step = 0;
			this.currentTip = null;
			this.activeIntro = activeIntro;
			$('#t3-tour-controls .t3-tour-idx').text (this.step);
			$('#t3-tour-controls .t3-tour-total').text (this.total_steps);			
			return true;
		},

		nextStep: function(){
			this.hideTip ();
			if (!this.activeTour) return;
			if(this.step >= this.total_steps){
				// endtour;
				this.endTour ();
				return false;
			}
			++this.step;
			this.direction = 1;

			this.showTip();
		},
		
		prevStep: function(){
			this.hideTip ();
			if(this.step <= 1){
				this.endTour ();
				return false;
			}
			--this.step;
			this.direction = -1;
			this.showTip();
		},
		
		endTour: function(){
			this.hideTip ();
			this.hideControls();
			this.activeTour = null;
			this.activated = false;			
		},
		
		restartTour: function(){
			this.step = 0;
			this.nextStep();
		},
		
		showTip: function(atip){
			this.actionStatus ();

			this.currentTip = atip === undefined ? this.activeTour[this.step-1] : atip;

			if (this.currentTip.beforeShow !== undefined && $.isFunction(this.currentTip.beforeShow)){
				this.currentTip.beforeShow.apply(this);
			}

			var tip = $(this.currentTip.element);
			if (!tip.length) {
				// show next tip
				if (this.direction == 1) this.nextStep ();
				else this.prevStep ();
				return ;
			}

			tip.popover ({
				html: true,
				placement: this.currentTip.position,
				trigger: 'manual',
				template: '<div class="popover t3-tour-popover"><div class="arrow"></div><div class="popover-inner">'
							+ '<h3 class="popover-title"></h3>'
							+ '<div class="popover-content"><div></div></div>'
							+ '<div class="popover-controls"></div>'
							+ '</div></div>',
				title: this.currentTip.title,
				content: this.currentTip.text
			});
			tip.popover ('show');

			// add active/highlight class
			if (this.currentTip.highlighter) $(this.currentTip.highlighter).addClass ('t3-tour-hilite');
			tip.addClass ('t3-tour-active t3-tour-hilite')

			if ($.isFunction(this.currentTip.afterShow)){
				this.currentTip.afterShow.apply(this);
			}

			// controls
			if ($('.popover-controls').length) $('.popover-controls').html('').append($('#t3-tour-controls'));
			if (atip !== undefined) {
				$('.popover-controls').addClass ('t3-tour-single-tip');
			}

			this.focusTip();
		},

		focusTip: function(){
			// scroll to target
			$('html, body').stop(true);

			setTimeout(function(){
				var tipover = $('.t3-tour-popover');
			
				tipover.t3imgload(function(){
					if(tipover.offset().top < $(window).scrollTop() || (tipover.offset().top + tipover.outerHeight(true)) > ($(window).scrollTop() + $(window).height())){
						$('html, body').animate({
							scrollTop: Math.max(0, tipover.offset().top - ($(window).height() - tipover.outerHeight(true))/ 2)
						});
					}
				});
			}, 160);
		},

		hideTip: function () {
			// hide current tips
			$('#t3-tour-controls').appendTo ($('body'));
			if (this.currentTip) {
				var tip = $(this.currentTip.element)
				tip.popover('destroy');
				if (this.currentTip.highlighter) $(this.currentTip.highlighter).removeClass ('t3-tour-hilite');
				tip.removeClass ('t3-tour-active t3-tour-hilite');
				this.currentTip = null;
			}

			if ($(document.body).data ('t3-tour-contextTip')) {
				var tip = $(document.body).data ('t3-tour-contextTip');
				$(document.body).data ('t3-tour-contextTip', null);
				this.unbindContextTip (tip);
			}
		},

		actionStatus: function () {
			if (this.step <= 1) $('.t3-tour-prevtourstep').addClass ('disabled'); else $('.t3-tour-prevtourstep').removeClass ('disabled');
			if (this.step >= this.total_steps) $('.t3-tour-nexttourstep').addClass ('disabled'); else $('.t3-tour-nexttourstep').removeClass ('disabled');
			$('#t3-tour-controls .t3-tour-idx').text (this.step);
		},

		bind: function(){
			if(Tour.isbind){
				return false;
			}

			Tour.isbind = true;

			var self = this;
			$(document.body).on('click', '.t3-tour-starttour, .t3-tour-canceltour, .t3-tour-endtour, .t3-tour-restarttour, .t3-tour-nexttourstep, .t3-tour-prevtourstep', function(){
				var $this = $(this);
				if ($this.hasClass ('disabled')) return;
				
				if ($this.hasClass ('t3-tour-starttour')) {
					self.startTour();
				}

				if ($this.hasClass ('t3-tour-endtour')) {
					self.endTour();
				}

				if ($this.hasClass ('t3-tour-restarttour')) {
					self.restartTour();
				}

				if ($this.hasClass ('t3-tour-nexttourstep')) {
					self.nextStep();
				}

				if ($this.hasClass ('t3-tour-prevtourstep')) {
					self.prevStep();
				}
			});

			$(document).keydown(function (e) {
				if (!self.activeTour) return true;
				if (e.keyCode == 27) { 
			       self.endTour();
			       return false;
			    }
				if (e.keyCode == 13) { 
			       self.startTour();
			       return false;
			    }

			    if (!self.activated) return true;

				if (e.keyCode == 37) { 
			       self.prevStep();
			       return false;
			    }
				if (e.keyCode == 39) { 
			       self.nextStep();
			       return false;
			    }

			});

			// add help button to tab description
			$('.t3-fieldset-desc').append ('<span class="t3-tour-help"><i class="icon-question-sign"></i></span>');
			$('.t3-tour-help').click(function(){
				self.showControls();
			})
		},
		
		showControls: function(firstTour){
			if(this.options.onShow && $.isFunction(this.options.onShow)){
				this.options.onShow(this);
			}

			if (this.moveControls === undefined) {
				this.moveControls = true;
				$('#t3-tour-overlay').appendTo ($('body'));
				// $('#t3-tour-controls').appendTo ($('body'));
			}
			if (!this.activateTour(firstTour)) return;

			if (this.activeIntro) {
				$('.t3-tour-intro').show().children('.t3-tour-intro-msg').html (this.activeIntro);
			} else {
				this.startTour();
			}
			// $('#t3-tour-controls').show();
			$('#t3-tour-overlay').show();
		},
		
		hideControls: function(){
			// $('#t3-tour-controls').hide();
			// $('#t3-tour-controls').removeClass ('t3-tour-activated');
			$('#t3-tour-overlay').hide();
		},

		bindContextTip: function (tip) {
			$(tip.element).on (tip.monitor, function (event) {
				event.stopPropagation();
				if ($(document.body).data ('t3-tour-contextTip')) return;
				$(document.body).data('t3tour').showTip (tip);
				$(document.body).data ('t3-tour-contextTip', tip);
				$(tip.element).off(tip.monitor);
			});
		}, 

		unbindContextTip: function (tip) {
			return;
		},

		defaultTour: function () {
			this.showControls (true);
		},

		firstShow: function () {
			if (!$.cookie('t3-tour-firstshow')) {
				//this.defaultTour();

				var placed = $('#t3-toolbar-help'),
					tip = $('#t3-tour-quickhelp');

				tip
				.appendTo($('#t3-toolbar'))
				.css({
					display: 'inline-block',
					opacity: 0,
				})
				.delay(2000).fadeTo(700, 1)
				.on('click', $.proxy(this.defaultTour, this))
				.find('.close')
					.on('click', function(){
						tip.fadeTo(500, 0, function(){
							$(this).remove();
						});

						return false;
					});

				$.cookie('t3-tour-firstshow', '1', { expires: 365, path: '/' });
			}
		}
	};

	$.fn.t3tour = function(option){
		return this.each(function () {
			var jelm = $(this),
				data = jelm.data('t3tour'),
				options = typeof option == 'object' && option;
			
			if (!data) {
				jelm.data('t3tour', (data = new Tour(options)));
			} else {
				if (typeof option == 'string' && data[option]){
					data[option]()
				}
			}
		})
	};

	$.fn.t3tour.defaults = {
		// JOOM: all tour elements
		tours: [],
		//define if steps should change automatically
		autoplay: false,
		//timeout for the step
		showtime: null,
		//current step of the tour
		step: 0,
		//total number of steps
		// JOOM: enable/disable overlay
		tour_overlay: true
	};
	
}(window.$T3 || window.jQuery);