/** 
 *------------------------------------------------------------------------------
 * @package   T3 Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright Copyright (C) 2004-2013 JoomlArt.com. All Rights Reserved.
 * @license   GNU General Public License; http://www.gnu.org/licenses/gpl.html
 * @author    JoomlArt, JoomlaBamboo 
 *            If you want to be come co-authors of this project, please follow 
 *            our guidelines at http://t3-framework.org/contribute
 *------------------------------------------------------------------------------
 */

!function($){

	var T3Depend = window.T3Depend = window.T3Depend || { 	
		
		depends: {},
		controls: {},
		ajaxs: {},

		register: function(to, depend){
			var controls = this.controls;
			
			if(!controls[to]){
				controls[to] = [];
				
				var inst = this;
				this.elmsFrom(to).on('change.less', function(e){
					inst.change(this);
				});
			}
			
			if($.inArray(depend, controls[to]) == -1){
				controls[to].push(depend);
			}
		},
		
		change: function(ctrlelm){
			var controls = this.controls,
				depends = this.depends,
				ctrls = controls[ctrlelm.name],
				form = this;

				
			if(!ctrls){
				ctrls = controls[ctrlelm.name.substr(ctrlelm.name.length - 2)];
			}
			
			if(!ctrls){
				return false;
			}
			
			$.each(ctrls, function(idx, ectrl){
				var showup = true;
				
				$.each(depends[ectrl], function(ctrl, cvals){
					if(showup){
						var celms = form.elmsFrom(ctrl);
						showup = showup && !!($.grep(celms, function(celm){ return celm._disabled; }).length == 0);
						if(showup){
							showup = showup && !!($.grep(form.valuesFrom(celms), function(val){ return ($.inArray(val, cvals) != -1); }).length);
						}
					}
				});
				
				form.elmsFrom(ectrl).each(function(){
					if(showup){
						form.enable(this);
					} else {
						form.disable(this);
					}
				});
				
				if(controls[ectrl] && controls[ectrl] != ectrl){
					form.elmsFrom(this).eq(0).trigger('change');
				}
				
			});
		},
		
		add: function(control, info){
			
			var depends = this.depends,
				form = this,
				name = info.group + '[' + control + ']';
				
			info = $.extend({
				group: 'params',
				control: name
			}, info);
			
			$.each(info.elms.split(','), function(el){
				var elm = info.group +'[' + $.trim(this) + ']';
				
				if (!depends[elm]) {
					depends[elm] = {};
				}
				
				if (!depends[elm][name]) {
					depends[elm][name] = [];
				}
				
				depends[elm][name].push(info.val);
				
				form.register(name, elm);
				
			});
		},
		
		start: function(){
			$(document.adminForm).find('h4.block-head').parent().addClass('segment');
			
			this.update();
		},
		
		update: function () {
			var form = this;
			$.each(this.controls, function(ctrl, ctrls){
				form.elmsFrom(ctrl).trigger('change');
			});
		},
		
		enable: function (el) {
			el._disabled = false; //selector 'li' is J2.5 compactible
			$(el).closest('li, div.control-group').css('display', 'block');
		},
		
		disable: function (el) {
			el._disabled = true; //selector 'li' is J2.5 compactible
			$(el).closest('li, div.control-group').css('display', 'none');
		},
		
		elmsFrom: function(name){
			var el = document.adminForm[name];
			if(!el){
				el = document.adminForm[name + '[]'];
			}
			
			return $(el);
		},
		
		valuesFrom: function(els){
			var vals = [];
			
			$(els).each(function(){
				var type = this.type,
					val = $.makeArray(((type == 'radio' || type == 'checkbox') && !this.checked) ? null : $(this).val());

				for (var i = 0, l = val.length; i < l; i++){
					if($.inArray(val[i], vals) == -1){
						vals.push(val[i]);
					}
				}
			});
			
			return vals;
		},

		addajax: function(name, info){
			var ajaxs = this.ajaxs;
				
			info = $.extend({
				url: info.site == 'admin' ? T3Depend.adminurl : T3Depend.rooturl,
				func: ''
			}, info);

			if(info.query){
				var urlparts = info.url.split('#');
				if(urlparts[0].indexOf('?') == -1){
					urlparts[0] += '?' + info.query;
				} else {
					urlparts[0] += '&' + info.query;
				}
				
				info.url = urlparts.join('#');
			}

			if(!ajaxs[name]){
				ajaxs[name] = {};

				var inst = this;
				ajaxs[name].indicator = this.elmsFrom(name).on('change.less', function(e){
					inst.loadajax(this);
				}).after('' +
					'<div class="progress progress-striped progress-mini active">' +
						'<div class="bar" style="width: 100%"></div>' +
					'</div>').next().hide();
			}

			ajaxs[name].info = info;
		},

		loadajax: function(ctrlelm){
			var ajaxs = this.ajaxs,
				name = ctrlelm.name,
				ctrl = ajaxs[name],
				form = this;

			if(!ctrl){
				ctrl = ajaxs[name.substr(name.length - 2)];
			}
			
			if(!ctrl){
				return false;
			}

			var info = ctrl.info;
			if(!info){
				return false;
			}

			if(ctrl.elms && ctrl.elms.length){
				$(ctrl.elms).remove();
				ctrl.elms.length = 0;
			} else {
				ctrl.elms = [];
			}

			if(ctrl.indicator.next('.chzn-container').length){
				ctrl.indicator.insertAfter(ctrl.indicator.next('.chzn-container'));
			}

			if(ctrl.indicator.next('#t3-layout-clone-btns').length){
				ctrl.indicator.insertAfter($('#t3-layout-clone-btns'));	
			}

			ctrl.indicator.show();
			$.get(info.url, { jvalue: form.valuesFrom(form.elmsFrom(name))[0], _: $.now() }, function(rsp){
				ctrl.indicator.hide();

				var parts = ctrl.info.func.split('.'),
					fobj = window;

				for(var i = 0; i < parts.length; i++){
					if(!(fobj = fobj[parts[i]])) {
						break;
					}
				}

				if(fobj && i == parts.length && $.isFunction(fobj)){
					fobj(form, ctrlelm, ctrl, rsp);
				}
			});
		},
		
		segment: function(seg){
			if($(seg).hasClass('close')){
				this.showseg(seg);
			} else {
				this.hideseg(seg);
			}
		},
		
		showseg: function(seg){
			
			var segelm = $(seg),
				snext = segelm.parent().next();
			
			while(snext.length && !snext.hasClass('segment')){
				snext.css('display', snext.data('jdisplay') || '');
				snext = snext.next();
			}
			
			segelm.removeClass('close').addClass('open');
		},
		
		hideseg: function(seg){
			var segelm = $(seg),
				snext = segelm.parent().next();
			
			while(snext.length && !snext.hasClass('segment')){
				snext.data('jdisplay', snext.css('display')).css('display', 'none');
				snext = snext.next();
			}
			
			segelm.removeClass('open').addClass('close');  
		}
	};

	var JAFileConfig = window.JAFileConfig = window.JAFileConfig || {
		
		vars: {
		},
		
		initialize: function(optionid){
			var vars = this.vars;
			vars.group = 't3form';
			vars.el = document.getElementById(optionid);
			
			var adminlist = $('#module-sliders').find('ul.adminformlist:first');
			if(adminlist.length){
				$('<li class="clearfix level2"></li>').appendTo(adminlist);
			}
		},
		
		changeProfile: function(profile){
			if(profile == ''){
				return;
			}
			
			this.vars.active = profile;
			this.fillData();
			
			if(T3Depend && T3Depend.update){
				T3Depend.update();
			}
		},
		
		serializeArray: function(){
			var vars = this.vars,
				els = [],
				allelms = document.adminForm.elements,
				pname1 = vars.group + '\\[params\\]\\[.*\\]',
				pname2 = vars.group + '\\[params\\]\\[.*\\]\\[\\]';
				
			for (var i = 0, il = allelms.length; i < il; i++){
			    var el = $(allelms[i]);
				
			    if (el.name && ( el.name.test(pname1) || el.name.test(pname2))){
			    	els.push(el);
			    }
			}
			
			return els;
		},

		fillData: function (){
			var vars = this.vars,
				els = this.serializeArray(),
				profile = T3Depend.profiles[vars.active],
				form = this;
				
			if(els.length == 0 || !profile){
				return;
			}
			
			$.each(els, function(){
				var name = this.getName(this),
					values = (profile[name] != undefined) ? profile[name] : '';
				
				form.setValues(this, $.makeArray(values));
			});
		},
		
		valuesFrom: function(els){
			var vals = [];
			
			$(els).each(function(){
				var type = this.type,
					val = $.makeArray(((type == 'radio' || type == 'checkbox') && !this.checked) ? null : $(this).val());

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
					jel.prop('checked', $.inArray(el.value, vals) != -1);
				} else {
					jel.attr('placeholder', vals[0]);
					jel.val(vals[0]);
				}
			}
		},
		
		getName: function(el){
			var matches = el.name.match(this.vars.group + '\\[params\\]\\[([^\\]]*)\\]');
			if (matches){
				return matches[1];
			}
			
			return '';
		},
		
		
		deleteProfile: function(){
			if(confirm(JAFileConfig.langs.confirmDelete)){			
				this.submitForm(JAFileConfig.mod_url + '?dptask=delete&profile=' + this.vars.active + '&template='+ JAFileConfig.template, {}, 'profile');
			}		
		},
		
		duplicateProfile: function (){
			var nname = prompt(JAFileConfig.langs.enterName);
			
			if(nname){
				nname = nname.replace(/[^0-9a-zA-Z_-]/g, '').replace(/ /, '').toLowerCase();
				if(nname == ''){
					alert(JAFileConfig.langs.correctName);
					return this.cloneProfile();
				}
				
				JAFileConfig.profiles[nname] = JAFileConfig.profiles[this.vars.active];
				
				this.submitForm(JAFileConfig.mod_url + '?dptask=duplicate&profile=' + nname + '&from=' + this.vars.active + '&template=' + JAFileConfig.template, {}, 'profile');
			}
		},
		
		saveProfile: function (task){

			if(task){
				JAFileConfig.profiles[this.vars.active] = this.rebuildData();
				this.submitForm(JAFileConfig.mod_url + '?dptask=save&profile=' + this.vars.active, JAFileConfig.profiles[this.vars.active], 'profile', task);
			}
		},
		
		submitForm: function(url, request, type, task){
			if(JAFileConfig.run){
				JAFileConfig.ajax.cancel();
			}
			
			JAFileConfig.run = true;
	    	
			JAFileConfig.ajax = $.ajax({
				type: 'post',
				url: url,
				data: request,
				complete: function(result){
					
					JAFileConfig.run = false;
					
					if(result == ''){
						return;
					}
					
					var vars = JAFileConfig;
					
					alert(json.error || json.successfull);
					
					if(result.profile){
						switch (result.type){	
							case 'new':
								Joomla.submitbutton(document.adminForm.task.value);
							break;
							
							case 'delete':
								if(result.template == 0){
									var opts = vars.el.options;
									
									for(var j = 0, jl = opts.length; j < jl; j++){
										if(opts[j].value == result.profile){
											vars.el.remove(j);
											break;
										}
									}
								} else {
									JAFileConfig.profiles[result.profile] = JAFileConfig.tempprofiles[result.profile];
								}
								
								vars.el.options[0].selected = true;					
								JAFileConfig.changeProfile(vars.el.options[0].value);
							break;
							
							case 'duplicate':
								vars.el.options[vars.el.options.length] = new Option(result.profile, result.profile);							
								vars.el.options[vars.el.options.length - 1].selected = true;
								JAFileConfig.changeProfile(result.profile);
							break;
							
							default:
						}
					}
				}
			});
		},
		
		rebuildData: function (){
			var els = this.serializeArray(this.group),
				form = this,
				json = {};
				
			$.each(els, function(){
				var values = form.valuesFrom(this);
				if(values.length){
					json[this.getName(this)] = this.name.substr(-2) == '[]' ? values : values[0];
				}
			});
			
			return json;
		}
	};

	$(window).on('load', function() {
		setTimeout($.proxy(T3Depend.start, T3Depend), 100);
	});

}(window.$T3 || window.jQuery);

