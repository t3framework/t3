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
    var $wrapper = $('.t3-wrapper'),
        $inner = $('.t3-wrapper-inner'),
        $toggles = $('.off-canvas-toggle'),
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