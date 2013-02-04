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
 

var T3Theme = window.T3Theme || {};

!function ($) {

	$.extend(T3Theme, {

		placeholder: 'placeholder' in document.createElement('input'),

		//cache the original link
		initialize: function(){
			this.initCPanel();
			this.initCacheSource();
			this.initThemeAction();
			this.initModalDialog();
			this.initRadioGroup();
		},
		
		initCacheSource: function(){
			T3Theme.links = [];

			$('link[rel="stylesheet/less"]').each(function(){
				$(this).data('original', this.href.split('?')[0]);
			});

			$.each(T3Theme.data, function(key){
				T3Theme.data[key] = $.extend({}, T3Theme.data.base, this);
			});
		},

		initCPanel: function(){
			
			$('#t3-admin-thememagic .themer-minimize').on('click', function(){
				if($(this).hasClass('active')){
					$(this).removeClass('active');
					$('#t3-admin-thememagic').css('left', 0);
					$('#preview').css('left', $('#t3-admin-thememagic').outerWidth(true));
				} else {
					$(this).addClass('active');
					$('#t3-admin-thememagic').css('left', - $('#t3-admin-thememagic').outerWidth(true));
					$('#preview').css('left', 0);
				}
				
				return false;
			});
		},

		initRadioGroup: function(){
			//clone from J3.0 a2
			$('#t3-admin-thememagic .radio.btn-group label').addClass('btn')
			$('#t3-admin-thememagic').on('click', '.btn-group label', function(){
				var label = $(this),
					input = $('#' + label.attr('for'));

				if (!input.prop('checked')){
					label.closest('.btn-group')
						.find('label')
						.removeClass('active btn-success btn-danger btn-primary');

					label.addClass('active ' + (input.val() == '' ? 'btn-primary' : (input.val() == 0 ? 'btn-danger' : 'btn-success')));
					
					input.prop('checked', true).trigger('change.less');
				}
			});
			$('#t3-admin-thememagic .radio.btn-group input:checked').each(function(){
				$('label[for=' + $(this).attr('id') + ']').addClass('active ' + ($(this).val() == '' ? 'btn-primary' : ($(this).val() == 0 ? 'btn-danger' : 'btn-success')));
			});

			$('#t3-admin-thememagic').on('change.depend', 'input[type=radio]', function(){
				if(this.checked){
					$(this)
						.closest('.btn-group')
						.find('label').removeClass('active btn-primary')
						.filter('[for="' + this.id + '"]').addClass('active ' + ($(this).val() == '' ? 'btn-primary' : ($(this).val() == 0 ? 'btn-danger' : 'btn-success')));
				}
			});
			
		},
		
		initThemeAction: function(){
			this.jel = document.getElementById('t3-admin-theme-list');
			
			//change theme
			$('#t3-admin-theme-list').on('change', function(){
				
				var val = this.value;

				if(T3Theme.admin && T3Theme.changed){

					if(T3Theme.active == 'base' || T3Theme.active == -1){
						T3Theme.confirm(T3Theme.langs.saveChange, function(option){
							if(option){
								T3Theme.nochange = 1;
								T3Theme.saveThemeAs(function(){
									T3Theme.changeTheme(val);
								});
							} else {
								T3Theme.changeTheme(val);
							}
						});
					}else {
						T3Theme.confirm(T3Theme.langs.saveChange.replace('%THEME%', T3Theme.theme), function(option){
							if(option){
								T3Theme.saveTheme();

								$('#t3-admin-thememagic-dlg').modal('hide');
							}

							T3Theme.changeTheme(val);
						});
					}
				} else {
					T3Theme.changeTheme(val);
				}
								
				return false;
			});
			
			//preview theme
			$('#t3-admin-tm-pvbtn').on('click', function(){
				if($('#t3-admin-tm-recss-progress').hasClass('invisible')){
					T3Theme.applyLess();
				}

				return false;
			});
			

			if(T3Theme.admin){

				//save theme
				$('#t3-admin-tm-save').on('click', function(e){
					e.preventDefault();

					if(!$(this).hasClass('disabled') && $('#t3-admin-tm-recss-progress').hasClass('invisible')){
						setTimeout(T3Theme.saveTheme, 1);
					}
				});
				//saveas theme
				$('#t3-admin-tm-saveas').on('click', function(e){
					e.preventDefault();
					
					if(!$(this).hasClass('disabled') && $('#t3-admin-tm-recss-progress').hasClass('invisible')){
						setTimeout(T3Theme.saveThemeAs, 1);
					}
				});
				
				//delete theme
				$('#t3-admin-tm-delete').on('click', function(e){
					e.preventDefault();
					
					if(!$(this).hasClass('disabled') && $('#t3-admin-tm-recss-progress').hasClass('invisible')){
						setTimeout(T3Theme.deleteTheme, 1);
					}
				});
			}

			if(T3Theme.active != -1){
				T3Theme.fillData();
			}

			$('#t3-admin-tm-save, #t3-admin-tm-delete')[($('#t3-admin-theme-list').val() == 'base' ? 'addClass' : 'removeClass')]('disabled');
		},

		initModalDialog: function(){
			$('#t3-admin-thememagic-dlg').on('click', '.modal-footer a', function(){
				T3Theme.addtime = 500; //add time for close popup

				if($.isFunction(T3Theme.modalCallback)){
					T3Theme.modalCallback($(this).hasClass('btn-primary'));
					return false;
				} else if($(this).hasClass('btn-primary')){
					$('#t3-admin-thememagic-dlg').modal('hide');
				}
			});

			$('#prompt-form').on('submit', function(){
				$('#t3-admin-thememagic-dlg .modal-footer a.btn-primary').trigger('click');

				return false;
			});
		},
		
		applyLess: function(force){
			
			var jpg = $('#t3-admin-tm-recss-progress');
			if(jpg.hasClass('invisible')){
				jpg.removeClass('invisible').addClass('in').find('.bar').width(0);
			}

			var nvars = T3Theme.rebuildData(true),
				jsonstr = JSON.stringify(nvars);

			if(!force && T3Theme.jsonstr === jsonstr){
				setTimeout(function(){
					jpg.addClass('invisible').find('.bar').width(0);
				}, 300);
			
				return false;
			}

			T3Theme.variables = nvars;
			T3Theme.jsonstr = jsonstr;

			setTimeout(function(){

				var wnd = (window.frames['t3-admin-tm-ifr-preview'] || document.getElementById('t3-admin-tm-ifr-preview').contentWindow);
				if(wnd.location.href.indexOf('themer=') == -1){
					var urlparts = wnd.location.href.split('#');
					urlparts[0] += urlparts[0].indexOf('?') == -1 ? '?themer=1' : '&themer=1';
					wnd.location.href = urlparts.join('#');
					
				} else {
					if(wnd.T3Theme){
						wnd.T3Theme.applyLess({
							vars: T3Theme.variables,
							theme: T3Theme.active,
							others: T3Theme.themes[T3Theme.active]
						});
					} else {
						T3Theme.alert('error', T3Theme.langs.previewWindowError);
					}
				}
			}, 10);
				
			return false;
		},
		
		changeTheme: function(theme, pass){
			if(pass && !$('#t3-admin-tm-recss-progress').hasClass('invisible')){
				setTimeout(function(){
					$('#t3-admin-tm-recss-progress').addClass('invisible').find('.bar').width(0);
				}, 1000);
			}

			if($.trim(theme) == ''){
				return false;
			}
			
			//enable or disable control buttons
			$('#t3-admin-tm-save, #t3-admin-tm-delete')[(theme == 'base' ? 'addClass' : 'removeClass')]('disabled');
			
			T3Theme.active = theme;	//store the current theme
			T3Theme.changed = false;

			if(!pass){
				this.fillData();			//fill the data
				this.applyLess();			//refresh   	
			}
			
            return true;
		},
		
		serializeArray: function(){
			var els = [],
				allelms = document.adminForm.elements,
				pname1 = 't3form\\[thememagic\\]\\[.*\\]',
				pname2 = 't3form\\[thememagic\\]\\[.*\\]\\[\\]';
				
			for (var i = 0, il = allelms.length; i < il; i++){
				var el = allelms[i];
				
				if (el.name && (el.name.match(pname1) || el.name.match(pname2))){
					els.push(el);
				}
			}
			
			return els;
		},

		fillData: function (){
			
			var els = this.serializeArray(),
				data = T3Theme.data[T3Theme.active];
				
			if(els.length == 0 || !data){
				return;
			}
			
			$.each(els, function(){
				var name = T3Theme.getName(this),
					values = (data[name] != undefined) ? data[name] : '';
				
				T3Theme.setValues(this, $.makeArray(values));
			});
			
			//reset form state when new data is filled
			$(document.adminForm).find('.changed').removeClass('changed');

			if(typeof T3Depend != 'undefined'){
				T3Depend.update();
			}
		},
		
		valuesFrom: function(els){
			var vals = [];
			
			$(els).each(function(){
				var type = this.type,
					val = $.makeArray(((type == 'radio' || type == 'checkbox') && !this.checked) ? null : $(this).val());

				if(type == 'text' && !val[0]){
					val[0] = $(this).attr('placeholder');
				}

				for (var i = 0, l = val.length; i < l; i++){
					if($.inArray(val[i], vals) == -1){
						vals.push(val[i]);
					}
				}
			});
			
			return vals;
		},
		
		setValues: function(el, vals){
			var jel = $(el);
			
			if(jel.prop('tagName').toUpperCase() == 'SELECT'){
				jel.val(vals);
				
				if($.makeArray(jel.val())[0] != vals[0]){
					jel.val('-1');
				}
			}else {
				if(jel.prop('type') == 'checkbox' || jel.prop('type') == 'radio'){
					jel.prop('checked', $.inArray(el.value, vals) != -1).trigger('change.depend');

				} else {
					jel.attr('placeholder', vals[0]);
					jel.val(vals[0]);

					if(T3Theme.placeholder && T3Theme.data.base[T3Theme.getName(el)] == vals[0]){
						jel.val('');
					}
				}
			}
		},
		
		rebuildData: function(optimize){
			var els = this.serializeArray(),
				json = {};
				
			$.each(els, function(){
				var values = T3Theme.valuesFrom(this);
				if(values.length && values[0] != '' && (!optimize || (optimize && !this._disabled))){
					var name = T3Theme.getName(this),
						val = this.name.substr(-2) == '[]' ? values : values[0],
						adjust = null,
						filter = this.className.match(/t3tm-(\w*)\s?/);

					if(filter && $.isFunction(T3Theme['filter' + filter[1]])){
						adjust = T3Theme['filter' + filter[1]](val);
					}

					if(adjust != null && adjust != val){
						val = adjust;
						T3Theme.setValues(this, $.makeArray(val));
					}

					json[name] = val;
				}
			});

			for(var k in json){
				if(json.hasOwnProperty(k)){
					if(k.match(/_custom/)){
						json[k.replace('_custom', '')] = json[k];	
					}

					if(json[k] == 'undefined' || json[k] == ''){
						delete json[k];
					}
				}
			}
			
			return json;
		},

		filtercolor: function(hex){
			if(hex.charAt(0) === '@' || hex.toLowerCase() == 'inherit' || hex.toLowerCase() == 'transparent' || T3Theme.colors[hex.toLowerCase()]){
				return hex;
			}

			if(!/^#(?:[0-9a-fA-F]{3}){1,2}$/.test(hex)){
				hex = hex.replace(/[^A-F0-9]/ig, '');
				hex = hex.substr(0, 6);

				if(hex.length !== 3 && hex.length !== 6){
					hex = T3Theme.padding(hex, hex.length < 3 ? 3 : 6);
				}

				hex = '#' + hex;
			}

			return hex;
		},

		filterdimension: function(val){
			val = /^(-?\d*\.?\d+)(px|%|em|rem|pc|ex|in|deg|s|ms|pt|cm|mm|rad|grad|turn)?/.exec(val);
			if(val && val[1]){
				val = val[1] + (val[2] || 'px');
			} else {
				val = '0px';
			}

			return val;
		},

		padding: function(str, limit, pad){
			pad = pad || '0';

			while(str.length < limit){
				str = pad + str;
			}

			return str;
		},
		
		getName: function(el){
			var matches = el.name.match('t3form\\[thememagic\\]\\[([^\\]]*)\\]');
			if (matches){
				return matches[1];
			}
			
			return '';
		},
		
		deleteTheme: function(){

			T3Theme.confirm(T3Theme.langs.delTheme, function(option){
				if(option){
					T3Theme.submitForm({
						t3task: 'delete',
						theme: T3Theme.active
					});

					$('#t3-admin-thememagic-dlg').modal('hide');
				}
			});
		},
		
		cloneTheme: function(){
			T3Theme.prompt(T3Theme.langs.addTheme, function(option){
				if(option){
					var nname = $('#theme-name').val();
					if(nname){
						nname = nname.replace(/[^0-9a-zA-Z_-]/g, '').replace(/ /, '').toLowerCase();
						if(nname == ''){
							T3Theme.alert('warning', T3Theme.langs.correctName);
							return T3Theme.cloneTheme();
						}
						
						T3Theme.data[nname] = T3Theme.data[T3Theme.active];
						T3Theme.themes[nname] = $.extend({}, T3Theme.themes[T3Theme.active]);
						
						T3Theme.submitForm({
							t3task: 'duplicate',
							theme: nname,
							from: T3Theme.active
						});
					}

					$('#t3-admin-thememagic-dlg').modal('hide');
				}
				
			});
			
            return true;
		},
		
		saveTheme: function(){
			T3Theme.data[T3Theme.active] = T3Theme.rebuildData();
			T3Theme.submitForm({
				t3task: 'save',
				theme: T3Theme.active
			}, T3Theme.data[T3Theme.active])		
		},
		
		saveThemeAs: function(callback){
			T3Theme.prompt(T3Theme.langs.addTheme, function(option){
				if(option){
					var nname = $('#theme-name').val();
					if(nname){
						nname = nname.replace(/[^0-9a-zA-Z_-]/g, '').replace(/ /, '').toLowerCase();
						if(nname == ''){
							T3Theme.alert('warning', T3Theme.langs.correctName);
							return T3Theme.saveThemeAs(callback);
						} else if(T3Theme[nname]){
							T3Theme.alert('warning', T3Theme.langs.themeExist);
							return T3Theme.saveThemeAs(callback);
						}
						
						T3Theme.data[nname] = T3Theme.rebuildData();
						T3Theme.themes[nname] = $.extend({}, T3Theme.themes[T3Theme.active]);

						T3Theme.submitForm({
							t3task: 'save',
							theme: nname,
							from: T3Theme.active
						}, T3Theme.data[nname]);
					}

					$('#t3-admin-thememagic-dlg').modal('hide');
				}

				if($.isFunction(callback)){
					callback();
				}

				return true;
			});

			return true;
		},
		
		submitForm: function(params, data){
			if(T3Theme.run){
				T3Theme.ajax.abort();
			}

			$('#t3-admin-tm-recss-progress').removeClass('invisible').addClass('in').find('.bar').width(0);
			clearTimeout(T3Theme.progressid);
			T3Theme.progressid = setTimeout(function(){
				$('#t3-admin-tm-recss-progress').find('.bar').width('10%');
			}, 500);
			
			T3Theme.run = true;
			T3Theme.ajax = $.post(
				T3Theme.url + (T3Theme.url.indexOf('?') != -1 ? '' : '?') +
				$.param($.extend(params, {
					t3action: 'theme',
					t3template: T3Theme.template
				})) , data, function(result){
					
				T3Theme.run = false;

				clearTimeout(T3Theme.progressid);
				$('#t3-admin-tm-recss-progress').find('.bar').width('100%');

				if(result == ''){
					return;
				}
				
				result = $.parseJSON(result);

				T3Theme.alert(result.error || result.success, result.error ? 'error' : (result.success ? 'success' : 'info'), result.theme);

				if(result.theme){
					
					var jel = T3Theme.jel;
					
					switch (result.type){	
						
						case 'new':
						case 'duplicate':			
							jel.options[jel.options.length] = new Option(result.theme, result.theme);							
							
							if(!T3Theme.nochange){
								jel.options[jel.options.length - 1].selected = true;
								T3Theme.changeTheme(result.theme, true);	
								T3Theme.nochange = 0;
							} else {

							}
						break;
						
						case 'delete':
							var opts = jel.options;
							
							for(var j = 0, jl = opts.length; j < jl; j++){
								if(opts[j].value == result.theme){
									jel.remove(j);
									break;
								}
							}
							
							jel.options[0].selected = true;					
							T3Theme.changeTheme(jel.options[0].value);
						break;
						
						default:
							setTimeout(function(){
								$('#t3-admin-tm-recss-progress').addClass('invisible').find('.bar').width(0);
							}, 1000);
						break;
					}
				} else {
					setTimeout(function(){
						$('#t3-admin-tm-recss-progress').addClass('invisible').find('.bar').width(0);
					}, 1000);
				}
			});
		},

		alert: function(msg, type, title){
			$('#t3-admin-thememagic .alert').remove();

			T3Theme.jalert = $([
				'<div class="alert alert-', (type || 'info'), '">',
					'<button type="button" class="close" data-dismiss="alert">×</button>',
					(title ? '<h4 class="alert-heading">' + title + '</h4>' : ''),
					'<p>', msg, '</p>',
				'</div>'].join(''))
				.prependTo($('#t3-admin-tm-variable-form'))
				.on('closed', function(){
					clearTimeout(T3Theme.salert);
					T3Theme.jalert = null;
				}).alert();

			clearTimeout(T3Theme.salert);
			T3Theme.salert = setTimeout(function(){
				if(T3Theme.jalert){
					T3Theme.jalert.alert('close');
					T3Theme.jalert = null;
				}
			}, 10000);
		},

		confirm: function(msg, callback){
			T3Theme.modalCallback = callback;

			var jdialog = $('#t3-admin-thememagic-dlg');
			jdialog.find('.prompt-block').hide();
			jdialog.find('.message-block').show().find('p').html(msg);
			jdialog.find('.cancel').html(T3Theme.langs.lblNo);
			jdialog.find('.btn-primary').html(T3Theme.langs.lblYes);

			jdialog.removeClass('modal-prompt modal-alert')
				.addClass('modal-confirm')
				.modal('show');
		},

		prompt: function(msg, callback){
			T3Theme.modalCallback = callback;
			var jdialog = $('#t3-admin-thememagic-dlg');
			jdialog.find('.message-block').hide();
			jdialog.find('.prompt-block').show().find('span').html(msg);
			jdialog.find('.cancel').html(T3Theme.langs.lblCancel);
			jdialog.find('.btn-primary').html(T3Theme.langs.lblOk);

			jdialog.removeClass('modal-alert modal-confirm')
				.addClass('modal-prompt')
				.modal('show');
		},
		
		onCompile: function(completed, total){
			
			var jpg = $('#t3-admin-tm-recss-progress'),
				percent = Math.max(jpg.data('percent') || 10, Math.ceil(completed / total * 100));
				//trick for user experience (10%)

			jpg.data('percent', percent).find('.bar').css('width', percent + '%');
			if(percent >= 100){
				setTimeout(function(){
					jpg.data('percent', 0).addClass('invisible').find('.bar').width('0%');
				}, 650);
			}
		}
	});

	$(document).ready(function(){
		T3Theme.initialize();
	});
	
}(jQuery);

