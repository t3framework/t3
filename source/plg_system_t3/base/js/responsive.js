
/* responsive */
jQuery(document).ready(function($){
  var current_layout = '';
  var responsive_elements = $('[class*="span"]');
  // build data & remove data attribute - make the source better view in inspector
  responsive_elements.each (function(){
    var $this = $(this);
    $this.data();
    $this.removeAttr ('data-default data-wide data-normal data-xtablet data-tablet data-mobile');
    if (!$this.data('default')) $this.data('default', $this.attr('class'));
  });

  // Get browser scrollbar width
  var scrollbarWidth = (function () { 
    var div = $('<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div>'); 
    // Append our div, do our calculation and then remove it 
    $('body').append(div); 
    var w1 = $('div', div).innerWidth(); 
    div.css('overflow-y', 'scroll'); 
    var w2 = $('div', div).innerWidth(); 
    $(div).remove(); 
    return (w1 - w2); 
  })();

  var update_layout_classes = function (new_layout){
    if (new_layout == current_layout) return ;
    responsive_elements.each(function(){
      var $this = $(this);
      // no override for all devices 
      if (!$this.data('default')) return;
      // keep default 
      if (!$this.data(new_layout) && (!current_layout || !$this.data(current_layout))) return;
      // remove current
      if ($this.data(current_layout)) $this.removeClass($this.data(current_layout));
      else $this.removeClass ($this.data('default'));
      // add new
      if ($this.data(new_layout)) $this.addClass ($this.data(new_layout));
      else $this.addClass ($this.data('default'));
    });
    current_layout = new_layout;
  };
  var detect_layout = function () {
    var devices = {
      wide: 1200,
      normal:    980,
      xtablet:  768,
      tablet:  600,
      mobile:  0
    };
    var width = $(window).width() + scrollbarWidth;
    for (var device in devices) {
      if (width >= devices[device]) return device;
    }
  }
  update_layout_classes (detect_layout());
  
  // bind resize 
  $(window).resize(function(){
    if ($.data(window, 'detect-layout-timeout')) {
      clearTimeout($.data(window, 'detect-layout-timeout'));
    }
    $.data(window, 'detect-layout-timeout', 
      setTimeout(function(){
        update_layout_classes (detect_layout());
      }, 200)
    )
  })
});