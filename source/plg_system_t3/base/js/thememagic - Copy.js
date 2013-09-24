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
	T3Theme = window.T3Theme || {};

	$.extend(T3Theme, {
		handleLink: function(){
			var links = document.links,
				forms = document.forms,
				origin = [window.location.protocol, '//', window.location.hostname, window.location.port].join(''),
				iter, i, il;

			for(i = 0, il = links.length; i < il; i++) {
				iter = links[i];

				if(iter.href && iter.hostname == window.location.hostname && iter.href.indexOf('#') == -1){
					iter.href = iter.href + (iter.href.lastIndexOf('?') != -1 ? '&' : '?') + (iter.href.lastIndexOf('themer=') == -1 ? 'themer=Y' : ''); 
				}
			}

			
			for(i = 0, il = forms.length; i < il; i++) {
				iter = forms[i];

				if(iter.action.indexOf(origin) == 0){
					iter.action = iter.action + (iter.action.lastIndexOf('?') != -1 ? '&' : '?') + (iter.action.lastIndexOf('themer=') == -1 ? 'themer=Y' : ''); 
				}
			}

			//10 seconds, if the Less build not complete, we just show the page instead of blank page
			T3Theme.sid = setTimeout(T3Theme.bodyReady, 10000);
		},
		applyLess: function(data){
			if(data && typeof data == 'object'){
				T3Theme.vars = data.vars;
				T3Theme.others = data.others;
				T3Theme.theme = data.theme;		
			}
			
			var links = document.getElementsByTagName('link');
			var typePattern = /^text\/(x-)?less$/;
			var sheets = [];

			for (var i = 0; i < links.length; i++) {
			    if (links[i].rel === 'stylesheet/less' || (links[i].rel.match(/stylesheet/) &&
			       (links[i].type.match(typePattern)))) {
			        sheets.push(links[i]);
			    }
			}

			for (var i = 0; i < sheets.length; i++) {
			    var worker = new Worker(this.base + 'plugins/system/t3/base/js/less-worker-1.3.3.js');
			    worker.onmessage = window.onmessage;
			    worker.postMessage({
			    	task: 'compile',
			    	sheet: {
			    		href: sheets[i].href,
			    		type: sheets[i].type
			    	}
			    })
			}

			//less.refresh(true);
		},

		onCompile: function(completed, total){
			if(window.parent != window && window.parent.T3Theme){
				window.parent.T3Theme.onCompile(completed, total);
			}

			if(completed >= total){
				T3Theme.bodyReady();
			}
		},

		bodyReady: function(){
			clearTimeout(T3Theme.sid);

			if(!this.ready){
				$(document).ready(function(){
					T3Theme.ready = 1;
					$(document.body).addClass('ready');
				});
			} else {
				$(document.body).addClass('ready');
			}
		},

		extractId: function(href) {
			return href.replace(/^[a-z]+:\/\/?[^\/]+/, '' )  // Remove protocol & domain
					   .replace(/^\//,                 '' )  // Remove root /
					   .replace(/\.[a-zA-Z]+$/,        '' )  // Remove simple extension
					   .replace(/[^\.\w-]+/g,          '-')  // Replace illegal characters
					   .replace(/\./g,                 ':'); // Replace dots with colons(for valid id)
		},

		createCSS: function (styles, sheet, lastModified) {
			var css;

			// Strip the query-string
			var href = sheet.href || '';

			// If there is no title set, use the filename, minus the extension
			var id = 'less:' + (sheet.title || this.extractId(href));

			// If the stylesheet doesn't exist, create a new node
			if ((css = document.getElementById(id)) === null) {
				css = document.createElement('style');
				css.type = 'text/css';
				if( sheet.media ){ css.media = sheet.media; }
				css.id = id;
				var nextEl = sheet && sheet.nextSibling || null;
				/* T3 framework: add to the sheet position inteads of at the end of head */
				//(nextEl || document.getElementsByTagName('head')[0]).parentNode.insertBefore(css, nextEl); 
			(nextEl && nextEl.parentNode || document.getElementsByTagName('head')[0]).insertBefore(css, nextEl);
			}

			if(typeof cssjanus != 'undefined'){
				styles = cssjanus.transform(styles);
			}

			if (css.styleSheet) { // IE
				try {
					css.styleSheet.cssText = styles;
				} catch (e) {
					throw new(Error)("Couldn't reassign styleSheet.cssText.");
				}
			} else {
				(function (node) {
					if (css.childNodes.length > 0) {
						if (css.firstChild.nodeValue !== node.nodeValue) {
							css.replaceChild(node, css.firstChild);
						}
					} else {
						css.appendChild(node);
					}
				})(document.createTextNode(styles));
			}
		}
	});

	$(document).ready(function(){
		T3Theme.handleLink();
	});
	
}(jQuery);

window.onmessage = function (e) {
	console.log(e);
	if(e.data && e.data.task == 'css'){
		T3Theme.createCSS(e.styles, e.sheet, e.lastModified);
	}
}