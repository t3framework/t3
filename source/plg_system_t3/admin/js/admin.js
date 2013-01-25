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

var T3Admin = window.T3Admin || {};

!function ($) {

	$.extend(T3Admin, {
		
		initBuildLessBtn: function(){
			//t3 added
			$('#t3-admin-tb-recompile').on('click', function(){
				var jrecompile = $(this);
				jrecompile.addClass('loading');
				$.get(T3Admin.adminurl, {'t3action': 'lesscall'}, function(rsp){
					jrecompile.removeClass('loading');

					rsp = $.trim(rsp);
					if(rsp){
						var json = rsp;
						if(rsp.charAt(0) != '[' && rsp.charAt(0) != '{'){
							json = rsp.match(/{.*?}/);
							if(json && json[0]){
								json = json[0];
							}
						}

						if(json && typeof json == 'string'){
							json = $.parseJSON(json);

							if(json && (json.error || json.successful)){
								T3Admin.systemMessage(json.error || json.successful);
							}
						}
					}
				});
				return false;
			});

			$('#t3-admin-tb-themer').on('click', function(){
				if(!T3Admin.themermode){
					alert(T3Admin.langs.enableThemeMagic);
				} else {
					window.location.href = T3Admin.themerUrl;
				}
				return false;
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
			//copy from J3.0
			// Turn radios into btn-group
			$('.radio.btn-group label').addClass('btn');
			$('.btn-group label').unbind('click').click(function() {
				var label = $(this),
					input = $('#' + label.attr('for'));

				if (!input.prop('checked')){
					label.closest('.btn-group')
						.find('label')
						.removeClass('active btn-success btn-danger btn-primary');

					label.addClass('active ' + (input.val() == '' ? 'btn-primary' : (input.val() == 0 ? 'btn-danger' : 'btn-success')));
					
					input.prop('checked', true).trigger('change');
				}
			});

			$('.t3-admin-form').on('update', 'input[type=radio]', function(){
				if(this.checked){
					$(this)
						.closest('.btn-group')
						.find('label').removeClass('active btn-success btn-danger btn-primary')
						.filter('[for="' + this.id + '"]')
							.addClass('active ' + ($(this).val() == '' ? 'btn-primary' : ($(this).val() == 0 ? 'btn-danger' : 'btn-success')));
				}
			});

			$('.btn-group input[checked=checked]').each(function(){
				if($(this).val() == ''){
					$('label[for=' + $(this).attr('id') + ']').addClass('active btn-primary');
				} else if($(this).val() == 0){
					$('label[for=' + $(this).attr('id') + ']').addClass('active btn-danger');
				} else {
					$('label[for=' + $(this).attr('id') + ']').addClass('active btn-success');
				}
			});
		},
		
		initChosen: function(){
			$('#style-form').find('select').chosen({
				disable_search_threshold : 10,
				allow_single_deselect : true
			});
		},

		initT3Title: function(){
			var jptitle = $('.pagetitle');
			if (!jptitle.length) jptitle = $('.page-title');
			var titles = jptitle.html().split(':');

			jptitle.html(titles[0] + '<small>' + titles[1] + '</small>');
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
				window.location.href = T3Admin.baseurl + '/index.php?option=com_templates&task=style.edit&id=' + this.value;
			});
		},

		initMarkChange: function(){
			$(document.adminForm).on('change', ':input', function(){
				var jinput = $(this),
					oval = jinput.data('org-val'),
					nval = jinput.val(),
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
					chretain = Math.max(0, (jgroup.data('chretain') || 0) + (eq ? -1 : 1));

				jgroup.data('chretain', chretain)
					[chretain ? 'addClass' : 'removeClass']('t3-changed');

				$('.t3-admin-nav .nav li').eq(jpane.index())[(!eq || jpane.find('.t3-changed').length) ? 'addClass' : 'removeClass']('t3-changed');

			}).find(':input').each(function(){
				$(this).data('org-val', $(this).val());
			});
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
			$('a[data-toggle="tab"]').on('shown', function (e) {
				var url = e.target.href;
			  	window.location.hash = url.substring(url.indexOf('#')).replace ('_params', '');
			});

			var hash = window.location.hash;
			if (hash) {
				$('a[href="' + hash + '_params' + '"]').tab ('show');
			} else {
				var url = $('ul.nav-tabs li.active a').attr('href');
				if (url) {
			  		window.location.hash = url.substring(url.indexOf('#')).replace ('_params', '');
				} else {
					$('ul.nav-tabs li:first a').tab ('show');
				}
			}
		}
	});
	
	$(document).ready(function(){
		T3Admin.initSystemMessage();
		T3Admin.initT3Title();
		T3Admin.initMarkChange();
		T3Admin.initBuildLessBtn();
		T3Admin.initRadioGroup();
		T3Admin.initChosen();
		T3Admin.initPreSubmit();
		T3Admin.hideDisabled();
		T3Admin.initChangeStyle();
		//T3Admin.initCheckupdate();
		T3Admin.switchTab();
	});
	
}(window.$T3 || window.jQuery);