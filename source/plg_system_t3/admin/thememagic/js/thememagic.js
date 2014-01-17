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
					$('#t3-admin-tm-preview').css('left', $('#t3-admin-thememagic').outerWidth(true));
				} else {
					$(this).addClass('active');
					$('#t3-admin-thememagic').css('left', - $('#t3-admin-thememagic').outerWidth(true));
					$('#t3-admin-tm-preview').css('left', 0);
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
			T3Theme.idle = true;
			this.jel = document.getElementById('t3-admin-theme-list');
			
			//change theme
			$('#t3-admin-theme-list').on('change', function(){
				
				var val = this.value;

				if(T3Theme.admin && $(document.adminForm).find('.t3-changed').length > 0){

					if(T3Theme.active == 'base' || T3Theme.active == -1){
						T3Theme.confirm(T3Theme.langs.saveChange.replace('%THEME%', T3Theme.langs.lblDefault), function(option){
							if(option){
								T3Theme.nochange = 1;
								T3Theme.saveThemeAs(function(){
									T3Theme.changeTheme(val);
								});
							} else {
								setTimeout(function(){
									T3Theme.changeTheme(val);
								}, 250); //delay to hide popup
							}
						});
					} else {
						T3Theme.confirm(T3Theme.langs.saveChange.replace('%THEME%', T3Theme.active), function(option){
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
				if(T3Theme.idle){
					T3Theme.applyLess();
				}

				return false;
			});
			

			if(T3Theme.admin){

				//save theme
				$('#t3-admin-tm-save').on('click', function(e){
					e.preventDefault();

					if(!$(this).hasClass('disabled') && T3Theme.idle){
						setTimeout(T3Theme.saveTheme, 1);
					}
				});
				//saveas theme
				$('#t3-admin-tm-saveas').on('click', function(e){
					e.preventDefault();
					
					if(!$(this).hasClass('disabled') && T3Theme.idle){
						setTimeout(T3Theme.saveThemeAs, 1);
					}
				});
				
				//delete theme
				$('#t3-admin-tm-delete').on('click', function(e){
					e.preventDefault();
					
					if(!$(this).hasClass('disabled') && T3Theme.idle){
						setTimeout(T3Theme.deleteTheme, 1);
					}
				});

				$(this.serializeArray()).on('change.less', function(){
					var jinput = $(this),
						oval = jinput.data('org-val'),
						nval = (this.type == 'radio' || this.type == 'checkbox') ? jinput.prop('checked') : jinput.val(),
						eq = true;

					if(oval != nval){
						if($.isArray(oval) && $.isArray(nval)){
							if(oval.length != nval.length){
								eq = false;
							} else {
								for(var i = 0; i < oval.length; i++){
									if(oval[i] != nval[i]){
										eq = false;
										break;
									}
								}
							}
						} else {
							eq = false;
						}
					}

					jinput.closest('.control-group')[eq ? 'removeClass' : 'addClass']('t3-changed');
				});
			}

			$(this.serializeArray()).each(function() {
				if(!$(this).attr('placeholder')){
					$(this).attr('placeholder', T3Theme.data.base[T3Theme.getName(this)]);
				}
			});

			if(T3Theme.active != -1){
				T3Theme.fillData();
			}

			$('#t3-admin-tm-save, #t3-admin-tm-delete').parent().toggle($('#t3-admin-theme-list').val() != 'base');
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
			
			T3Theme.setProgress(0);

			var nvars = T3Theme.rebuildData(true),
				jsonstr = JSON.stringify(nvars);

			if(!force && T3Theme.jsonstr === jsonstr){
	
				T3Theme.setProgress(100);
			
				return false;
			}

			T3Theme.variables = nvars;
			T3Theme.jsonstr = jsonstr;

			setTimeout(function(){

				var wnd = (document.getElementById('t3-admin-tm-ifr-preview').contentWindow || window.frames['t3-admin-tm-ifr-preview']);
				if(wnd.location.href.indexOf('themer=') == -1){
					var urlparts = wnd.location.href.split('#');
					urlparts[0] += urlparts[0].indexOf('?') == -1 ? '?themer=1' : '&themer=1';
					wnd.location.href = urlparts.join('#');
					
				} else {
					if(!wnd.T3Theme || !wnd.T3Theme.applyLess({
							template: T3Theme.template,
							vars: T3Theme.variables,
							theme: T3Theme.active,
							others: T3Theme.themes[T3Theme.active]
						})){

						T3Theme.showMsg(T3Theme.langs.previewError, '', true, function(option){
							$('#t3-admin-thememagic-dlg').modal('hide');
						});
					}
				}
			}, 10);
				
			return false;
		},
		
		changeTheme: function(theme, pass){
			if($.trim(theme) == ''){
				return false;
			}
			
			//enable or disable control buttons
			$('#t3-admin-tm-save, #t3-admin-tm-delete').parent().toggle(theme != 'base');

			T3Theme.active = theme;	//store the current theme
			
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

				//store new original value
				$(this).data('org-val', (this.type == 'radio' || this.type == 'checkbox') ? $(this).prop('checked') : $(this).val());
			});

			if(typeof T3Depend != 'undefined'){
				T3Depend.update();
			}

			//reset form state when new data is filled
			T3Theme.updateColor();
			$(document.adminForm).find('.t3-changed').removeClass('t3-changed');
		},

		updateColor: function(){
			$(document.adminForm).find('.t3tm-color').each(function(){
				var hex = this.value;
				if(hex == ''){
					hex = $(this).attr('placeholder');
				}

				if(hex.charAt(0) === '@' || hex.toLowerCase() == 'inherit' || hex.toLowerCase() == 'transparent' || hex.match(/[\(\){}]/)){
					$(this).nextAll('.miniColors-triggerWrap').find('.miniColors-trigger').css('background-color', '#fff');
				} else {
					$(this).next().val(hex).trigger('keyup.miniColors');
				}
			});
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

		elmsFrom: function(name){
			var el = document.adminForm[name];
			if(!el){
				el = document.adminForm[name + '[]'];
			}
			
			return $(el);
		},
		
		setValues: function(el, vals){
			var jel = $(el);
			
			if(jel.prop('tagName').toUpperCase() == 'SELECT'){
				jel.val(vals);
				
				if($.makeArray(jel.val())[0] != vals[0]){

					if(T3Theme.placeholder && T3Theme.data.base[T3Theme.getName(el)] == vals[0]){
						jel.val('-1');
					} else {
						var name = T3Theme.getName(el),
							celm = T3Theme.elmsFrom('t3form[thememagic][' + name + '-custom]');

						if(!celm.length){
							celm = T3Theme.elmsFrom('t3form[thememagic][' + name + '_custom]');						
						}

						if(celm.length){
							jel.val('undefined').trigger('change.depend');

							//T3Theme.setValues(celm, vals);
						} else {
							jel.val('-1');
						}
					}
				}
			}else {
				if(jel.prop('type') == 'checkbox' || jel.prop('type') == 'radio'){
					jel.prop('checked', $.inArray(el.value, vals) != -1).trigger('change.depend');

				} else {
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
					
					if(json[k] == 'undefined' || json[k] == ''){
						delete json[k];
					} else {
						if(k.match(/([_-])custom/)){
							json[k.replace(/[_-]custom/, '')] = json[k];	
						}
					}
				}
			}
			
			return json;
		},

		filtercolor: function(hex){
			if(hex.charAt(0) === '@' || hex.toLowerCase() == 'inherit' || hex.toLowerCase() == 'transparent' || T3Theme.colors[hex.toLowerCase()] || hex.match(/[\(\){}]/)){
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

		filterfont: function(val){			
			val = val.split(',');
			if(val.length > 1){
				for(var i = 0; i < val.length; i++){
					if($.trim(val[i]).indexOf(' ') !== -1){
						val[i] = '\'' + val[i].replace(/['"]/g, '') + '\'';
					}
				}
			}

			val = val.join(', ');
			return val.replace(/\s+/g, ' ');
		},

		padding: function(str, limit, pad){
			pad = pad || '0';

			while(str.length < limit){
				str = pad + str;
			}

			return str;
		},
		
		getName: function(el){
			var matches = (el.name || el[0].name).match('t3form\\[thememagic\\]\\[([^\\]]*)\\]');
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

					var nname = $('#theme-name').val() || '';
					nname = nname.replace(/[^0-9a-zA-Z_-]/g, '').replace(/ /, '').toLowerCase();

					if(nname == ''){

						T3Theme.saveThemeAs(callback);
						T3Theme.showMsg(T3Theme.langs.correctName);
						
						return false;
					} else if(T3Theme.themes && T3Theme.themes[nname] && nname != T3Theme.active){
						return T3Theme.confirm(T3Theme.langs.overwriteTheme.replace('%THEME%', nname), function(option){
							if(option){
								
								$('#t3-admin-thememagic-dlg').modal('hide');

								T3Theme.active = nname;
								T3Theme.saveTheme();
								$(T3Theme.jel).val(nname);

								if($.isFunction(callback)){
									callback();
								}
							}
						});
					}
					
					T3Theme.data[nname] = T3Theme.rebuildData();
					T3Theme.themes[nname] = $.extend({}, T3Theme.themes[T3Theme.active]);

					T3Theme.submitForm({
						t3task: 'save',
						theme: nname,
						from: T3Theme.active
					}, T3Theme.data[nname]);
				

					$('#t3-admin-thememagic-dlg').modal('hide');
				}

				if($.isFunction(callback)){
					callback();
				}

				return true;
			});

			return true;
		},

		//simple progress bar
		setProgress: function(ajax, less){
			var jpg = $('#t3-admin-tm-recss'),
				ajaxp = typeof ajax != 'undefined' ? ajax : ((jpg.data('ajaxpercent') || 100)),
				lessp = typeof less != 'undefined' ? less : ((jpg.data('lesspercent') || 100)),
				percent = Math.max((ajaxp + lessp) / 2, 1);

			if(jpg.hasClass('t3-anim-finish')){
				jpg.removeClass('t3-anim-slow t3-anim-finish').css('width', '0%');
			}

			jpg
				.data('ajaxpercent', ajaxp)
				.data('lesspercent', lessp)
				.addClass('t3-anim-slow')
				.css('width', percent + '%');
			
			clearTimeout(T3Theme.progressid);

			if(percent >= 100){
				jpg
					.removeClass('t3-anim-slow')
					.addClass('t3-anim-finish')
					.one($.support.transition.end, function () {
						setTimeout(function(){
							if(jpg.hasClass('t3-anim-finish')){
								jpg.removeClass('t3-anim-finish').css('width', '0%');
							}
						}, 1000);
					});

				T3Theme.idle = true;
			} else {
				T3Theme.idle = false;
			}
		},
		
		submitForm: function(params, data){
			if(T3Theme.run){
				T3Theme.ajax.abort();
			}

			//set initial to 1%
			T3Theme.setProgress(1);

			clearTimeout(T3Theme.progressid);
			T3Theme.progressid = setTimeout(function(){
				T3Theme.setProgress(10);
			}, 500);
			
			T3Theme.run = true;
			T3Theme.ajax = $.post(
				T3Theme.url + (T3Theme.url.indexOf('?') != -1 ? '' : '?') +
				$.param($.extend(params, {
					t3action: 'theme',
					t3template: T3Theme.template,
					styleid: T3Theme.templateid
				})) , data, function(result){
					
				T3Theme.run = false;

				clearTimeout(T3Theme.progressid);
				T3Theme.setProgress(100);

				if(result == ''){
					return;
				}
				
				try {
					result = $.parseJSON(result);
				} catch (e) {
					result = { error: T3Theme.langs.unknownError };
				}

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
						break;
					}

					if(result.type != 'delete'){
						$(document.adminForm).find('.t3-changed').removeClass('t3-changed');
					}
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

		showMsg: function(msg, type, hideprompt, callback){
			if(callback && $.isFunction(callback)){
				T3Theme.modalCallback = callback;
			}

			var jdialog = $('#t3-admin-thememagic-dlg');

			jdialog.find('.message-block').show().html('<div class="alert fade in">' + msg + '</div>');
			if(hideprompt){
				jdialog.find('.prompt-block').hide();
			}
			
			jdialog.find('.cancel').html(T3Theme.langs.lblCancel);
			jdialog.find('.btn-primary').html(T3Theme.langs.lblOk);

			jdialog.modal('show');
		},

		confirm: function(msg, callback){
			T3Theme.modalCallback = callback;

			var jdialog = $('#t3-admin-thememagic-dlg');
			jdialog.find('.prompt-block').hide();
			jdialog.find('.message-block').show().html(msg);
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
			T3Theme.setProgress(undefined, Math.max(1, Math.ceil(completed / total * 100)));
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

			$('.input-colorpicker, .minicolors, .t3tm-color').on('keyup.t3color paste.t3color', function(e){
				if( e.keyCode === 9 ) {
					this.value = $(this).next().val();
				} else {
					var color = $.trim(this.value);
					if(!color){
						color = $(this).attr('placeholder');
					}

					if(color.charAt(0) === '@' || color.toLowerCase() == 'inherit' || color.toLowerCase() == 'transparent' || color.match(/[\(\){}]/)){
						$(this).nextAll('.miniColors-triggerWrap').find('.miniColors-trigger').css('background-color', '#fff');
						return;
					}

					color = T3Theme.colors[$.trim(this.value.toLowerCase())];

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
						$(this).prev().val(hex).trigger('change.less');
					}
				}
			});
		}
	});
	
}(jQuery);
