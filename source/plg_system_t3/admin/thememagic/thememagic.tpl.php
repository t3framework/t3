<?php
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

defined('_JEXEC') or die;

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title><?php echo JText::_('T3_TM_TITLE'); ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/admin/bootstrap/css/bootstrap.css" />
		<link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/admin/plugins/miniColors/jquery.miniColors.css" />
		<link type="text/css" rel="stylesheet" href="<?php echo T3_ADMIN_URL; ?>/admin/thememagic/css/thememagic.css" />

		<script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/js/jquery-1.8.0.min.js"></script>
		<script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/bootstrap/js/bootstrap.js"></script>
	</head>

	<body<?php echo $tplparams->get('themermode', 1) == 0 ? ' class="no-magic"' : ''?>>
		<div id="wrapper">
			<?php if($tplparams->get('themermode', 1)): ?>
			<div id="t3-admin-thememagic">
				<a href="<?php echo JURI::base(true); ?>" class="themer-minimize"><i class="icon-remove-sign"></i><i class="icon-magic"></i>  <span><?php echo JText::_('T3_TM_MINIMIZE') ; ?></span></a>
				<a href="<?php echo $backurl; ?>" class="themer-close" title="<?php echo JText::_($isadmin ? 'T3_TM_BACK_TO_ADMIN' : 'T3_TM_EXIT'); ?>"><i class="icon-arrow-left"></i><?php echo JText::_($isadmin ? 'T3_TM_BACK_TO_ADMIN' : 'T3_TM_EXIT'); ?></a>

				<div class="t3-admin-tm-header">
				  <h2><strong><?php echo JText::_('T3_TM_CUSTOMIZING'); ?></strong> <span><?php echo $tplparams->get('sitename'); ?></span></h2>
				  <form id="t3-admin-tm-form" name="t3-admin-tm-form" class="form-validate form-inline">
					<div class="controls controls-row">
						<label for="t3-admin-theme-list"><?php echo JText::_('T3_TM_THEME_LABEL'); ?></label>
					  <?php
						echo JHTML::_('select.genericlist', $themes, 't3-admin-theme-list', 'autocomplete="off"', 'id', 'title', $tplparams->get('theme', -1));
					  ?>
					 
					  <div class="btn-group">
						<button id="t3-admin-tm-pvbtn" class="btn btn-primary"><?php echo JText::_('T3_TM_PREVIEW') ?></button>
						<?php if( $isadmin) : ?>
						<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
						<ul class="dropdown-menu">
						  <li><a id="t3-admin-tm-save" href="" title="<?php echo JText::_('T3_TM_SAVE') ?>"><?php echo JText::_('T3_TM_SAVE') ?></a></li>
						  <li><a id="t3-admin-tm-saveas" href="" title="<?php echo JText::_('T3_TM_SAVEAS') ?>"><?php echo JText::_('T3_TM_SAVEAS') ?></a></li>
						  <li><a id="t3-admin-tm-delete" href="" title="<?php echo JText::_('T3_TM_DELETE') ?>"><?php echo JText::_('T3_TM_DELETE') ?></a></li>
						</ul>
					  	<?php endif; ?>
					  </div>
					</div>
				  </form>
				</div>
	
				<form id="t3-admin-tm-variable-form" name="adminForm" class="form-validate">
					<div id="t3-admin-tm-recss-progress" class="progress progress-striped active fade invisible">
						<div class="bar"></div>
					</div>

					<div class="accordion" id="t3-admin-tm-accord">
						<?php
						$i = 0;
						foreach ($fieldSets as $name => $fieldSet) :
							$label = !empty($fieldSet->label) ? $fieldSet->label : 'T3T3'.$name.'_FIELDSET_LABEL';
						?>
							
						<div class="accordion-group<?php echo $i == 0?' active':'' ?>">
							<div class="accordion-heading">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#t3-admin-tm-accord" href="#<?php echo preg_replace( '/\s+/', ' ', $name);?>"><?php echo JText::_($label) ?></a>
							</div>
							<div id="<?php echo preg_replace( '/\s+/', ' ', $name);?>" class="accordion-body collapse<?php echo (($i == 0)? ' in' : ''); ?>">
								<div class="accordion-inner">
									<?php
									$fields = $form->getFieldset($name);
									$forders = array();
									foreach ($fields as $field) {
										$after = 0;
										$compare = $form->getFieldAttribute($field->fieldname, 'before', '', $field->group);
										if(empty($compare)){
											$compare = $form->getFieldAttribute($field->fieldname, 'after', '', $field->group);
											$after = 1;
										}
										if(!empty($compare)){
											$found = null;
											$i = 0;
											$compare = $field->formControl . '[' . $field->group . ']' . '[' . $compare . ']';
											
											foreach($forders as $ofield) {
												if ($compare == $ofield->name) {
													$found = $ofield;
													break;
												}
												$i++;
											}

											if($found && $i + $after < count($forders)){
												array_splice($forders, $i + $after, 0, array($field));
												continue;
											}
										}

										$forders[] = $field;
									}

									foreach ($forders as $field) :
										$hide = ($field->type === 'T3Depend' && $form->getFieldAttribute($field->fieldname, 'function', '', $field->group) == '@group');
										// add placeholder to Text input
										if ($field->type == 'Text') {
											$textinput = str_replace ('/>', ' placeholder="' . $form->getFieldAttribute($field->fieldname, 'default', '', $field->group).'"/>', $field->input);
										}
									?>
										<div class="control-group t3-control-group<?php echo $hide ? ' hide' : ''?>">
										<?php if (!$field->hidden) : ?>
											<div class="control-label t3-control-label">
												<?php echo preg_replace('/(\s*)for="(.*?)"(\s*)/i', ' ', $field->label); ?>
											</div>
										<?php endif; ?>
											<div class="controls t3-controls">
												<?php echo $field->type == 'Text'? $textinput : str_replace('value="#000000"', 'value=""', $field->input); ?>
											</div>
										</div>
									<?php
									endforeach;
									?>
								</div>
							</div>
						</div>

					<?php
					$i++;
						endforeach;
					?>
				</div>
			</form>
			</div>
			<?php else :?>
			
			<div id="t3-admin-tm-warning" class="modal hide fade">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3><?php echo JText::_('T3_TM_TITLE'); ?></h3>
				</div>
				<div class="modal-body">
					<p><?php echo JText::_('T3_MSG_ENABLE_THEMEMAGIC'); ?></p>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-primary" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('T3_LBL_OK') ?></a>
				</div>
			</div>

			<?php endif;?>
			<div id="t3-admin-tm-preview">
				<iframe id="t3-admin-tm-ifr-preview" frameborder="0" src="<?php echo $url . ($tplparams->get('theme', -1) != -1 ? ('&t3style=' . $tplparams->get('theme')) : '') ?>"></iframe>
			</div>

		</div>

		<?php if($tplparams->get('themermode', 1)): ?>
		<div id="t3-admin-thememagic-dlg" class="modal hide fade">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Save this theme as...</h3>
			</div>
			<div class="modal-body">
				<form id="prompt-form" name="prompt-form" class="form-horizontal prompt-block">
					<span class="help-block"><?php echo JText::_('T3_THEME_ASK_ADD_THEME') ?></span>
					<p>
						<input type="text" id="theme-name" placeholder="Theme name" style="width: 90%; margin-top: 10px;">
					</p>
				</form>
				<div class="message-block">
					<p></p>
				</div>
			</div>
			<div class="modal-footer">
				<a href="" class="btn cancel" data-dismiss="modal" aria-hidden="true"></a>
				<a href="" class="btn btn-primary"></a>
			</div>
		</div>
		
		
		<script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/js/json2.js"></script>
		<script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/plugins/miniColors/jquery.miniColors.js"></script>
		<script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/includes/depend/js/depend.js"></script>
		<script type="text/javascript" src="<?php echo T3_ADMIN_URL; ?>/admin/thememagic/js/thememagic.js"></script>
		<script type="text/javascript">
			// add class active for open 
			$('#t3-admin-tm-accord .accordion-group').on('hide', function () {
				$(this).removeClass('active');
			}).on('show', function() {
				$(this).addClass('active');
			});
			
			var T3Theme = window.T3Theme || {};
			T3Theme.admin = <?php echo intval($isadmin); ?>;
			T3Theme.data = <?php echo json_encode($jsondata); ?>;
			T3Theme.themes = <?php echo json_encode($themes); ?>;
			T3Theme.template = '<?php echo T3_TEMPLATE; ?>';
			T3Theme.url = '<?php echo JURI::root(true) . '/administrator/index.php'; ?>';
			T3Theme.langs = <?php echo json_encode($langs); ?>;
			T3Theme.active = '<?php echo $tplparams->get('theme', 'base')?>';
			T3Theme.variables = <?php echo ($tplparams->get('theme', -1) == -1 ? '{}' : 'T3Theme.data[T3Theme.active]') ?>;
			T3Theme.colorimgurl = '<?php echo T3_ADMIN_URL; ?>/admin/plugins/colorpicker/images/ui-colorpicker.png';
		</script>
		<?php else :?>
			<script type="text/javascript">
				$(document).ready(function(){
					$('#t3-admin-tm-warning').modal('show')
				});
			</script>
		<?php endif;?>
	</body>
</html>