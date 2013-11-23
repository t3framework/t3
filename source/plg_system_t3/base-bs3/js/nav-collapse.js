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

jQuery(document).ready(function ($) {

    // clone the collapse menu from mainnav (.t3-navbar)
    var $navwrapper = $('.t3-navbar'), $menu = null;
    if ($navwrapper.find('.t3-megamenu').length) {
        // clone for megamenu
        $menu = $navwrapper.find('ul.level0').clone();
        var lis = $menu.find('li[data-id]'),
            liactive = lis.filter('.current');
        // clean class
        lis.removeClass('mega dropdown mega-align-left mega-align-right mega-align-center mega-align-adjust');
        // rebuild
        lis.each(function () {
            // get firstchild - a or span
            var $li = $(this),
                $child = $li.find('>:first-child');

            if ($child[0].nodeName == 'DIV') {
                $child.find('>:first-child').prependTo($li);
                $child.remove();
            }

            // find subnav and inject into one ul
            var subul = $li.find('ul.level' + $li.data('level'));
            if (subul.length) {
                // create subnav
                $ul = $('<ul class="level' + $li.data('level') + ' dropdown-menu">').appendTo($li);
                subul.each(function () {
                    $(this).find('>li').appendTo($ul);
                });
            }

            // remove all child div
            $li.find('>div').remove();
            // clear all attributes
            $li.removeAttr('class');
            for (var x in $li.data()) {
                $li.removeAttr('data-' + x)
            }
            $child.removeAttr('class');
            for (var x in $child.data()) {
                $child.removeAttr('data-' + x)
            }
            // remove carret
            // $child.find('b').remove();
        });

        //so we have all structure, add standard bootstrap class
        $menu
            .find('ul.dropdown-menu')
            .prev('a').attr('data-toggle', 'dropdown')
            .parent('li')
            .addClass(function(){
                return 'dropdown' + ($(this).data('level') > 1 ? ' dropdown-submenu' : '');
            });

        // update class current
        liactive.addClass('current');
    } else {
        // clone for bootstrap menu
        $menu = $navwrapper.find ('ul.nav').clone();
        // remove all dropdown, dropdown-menu, dropdown-submenu class
        //$menu.find ('.dropdown, .dropdown-menu, .dropdown-submenu').removeClass ('dropdown dropdown-menu dropdown-submenu');
    }

    // inject into .t3-navbar-collapse
    $menu.appendTo ($('.t3-navbar-collapse'));

})
