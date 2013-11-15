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
 * @Link:         http://t3-framework.org 
 *------------------------------------------------------------------------------
 */

// Define constant
class T3Admin {

	protected $langs = array();

	/**
	 * function render
	 * render T3 administrator configuration form
	 *
	 * @return render success or not
	 */
	public function render(){
		$input  = JFactory::getApplication()->input;
		$body   = JResponse::getBody();
		$layout = T3_ADMIN_PATH . '/admin/tpls/default.php';

		if(file_exists($layout) && 'style' == $input->getCmd('view')){
			
			ob_start();
			$this->renderAdmin();
			$buffer = ob_get_clean();

			//this cause backtrack_limit in some server
			//$body = preg_replace('@<form\s[^>]*name="adminForm"[^>]*>(.*)</form>@msU', $buffer, $body);
			$opentags = explode('<form', $body);
			$endtags = explode('</form>', $body);
			$open = array_shift($opentags);
			$close = array_pop($endtags);

			//should not happend
			if(count($opentags) > 1){
				foreach ($opentags as $index => $value) {
					if(strpos($value, 'name="adminForm"') !== false){
						break;
					}

					$open = $open . '<form' . $value;
					$close = array_pop($endtags) . '</form>' . $close;
				}
			}

			$body = $open . $buffer . $close;
		}

		if(!$input->getCmd('file')){
			$body = $this->replaceToolbar($body);
		}

		$body = $this->replaceDoctype($body);

		JResponse::setBody($body);
	}

