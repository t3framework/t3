<?php
// Define constant

class T3v3Admin {

	protected $langs = array();
	
	/**
	 * function render
	 * render T3v3 administrator configuration form
	 *
	 * @return render success or not
	 */
	public function render(){
		$body = JResponse::getBody();
		$layout = T3V3_ADMIN_PATH . '/admin/tpls/default.php';
		if(file_exists($layout) && JFactory::getApplication()->input->getCmd('view') == 'style'){
			ob_start();
			$this->loadParams();
			$buffer = ob_get_clean();

			$body = preg_replace('@<form\s[^>]*name="adminForm"[^>]*>?.*?</form>@siu', $buffer, $body);
		}

		$body = $this->replaceToolbar($body);
		$body = $this->replaceDoctype($body);

		JResponse::setBody($body);
	}

	public function addAssets(){

		// load template language
		JFactory::getLanguage()->load(T3V3_PLUGIN, JPATH_ADMINISTRATOR);
		JFactory::getLanguage()->load ('tpl_'.T3V3_TEMPLATE.'.sys', JPATH_ROOT, null, true);

		/*
		$langs = array(
			'T3V3_LBL_RECOMPILE',
			'T3V3_LBL_VIEWTHEMER',
			'T3V3_MSG_ENABLE_THEMEMAGIC',
		
			'T3V3_LAYOUT_POPOVER_TITLE',
			'T3V3_LAYOUT_POPOVER_DESC',
			'T3V3_LAYOUT_RESPON_PTITLE',
			'T3V3_LAYOUT_RESPON_PDESC',
			'T3V3_LAYOUT_LOGO_TEXT',
			'T3V3_LAYOUT_EMPTY_POSITION',
			'T3V3_LAYOUT_DEFAULT_POSITION',
		
			'T3V3_LAYOUT_DVI_WIDE',
			'T3V3_LAYOUT_DVI_NORMAL',
			'T3V3_LAYOUT_DVI_XTABLET',
			'T3V3_LAYOUT_DVI_TABLET',
			'T3V3_LAYOUT_DVI_MOBILE',
			'T3V3_LAYOUT_MODE_STRUCTURE',
			'T3V3_LAYOUT_MODE_LAYOUT',
			'T3V3_LAYOUT_RESET_ALL',
			'T3V3_LAYOUT_RESET_PER_DEVICE',
			'T3V3_LAYOUT_RESET_POSITION',
		
			'T3V3_LAYOUT_CONFIG_TITLE',
			'T3V3_LAYOUT_CONFIG_DESC',
			'T3V3_LAYOUT_UNKN_WIDTH',
			'T3V3_LAYOUT_POS_WIDTH',
			'T3V3_LAYOUT_POS_NAME',
			'T3V3_LAYOUT_LOAD_ERROR',
			'T3V3_LAYOUT_EDIT_POSITION',
			'T3V3_LAYOUT_SHOW_POSITION',
			'T3V3_LAYOUT_HIDE_POSITION',
			'T3V3_LAYOUT_CHANGE_NUMPOS',
			'T3V3_LAYOUT_DRAG_RESIZE',
			'T3V3_LAYOUT_HIDDEN_POS_DESC',
		
			'T3V3_OVERVIEW_FAILED_GETLIST',
			'T3V3_OVERVIEW_GO_DOWNLOAD',
			'T3V3_OVERVIEW_CHECK_UPDATE',
			'T3V3_OVERVIEW_CHK_UPDATE_OK',
			'T3V3_OVERVIEW_TPL_NEW',
			'T3V3_OVERVIEW_TPL_COMPARE'
		);

		foreach ($langs as $lkey) {
			$this->addJSLang($lkey);
		}
		
		*/
		

		$langs = array(
			'lblCompile' => JText::_('T3V3_LBL_RECOMPILE'),
			'lblThemer' => JText::_('T3V3_LBL_VIEWTHEMER'),
			'enableThemeMagic' => JText::_('T3V3_MSG_ENABLE_THEMEMAGIC'),

			'logoPresent' => JText::_('T3V3_LAYOUT_LOGO_TEXT'),
			'emptyLayoutPosition' => JText::_('T3V3_LAYOUT_EMPTY_POSITION'),
			'defaultLayoutPosition' => JText::_('T3V3_LAYOUT_DEFAULT_POSITION'),
			
			'layoutConfig' => JText::_('T3V3_LAYOUT_CONFIG_TITLE'),
			'layoutConfigDesc' => JText::_('T3V3_LAYOUT_CONFIG_DESC'),
			'layoutUnknownWidth' => JText::_('T3V3_LAYOUT_UNKN_WIDTH'),
			'layoutPosWidth' => JText::_('T3V3_LAYOUT_POS_WIDTH'),
			'layoutPosName' => JText::_('T3V3_LAYOUT_POS_NAME'),

			'layoutCanNotLoad' => JText::_('T3V3_LAYOUT_LOAD_ERROR'),

			'askCloneLayout' => JText::_('T3V3_LAYOUT_ASK_ADD_LAYOUT'),
			'correctLayoutName' => JText::_('T3V3_LAYOUT_ASK_CORRECT_NAME'),
			'askDeleteLayout' => JText::_('T3V3_LAYOUT_ASK_DEL_LAYOUT'),

			'lblDeleteIt' => JText::_('T3V3_LAYOUT_LABEL_DELETEIT'),
			'lblCloneIt' => JText::_('T3V3_LAYOUT_LABEL_CLONEIT'),

			'layoutEditPosition' => JText::_('T3V3_LAYOUT_EDIT_POSITION'),
			'layoutShowPosition' => JText::_('T3V3_LAYOUT_SHOW_POSITION'),
			'layoutHidePosition' => JText::_('T3V3_LAYOUT_HIDE_POSITION'),
			'layoutChangeNumpos' => JText::_('T3V3_LAYOUT_CHANGE_NUMPOS'),
			'layoutDragResize' => JText::_('T3V3_LAYOUT_DRAG_RESIZE'),
			'layoutHiddenposDesc' => JText::_('T3V3_LAYOUT_HIDDEN_POS_DESC'),
			
			'updateFailedGetList' => JText::_('T3V3_OVERVIEW_FAILED_GETLIST'),
			'updateDownLatest' => JText::_('T3V3_OVERVIEW_GO_DOWNLOAD'),
			'updateCheckUpdate' => JText::_('T3V3_OVERVIEW_CHECK_UPDATE'),
			'updateChkComplete' => JText::_('T3V3_OVERVIEW_CHK_UPDATE_OK'),
			'updateHasNew' => JText::_('T3V3_OVERVIEW_TPL_NEW'),
			'updateCompare' => JText::_('T3V3_OVERVIEW_TPL_COMPARE')
		);
		
		$japp = JFactory::getApplication();
		$jdoc = JFactory::getDocument();

		$params = new JRegistry;
		$db = JFactory::getDbo();

		//get params of templates
		$query = $db->getQuery(true);
		$query
			->select('params')
			->from('#__template_styles')
			->where('template='. $db->quote(T3V3_TEMPLATE));
		
		$db->setQuery($query);
		$params->loadString($db->loadResult());

		//get extension id of framework and template
		$query = $db->getQuery(true);
		$query
			->select('extension_id')
			->from('#__extensions')
			->where('(element='. $db->quote(T3V3_TEMPLATE) . ' AND type=' . $db->quote('template') . ') 
					OR (element=' . $db->quote(T3V3_ADMIN) . ' AND type=' . $db->quote('plugin'). ')');

		$db->setQuery($query);
		$results = $db->loadRowList();
		$eids = array();
		foreach ($results as $eid) {
			$eids[] = $eid[0];
		}

		//check for version compactible
		$jversion  = new JVersion;
		if(!$jversion->isCompatible('3.0')){
			$jdoc->addStyleSheet(T3V3_ADMIN_URL . '/admin/bootstrap/css/bootstrap.css');
			
			$jdoc->addScript(T3V3_ADMIN_URL . '/admin/js/jquery-1.8.0.min.js');
			$jdoc->addScript(T3V3_ADMIN_URL . '/admin/bootstrap/js/bootstrap.js');
			$jdoc->addScript(T3V3_ADMIN_URL . '/admin/js/jquery.noconflict.js');
		}

		$jdoc->addStyleSheet(T3V3_ADMIN_URL . '/admin/plugins/chosen/chosen.css');
		$jdoc->addStyleSheet(T3V3_ADMIN_URL . '/includes/depend/css/jadepend.css');
		$jdoc->addStyleSheet(T3V3_ADMIN_URL . '/admin/layout/css/layout-custom.css');
		$jdoc->addStyleSheet(T3V3_ADMIN_URL . '/admin/layout/css/layout.css');
		$jdoc->addStyleSheet(T3V3_ADMIN_URL . '/admin/css/t3v3admin.css');
		if(!$jversion->isCompatible('3.0')){
			$jdoc->addStyleSheet(T3V3_ADMIN_URL . '/admin/css/t3v3admin-j25.css');
		} else {
			$jdoc->addStyleSheet(T3V3_ADMIN_URL . '/admin/css/t3v3admin-j30.css');
		}

		$jdoc->addScript(T3V3_ADMIN_URL . '/admin/plugins/chosen/chosen.jquery.min.js');	
		$jdoc->addScript(T3V3_ADMIN_URL . '/includes/depend/js/jadepend.js');
		$jdoc->addScript(T3V3_ADMIN_URL . '/admin/js/json2.js');
		$jdoc->addScript(T3V3_ADMIN_URL . '/admin/js/jimgload.js');
		$jdoc->addScript(T3V3_ADMIN_URL . '/admin/layout/js/layout.js');
		$jdoc->addScript(T3V3_ADMIN_URL . '/admin/js/t3v3admin.js');

		JFactory::getDocument()->addScriptDeclaration ( '
			var T3V3Admin = window.T3V3Admin || {};
			T3V3Admin.adminurl = \'' . JFactory::getURI()->toString() . '\';
			T3V3Admin.t3adminurl = \'' . T3V3_ADMIN_URL . '\';
			T3V3Admin.baseurl = \'' . JURI::base(true) . '\';
			T3V3Admin.rooturl = \'' . JURI::root() . '\';
			T3V3Admin.template = \'' . T3V3_TEMPLATE . '\';
			T3V3Admin.langs = ' . json_encode($langs) . ';
			T3V3Admin.devmode = ' . $params->get('devmode', 0) . ';
			T3V3Admin.themermode = ' . $params->get('themermode', 0) . ';
			T3V3Admin.eids = [' . implode($eids, ',') .'];
			T3V3Admin.telement = \'' . T3V3_TEMPLATE . '\';
			T3V3Admin.felement = \'' . T3V3_ADMIN . '\';
			T3V3Admin.themerUrl = \'' . JFactory::getURI()->toString() . '&t3action=theme&t3task=thememagic' . '\';
			T3V3Admin.t3updateurl = \'' . JURI::base() . 'index.php?option=com_installer&view=update&task=update.ajax' . '\';
			T3V3Admin.t3layouturl = \'' . JURI::base() . 'index.php?t3action=layout' . '\';
			T3V3Admin.jupdateUrl = \'' . JURI::base() . 'index.php?option=com_installer&view=update' . '\';'
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
	function loadParams(){
		$frwXml = T3V3_ADMIN_PATH . '/'. T3V3_ADMIN . '.xml';
		$tplXml = T3V3_TEMPLATE_PATH . '/templateDetails.xml';
		$jtpl = T3V3_ADMIN_PATH . '/admin/tpls/default.php';
		
		if(file_exists($tplXml) && file_exists($jtpl)){
			
			//get the current joomla default instance
			$form = JForm::getInstance('com_templates.style', 'style', array('control' => 'jform', 'load_data' => true));
			
			//remove all fields from group 'params' and reload them again in right other base on template.xml
			$form->removeGroup('params');
			$form->loadFile(T3V3_PATH . '/params/' . 'template.xml');
			$form->loadFile(T3V3_TEMPLATE_PATH . DIRECTORY_SEPARATOR . 'templateDetails.xml', true, '//config');
			
			$xml = JFactory::getXML($tplXml);
			$fxml = JFactory::getXML($frwXml);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select('id, title')
				->from('#__template_styles')
				->where('template='. $db->quote(T3V3_TEMPLATE));
			
			$db->setQuery($query);
			$styles = $db->loadObjectList();
			
			$session = JFactory::getSession();
			$t3lock = $session->get('T3v3.t3lock', 'overview_params');
			$session->set('T3v3.t3lock', null);
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
		$t3toolbar = T3V3_ADMIN_PATH . '/admin/tpls/toolbar.php';
		$input = JFactory::getApplication()->input;

		if(file_exists($t3toolbar) && class_exists('JToolBar')){
			//get the existing toolbar html
			jimport('joomla.language.help');
			$toolbar = JToolBar::getInstance('toolbar')->render('toolbar');
			$helpurl = JHelp::createURL($input->getCmd('view') == 'template' ? 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT' : 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_STYLES_EDIT');
			$helpurl = htmlspecialchars($helpurl, ENT_QUOTES);
		
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
}

?>