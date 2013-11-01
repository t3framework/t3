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

var T3Admin = window.T3Admin || {};

!function ($) {

	$.extend(T3Admin, {
		
		initToolbar: function(){
			//t3 added
			$('#t3-admin-tb-recompile').on('click', function(){
				var jrecompile = $(this);
				jrecompile.addClass('loading');

				$.ajax({
					url: T3Admin.adminurl,
					data: {'t3action': 'lesscall', 'styleid': T3Admin.templateid },
					success: function(rsp){
						jrecompile.removeClass('loading');

						rsp = $.trim(rsp);
						if(rsp){
							var json = rsp;
							if(rsp.charAt(0) != '[' && rsp.charAt(0) != '{'){
								json = rsp.match(new RegExp('{[\["].*}'));
								if(json && json[0]){
									json = json[0];
								}
							}

							if(json && typeof json == 'string'){
								
								rsp = rsp.replace(json, '');

								try {
									json = $.parseJSON(json);
								} catch (e){
									json = {
										error: T3Admin.langs.unknownError
									}
								}
							}

							T3Admin.systemMessage(rsp || json.error || json.successful);
						}
					},

					error: function(){
						jrecompile.removeClass('loading');
						T3Admin.systemMessage(T3Admin.langs.unknownError);
					}
				});
				return false;
			});

			$('#t3-admin-tb-themer button').on('click', function(){
				if(!T3Admin.themermode){
					
					$('#t3-admin-tb-megamenu button').popover('hide');
					T3Admin.tbmmid = 0;
					
					$(this).popover('show');

					clearTimeout(T3Admin.tbthemerid);
					T3Admin.tbthemerid = setTimeout(function(){
						$('#t3-admin-tb-themer button').popover('hide');
					}, 2000);
				} else {
					$(this).popover('hide');
					
					window.location.href = T3Admin.themerUrl;
				}
				return false;
			}).popover({
				trigger: 'manual',
				placement: 'bottom',
				container: 'body'
			});
		

			$('#t3-admin-tb-megamenu button').on('click', function(){
				
				if($('#jform_params_navigation_type').val() != 'megamenu' && !T3Admin.tbmmid){
					
					$('#t3-admin-tb-themer button').popover('hide');
					$(this).popover('show');

					clearTimeout(T3Admin.tbmmid);
					T3Admin.tbmmid = setTimeout(function(){
						$('#t3-admin-tb-megamenu button').popover('hide');
						T3Admin.tbmmid = 0;
					}, 3000);
				} else {
					window.location.href = T3Admin.megamenuUrl;
				}
				
				return false;
			}).popover({
				trigger: 'manual',
				placement: 'bottom',
				container: 'body'
			});		

			//for style toolbar
			$('#t3-admin-tb-style-save-save').on('click', function(){
				Joomla.submitbutton('style.apply');
			});

			$('#t3-admin-tb-style-save-close').on('click', function(){
				Joomla.submitbutton('style.save');
			});
			
			$('#t3-admin-tb-style-save-clone').on('click', function(){
				Joomla.submitbutton('style.save2copy');
			});

			$('#t3-admin-tb-close').on('click', function(){
				Joomla.submitbutton(($(this).hasClass('template') ? 'template' : 'style') + '.cancel');
			});
		},

		initRadioGroup: function(){
			
			$('.radio label').unbind('click').click(function() {
				var label = $(this),
					input = $('#' + label.attr('for'));

				if (!input.prop('checked')){
					label.addClass('active').siblings().removeClass('active');

					input.prop('checked', true).trigger('change');
				}
			});

			$('fieldset.radio')
				.removeClass('btn-group')
				.find('label').removeClass('btn btn-success btn-danger btn-primary');


			$('.radio input[checked=checked]').each(function(){
				$('label[for=' + $(this).attr('id') + ']').addClass('active');
			});

			$('.t3-admin-form').on('update', 'input[type=radio]', function(){
				if(this.checked){
					$(this)
						.closest('.radio')
						.find('label').removeClass('active')
						.filter('[for="' + this.id + '"]')
							.addClass('active');
				}
			});
		},
		
		initChosen: function(){

			$('#style-form').find('select').chosen({
				disable_search_threshold : 10,
				allow_single_deselect : true
			});
		},

		improveMarkup: function(){
			var jptitle = $('.pagetitle');
			if (!jptitle.length){
				jptitle = $('.page-title');
			}

			if(!jptitle.length){
				return;
			}

			var titles = jptitle.html().split(':');

			jptitle.removeClass('icon-48-thememanager').html(titles[0] + '<small>' + titles[1] + '</small>');

			//remove joomla title
			$('#template-manager .tpl-desc-name').remove();

			//template manager - J2.5
			$('#template-manager-css')
				.closest('form').addClass('form-inline')
				.find('button[type=submit]').addClass('btn');
		},

		hideDisabled: function(){
			$('#style-form').find('[disabled="disabled"]').filter(function(){
				return this.name.match(/^.*?\[params\]\[(.*?)\]/)
			}).closest('.control-group').hide();
		},

		initPreSubmit: function(){

			var form = document.adminForm;
			if(!form){
				return false;
			}

			var onsubmit = form.onsubmit;

			form.onsubmit = function(e){
				var json = {},
					urlparts = form.action.split('#');
					
				if(/apply|save2copy/.test(form['task'].value)){
					t3active = $('.t3-admin-nav .active a').attr('href').replace(/.*(?=#[^\s]*$)/, '').substr(1);

					if(urlparts[0].indexOf('?') == -1){
						urlparts[0] += '?t3lock=' + t3active;
					} else {
						urlparts[0] += '&t3lock=' + t3active;
					}
					
					form.action = urlparts.join('#');
				}
					
				if($.isFunction(onsubmit)){
					onsubmit();
				}
			};
		},

		initChangeStyle: function(){
			$('#t3-styles-list').on('change', function(){
				window.location.href = T3Admin.baseurl + '/index.php?option=com_templates&task=style.edit&id=' + this.value + window.location.hash;
			});
		},

		initMarkChange: function(){
			var allinput = $(document.adminForm).find(':input')
				.each(function(){
					$(this).data('org-val', (this.type == 'radio' || this.type == 'checkbox') ? $(this).prop('checked') : $(this).val());
				});

			setTimeout(function() {
				allinput.on('change', function(){
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

					var jgroup = jinput.closest('.control-group'),
						jpane = jgroup.closest('.tab-pane'),
						chretain = Math.max(0, (jgroup.data('chretain') || 0) + (!eq && jinput.data('included') ? 0 : (eq ? -1 : 1)));

					jgroup.data('chretain', chretain).toggleClass('t3-changed', !!(chretain));

					$('.t3-admin-nav .nav li').eq(jpane.index()).toggleClass('t3-changed', !!(!eq || jpane.find('.t3-changed').length));

					if(this.type == 'radio'){
						jinput = jinput.add(jgroup.find('[name="' + this.name + '"]'));
					}
					jinput.data('included', !eq);
				});
			}, 500);
		},

		initCheckupdate: function(){
			
			var tinfo = $('#t3-admin-tpl-info dd'),
				finfo = $('#t3-admin-frmk-info dd');

			T3Admin.chkupdating = null;
			T3Admin.tplname = tinfo.eq(0).html();
			T3Admin.tplversion = tinfo.eq(1).html();
			T3Admin.frmkname = finfo.eq(0).html();
			T3Admin.frmkversion = finfo.eq(1).html();
			
			$('#t3-admin-framework-home .updater, #t3-admin-template-home .updater').on('click', 'a.btn', function(){
				
				//if it is outdated, then we go direct to link
				if($(this).closest('.updater').hasClass('outdated')){
					return true;
				}

				//if we are checking, ignore this click, wait for it complete
				if(T3Admin.chkupdating){
					return false;
				}

				//checking
				$(this).addClass('loading');
				T3Admin.chkupdating = this;
				T3Admin.checkUpdate();

				return false;
			});
		},

		checkUpdate: function(){
			$.ajax({
				url: T3Admin.t3updateurl,
				data: {eid: T3Admin.eids},
				success: function(data) {
					var jfrmk = $('#t3-admin-framework-home .updater:first'),
						jtemp = $('#t3-admin-template-home .updater:first');

					jfrmk.find('.btn').removeClass('loading');
					jtemp.find('.btn').removeClass('loading');
					
					try {
						var ulist = $.parseJSON(data);
					} catch(e) {
						T3Admin.alert(T3Admin.langs.updateFailedGetList, T3Admin.chkupdating);
					}

					if (ulist instanceof Array) {
						if (ulist.length > 0) {
							
							var	chkfrmk = !jfrmk.hasClass('outdated'),
								chktemp = !jtemp.hasClass('outdated');

							if(chkfrmk || chktemp){
								for(var i = 0, il = ulist.length; i < il; i++){

									if(chkfrmk && ulist[i].element == T3Admin.felement && ulist[i].type == 'plugin'){
										jfrmk.addClass('outdated');
										jfrmk.find('.btn').attr('href', T3Admin.jupdateUrl).html(T3Admin.langs.updateDownLatest);
										jfrmk.find('h3').html(T3Admin.langs.updateHasNew.replace(/%s/g, T3Admin.frmkname));
										
										var ridx = 0,
											rvals = [T3Admin.frmkversion, T3Admin.frmkname, ulist[i].version];
										jfrmk.find('p').html(T3Admin.langs.updateCompare.replace(/%s/g, function(){
											return rvals[ridx++];
										}));

										T3Admin.langs.updateCompare.replace(/%s/g, function(){ return '' })
									}
									if(chktemp && ulist[i].element == T3Admin.telement && ulist[i].type == 'template'){
										jtemp.addClass('outdated');
										jtemp.find('.btn').attr('href', T3Admin.jupdateUrl).html(T3Admin.langs.updateDownLatest);

										jtemp.find('h3').html(T3Admin.langs.updateHasNew.replace(/%s/g, T3Admin.tplname));
										
										var ridx = 0,
											rvals = [T3Admin.tplversion, T3Admin.tplname, ulist[i].version];
										jtemp.find('p').html(T3Admin.langs.updateCompare.replace(/%s/g, function(){
											return rvals[ridx++];
										}));
									}
								}

								T3Admin.alert(T3Admin.langs.updateChkComplete, T3Admin.chkupdating);
							}
						}
					} else {
						T3Admin.alert(T3Admin.langs.updateFailedGetList, T3Admin.chkupdating);
					}

					T3Admin.chkupdating = null;
				},
				error: function() {
					T3Admin.alert(T3Admin.langs.updateFailedGetList, T3Admin.chkupdating);
					T3Admin.chkupdating = null;
				}
			});
		},

		initSystemMessage: function(){
			var jmessage = $('#system-message');
				
			if(!jmessage.length){
				jmessage = $('' + 
					'<dl id="system-message">' +
						'<dt class="message">Message</dt>' +
						'<dd class="message">' +
							'<ul><li></li></ul>' +
						'</dd>' +
					'</dl>').hide().appendTo($('#system-message-container'));
			}

			T3Admin.message = jmessage;
		},

		systemMessage: function(msg){
			T3Admin.message.show();
			if(T3Admin.message.find('li:first').length){
				T3Admin.message.find('li:first').html(msg).show();
			} else {
				T3Admin.message.html('' + 
					'<div class="alert">' +
						'<h4>Message</h4>' + 
						'<p>' + msg + '</p>' +
					'</div>');
			}
			
			clearTimeout(T3Admin.msgid);
			T3Admin.msgid = setTimeout(function(){
				T3Admin.message.hide();
			}, 5000);
		},

		alert: function(msg, place){
			clearTimeout($(place).data('alertid'));
			$(place).after('' + 
				'<div class="alert">' +
					'<p>' + msg + '</p>' +
				'</div>').data('alertid', setTimeout(function(){
					$(place).nextAll('.alert').remove();
				}, 5000));
		},

		switchTab: function () {
			$('.t3-admin-nav a[data-toggle="tab"]').on('shown', function (e) {
				var url = e.target.href;
			  	window.location.hash = url.substring(url.indexOf('#')).replace ('_params', '');
			});

			var hash = window.location.hash;
			if (hash) {
				$('a[href="' + hash + '_params' + '"]').tab ('show');
			} else {
				var url = $('.t3-admin-nav .nav-tabs li.active a').attr('href');
				if (url) {
			  		window.location.hash = url.substring(url.indexOf('#')).replace ('_params', '');
				} else {
					$('.t3-admin-nav .nav-tabs li:first a').tab ('show');
				}
			}
		},

		fixValidate: function(){
			if(typeof JFormValidator != 'undefined'){
				
				//overwrite
				JFormValidator.prototype.isValid = function (form) {
					
					var valid = true;

					// Precompute label-field associations
					var labels = document.getElementsByTagName('label');
					for (var i = 0; i < labels.length; i++) {
						if (labels[i].htmlFor != '') {
							var element = document.getElementById(labels[i].htmlFor);
							if (element) {
								element.labelref = labels[i];
							}
						}
					}

					// Validate form fields
					var elements = form.getElements('fieldset').concat(Array.from(form.elements));
					for (var i = 0; i < elements.length; i++) {
						if (this.validate(elements[i]) == false) {
							valid = false;
						}
					}

					// Run custom form validators if present
					new Hash(this.custom).each(function (validator) {
						if (validator.exec() != true) {
							valid = false;
						}
					});

					if (!valid) {
						var message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
						var errors = jQuery("label.invalid");
						var error = new Object();
						error.error = new Array();
						for (var i=0;i < errors.length; i++) {
							var label = jQuery(errors[i]).text();
							if (label != 'undefined') {
								error.error[i] = message+label.replace("*", "");
							}
						}
						Joomla.renderMessages(error);
					}

					return valid;
				};

				JFormValidator.prototype.handleResponse = function(state, el){
					// Find the label object for the given field if it exists
					//if (!(el.labelref)) {
					//	var labels = $$('label');
					//	labels.each(function(label){
					//		if (label.get('for') == el.get('id')) {
					//			el.labelref = label;
					//		}
					//	});
					//}

					// Set the element and its label (if exists) invalid state
					if (state == false) {
						el.addClass('invalid');
						el.set('aria-invalid', 'true');
						if (el.labelref) {
							document.id(el.labelref).addClass('invalid');
							document.id(el.labelref).set('aria-invalid', 'true');
						}
					} else {
						el.removeClass('invalid');
						el.set('aria-invalid', 'false');
						if (el.labelref) {
							document.id(el.labelref).removeClass('invalid');
							document.id(el.labelref).set('aria-invalid', 'false');
						}
					}
				};

			}
		}
	});
	
	$(document).ready(function(){
		T3Admin.initSystemMessage();
		T3Admin.improveMarkup();
		T3Admin.initMarkChange();
		T3Admin.initToolbar();
		T3Admin.initRadioGroup();
		T3Admin.initChosen();
		T3Admin.initPreSubmit();
		T3Admin.hideDisabled();
		T3Admin.initChangeStyle();
		//T3Admin.initCheckupdate();
		T3Admin.switchTab();
		T3Admin.fixValidate();
	});
	
}(jQuery);