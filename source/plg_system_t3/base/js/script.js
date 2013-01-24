//jquery no-conflict
if(typeof jQuery != 'undefined'){
	window.$T3 = jQuery.noConflict();
}

!function($){
	$(document).ready(function(){
		//remove conflict of mootools more show/hide function of element
		if(window.MooTools && window.MooTools.More && Element && Element.implement){
			$('.collapse').each(function(){this.show = null; this.hide = null});
		}

		$(document.body).on('click', '[data-toggle="dropdown"]' ,function(){
			if(!$(this).parent().hasClass('open') && this.href && this.href != '#'){
				window.location.href = this.href;
			}
		});
	});
}(window.$T3 || window.jQuery);