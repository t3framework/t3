;(function($, undefined) {
	'use strict';
	
	// blank image data-uri bypasses webkit log warning (thx doug jones)
	var blank = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

	$.fn.jaload = function(option){
		var opts = $.extend({onload: false}, $.isFunction(option) ? {onload: option} : option),
			jimgs = this.find('img').add(this.filter('img')),
			total = jimgs.length,
			loaded = [],
			onload = function(){
				if(this.src === blank || $.inArray(this, loaded) !== -1){
					return;
				}

				loaded.push(this);

				$.data(this, 'JAImgLoad', {src: this.src});
				if (total === loaded.length){
					$.isFunction(opts.onload) && setTimeout(opts.onload);
					jimgs.unbind('.JAImgLoad');
				}
			};

		if (!total){
			$.isFunction(opts.onload) && opts.onload();
		} else {
			jimgs.on('load.JAImgLoad error.JAImgLoad', onload).each(function(i, el){
				var src = el.src,
					cached = $.data(el, 'JAImgLoad');

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
})(window.$ja || window.jQuery);