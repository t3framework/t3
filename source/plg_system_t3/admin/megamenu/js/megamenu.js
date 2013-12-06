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

var T3AdminMegamenu = window.T3AdminMegamenu || {};

!function ($) {
	var currentSelected = null,
		megamenu, nav_items, nav_subs, nav_cols, nav_all;

	$.fn.megamenuAdmin = function (options) {
		
		options = $.extend({}, $.fn.megamenuAdmin.defaults, options);
		
		//get the first (top most megamenu)
		megamenu = $(this).find('.t3-megamenu:first');

		//find all class
		nav_items = megamenu.find('ul[class*="level"]>li>:first-child');
		nav_subs = megamenu.find('.nav-child');
		nav_cols = megamenu.find('[class*="span"]');
		
		nav_all = nav_items.add(nav_subs).add(nav_cols);
		// hide sub 
		nav_items.each (function () {			
			var a = $(this),
				liitem = a.closest('li');
			if (liitem.data ('hidesub') == 1) {
				var sub = liitem.find('.nav-child:first');
				// check if have menu-items in sub
				sub.css('display','none');
				a.removeClass ('dropdown-toggle').data('toggle', '');
				liitem.removeClass('dropdown dropdown-submenu mega');
			}
		});
		// hide toolbox
		hide_toolbox(true);
		// bind events for all selectable elements
		bindEvents (nav_all);

		// unbind all events for toolbox actions & inputs
		$('.toolbox-action, .toolbox-toggle, .toolbox-input').unbind ("focus blur click change keydown");

		// stop popup event when click in toolbox area
		$('.t3-admin-mm-row').click (function(event) {
			event.stopPropagation();
			// return false;
		});
		// deselect when click outside menu
		$(document.body).click (function(event) {
			hide_toolbox (true);
			//event.stopPropagation();
		});

		// bind event for action
		$('.toolbox-action').click (function(event) {
			var action = $(this).data ('action');

			if (action) {
				actions.datas = $(this).data();
				actions[action] ();
			}
			event.stopPropagation();
			return false;
		});
		$('.toolbox-toggle').change (function(event) {
			var action = $(this).data ('action');
			if (action) {
				actions.datas = $(this).data();
				actions[action] ();
			}
			event.stopPropagation();
			return false;
		});
		// ignore events
		$('.toolbox-input').bind ('focus blur click', function(event) {
			event.stopPropagation();
			return false;
		});
		$('.toolbox-input').bind ('keydown', function(event) {
			if (event.keyCode == '13') {
				apply_toolbox (this);
				event.preventDefault();
			}
		});

		$('.toolbox-input').change (function(event) {
			apply_toolbox (this);
			event.stopPropagation();
			return false;
		});

		return this;
	};

	$.fn.megamenuAdmin.defaults = {};

	// Actions
	var actions = {};
	actions.data = {};

	actions.toggleSub = function () {
		if (!currentSelected) return ;
		var liitem = currentSelected.closest('li'),
		sub = liitem.find ('.nav-child:first');
		if (liitem.data('group')) return; // not allow do with group
		if (sub.length == 0 || sub.css('display') == 'none') {
			// add sub
			if (sub.length == 0) {
				sub = $('<div class="nav-child dropdown-menu mega-dropdown-menu"><div class="row-fluid"><div class="span12" data-width="12"><div class="mega-inner"></div></div></div></div>').appendTo(liitem);
				bindEvents (sub.find ('[class*="span"]'));
				liitem.addClass ('mega');
			} else {
				// sub.attr('style', '');
				sub.css('display','');
				liitem.data('hidesub', 0);
			}
			liitem.data('group', 0);
			currentSelected.addClass ('dropdown-toggle').data('toggle', 'dropdown');
			liitem.addClass(liitem.data('level') == 1 ? 'dropdown' : 'dropdown-submenu');
			bindEvents(sub);
		} else {
			unbindEvents(sub);
			// check if have menu-items in sub
			if (liitem.find('ul.level'+liitem.data('level')).length > 0) {
				sub.css('display','none');
				liitem.data('hidesub', 1);
			} else {
				// just remove it
				sub.remove();
			}
			liitem.data('group', 0);
			currentSelected.removeClass ('dropdown-toggle').data('toggle', '');
			liitem.removeClass('dropdown dropdown-submenu mega');
		}
		// update toolbox status
		update_toolbox ();
	}

	actions.toggleGroup = function () {
		if (!currentSelected) return ;
		var liitem = currentSelected.parent(),
			sub = liitem.find ('.nav-child:first');
		if (liitem.data('level') == 1) return; // ignore for top level
		if (liitem.data('group')) {
			liitem.data('group', 0);
			liitem.removeClass('mega-group').addClass('dropdown-submenu');
			currentSelected.addClass ('dropdown-toggle').data('toggle', 'dropdown');
			sub.removeClass ('mega-group-ct').addClass ('dropdown-menu mega-dropdown-menu');
			sub.css('width', sub.data('width'));
			rebindEvents(sub);
		} else {
			currentSelected.removeClass ('dropdown-toggle').data('toggle', '');
			liitem.data('group', 1);
			liitem.removeClass('dropdown-submenu').addClass('mega-group');
			sub.removeClass ('dropdown-menu mega-dropdown-menu').addClass ('mega-group-ct');
			sub.css('width', '');
			rebindEvents(sub);
		}
		// update toolbox status
		update_toolbox ();
	}

	actions.moveItemsLeft = function () {
		if (!currentSelected) return ;
		var $item = currentSelected.closest('li'),
		$liparent = $item.parent().closest('li'),
		level = $liparent.data('level'),
		$col = $item.closest ('[class*="span"]'),
		$items = $col.find ('ul:first > li'),
		itemidx = $items.index ($item),
		$moveitems = $items.slice (0, itemidx+1),
		itemleft = $items.length - $moveitems.length,
		$rows = $col.parent().parent().children ('[class*="row"]'),
		$cols = $rows.children('[class*="span"]').filter (function(){return !$(this).data('position')}),
		colidx = $cols.index ($col);
		if (!$liparent.length) return ; // need make this is mega first

		if (colidx == 0) {
			// add new col
			var oldSelected = currentSelected;
			currentSelected = $col;
			// add column to first
			actions.datas.addfirst = true;
			actions.addColumn ();
			$cols = $rows.children('[class*="span"]').filter (function(){return !$(this).data('position')});
			currentSelected = oldSelected;
			colidx++;
		}
		// move content to right col
		var $tocol = $($cols[colidx-1]);
		var $ul = $tocol.find('ul:first');
		if (!$ul.length) {
			$ul = $('<ul class="mega-nav level'+level+'">').appendTo ($tocol.children('.mega-inner'));
		}
		$moveitems.appendTo($ul);
		if (itemleft == 0) {
			$col.find('ul:first').remove();
		}
		// update toolbox status
		update_toolbox ();
	}

	actions.moveItemsRight = function () {
		if (!currentSelected) return ;
		var $item = currentSelected.closest('li'),
		$liparent = $item.parent().closest('li'),
		level = $liparent.data('level'),
		$col = $item.closest ('[class*="span"]'),
		$items = $col.find ('ul:first > li'),
		itemidx = $items.index ($item),
		$moveitems = $items.slice (itemidx),
		itemleft = $items.length - $moveitems.length,
		$rows = $col.parent().parent().children ('[class*="row"]'),
		$cols = $rows.children('[class*="span"]').filter (function(){return !$(this).data('position')}),
		colidx = $cols.index ($col);
		if (!$liparent.length) return ; // need make this is mega first

		if (colidx == $cols.length - 1) {
			// add new col
			var oldSelected = currentSelected;
			currentSelected = $col;
			actions.datas.addfirst = false;
			actions.addColumn ();
			$cols = $rows.children('[class*="span"]').filter (function(){return !$(this).data('position')});
			currentSelected = oldSelected;
		}
		// move content to right col
		var $tocol = $($cols[colidx+1]);
		var $ul = $tocol.find('ul:first');
		if (!$ul.length) {
			$ul = $('<ul class="mega-nav level'+level+'">').appendTo ($tocol.children('.mega-inner'));
		}
		$moveitems.prependTo($ul);
		if (itemleft == 0) {
			$col.find('ul:first').remove();
		}
		// update toolbox status
		show_toolbox (currentSelected);
	}

	actions.addRow = function () {
		if (!currentSelected) return ;
		var $row = $('<div class="row-fluid"><div class="span12"><div class="mega-inner"></div></div></div>').appendTo(currentSelected.find('[class*="row"]:first').parent()),
		$col = $row.children();
		// bind event
		bindEvents ($col);
		currentSelected = null;
		// switch selected to new column
		show_toolbox ($col);
	}

	actions.alignment = function () {
		var liitem = currentSelected.closest ('li');
		liitem.removeClass ('mega-align-left mega-align-center mega-align-right mega-align-justify').addClass ('mega-align-'+actions.datas.align);
		if (actions.datas.align == 'justify') {
			currentSelected.addClass('span12');
			currentSelected.css('width', '');
		} else {
			currentSelected.removeClass('span12');
			if (currentSelected.data('width')) currentSelected.css('width', currentSelected.data('width'));
		}
		liitem.data('alignsub', actions.datas.align);
		update_toolbox ();
	}

	actions.addColumn = function () {
		if (!currentSelected) return ;
		var $cols = currentSelected.parent().children('[class*="span"]'),
			colcount = $cols.length + 1,
			colwidths = defaultColumnsWidth (colcount);
			
		// add new column  
		var $col = $('<div><div class="mega-inner"></div></div>');
		if (actions.datas.addfirst) 
			$col.prependTo (currentSelected.parent());
		else {
			$col.insertAfter (currentSelected);
		}
		$cols = $cols.add ($col);
		// bind event
		bindEvents ($col);
		// update width
		$cols.each (function (i) {
			$(this).removeClass ('span'+$(this).data('width')).addClass('span'+colwidths[i]).data('width', colwidths[i]);
		});
		// switch selected to new column
		show_toolbox ($col);
	}

	actions.removeColumn = function () {
		if (!currentSelected){
			return;
		}

		var $col = currentSelected,
			$row = $col.parent(),
			$rows = $row.parent().children ('[class*="row"]'),
			$allcols = $rows.children('[class*="span"]'),
			$allmenucols = $allcols.filter (function(){return !$(this).data('position')}),
			$haspos = $allcols.filter (function(){return $(this).data('position')}).length,
			$cols = $row.children('[class*="span"]'),
			colcount = $cols.length - 1,
			colwidths = defaultColumnsWidth (colcount),
			type_menu = $col.data ('position') ? false : true;

		if ((type_menu && ((!$haspos && $allmenucols.length == 1) || ($haspos && $allmenucols.length == 0))) 
			|| $allcols.length == 1) {
			// if this is the only one column left
			return;
		}

		// remove column  
		// check and move content to other column        
		if (type_menu) {
			var colidx = $allmenucols.index($col),
				tocol = colidx == 0 ? $allmenucols[1] : $allmenucols[colidx-1];

			$col.find ('ul:first > li').appendTo ($(tocol).find('ul:first'));
		} 

		var colidx = $allcols.index($col),
			nextActiveCol = colidx == 0 ? $allcols[1] : $allcols[colidx-1];
		
		if (colcount < 1) {
			$row.remove();
		} else {            
			$cols = $cols.not ($col);
			// update width
			$cols.each (function (i) {
				$(this).removeClass ('span'+$(this).data('width')).addClass('span'+colwidths[i]).data('width', colwidths[i]);
			});
			// remove col
			$col.remove();
		}

		show_toolbox ($(nextActiveCol));
	}

	actions.hideWhenCollapse = function () {		
		if (!currentSelected) return ;
		var type = toolbox_type ();
		if (type == 'sub') {
			var liitem = currentSelected.closest('li');
			if (liitem.data('hidewcol')) {
				liitem.data('hidewcol', 0);
				liitem.removeClass ('sub-hidden-collapse');
			} else {
				liitem.data('hidewcol', 1);
				liitem.addClass ('sub-hidden-collapse');
			}			
		} else if (type == 'col') {
			if (currentSelected.data('hidewcol')) {
				currentSelected.data('hidewcol', 0);
				currentSelected.removeClass ('hidden-collapse');
			} else {
				currentSelected.data('hidewcol', 1);
				currentSelected.addClass ('hidden-collapse');			
			}			
		}
		update_toolbox ();
	}

	// toggle screen
	actions.toggleScreen = function () {
		if ($('.toolbox-togglescreen').hasClass('t3-fullscreen-full')) {
			$('.subhead-collapse').removeClass ('subhead-fixed');
			$('#t3-admin-megamenu').closest('.controls').removeClass ('t3-admin-control-fixed');			
			$('.toolbox-togglescreen').removeClass ('t3-fullscreen-full').find('i').removeClass().addClass(actions.datas.iconfull);
		} else {
			$('.subhead-collapse').addClass ('subhead-fixed');
			$('#t3-admin-megamenu').closest('.controls').addClass ('t3-admin-control-fixed');
			$('.toolbox-togglescreen').addClass ('t3-fullscreen-full').find('i').removeClass().addClass(actions.datas.iconsmall);
		}
	}

	actions.saveConfig = function (e) {
		
		//blocking
		var savebtn = $(this);
		if(savebtn.hasClass('loading')){
			return false;
		}
		savebtn.addClass('loading');

		var config = {},
		items = megamenu.find('ul[class*="level"] > li');
		items.each (function(){
			var $this = $(this),
			id = 'item-'+$this.data('id'),
			item = {};
			if ($this.hasClass ('mega')) {
				var $sub = $this.find ('.nav-child:first');
				item['sub'] = {};
				
				for (var d in $sub.data()) {
					if (d != 'id' && d != 'level' && $sub.data(d))
						item['sub'][d] = $sub.data(d);
				}
				// build row
				var $rows = $sub.find('[class*="row"]:first').parent().children('[class*="row"]'),
				rows = [],
				i = 0;

				$rows.each (function () {
					var row = [],
					$cols = $(this).children('[class*="span"]'),
					j = 0;
					$cols.each (function(){
						var li = $(this).find('ul[class*="level"] > li:first'),
						col = {};
						if (li.length) {
							col['item'] = li.data('id');
						} else if ($(this).data('position')) {
							col['position'] = $(this).data('position');
						} else {
							col['item'] = -1;
						}
						
						for (var d in $(this).data()) {
							if (d != 'id' && d != 'level' && d != 'position' && $(this).data(d))
								col[d] = $(this).data(d);
						}
						row[j++] = col;
					});
					rows[i++] = row;
				});
				item['sub']['rows'] = rows;
			}

			for (var d in $this.data()) {
				if (d != 'id' && d != 'level' && $this.data(d)) {
					if (d == 'caption') {
						item[d] = $this.data(d).replace(/</g, "[lt]").replace(/>/g, "[gt]");
					}
					else 
						item[d] = $this.data(d);
				}
			}

			if (!$.isEmptyObject(item)){
				config[id] = item;
			}
		});

		var menutype = $('#jform_params_mm_type').val(),
			curconfig = T3AdminMegamenu.config;

		if($.isArray(curconfig) && curconfig.length == 0){
			curconfig = {};
		}

		curconfig[menutype] = config;

		$.ajax({
			url: T3AdminMegamenu.referer,
			type: 'post',
			data: {
				t3action: 'megamenu',
				t3task: 'save',
				styleid: T3AdminMegamenu.styleid,
				template: T3AdminMegamenu.template,

				mmkey: $('#megamenu-key').val(),
				config: JSON.stringify(config)
			}
		}).done(function(rsp){

			try {
				rsp = $.parseJSON(rsp);
			} catch(e){
				rsp = false;
			}

			if(rsp){
				clearTimeout($('#ajax-message').data('sid'));
				$('#ajax-message')
					.removeClass('alert-error alert-success')
					.addClass(rsp.status ? 'alert-success' : 'alert-error')
					.addClass('in')
					.data('sid', setTimeout(function(){
							$('#ajax-message').removeClass('in')
						}, 5000))
					.find('strong')
						.html(rsp.message);
			}
			
		}).always(function(){
			savebtn.removeClass('loading')
		});
	}

	toolbox_type = function () {
		return currentSelected.hasClass ('nav-child') ? 'sub' : (!currentSelected.hasClass('mega-group-title') && currentSelected[0].tagName == 'DIV' ? 'col':'item');
	}

	hide_toolbox = function (show_intro) {
		$('#t3-admin-mm-tb .admin-toolbox').hide();
		currentSelected = null;
		if (megamenu && megamenu.data('nav_all')) megamenu.data('nav_all').removeClass ('selected');
		megamenu.find ('li').removeClass ('open');
		if (show_intro) {
			$('#t3-admin-mm-intro').show();
		} else {
			$('#t3-admin-mm-intro').hide();
		}
	}

	show_toolbox = function (selected) {
		hide_toolbox (false);
		if (selected) currentSelected = selected;
		// remove class open for other
		megamenu.find ('ul[class*="level"] > li').each (function(){
			if (!$(this).has (currentSelected).length > 0) $(this).removeClass ('open');
			else $(this).addClass ('open');
		});            

		// set selected
		megamenu.data('nav_all').removeClass ('selected');
		currentSelected.addClass ('selected');		
		var type = toolbox_type ();
		$('#t3-admin-mm-tool' + type).show();
		update_toolbox (type);

		$('#t3-admin-mm-tb').show();
	}

	update_toolbox = function (type) {
		if (!type) type = toolbox_type ();
		// remove all disabled status
		$('#t3-admin-mm-tb .disabled').removeClass('disabled');
		//$('#t3-admin-mm-tb .active').removeClass('active');
		switch (type) {
			case 'item':
				// value for toggle
				var liitem = currentSelected.closest('li'),
					liparent = liitem.parent().closest('li'),
					sub = liitem.find ('.nav-child:first');
					
				$('.toolitem-exclass').attr('value', liitem.data ('class') || '');
				$('.toolitem-xicon').attr('value', liitem.data ('xicon') || '');
				$('.toolitem-caption').attr('value', liitem.data ('caption') || '');
				// toggle Submenu
				var toggle = $('.toolitem-sub');
				//toggle.find('label').removeClass('active');
				if (liitem.data('group')) {
					// disable the toggle
					$('.toolitem-sub').addClass ('disabled');
				} else if (sub.length == 0 || sub.css('display') == 'none') {
					// sub disabled
					update_toggle (toggle, 0);
				} else {
					// sub enabled
					update_toggle (toggle, 1);
				}				

				// toggle Group
				var toggle = $('.toolitem-group');
				//toggle.find('label').removeClass('active');
				if (liitem.data('level') == 1 || sub.length == 0 || liitem.data('hidesub') == 1) {
					// disable the toggle
					$('.toolitem-group').addClass ('disabled');
				} else if (liitem.data('group')) {
					// Group off
					update_toggle (toggle, 1);
				} else {
					// Group on
					update_toggle (toggle, 0);				
				}

				// move left/right column action: disabled if this item is not in a mega submenu
				if (!liparent.length || !liparent.hasClass('mega')) {
					$('.toolitem-moveleft, .toolitem-moveright').addClass ('disabled');
				}

				break;

			case 'sub':
				var liitem = currentSelected.closest('li');
				$('.toolsub-exclass').attr('value', currentSelected.data ('class') || '');
				$('.toolsub-alignment .toolbox-action').removeClass('active');
				
				if (liitem.data('group')) {
					$('.toolsub-width').attr('value', '').addClass ('disabled');
					// disable alignment
					$('.toolsub-alignment').addClass ('disabled');
				} else {
					$('.toolsub-width').attr('value', currentSelected.data ('width') || '');
					// if not top level, allow align-left & right only
					if (liitem.data('level') > 1) {
						$('.toolsub-align-center').addClass ('disabled');
						$('.toolsub-align-justify').addClass ('disabled');
					} 

					// active align button
					if (liitem.data('alignsub')) {
						$('.toolsub-align-'+liitem.data('alignsub')).addClass ('active').siblings().removeClass('active');
						if (liitem.data('alignsub') == 'justify') {
							$('.toolsub-width').addClass ('disabled');
						}
					}					
				}

				// toggle hidewhencollapse
				var toggle = $('.toolsub-hidewhencollapse');
				//toggle.find('label').removeClass('active');
				if (liitem.data('hidewcol')) {
					// toggle enable
					update_toggle (toggle, 1);
				} else {
					// toggle disable
					update_toggle (toggle, 0);
				}	

				break;

			case 'col':
				$('.toolcol-exclass').attr('value', currentSelected.data ('class') || '');
				//$('.toolcol-position').attr('value', currentSelected.data ('position') || '');
				//$('.toolcol-width').attr('value', currentSelected.data ('width') || '');
				$('.toolcol-position').val (currentSelected.data ('position') || '').trigger("liszt:updated");
				$('.toolcol-width').val (currentSelected.data ('width') || '').trigger("liszt:updated");
				/* enable/disable module chosen */
				if (currentSelected.find ('.mega-nav').length > 0) {
					$('.toolcol-position').parent().addClass('disabled');
				}
				// disable choose width if signle column
				if (currentSelected.parent().children().length == 1) {
					$('.toolcol-width').parent().addClass ('disabled');
				}

				// toggle hidewhencollapse
				var toggle = $('.toolcol-hidewhencollapse');
				//toggle.find('label').removeClass('active');
				if (currentSelected.data('hidewcol')) {
					// toggle enable
					update_toggle (toggle, 1);
				} else {
					// toggle disable
					update_toggle (toggle, 0);
				}	
					
				break;
		}
	}

	update_toggle = function (toggle, val) {
		$input = toggle.find('input[value="'+val+'"]');
		$input.attr('checked', 'checked');
		$input.trigger ('update');
	}

	apply_toolbox = function (input) {
		var name = $(input).data ('name'), 
		value = input.value,
		type = toolbox_type ();
		switch (name) {
			case 'width':
				if (type == 'sub') {
					currentSelected.width(value);
				}
				if (type == 'col') {
					currentSelected.removeClass('span'+currentSelected.data(name)).addClass ('span'+value);
				}
				currentSelected.data (name, value);
				break;

			case 'class':
				if (type == 'item') {
					var item = currentSelected.closest('li');
				} else {
					var item = currentSelected;
				}
				item.removeClass(item.data(name) || '').addClass (value);
				item.data (name, value);
				break;

			case 'xicon':
				if (type == 'item') {
					currentSelected.closest('li').data (name, value);
					currentSelected.find('i').remove();
					if (value) currentSelected.prepend($('<i class="'+value+'"></i>'));
				}
				break;

			case 'caption':
				if (type == 'item') {
					currentSelected.closest('li').data (name, value);
					currentSelected.find('span.mega-caption').remove();
					if (value) currentSelected.append($('<span class="mega-caption">'+value+'</span>'));
				}
				break;

			case 'position':
				// replace content if this is not menu-items type
				if (currentSelected.find ('ul[class*="level"]').length == 0) {
					// get module content
					if (value) {
						$.ajax({
							url: T3AdminMegamenu.site,
							data: {
								t3action: 'module',
								mid: value,
								styleid: T3AdminMegamenu.styleid,
								template: T3AdminMegamenu.template,

								t3menu: $('#menu-type').val(),
								t3acl: $('#access-level').val(),
								t3lang: $('#menu-type :selected').attr('data-language') || '*'
							}
						}).done(function ( data ) {
							if(data){
								if(data.charAt(0) == '{' || data.charAt(0) == '['){
									try {
										data = $.parseJSON(data);
									} catch(e){
										data = false;
									}

									if(data && data.message){
										clearTimeout($('#ajax-message').data('sid'));
										$('#ajax-message')
											.removeClass('alert-error alert-success')
											.addClass('alert-error')
											.addClass('in')
											.data('sid', setTimeout(function(){
													$('#ajax-message').removeClass('in')
												}, 5000))
											.find('strong')
												.html(data.message);
									}

									//not valid value => we set to empty
									$(input).val('').trigger('liszt:updated');
									currentSelected.data (name, '');

								} else {
									currentSelected.find('.mega-inner').html(data).find(':input').removeAttr('name');
								}
							}
						});
					} else {
						currentSelected.find('.mega-inner').html('');
					}
					currentSelected.data (name, value);
				}
				break;
		}
	}

	defaultColumnsWidth = function (count) {
		if (count < 1) return null;
		var total = 12,
		min = Math.floor(total / count),
		widths = [];
		for(var i=0;i<count;i++) {
			widths[i] = min;
		}
		widths[count - 1] = total - min*(count-1);
		return widths;
	}

	bindEvents = function (els) {
		if (megamenu.data('nav_all')) 
			megamenu.data('nav_all', megamenu.data('nav_all').add(els));
		else
			megamenu.data('nav_all', els);

		els.mouseover(function(event) {
			megamenu.data('nav_all').removeClass ('hover');
			$this = $(this);
			clearTimeout (megamenu.data('hovertimeout'));
			megamenu.data('hovertimeout', setTimeout("$this.addClass('hover')", 100));
			event.stopPropagation();
		});
		els.mouseout(function(event) {
			clearTimeout (megamenu.data('hovertimeout'));
			$(this).removeClass('hover');
		});

		els.click (function(event){
			show_toolbox ($(this));
			event.stopPropagation();                
			return false;
		});
	}

	unbindEvents = function (els) {
		megamenu.data('nav_all', megamenu.data('nav_all').not(els));
		els.unbind('mouseover').unbind('mouseout').unbind('click');
	}

	rebindEvents = function (els) {
		unbindEvents(els);
		bindEvents(els);
	}
}(jQuery);

