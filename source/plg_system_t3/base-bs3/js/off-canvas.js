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

jQuery (document).ready(function($){
    // fix for old ie
    if ($.browser.msie && $.browser.version < 10) {
        $('html').addClass ('old-ie');
    }

    var $wrapper = $('body'),
        $inner = $('.t3-wrapper'),
        $toggles = $('.off-canvas-toggle'),
        $offcanvas = $('.t3-off-canvas'),
        $close = $('.t3-off-canvas .close'),
        $btn=null;
    // no wrapper, just exit
    if (!$wrapper.length) return ;

    // store original class
    $wrapper.data('oclass', $wrapper[0].className);

    // add effect class for nav
    $toggles.each (function () {
        var $this = $(this),
            $nav = $($this.data('nav')),
            effect = $this.data('effect');
        $nav.addClass (effect);
        // move to outside wrapper-content
        var inside_effect = ['off-canvas-effect-3','off-canvas-effect-6','off-canvas-effect-7','off-canvas-effect-8','off-canvas-effect-14'];
        if ($.inArray(effect, inside_effect) == -1) {
            $inner.before($nav);
        } else {
            $inner.prepend($nav);
        }
    });

    $toggles.click (function(e){
        stopBubble (e);
        if ($wrapper.hasClass ('off-canvas-open')) {
            oc_hide (e);
            return;
        }

        $btn = $(this);

        // update effect class
        $wrapper[0].className = $wrapper.data('oclass') + ' ' + $btn.data('effect');

        // disable scroll on page
        var scrollTop = ($('html').scrollTop()) ? $('html').scrollTop() : $('body').scrollTop(); // Works for Chrome, Firefox, IE...
        $('html').addClass('noscroll').css('top',-scrollTop).data('top', scrollTop);
        $('.t3-off-canvas').css('top',scrollTop);

        setTimeout(oc_show, 50);

        return;
    });
    var oc_show = function () {
        $wrapper.addClass ('off-canvas-open');
        $wrapper.on ('click', oc_hide);
        $close.on ('click', oc_hide);
        $offcanvas.on ('click', stopBubble);

        // fix for old ie
        if ($.browser.msie && $.browser.version < 10) {
            $inner.animate ({'padding-left':$('.t3-off-canvas').width()});
            $('.t3-off-canvas').animate ({left: 0});
        }
    };

    var oc_hide = function () {
        $wrapper.removeClass ('off-canvas-open');
        $wrapper.off ('click', oc_hide);
        $close.off ('click', oc_hide);
        $offcanvas.off ('click', stopBubble);
        setTimeout (function (){
            $wrapper.removeClass ($btn.data('effect'));
            // enable scroll
            $('html').removeClass ('noscroll').css('top', '');
            $('html,body').scrollTop ($('html').data('top'));
        }, 550);

        // fix for old ie
        if ($.browser.msie && $.browser.version < 10) {
            $inner.animate ({'padding-left':0});
            $('.t3-off-canvas').animate ({left: -$('.t3-off-canvas').width()});
        }
    };

    var stopBubble = function (e) {
        e.stopPropagation();
    }
})