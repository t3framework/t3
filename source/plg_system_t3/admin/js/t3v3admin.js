var T3V3Admin = window.T3V3Admin || {};

!function ($) {

	$.extend(T3V3Admin, {
		
		initBuildLessBtn: function(){
			//t3 added
			$('#t3-toolbar-recompile').on('click', function(){
				var jrecompile = $(this);
				jrecompile.addClass('loading');
				$.get(T3V3Admin.adminurl, {'t3action': 'lesscall'}, function(rsp){
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
								T3V3Admin.systemMessage(json.error || json.successful);
							}
						}
					}
				});
				return false;
			});

			$('#t3-toolbar-themer').on('click', function(){
				if(!T3V3Admin.themermode){
					alert(T3V3Admin.langs.enableThemeMagic);
				} else {
					window.location.href = T3V3Admin.themerUrl;
				}
				return false;
			});

			//for style toolbar
			$('#t3-toolbar-style-save-save').on('click', function(){
				Joomla.submitbutton('style.apply');
			});

			$('#t3-toolbar-style-save-close').on('click', function(){
				Joomla.submitbutton('style.save');
			});
			
			$('#t3-toolbar-style-save-clone').on('click', function(){
				Joomla.submitbutton('style.save2copy');
			});

			$('#t3-toolbar-close').on('click', function(){
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

			$('.t3-adminform').on('update', 'input[type=radio]', function(){
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
				window.location.href = T3V3Admin.baseurl + '/index.php?option=com_templates&task=style.edit&id=' + this.value;
			});
		},

		initMarkChange: function(){
			$(document.adminForm).on('change', ':input', function(){
				var jinput = $(this),
					oval = jinput.data('org-val'),
					nval = jinput.val(),
					mthd = 'removeClass',
					cmp = true;

				if(oval != nval){
					if($.isArray(oval) && $.isArray(nval)){
						if(oval.length != nval.length){
							cmp = false;
						} else {
							for(var i = 0; i < oval.length; i++){
								if(oval[i] != nval[i]){
									cmp = false;
									break;
								}
							}
						}
					} else {
						cmp = false;
					}
				}

				if(!cmp) {
					mthd = 'addClass';
				}

				jinput
					.add(jinput.next('.chzn-container'))
					[mthd]('t3-changed');

				var jpane = jinput.closest('.tab-pane');
				$('.t3-admin-nav .nav li').eq(jpane.index())[(!cmp || jpane.find('.t3-changed').length) ? 'addClass' : 'removeClass']('t3-changed');

			}).find(':input').each(function(){
				$(this).data('org-val', $(this).val());
			});
		},

		initCheckupdate: function(){
			
			var tinfo = $('#tpl-info dd'),
				finfo = $('#frmk-info dd');

			T3V3Admin.chkupdating = null;
			T3V3Admin.tplname = tinfo.eq(0).html();
			T3V3Admin.tplversion = tinfo.eq(1).html();
			T3V3Admin.frmkname = finfo.eq(0).html();
			T3V3Admin.frmkversion = finfo.eq(1).html();
			
			$('#framework-home .updater, #template-home .updater').on('click', 'a.btn', function(){
				
				//if it is outdated, then we go direct to link
				if($(this).closest('.updater').hasClass('outdated')){
					return true;
				}

				//if we are checking, ignore this click, wait for it complete
				if(T3V3Admin.chkupdating){
					return false;
				}

				//checking
				$(this).addClass('loading');
				T3V3Admin.chkupdating = this;
				T3V3Admin.checkUpdate();

				return false;
			});
		},

		checkUpdate: function(){
			$.ajax({
				url: T3V3Admin.t3updateurl,
				data: {eid: T3V3Admin.eids},
				success: function(data) {
					var jfrmk = $('#framework-home .updater:first'),
						jtemp = $('#template-home .updater:first');

					jfrmk.find('.btn').removeClass('loading');
					jtemp.find('.btn').removeClass('loading');
					
					try {
						var ulist = $.parseJSON(data);
					} catch(e) {
						T3V3Admin.alert(T3V3Admin.langs.updateFailedGetList, T3V3Admin.chkupdating);
					}

					if (ulist instanceof Array) {
						if (ulist.length > 0) {
							
							var	chkfrmk = !jfrmk.hasClass('outdated'),
								chktemp = !jtemp.hasClass('outdated');

							if(chkfrmk || chktemp){
								for(var i = 0, il = ulist.length; i < il; i++){

									if(chkfrmk && ulist[i].element == T3V3Admin.felement && ulist[i].type == 'plugin'){
										jfrmk.addClass('outdated');
										jfrmk.find('.btn').attr('href', T3V3Admin.jupdateUrl).html(T3V3Admin.langs.updateDownLatest);
										jfrmk.find('h3').html(T3V3Admin.langs.updateHasNew.replace(/%s/g, T3V3Admin.frmkname));
										
										var ridx = 0,
											rvals = [T3V3Admin.frmkversion, T3V3Admin.frmkname, ulist[i].version];
										jfrmk.find('p').html(T3V3Admin.langs.updateCompare.replace(/%s/g, function(){
											return rvals[ridx++];
										}));

										T3V3Admin.langs.updateCompare.replace(/%s/g, function(){ return '' })
									}
									if(chktemp && ulist[i].element == T3V3Admin.telement && ulist[i].type == 'template'){
										jtemp.addClass('outdated');
										jtemp.find('.btn').attr('href', T3V3Admin.jupdateUrl).html(T3V3Admin.langs.updateDownLatest);

										jtemp.find('h3').html(T3V3Admin.langs.updateHasNew.replace(/%s/g, T3V3Admin.tplname));
										
										var ridx = 0,
											rvals = [T3V3Admin.tplversion, T3V3Admin.tplname, ulist[i].version];
										jtemp.find('p').html(T3V3Admin.langs.updateCompare.replace(/%s/g, function(){
											return rvals[ridx++];
										}));
									}
								}

								T3V3Admin.alert(T3V3Admin.langs.updateChkComplete, T3V3Admin.chkupdating);
							}
						}
					} else {
						T3V3Admin.alert(T3V3Admin.langs.updateFailedGetList, T3V3Admin.chkupdating);
					}

					T3V3Admin.chkupdating = null;
				},
				error: function() {
					T3V3Admin.alert(T3V3Admin.langs.updateFailedGetList, T3V3Admin.chkupdating);
					T3V3Admin.chkupdating = null;
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

			T3V3Admin.message = jmessage;
		},

		systemMessage: function(msg){
			T3V3Admin.message.show();
			if(T3V3Admin.message.find('li:first').length){
				T3V3Admin.message.find('li:first').html(msg).show();
			} else {
				T3V3Admin.message.html('' + 
					'<div class="alert">' +
						'<h4>Message</h4>' + 
						'<p>' + msg + '</p>' +
					'</div>');
			}
			
			clearTimeout(T3V3Admin.msgid);
			T3V3Admin.msgid = setTimeout(function(){
				T3V3Admin.message.hide();
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
		T3V3Admin.initSystemMessage();
		T3V3Admin.initT3Title();
		T3V3Admin.initMarkChange();
		T3V3Admin.initBuildLessBtn();
		T3V3Admin.initRadioGroup();
		T3V3Admin.initChosen();
		T3V3Admin.initPreSubmit();
		T3V3Admin.hideDisabled();
		T3V3Admin.initChangeStyle();
		//T3V3Admin.initCheckupdate();
		T3V3Admin.switchTab();
	});
	
}(window.$ja || window.jQuery);