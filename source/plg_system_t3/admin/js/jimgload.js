/** 
 *------------------------------------------------------------------------------
 * @package   T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license   GNU General Public License; http://www.gnu.org/licenses/gpl.html
 * @author    JoomlArt, JoomlaBamboo 
 *            If you want to be come co-authors of this project, please follow 
 *            our guidelines at http://t3-framework.org/contribute
 *------------------------------------------------------------------------------
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