	public function addAssets(){

		// load template language
		JFactory::getLanguage()->load ('tpl_'.T3_TEMPLATE.'.sys', JPATH_ROOT, null, true);

		$langs = array(
			'unknownError' => JText::_('T3_MSG_UNKNOWN_ERROR'),

			'logoPresent' => JText::_('T3_LAYOUT_LOGO_TEXT'),
			'emptyLayoutPosition' => JText::_('T3_LAYOUT_EMPTY_POSITION'),
			'defaultLayoutPosition' => JText::_('T3_LAYOUT_DEFAULT_POSITION'),
			
			'layoutConfig' => JText::_('T3_LAYOUT_CONFIG_TITLE'),
			'layoutConfigDesc' => JText::_('T3_LAYOUT_CONFIG_DESC'),
			'layoutUnknownWidth' => JText::_('T3_LAYOUT_UNKN_WIDTH'),
			'layoutPosWidth' => JText::_('T3_LAYOUT_POS_WIDTH'),
			'layoutPosName' => JText::_('T3_LAYOUT_POS_NAME'),

			'layoutCanNotLoad' => JText::_('T3_LAYOUT_LOAD_ERROR'),

			'askCloneLayout' => JText::_('T3_LAYOUT_ASK_ADD_LAYOUT'),
			'correctLayoutName' => JText::_('T3_LAYOUT_ASK_CORRECT_NAME'),
			'askDeleteLayout' => JText::_('T3_LAYOUT_ASK_DEL_LAYOUT'),

			'lblDeleteIt' => JText::_('T3_LAYOUT_LABEL_DELETEIT'),
			'lblCloneIt' => JText::_('T3_LAYOUT_LABEL_CLONEIT'),

			'layoutEditPosition' => JText::_('T3_LAYOUT_EDIT_POSITION'),
			'layoutShowPosition' => JText::_('T3_LAYOUT_SHOW_POSITION'),
			'layoutHidePosition' => JText::_('T3_LAYOUT_HIDE_POSITION'),
			'layoutChangeNumpos' => JText::_('T3_LAYOUT_CHANGE_NUMPOS'),
			'layoutDragResize' => JText::_('T3_LAYOUT_DRAG_RESIZE'),
			'layoutHiddenposDesc' => JText::_('T3_LAYOUT_HIDDEN_POS_DESC'),
			
			'updateFailedGetList' => JText::_('T3_OVERVIEW_FAILED_GETLIST'),
			'updateDownLatest' => JText::_('T3_OVERVIEW_GO_DOWNLOAD'),
			'updateCheckUpdate' => JText::_('T3_OVERVIEW_CHECK_UPDATE'),
			'updateChkComplete' => JText::_('T3_OVERVIEW_CHK_UPDATE_OK'),
			'updateHasNew' => JText::_('T3_OVERVIEW_TPL_NEW'),
			'updateCompare' => JText::_('T3_OVERVIEW_TPL_COMPARE')
		);
		
		$japp   = JFactory::getApplication();
		$jdoc   = JFactory::getDocument();
		$db     = JFactory::getDbo();
		$params = T3::getTplParams();
		$input  = $japp->input;

		//get extension id of framework and template
		$query  = $db->getQuery(true);
		$query
			->select('extension_id')
			->from('#__extensions')
			->where('(element='. $db->quote(T3_TEMPLATE) . ' AND type=' . $db->quote('template') . ') 
					OR (element=' . $db->quote(T3_ADMIN) . ' AND type=' . $db->quote('plugin'). ')');

		$db->setQuery($query);
		$results = $db->loadRowList();
		$eids = array();
		foreach ($results as $eid) {
			$eids[] = $eid[0];
		}

		//check for version compatible
		if(version_compare(JVERSION, '3.0', 'ge')){
			JHtml::_('bootstrap.framework');
		} else {
			$jdoc->addStyleSheet(T3_ADMIN_URL . '/admin/bootstrap/css/bootstrap.css');

			$jdoc->addScript(T3_ADMIN_URL . '/admin/js/jquery-1.8.3.min.js');
			$jdoc->addScript(T3_ADMIN_URL . '/admin/bootstrap/js/bootstrap.js');
			$jdoc->addScript(T3_ADMIN_URL . '/admin/js/jquery.noconflict.js');
		}

		if(!$this->checkAssetsLoaded('chosen.css', '_styleSheets')){
			$jdoc->addStyleSheet(T3_ADMIN_URL . '/admin/plugins/chosen/chosen.css');
		}

		$jdoc->addStyleSheet(T3_ADMIN_URL . '/includes/depend/css/depend.css');
		$jdoc->addStyleSheet(T3_URL . '/css/layout-preview.css');
		$jdoc->addStyleSheet(T3_ADMIN_URL . '/admin/layout/css/layout.css');
		if(file_exists(T3_TEMPLATE_PATH . '/admin/layout-custom.css')) {
			$jdoc->addStyleSheet(T3_TEMPLATE_URL . '/admin/layout-custom.css');
		}
		$jdoc->addStyleSheet(T3_ADMIN_URL . '/admin/css/admin.css');

		if(version_compare(JVERSION, '3.0', 'ge')){
			$jdoc->addStyleSheet(T3_ADMIN_URL . '/admin/css/admin-j30.css');

			if($input->get('file') && version_compare(JVERSION, '3.2', 'ge')){
				$jdoc->addStyleSheet(T3_ADMIN_URL . '/admin/css/file-manager.css');
			}
		} else {
			$jdoc->addStyleSheet(T3_ADMIN_URL . '/admin/css/admin-j25.css');
		}

		if(!$this->checkAssetsLoaded('chosen.jquery.min.js', '_scripts')){
			$jdoc->addScript(T3_ADMIN_URL . '/admin/plugins/chosen/chosen.jquery.min.js');	
		}

		$jdoc->addScript(T3_ADMIN_URL . '/includes/depend/js/depend.js');
		$jdoc->addScript(T3_ADMIN_URL . '/admin/js/json2.js');
		$jdoc->addScript(T3_ADMIN_URL . '/admin/js/jimgload.js');
		$jdoc->addScript(T3_ADMIN_URL . '/admin/layout/js/layout.js');
		$jdoc->addScript(T3_ADMIN_URL . '/admin/js/admin.js');


		$jdoc->addScriptDeclaration ( '
			T3Admin = window.T3Admin || {};
			T3Admin.adminurl = \'' . JUri::getInstance()->toString() . '\';
			T3Admin.t3adminurl = \'' . T3_ADMIN_URL . '\';
			T3Admin.baseurl = \'' . JURI::base(true) . '\';
			T3Admin.rooturl = \'' . JURI::root() . '\';
			T3Admin.template = \'' . T3_TEMPLATE . '\';
			T3Admin.templateid = \'' . JFactory::getApplication()->input->get('id') . '\';
			T3Admin.langs = ' . json_encode($langs) . ';
			T3Admin.devmode = ' . $params->get('devmode', 0) . ';
			T3Admin.themermode = ' . $params->get('themermode', 1) . ';
			T3Admin.eids = [' . implode($eids, ',') .'];
			T3Admin.telement = \'' . T3_TEMPLATE . '\';
			T3Admin.felement = \'' . T3_ADMIN . '\';
			T3Admin.themerUrl = \'' . JUri::getInstance()->toString() . '&t3action=theme&t3task=thememagic' . '\';
			T3Admin.megamenuUrl = \'' . JUri::getInstance()->toString() . '&t3action=megamenu&t3task=megamenu' . '\';
			T3Admin.t3updateurl = \'' . JURI::base() . 'index.php?option=com_installer&view=update&task=update.ajax' . '\';
			T3Admin.t3layouturl = \'' . JURI::base() . 'index.php?t3action=layout' . '\';
			T3Admin.jupdateUrl = \'' . JURI::base() . 'index.php?option=com_installer&view=update' . '\';'
		);
	}

	public function addJSLang($key = '', $value = '', $overwrite = true){
		if($key && $value && ($overwrite || !array_key_exists($key, $this->langs))){
			$this->langs[$key] = $value ? $value : JText::_($key);
		}
	}
	
	/**
	 * function loadParam
	 * load and re-render parameters
	 *
	 * @return render success or not
	 */
	function renderAdmin(){
		$frwXml = T3_ADMIN_PATH . '/'. T3_ADMIN . '.xml';
		$tplXml = T3_TEMPLATE_PATH . '/templateDetails.xml';
		$jtpl = T3_ADMIN_PATH . '/admin/tpls/default.php';
		
		if(file_exists($tplXml) && file_exists($jtpl)){
			
			T3::import('depend/t3form');

			//get the current joomla default instance
			$form = JForm::getInstance('com_templates.style', 'style', array('control' => 'jform', 'load_data' => true));

			//wrap
			$form = new T3Form($form);
			
			//remove all fields from group 'params' and reload them again in right other base on template.xml
			$form->removeGroup('params');
			//load the template
			$form->loadFile(T3_PATH . '/params/template.xml');
			//overwrite / extend with params of template
			$form->loadFile(T3_TEMPLATE_PATH . '/templateDetails.xml', true, '//config');
			
			$xml = JFactory::getXML($tplXml);
			$fxml = JFactory::getXML($frwXml);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('id, title')
				->from('#__template_styles')
				->where('template='. $db->quote(T3_TEMPLATE));
			
			$db->setQuery($query);
			$styles = $db->loadObjectList();
			foreach ($styles as $key => &$style) {
				$style->title = ucwords(str_replace('_', ' ', $style->title));
			}
			
			$session = JFactory::getSession();
			$t3lock = $session->get('T3.t3lock', 'overview_params');
			$session->set('T3.t3lock', null);
			$input = JFactory::getApplication()->input;

			include $jtpl;
			
			//search for global parameters
			$japp = JFactory::getApplication();
			$pglobals = array();
			foreach($form->getGroup('params') as $param){
				if($form->getFieldAttribute($param->fieldname, 'global', 0, 'params')){
					$pglobals[] = array('name' => $param->fieldname, 'value' => $form->getValue($param->fieldname, 'params')); 
				}
			}
			$japp->setUserState('oparams', $pglobals);

			return true;
		}
		
		return false;
	}

	function replaceToolbar($body){
		$t3toolbar = T3_ADMIN_PATH . '/admin/tpls/toolbar.php';
		$input = JFactory::getApplication()->input;

		if(file_exists($t3toolbar) && class_exists('JToolBar')){
			//get the existing toolbar html
			jimport('joomla.language.help');
			$toolbar = JToolBar::getInstance('toolbar')->render('toolbar');
			$helpurl = JHelp::createURL($input->getCmd('view') == 'template' ? 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT' : 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_STYLES_EDIT');
			$helpurl = htmlspecialchars($helpurl, ENT_QUOTES);
			
			$params  = T3::getTplParams();

			//render our toolbar
			ob_start();
			include $t3toolbar;
			$t3toolbar = ob_get_clean();

			//replace it
			$body = str_replace($toolbar, $t3toolbar, $body);
		}

		return $body;
	}

	function replaceDoctype($body){
		return preg_replace('@<!DOCTYPE\s(.*?)>@', '<!DOCTYPE html>', $body);
	}

	function checkAssetsLoaded($pattern, $hash){
		$doc = JFactory::getDocument();
		$hash = $doc->$hash;

		foreach ($hash as $path => $object) {
			if(strpos($path, $pattern) !== false){
				return true;
			}
		}

		return false;
	}
}

?>