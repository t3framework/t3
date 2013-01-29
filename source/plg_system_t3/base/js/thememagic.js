/** 
 *------------------------------------------------------------------------------
 * @package       T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       JoomlArt, JoomlaBamboo, (contribute to this project at github 
 *                & Google group to become co-author)
 * @Google group: https://groups.google.com/forum/#!forum/t3fw
 * @Link:         https://github.com/t3framework/ 
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
		},
		applyLess: function(data){
			if(data && typeof data == 'object'){
				T3Theme.vars = data.vars;
				T3Theme.others = data.others;
				T3Theme.theme = data.theme;		
			}
			
			less.refresh(true);
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
			if(!this.ready){
				$(document).ready(function(){
					T3Theme.ready = 1;
					$(document.body).addClass('ready');
				});
			} else {
				$(document.body).addClass('ready');
			}
		}
	});

	$(document).ready(function(){
		T3Theme.handleLink();
	});
	
}(jQuery);
