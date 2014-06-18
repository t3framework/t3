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

;(function($){


    var T3Menu = function(elm, options){
        this.$menu = $(elm);
        if (!this.$menu.length){
            return;
        }

        this.options = $.extend({}, $.fn.t3menu.defaults, options);
        this.child_open = [];
        this.loaded = false;

        this.start();
    };

    T3Menu.prototype = {
        constructor: T3Menu,

        start: function () {
            //init once
            if (this.loaded){
                return;
            }
            this.loaded = true;

            //start
            var self = this,
                options = this.options,
                $menu = this.$menu;

            this.$items = $menu.find('li');
            this.$items.each(function (idx, li) {

                var $item = $(this),
                    $child = $item.children('.dropdown-menu'),
                    $link = $item.children('a'),
                    item = {
                        $item: $item,
                        child: $child.length,
                        link: $link.length,
                        clickable: !($link.length && $child.length),
                        mega: $item.hasClass('mega'),
                        status: 'close',
                        timer: null,
                        atimer: null
                    };

                //store
                $item.data('t3menu.item', item);

                //click action
                if ($child.length && !options.hover) {
                    $item.on('click', function (e) {
                        e.stopPropagation();

                        if ($item.hasClass('group')) {
                            return;
                        }

                        if (item.status == 'close') {
                            e.preventDefault();
                            self.show(item);
                        }
                    });
                } else {

                    //stop if click on menu item - prevent bubble event
                    $item.on('click', function (e) {
                        e.stopPropagation()
                    });
                }

                if (options.hover) {
                    $item.on('mouseover', function (e) {
                        if ($item.hasClass('group')) {
                            return;
                        }

                        e.stopPropagation();
                        self.show(item);

                    }).on('mouseleave', function (e) {
                        if ($item.hasClass('group')) {
                            return;
                        }

                        e.stopPropagation();
                        self.hide(item);
                    });

                    //if has child, don't goto link before open child - fix for touch screen
                    if ($link.length && $child.length) {
                        $link.on('click', function (e) {
                            return item.clickable;
                        });
                    }
                }

            });

            $(document.body).on('tap hideall.t3menu', function(e){
                clearTimeout(self.timer);
                self.timer = setTimeout($.proxy(self.hide_alls, self), e.type == 'tap' ? 500 : self.options.hidedelay);
            });
        },

        show: function (item) {
            // hide all others menu of this instance
            if($.inArray(item, this.child_open) < this.child_open.length -1){
                this.hide_others(item);
            }

            // hide all for other instances as well
            $(document.body).trigger('hideall.t3menu', [this]);

            clearTimeout(this.timer);		//hide alls
            clearTimeout(item.timer);		//hide this item
            clearTimeout(item.ftimer);	//on hidden
            clearTimeout(item.ctimer);	//on hidden

            if(item.status != 'open' || !item.$item.hasClass('open') || !this.child_open.length){
                if(item.mega){
                    //remove timer
                    clearTimeout(item.astimer);	//animate
                    clearTimeout(item.atimer);	//animate

                    //place menu
                    this.position(item.$item);

                    // add class animate
                    item.astimer = setTimeout(function(){item.$item.addClass('animating')}, 10);
                    item.atimer = setTimeout(function(){item.$item.removeClass('animating')}, this.options.duration + 50);
                    item.timer = setTimeout(function(){item.$item.addClass('open')}, 100);

                } else {
                    item.$item.addClass('open');
                }

                item.status = 'open';
                if (item.child && $.inArray(item, this.child_open) == -1) {
                    this.child_open.push(item);
                }
            }

            item.ctimer = setTimeout($.proxy(this.clickable, this, item), 300);
        },

        hide: function (item) {
            clearTimeout(this.timer);		//hide alls
            clearTimeout(item.timer);		//hide this item
            clearTimeout(item.astimer);	//animate timer
            clearTimeout(item.atimer);	//animate timer
            clearTimeout(item.ftimer);	//on hidden

            if(item.mega){
                //animate out
                item.$item.addClass('animating');
                item.atimer = setTimeout(function(){item.$item.removeClass('animating')}, this.options.duration);
                item.timer = setTimeout(function(){item.$item.removeClass('open')}, 100);
            } else {
                item.$item.removeClass('open');
            }

            item.status = 'close';
            for (var i = this.child_open.length; i--;){
                if (this.child_open[i] === item){
                    this.child_open.splice(i, 1);
                }
            }

            item.ftimer = setTimeout($.proxy(this.hidden, this, item), this.options.duration);
            this.timer = setTimeout($.proxy(this.hide_alls, this), this.options.hidedelay);
        },

        hidden: function (item) {
            //hide done
            if (item.status == 'close') {
                item.clickable = false;
            }
        },

        hide_others: function (item) {
            var self = this;
            $.each(this.child_open.slice(), function (idx, open) {
                if (!item || (open != item && !open.$item.has(item.$item).length)) {
                    self.hide(open);
                }
            });
        },

        hide_alls: function (e, inst) {
            if(!e || e.type == 'tap' || (e.type == 'hideall' && this != inst)){
                var self = this;
                $.each(this.child_open.slice(), function (idx, item) {
                    item && self.hide(item);
                });
            }
        },

        clickable: function (item) {
            item.clickable = true;
        },

        position: function ($item) {
            var sub = $item.children('.mega-dropdown-menu'),
                is_show = sub.is(':visible');

            if(!is_show){
                sub.show();
            }

            var offset = $item.offset(),
                width = $item.outerWidth(),
                screen_width = $(window).width() - this.options.sb_width,
                sub_width = sub.outerWidth(),
                level = $item.data('level');

            if(!is_show){
                sub.css('display', '');
            }

            //reset custom align
            sub.css({left : '', right : ''});

            if(level == 1){

                var align = $item.data('alignsub'),
                    align_offset = 0,
                    align_delta = 0,
                    align_trans = 0;

                if(align == 'justify'){
                    return;	//do nothing
                }

                if(!align){
                    align = 'left';
                }

                if(align == 'center'){
                    align_offset = offset.left + (width /2);

                    if(!$.support.t3transform){
                        align_trans = -sub_width /2;
                        sub.css(this.options.rtl ? 'right' : 'left', align_trans + width /2);
                    }

                } else {
                    align_offset = offset.left + ((align == 'left' && this.options.rtl || align == 'right' && !this.options.rtl) ? width : 0);
                }

                if (this.options.rtl) {

                    if(align == 'right'){
                        if(align_offset + sub_width > screen_width){
                            align_delta = screen_width - align_offset - sub_width;
                            sub.css('left', align_delta);

                            if(screen_width < sub_width){
                                sub.css('left', align_delta + sub_width - screen_width);
                            }
                        }
                    } else {
                        if(align_offset < (align == 'center' ? sub_width /2 : sub_width)){
                            align_delta = align_offset - (align == 'center' ? sub_width /2 : sub_width);
                            sub.css('right', align_delta + align_trans);
                        }

                        if(align_offset + (align == 'center' ? sub_width /2 : 0) - align_delta > screen_width){
                            sub.css('right', align_offset + (align == 'center' ? (sub_width + width) /2 : 0) + align_trans - screen_width);
                        }
                    }

                } else {

                    if(align == 'right'){
                        if(align_offset < sub_width){
                            align_delta = align_offset - sub_width;
                            sub.css('right', align_delta);

                            if(sub_width > screen_width){
                                sub.css('right', sub_width - screen_width + align_delta);
                            }
                        }
                    } else {

                        if(align_offset + (align == 'center' ? sub_width /2 : sub_width) > screen_width){
                            align_delta = screen_width - align_offset -(align == 'center' ? sub_width /2 : sub_width);
                            sub.css('left', align_delta + align_trans);
                        }

                        if(align_offset - (align == 'center' ? sub_width /2 : 0) + align_delta < 0){
                            sub.css('left', (align == 'center' ? (sub_width + width) /2 : 0) + align_trans - align_offset);
                        }
                    }
                }
            } else {

                if (this.options.rtl) {
                    if ($item.closest('.mega-dropdown-menu').parent().hasClass('mega-align-right')) {

                        //should be align to the right as parent
                        // $item.removeClass('mega-align-left').addClass('mega-align-right');

                        // check if not able => revert the direction
                        if (offset.left + width + sub_width > screen_width) {
                            $item.removeClass('mega-align-right'); //should we add align left ? it is th default now

                            if(offset.left - sub_width < 0){
                                sub.css('right', offset.left + width - sub_width);
                            }
                        }
                    } else {
                        if (offset.left - sub_width < 0) {
                            $item.removeClass('mega-align-left').addClass('mega-align-right');

                            if(offset.left + width + sub_width > screen_width){
                                sub.css('left', screen_width - offset.left - sub_width);
                            }
                        }
                    }
                } else {

                    if ($item.closest('.mega-dropdown-menu').parent().hasClass('mega-align-right')) {
                        //should be align to the right as parent
                        // $item.removeClass('mega-align-left').addClass('mega-align-right');

                        // check if not able => revert the direction
                        if (offset.left - sub_width < 0) {
                            $item.removeClass('mega-align-right'); //should we add align left ? it is th default now

                            if(offset.left + width + sub_width > screen_width){
                                sub.css('left', screen_width - offset.left - sub_width);
                            }
                        }
                    } else {

                        if (offset.left + width + sub_width > screen_width) {
                            $item.removeClass('mega-align-left').addClass('mega-align-right');

                            if(offset.left - sub_width < 0){
                                sub.css('right', offset.left + width - sub_width);
                            }
                        }
                    }
                }
            }
        }
    };

    $.fn.t3menu = function (option) {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('megamenu'),
                options = typeof option == 'object' && option;

            if (!data) {
                $this.data('megamenu', (data = new T3Menu(this, options)));

            } else {
                if (typeof option == 'string' && data[option]){
                    data[option]()
                }
            }
        })
    };

    $.fn.t3menu.defaults = {
        duration: 400,
        timeout: 100,
        hidedelay: 200,
        hover: true,
        sb_width: 20
    };


    //apply script
    $(document).ready(function(){

        //detect settings
        var mm_duration = $('.t3-megamenu').data('duration') || 0;
        if (mm_duration) {

            $('<style type="text/css">' +
                '.t3-megamenu.animate .animating > .mega-dropdown-menu,' +
                '.t3-megamenu.animate.slide .animating > .mega-dropdown-menu > div {' +
                'transition-duration: ' + mm_duration + 'ms !important;' +
                '-webkit-transition-duration: ' + mm_duration + 'ms !important;' +
                '}' +
                '</style>').appendTo ('head');
        }

        var mm_timeout = mm_duration ? 100 + mm_duration : 500,
            mm_rtl = $(document.documentElement).attr('dir') == 'rtl',
            mm_trigger = $(document.documentElement).hasClass('mm-hover'),
            sb_width = (function () {
                var parent = $('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo('body'),
                    child = parent.children(),
                    width = child.innerWidth() - child.height(100).innerWidth();

                parent.remove();

                return width;
            })();

        //lt IE 10
        if(!$.support.transition){
            //it is not support animate
            $('.t3-megamenu').removeClass('animate');

            mm_timeout = 100;
        }

        //get ready
        $('.nav').has('.dropdown-menu').t3menu({
            duration: mm_duration,
            timeout: mm_timeout,
            rtl: mm_rtl,
            sb_width: sb_width,
            hover: mm_trigger
        });


        $(window).load(function(){

            //check we miss any nav
            $('.nav').has('.dropdown-menu').t3menu({
                duration: mm_duration,
                timeout: mm_timeout,
                rtl: mm_rtl,
                sb_width: sb_width,
                hover: mm_trigger
            });

        });
    });

})(jQuery);
