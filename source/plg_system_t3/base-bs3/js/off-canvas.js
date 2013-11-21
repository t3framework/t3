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
var OffcanvasMenu = function($, opt){
    var options = $.extend({
        mainnav: '.t3-megamenu',
        action: '.navbar-toggle',
        style: 'st-effect-1'
    }, opt);

    var cloneMenu = function () {

        var menu = $(options.mainnav).find ('ul.level0').clone(),
            lis = menu.find('li[data-id]'),
            liactive = lis.filter('.current');
        // clean class
        lis.removeClass ('mega dropdown mega-align-left mega-align-right mega-align-center mega-align-adjust');
        // rebuild
        lis.each (function (){
            // get firstchild - a or span
            var $li = $(this),
                $child = $li.find('>:first-child');

            if ($child[0].nodeName == 'DIV') {
                $child.find('>:first-child').prependTo ($li);
                $child.remove();
            }

            // find subnav and inject into one ul
            var subul = $li.find ('ul.level' + $li.data('level'));
            if (subul.length) {
                // create subnav
                $ul = $('<ul class="level'+$li.data('level') + '">').appendTo ($li);
                subul.each (function (){
                    $(this).find('>li').appendTo($ul);
                });
            }

            // remove all child div
            $li.find('>div').remove();
            // clear all attributes
            $li.removeAttr('class');
            for (var x in $li.data()) {$li.removeAttr('data-'+x)}
            $child.removeAttr('class');
            for (var x in $child.data()) {$child.removeAttr('data-'+x)}
            // remove carret
            $child.find ('b').remove();
        });

        // update class current
        liactive.addClass ('current');

        // append wrapper for current content
        $(document.body).children().appendTo ($('<div class="st-content" />').appendTo ($('<div class="st-pusher" />').appendTo (document.body)));
        menu.appendTo ($('<nav class="st-menu" />').appendTo($('body')));
        // wrap all into a wrapper
        $(document.body).children().appendTo ($('<div id="st-container" class="st-container" />').appendTo (document.body));

        // add effect style
        $('html').addClass (options.style);
    };

    var showNav = function () {
        $('.st-container').addClass ('st-menu-open');
        $('.st-pusher').on ('click', hideNav);
        // cancel touch move
        $('.st-pusher').on ('touchmove', cancelEvent);
    };

    var hideNav = function () {
        $('.st-container').removeClass ('st-menu-open');
        $('.st-pusher').off ('click', hideNav);
        // enable touch move
        $('.st-pusher').off ('touchmove', cancelEvent);
    };

    var cancelEvent = function (e) {
        e.preventDefault();
        return false;
    };

    var init = function () {
        $(options.action).click (function (e) {
            e.preventDefault();
            // check if sidebar menu is built
            if ($('.st-container').length == 0) {
                cloneMenu();
                setTimeout (showNav, 200);
                return false;
            }
            showNav();
            return false;
        })
    };

    init ();
};

jQuery (document).ready(function($){
    var $wrapper = $('.t3-wrapper'), $btn=null;
    // no wrapper, just exit
    if (!$wrapper.length) return ;

    // store original class
    $wrapper.data('oclass', $wrapper[0].className);

    // add effect class for nav
    $('.off-canvas-btn').each (function () {
        var $this = $(this),
            $nav = $($this.data('nav')),
            effect = $this.data('effect');
        $nav.addClass (effect);
        // move to outside wrapper-content
        var inside_effect = ['off-canvas-effect-3','off-canvas-effect-6','off-canvas-effect-7','off-canvas-effect-8','off-canvas-effect-14'];
        if ($.inArray(effect, inside_effect) == -1) {
            $nav.parent().parent().before($nav);
        } else {
            $nav.parent().before($nav);
        }
    });

    $('.off-canvas-btn').click (function(e){
        if ($btn) {
            // toggle
            oc_hide();
            if ($btn == $(this)) {
                return false;
            }
        }
        e.preventDefault();
        e.stopPropagation();
        $btn = $(this);

        // update effect class
        $wrapper[0].className = $wrapper.data('oclass') + ' ' + $btn.data('effect');

        setTimeout(oc_show, 50);
        return false;
    });
    var oc_show = function () {
        $wrapper.addClass ('off-canvas-open');
        $wrapper.on ('click', oc_hide);
    };

    var oc_hide = function () {
        $wrapper.removeClass ('off-canvas-open');
        // + $btn.data('effect'));
        $wrapper.off ('click', oc_hide);
        $btn = null;
    };
})