!function ($) {
	
	$(document).ready(function(){
		if(typeof MooRainbow == 'undefined'){ //only initialize when there was no Joomla default color picker

			$.extend(T3Theme, {

				colors: {
					aliceblue: '#F0F8FF',
					antiquewhite: '#FAEBD7',
					aqua: '#00FFFF',
					aquamarine: '#7FFFD4',
					azure: '#F0FFFF',
					beige: '#F5F5DC',
					bisque: '#FFE4C4',
					black: '#000000',
					blanchedalmond: '#FFEBCD',
					blue: '#0000FF',
					blueviolet: '#8A2BE2',
					brown: '#A52A2A',
					burlywood: '#DEB887',
					cadetblue: '#5F9EA0',
					chartreuse: '#7FFF00',
					chocolate: '#D2691E',
					coral: '#FF7F50',
					cornflowerblue: '#6495ED',
					cornsilk: '#FFF8DC',
					crimson: '#DC143C',
					cyan: '#00FFFF',
					darkblue: '#00008B',
					darkcyan: '#008B8B',
					darkgoldenrod: '#B8860B',
					darkgray: '#A9A9A9',
					darkgrey: '#A9A9A9',
					darkgreen: '#006400',
					darkkhaki: '#BDB76B',
					darkmagenta: '#8B008B',
					darkolivegreen: '#556B2F',
					darkorange: '#FF8C00',
					darkorchid: '#9932CC',
					darkred: '#8B0000',
					darksalmon: '#E9967A',
					darkseagreen: '#8FBC8F',
					darkslateblue: '#483D8B',
					darkslategray: '#2F4F4F',
					darkslategrey: '#2F4F4F',
					darkturquoise: '#00CED1',
					darkviolet: '#9400D3',
					deeppink: '#FF1493',
					deepskyblue: '#00BFFF',
					dimgray: '#696969',
					dimgrey: '#696969',
					dodgerblue: '#1E90FF',
					firebrick: '#B22222',
					floralwhite: '#FFFAF0',
					forestgreen: '#228B22',
					fuchsia: '#FF00FF',
					gainsboro: '#DCDCDC',
					ghostwhite: '#F8F8FF',
					gold: '#FFD700',
					goldenrod: '#DAA520',
					gray: '#808080',
					grey: '#808080',
					green: '#008000',
					greenyellow: '#ADFF2F',
					honeydew: '#F0FFF0',
					hotpink: '#FF69B4',
					indianred : '#CD5C5C',
					indigo : '#4B0082',
					ivory: '#FFFFF0',
					khaki: '#F0E68C',
					lavender: '#E6E6FA',
					lavenderblush: '#FFF0F5',
					lawngreen: '#7CFC00',
					lemonchiffon: '#FFFACD',
					lightblue: '#ADD8E6',
					lightcoral: '#F08080',
					lightcyan: '#E0FFFF',
					lightgoldenrodyellow: '#FAFAD2',
					lightgray: '#D3D3D3',
					lightgrey: '#D3D3D3',
					lightgreen: '#90EE90',
					lightpink: '#FFB6C1',
					lightsalmon: '#FFA07A',
					lightseagreen: '#20B2AA',
					lightskyblue: '#87CEFA',
					lightslategray: '#778899',
					lightslategrey: '#778899',
					lightsteelblue: '#B0C4DE',
					lightyellow: '#FFFFE0',
					lime: '#00FF00',
					limegreen: '#32CD32',
					linen: '#FAF0E6',
					magenta: '#FF00FF',
					maroon: '#800000',
					mediumaquamarine: '#66CDAA',
					mediumblue: '#0000CD',
					mediumorchid: '#BA55D3',
					mediumpurple: '#9370D8',
					mediumseagreen: '#3CB371',
					mediumslateblue: '#7B68EE',
					mediumspringgreen: '#00FA9A',
					mediumturquoise: '#48D1CC',
					mediumvioletred: '#C71585',
					midnightblue: '#191970',
					mintcream: '#F5FFFA',
					mistyrose: '#FFE4E1',
					moccasin: '#FFE4B5',
					navajowhite: '#FFDEAD',
					navy: '#000080',
					oldlace: '#FDF5E6',
					olive: '#808000',
					olivedrab: '#6B8E23',
					orange: '#FFA500',
					orangered: '#FF4500',
					orchid: '#DA70D6',
					palegoldenrod: '#EEE8AA',
					palegreen: '#98FB98',
					paleturquoise: '#AFEEEE',
					palevioletred: '#D87093',
					papayawhip: '#FFEFD5',
					peachpuff: '#FFDAB9',
					peru: '#CD853F',
					pink: '#FFC0CB',
					plum: '#DDA0DD',
					powderblue: '#B0E0E6',
					purple: '#800080',
					red: '#FF0000',
					rosybrown: '#BC8F8F',
					royalblue: '#4169E1',
					saddlebrown: '#8B4513',
					salmon: '#FA8072',
					sandybrown: '#F4A460',
					seagreen: '#2E8B57',
					seashell: '#FFF5EE',
					sienna: '#A0522D',
					silver: '#C0C0C0',
					skyblue: '#87CEEB',
					slateblue: '#6A5ACD',
					slategray: '#708090',
					slategrey: '#708090',
					snow: '#FFFAFA',
					springgreen: '#00FF7F',
					steelblue: '#4682B4',
					tan: '#D2B48C',
					teal: '#008080',
					thistle: '#D8BFD8',
					tomato: '#FF6347',
					turquoise: '#40E0D0',
					violet: '#EE82EE',
					wheat: '#F5DEB3',
					white: '#FFFFFF',
					whitesmoke: '#F5F5F5',
					yellow: '#FFFF00',
					yellowgreen: '#9ACD32'
				},

				cleanHex: function(hex) {
					return hex.replace(/[^A-F0-9]/ig, '');
				},

				expandHex: function(hex) {
					hex = T3Theme.cleanHex(hex);
					if( !hex ) return null;
					if( hex.length === 3 ) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
					return hex.length === 6 ? hex : null;
				}
			});

			$('.input-colorpicker').on('keyup.t3color paste.t3color', function(e){
				if( e.keyCode === 9 ) {
					this.value = $(this).next().val();
				} else {
					var color = T3Theme.colors[this.value.toLowerCase()];
					if(!color){
						color = T3Theme.expandHex(this.value);	
					}
					
					if(color){
						$(this).next().data('t3force', 1).val(color).trigger('keyup.miniColors');
					}
				}	
			}).after('<input type="hidden" />').next().miniColors({
				opacity: true,
				change: function(hex, rgba) {
					if($(this).data('t3force')){
						$(this).data('t3force', 0);
					} else {
						$(this).prev().val(hex);
					}
				}
			});
		}
	});
	
}(jQuery);
