(function ($)
{
	$.fn.equalHeight = function (options)
	{
		var tallest = 0;
    $(this).each(function() {
        $(this).css({height:"", "min-height":""});
        var thisHeight = $(this).height();
        if(thisHeight > tallest) {
            tallest = thisHeight;
        }
    });

    $(this).each(function() {
        $(this).css( "min-height", tallest );
    });
	}
})(jQuery);