!function($){
	$.extend(T3AdminMegamenu, {
		// put megamenu admin panel into right place
		
		t3megamenu: function(rsp){
			$('#t3-admin-mm-container').html(rsp).megamenuAdmin().find(':input').removeAttr('name');
		},

		initCustomForm: function(){
			//copy from J3.0
			// Turn radios into btn-group
			if(typeof T3Admin != 'undefined'){
				return true;
			}

			var jt3menu = $('.t3-admin-megamenu');

			//convert to on/off
			jt3menu.find('.radio').filter(function(){
			
				return $(this).find('input').length == 2 && $(this).find('input').filter(function(){
						return $.inArray(this.value + '', ['0', '1']) !== -1;
					}).length == 2;

			}).addClass('t3onoff').removeClass('btn-group')
				.find('label').addClass(function(){
					return $(this).prev('input').val() == '0' ? 'off' : 'on'
				});

			//action
			jt3menu.find('.radio label').unbind('click').click(function() {
				var label = $(this),
					input = $('#' + label.attr('for'));

				if (!input.prop('checked')){
					label.addClass('active').siblings().removeClass('active');

					input.prop('checked', true).trigger('change');
				}
			});

			jt3menu.find('.radio input:checked').each(function(){
				$('label[for=' + $(this).attr('id') + ']').addClass('active');
			});

			jt3menu.on('update', 'input[type=radio]', function(){
				if(this.checked){
					$(this)
						.closest('.radio')
						.find('label').removeClass('active')
						.filter('[for="' + this.id + '"]')
							.addClass('active');
				}
			});

			//init chosen
			$('select').chosen({
				allow_single_deselect: true,
				disable_search_threshold : 10
			});

			$('#access-level').val(1).trigger('liszt:updated');
		},

		initAjaxmenu: function(){

			var	lid = null,
				ajax = null,
				ajaxing = false,
				doajax = function(){

					if(ajaxing && ajax){
						ajax.abort();
					}

					ajax = $.ajax({
						url: T3AdminMegamenu.site,
						data: {
							t3action: 'megamenu',
							t3task: 'display',
							styleid: T3AdminMegamenu.styleid,
							template: T3AdminMegamenu.template,

							t3menu: $('#menu-type').val(),
							t3acl: $('#access-level').val(),
							t3lang: $('#menu-type :selected').attr('data-language') || '*'
						},

						beforeSend: function(){
							clearTimeout(lid);

							//progress bar
							$('#t3-admin-megamenu').addClass('loading');
							if($.support.transition){
								T3AdminMegamenu.progElm
									.removeClass('t3-anim-slow t3-anim-finish')
									.css('width', '');

								setTimeout(function(){
									T3AdminMegamenu.progElm
										.addClass('t3-anim-slow')
										.css('width', 50 + Math.floor(Math.random() * 20) + '%');
								});
							} else {
								T3AdminMegamenu.progElm.stop(true).css({
									width: '0%',
									display: 'block'
								}).animate({
									width: 50 + Math.floor(Math.random() * 20) + '%'
								});
							}

						}
					}).done(function(rsp){
						T3AdminMegamenu.t3megamenu(rsp);
					}).fail(function(){

					}).always(function(){
						clearTimeout(lid);
						lid = setTimeout(function(){
							$('#t3-admin-megamenu').removeClass('loading');

							//progress bar
							if($.support.transition){
								
								T3AdminMegamenu.progElm
									.removeClass('t3-anim-slow')
									.addClass('t3-anim-finish')
									.one($.support.transition.end, function () {
										setTimeout(function(){
											if(T3AdminMegamenu.progElm.hasClass('t3-anim-finish')){
												$(T3AdminMegamenu.progElm).removeClass('t3-anim-finish');
											}

										}, 1000);
									});

							} else {
								$(T3AdminMegamenu.progElm).stop(true).animate({
									width: '100%'
								}, function(){
									$(T3AdminMegamenu.progElm).hide();
								});
							}

						}, 500);
					})
				};

			$('#menu-type, #access-level').on('change.mm', doajax);

			//init once
			doajax();

			T3AdminMegamenu.doajax = doajax;
		},

		initToolbar: function(){
			$('#t3-admin-mm-save').off('click.mm').on('click.mm', function(){
				$('.toolbox-saveConfig').trigger('click');

				return false;
			});

			$('#t3-admin-mm-delete').off('click.mm').on('click.mm', function(){

				var delbtn = $(this);

				if(delbtn.hasClass('loading')){
					return false;
				}

				delbtn.addClass('loading');

				T3AdminMegamenu.confirm(function(ok){
					if(ok != undefined && !ok){
						delbtn.removeClass('loading');

						return false;
					}

					$.ajax({
						url: T3AdminMegamenu.referer,
						type: 'post',
						data: {
							t3action: 'megamenu',
							t3task: 'delete',
							styleid: T3AdminMegamenu.styleid,
							template: T3AdminMegamenu.template,

							mmkey: $('#megamenu-key').val()
						}
					}).done(function(rsp){

						$('#t3-admin-megamenu-dlg').modal('hide');

						try {
							rsp = $.parseJSON(rsp);
						} catch(e){
							rsp = false;
						}

						if(rsp){
							clearTimeout($('#ajax-message').data('sid'));
							$('#ajax-message')
								.removeClass('alert-error alert-success')
								.addClass(rsp.status ? 'alert-success' : 'alert-error')
								.addClass('in')
								.data('sid', setTimeout(function(){
										$('#ajax-message').removeClass('in')
									}, 5000))
								.find('strong')
									.html(rsp.message);
						}

					}).always(function(){
						delbtn.removeClass('loading');

						T3AdminMegamenu.doajax();
					});
				});

				return false;
			});

			$('#t3-admin-mm-close').off('click.mm').on('click.mm', function(){
				window.location.href = T3AdminMegamenu.referer;

				return false;
			});
		},

		initAjaxMessage: function(){
			$('#ajax-message').on('click', '.close', function(){
				clearTimeout($('#ajax-message').removeClass('in').data('sid'));
			});
		},

		initModalDialog: function(){
			$('#t3-admin-megamenu-dlg')
				.prop('hide', false) //remove mootool hide function
				.on('click', '.modal-footer button', function(e){
					if($.isFunction(T3AdminMegamenu.modalCallback)){
						T3AdminMegamenu.modalCallback($(this).hasClass('yes'));
					} else if($(this).hasClass('yes')){
						$('#t3-admin-megamenu-dlg').modal('hide');
					}
					return false;
				}).on('hidden', function(){
					$('#t3-admin-mm-delete').removeClass('loading');
				})
		},

		confirm: function(callback){
			T3AdminMegamenu.modalCallback = callback;

			$('#t3-admin-megamenu-dlg').addClass('modal-confirm').modal('show');
		},

		initLoadingBar: function(){
			if(!T3AdminMegamenu.progElm){
				T3AdminMegamenu.progElm = $('.t3-progress');

				if(!T3AdminMegamenu.progElm.length){
					T3AdminMegamenu.progElm = $('<div class="t3-progress"></div>');
				}

				T3AdminMegamenu.progElm.appendTo(document.body);

				var placed = $('.t3-admin-header');
				if(placed.length){
					T3AdminMegamenu.progElm.appendTo(placed);
				}
			}
		}
	});

	$(document).ready(function(){
		T3AdminMegamenu.initLoadingBar();
		T3AdminMegamenu.initCustomForm();
		T3AdminMegamenu.initToolbar();
		T3AdminMegamenu.initAjaxmenu();
		T3AdminMegamenu.initModalDialog();
		T3AdminMegamenu.initAjaxMessage();
	});

}(jQuery);