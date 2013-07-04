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

// No direct access
defined('_JEXEC') or die();

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

if (!class_exists('lessc')) {
	T3::import('lessphp/lessc.inc');
}
T3::import('core/path');

/**
 * T3Less class compile less
 *
 * @package T3
 */
class T3Less extends lessc
{
	public static function getInstance()
	{
		static $t3less = null;
		if (!$t3less) {
			$t3less = new T3Less;
		}
		return $t3less;
	}
	
	function getCss($path)
	{
		// get vars last-modified
		$vars_lm = JFactory::getApplication()->getUserState('vars_last_modified', 0);
		
		// less file last-modified
		$filepath = JPATH_ROOT . '/' . $path;
		$less_lm  = filemtime($filepath);
		
		// cache key
		$key   = md5($vars_lm . ':' . $less_lm . ':' . $path);
		$group = 't3';
		$cache = JCache::getInstance('output', array(
			'lifetime' => 1440
		));

		// get cache
		$data  = $cache->get($key, $group);
		if ($data) {
			return $data;
		}
		
		// not cached, build & store it
		$data = $this->compileCss($path) . "\n";
		$cache->store($data, $key, $group);
		
		return $data;
	}
	
	function buildCss($path)
	{
		$app     = JFactory::getApplication();
		$theme   = $app->getUserState('vars_theme', '');
		
		// less file last-modified
		$is_rtl      = ($app->getUserState('DIRECTION') == 'rtl');
		$filepath    = JPATH_ROOT . '/' . $path;
		$less_lm     = is_file($filepath) ? filemtime($filepath) : 0;
		$less_lm_rtl = 0;

		if ($is_rtl) {
			$filepath_rtl = preg_replace('/\/less\/(themes\/)?/', '/less/rtl/', $filepath);
			if (is_file($filepath_rtl)){
				$less_lm_rtl = filemtime($filepath_rtl);
			}
		}

		// get vars last-modified
		$vars_lm     = $app->getUserState('vars_last_modified', 0);
		
		// get css cached file
		$subdir  = ($is_rtl ? 'rtl/' : '') . ($theme ? $theme . '/' : '');
		$cssfile = T3_DEV_FOLDER . '/' . $subdir . str_replace('/', '.', $path) . '.css';
		$cssurl  = JURI::base(true) . '/' . $cssfile;
		$csspath = JPATH_ROOT . '/' . $cssfile;
		if (is_file($csspath)){
			$css_lm = filemtime($csspath);

			if($css_lm > $less_lm &&
			$css_lm > $vars_lm &&
			$css_lm > $less_lm_rtl) {
				return $cssurl;
			}
		}
		
		// not cached, build & store it
		if (!$this->compileCss($path, $cssfile)) {
			T3::error(JText::sprintf('T3_MSG_DEVFOLDER_NOT_WRITABLE', T3_DEV_FOLDER));
		}
		
		return $cssurl;
	}
	
	function compileCss($path, $topath = '')
	{
		$app    = JFactory::getApplication();
		$tpl    = T3_TEMPLATE;
		$theme  = $app->getUserState('vars_theme');
		$tofile = null;
		
		if ($topath) {
			$tofile = JPATH_ROOT . '/' . $topath;
			if (!is_dir(dirname($tofile))) {
				JFolder::create(dirname($tofile));
			}
		}
		
		// check path
		$realpath = realpath(JPATH_ROOT . '/' . $path);
		if (!is_file($realpath)) {
			//if (!JPath::check ($realpath)){
			return;
		}

		// get file content
		$content = JFile::read($realpath);
		// remove comments
		$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
		// split into array, separated by the import
		$arr = preg_split('#^\s*@import\s+"([^"]*)"\s*;#im', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		// check and add theme less if not is theme less
		if ($theme && !preg_match('#themes/#', $path)) {
			$themepath = 'themes/' . $theme . '/' . basename($path);
			if (is_file(T3_TEMPLATE_PATH . '/less/' . $themepath)) {
				$arr[] = $themepath;
				$arr[] = '';
			}
		}
		
		// variables & mixin
		$vars       = $this->getVars();
		$importdirs = array();
		
		// compile chunk
		$import = false;
		$output = '';
		foreach ($arr as $s) {
			if ($import) {
				$import = false;
				
				$url = T3Path::cleanPath(dirname($path) . '/' . $s);
				// ignore vars.less and variables.less if they are in template folder
				// cause there may have other css less file with the same name (eg. font awesome)
				
				if(strpos($url, $tpl . '/less/vars.less') !== false ||
					strpos($url,  $tpl . '/less/variables.less') !== false || 
					strpos($url, '/bootstrap/less/variables.less') !== false){

					continue;
				}
				
				// process import file
				$importcontent = JFile::read(JPATH_ROOT . '/' . $url);
				if(strpos($url, $tpl . '/less/') !== false){
					$importcontent = preg_replace('#^\s*@import\s+".*(variables-custom|variables|vars)\.less"\s*;#im', '', $importcontent);
				}

				// remember this path when lookup for import
				if (preg_match('#^\s*@import\s+"([^"]*)"\s*;#im', $importcontent)) {
					$importdirs[] = dirname(JPATH_ROOT . '/' . $url);
				}
				
				$output .= "#less-file-path{content: \"$url\";}\n" . $importcontent . "\n\n";
			} else {
				$import = true;
				$s      = trim($s);
				if ($s) {
					$output .= "#less-file-path{content: \"$path\";}\n" . $s . "\n\n";
				}
			}
		}
		
		// convert to RTL if using RTL
		if ($app->getUserState('DIRECTION') == 'rtl' && strpos($path, 'rtl/') === false) {
			// transform LTR to RTL
			T3::import('jacssjanus/ja.cssjanus');
			// $output = JACSSJanus::transform($output, true);
			$reg = '/^(#less-file-path.*)$/m';
			$arr = preg_split ($reg, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
			if (count($arr)) {
				$is_source = true;
				$output = "";
				foreach ($arr as $s) {
					if ($is_source) {
						$output .= JACSSJanus::transform($s, true);
						$is_source = false;
					} else {
						$output .= $s;
						$is_source = true;
					}
				}
			}
						
			// import rtl override
			// check override for import
			$import = false;
			foreach ($arr as $s) {
				if ($import) {
					$import = false;
					
					if ($s == 'vars.less'){
						continue;
					}

					// process import file
					$url = T3Path::cleanPath(dirname($path) . '/' . $s);
					$url = preg_replace('/\/less\/(themes\/)?/', '/less/rtl/', $url);
					
					if (!is_file(JPATH_ROOT . '/' . $url)){
						continue;
					}

					$importcontent = JFile::read(JPATH_ROOT . '/' . $url);
					$output .= "#less-file-path-rtl{content: \"$url\";}\n" . $importcontent . "\n\n";
				} else {
					$import = true;
				}
			}
			
			// override in template for this file
			$rtlpath = preg_replace('/\/less\/(themes\/[^\/]*\/)?/', '/less/rtl/', $path);
			if (is_file(JPATH_ROOT . '/' . $rtlpath)) {
				// process import file
				$importcontent = JFile::read(JPATH_ROOT . '/' . $rtlpath);
				$output .= "#less-file-path-rtl{content: \"$rtlpath\";}\n" . $importcontent . "\n\n";
			}
			// rtl theme
			if ($theme) {
				$rtlthemepath = preg_replace('/\/less\/(themes\/[^\/]*\/)?/', '/less/rtl/' . $theme . '/', $path);
				if (is_file(JPATH_ROOT . '/' . $rtlthemepath)) {
					// process import file
					$importcontent = JFile::read(JPATH_ROOT . '/' . $rtlthemepath);
					$output .= "#less-file-path-rtl{content: \"$rtlthemepath\";}\n" . $importcontent . "\n\n";
				}
			}
		}

		$importdirs[] = dirname($realpath);
		$importdirs[] = T3_TEMPLATE_PATH . '/less/';
		$this->setImportDir($importdirs);
		
		$source = $vars . "\n/**** Content ****/\n" . $output;
		// compile less to css using lessphp
		$output = $this->compile($source);
		
		$arr = preg_split('#^\s*\#less-file-path\s*{\s*[\r\n]*\s*content:\s*"([^"]*)";\s*[\r\n]*\s*}#im', $output, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		$output = '';
		$file   = '';
		$isfile = false;
		foreach ($arr as $s) {
			if ($isfile) {
				$isfile  = false;
				$file    = $s;
				$relpath = $topath ? T3Path::relativePath(dirname($topath), dirname($file)) : JURI::base(true) . '/' . dirname($file);
			} else {
				$output .= ($file ? T3Path::updateUrl($s, $relpath) : $s) . "\n\n";
				$isfile = true;
			}
		}
		
		// remove the dupliate clearfix at the beggining if not bootstrap.css file
		if (strpos($path, $tpl . '/less/bootstrap.less') === false) {
			$arr = preg_split('/[\r?\n]{2,}/', $output);
			// ignore first one, it's clearfix
			array_shift($arr);
			$output = implode("\n", $arr);
		}
		
		if ($tofile) {
			$ret = JFile::write($tofile, $output);
			@chmod($tofile, 0644);
			
			return $ret;
		}
		
		return $output;
	}
	
	function getVars()
	{
		$app  = JFactory::getApplication();
		$rtl  = $app->getUserState('DIRECTION') == 'rtl' ? '_rtl' : '';
		$vars = $app->getUserState('vars_content' . $rtl);

		return $vars;
	}
	
	public static function buildVars($theme = null, $dir = null)
	{
		$app  = JFactory::getApplication();
		$path = T3_TEMPLATE_PATH . '/less/vars.less';
		if(!is_file($path)){
			T3::error(JText::_('T3_MSG_LESS_NOT_VALID'));
			exit;
		}

		// get last-modified
		$last_modified = filemtime($path);
		$vars          = JFile::read($path);
	
		preg_match_all('#^\s*@import\s+"([^"]*)"#im', $vars, $matches);
		if (count($matches[0])) {

			$vars = '';
			foreach ($matches[1] as $url) {
				$path = T3Path::cleanPath(T3_TEMPLATE_PATH . '/less/' . $url);
				if (file_exists($path)) {
					$last_modified = max($last_modified, filemtime($path));
					$vars .= JFile::read($path);
				}
			}
		}

		// theme style
		if ($theme === null) {
			$tpl   = $app->getTemplate(true);
			$theme = $tpl->params->get('theme');
		}
		$app->setUserState('vars_theme', $theme);
		
		// detect RTL
		if ($dir === null) {
			$doc = JFactory::getDocument();
			$dir = $doc->direction;
		}
		$app->setUserState('DIRECTION', $dir);
		
		if ($theme) {
			// add theme variables.less and variables-custom.less
			foreach (array('variables.less', 'variables-custom.less') as $file) {
				$path = T3_TEMPLATE_PATH . '/less/themes/' . $theme . '/' . $file;
				if (is_file($path)) {
					$last_modified = max($last_modified, filemtime($path));
					$vars .= JFile::read($path);
				}
			}
		}
		
		// RTL variables
		$rtl = '';
		if ($dir == 'rtl') {
			// add rtl variables.less and rtl theme variables.less
			foreach (array('variables.less', $theme . '/variables.less') as $file) {
				$path = T3_TEMPLATE_PATH . '/less/rtl/' . $file;
				if (is_file($path)) {
					$last_modified = max($last_modified, filemtime($path));
					// append rtl file into vars
					$vars .= JFile::read($path);
				}
			}
			
			$rtl = '_rtl';
		}
		
		if ($app->getUserState('vars_last_modified' . $rtl) != $last_modified . $theme . $rtl) {
			$app->setUserState('vars_last_modified' . $rtl, $last_modified . $theme . $rtl);
		} else {
			return $app->getUserState('vars_content' . $rtl);
		}
		
		if ($rtl) {
			// transform LTR to RTL
			T3::import('jacssjanus/ja.cssjanus');
			$vars = JACSSJanus::transform($vars, true);
		}

		$app->setUserState('vars_content' . $rtl, $vars);
	}
	
	public static function addStylesheet($lesspath)
	{
		// build less vars, once only
		static $vars_built = false;
		$t3less = T3Less::getInstance();
		if (!$vars_built) {
			self::buildVars();
			$vars_built = true;
		}
		
		$app   = JFactory::getApplication();
		$doc   = JFactory::getDocument();
		$tpl   = $app->getTemplate(true);
		$theme = $tpl->params->get('theme');

		if (defined('T3_THEMER')) {
			// in Themer mode, using js to parse less for faster
			$doc->addStylesheet(JURI::base(true) . '/' . T3Path::cleanPath($lesspath), 'text/less');

			if(!defined('LESS_JS')){
				// Add lessjs to process lesscss
				$doc->addScript(T3_URL . '/js/less-1.3.3.js');

				if($doc->direction == 'rtl'){
					$doc->addScript(T3_URL . '/js/cssjanus.js');
				}

				define('LESS_JS', 1);
			}
			
		} else {
			// in development mode, using php to compile less for a better view of development
			if (preg_match('#(template(-responsive)?.less)#', $lesspath)) {
				// Development mode is on, try to include less file inside folder less/
				// get the less content
				$lessContent = JFile::read(JPATH_ROOT . '/' . $lesspath);
				$path        = dirname($lesspath);
				
				// parse less content
				if (preg_match_all('#^\s*@import\s+"([^"]*)"#im', $lessContent, $matches)) {
					foreach ($matches[1] as $url) {
						if ($url == 'vars.less') {
							continue;
						}
						$url    = $path . '/' . $url;
						$cssurl = $t3less->buildCss(T3Path::cleanPath($url));
						$doc->addStyleSheet($cssurl);
					}
				}
				
				// check and add theme, rtl less
				if ($theme) {
					$themepath = str_replace('/less/', '/less/themes/' . $theme . '/', $lesspath);
					if (is_file(JPATH_ROOT . '/' . $themepath)) {
						$cssurl = $t3less->buildCss(T3Path::cleanPath($themepath));
						$doc->addStyleSheet($cssurl);
					}
				} elseif ($doc->direction == 'rtl' && !preg_match('/rtl/', $lesspath)) {
					$rtlpath = str_replace('/less/', '/less/rtl/', $lesspath);
					if (is_file(JPATH_ROOT . '/' . $rtlpath)) {
						$cssurl = $t3less->buildCss(T3Path::cleanPath($rtlpath));
						$doc->addStyleSheet($cssurl);
					}
				}
				
			} else {
				$cssurl = $t3less->buildCss(T3Path::cleanPath($lesspath));
				$doc->addStyleSheet($cssurl);
			}
		}
	}
	
	public static function compileAll($theme = null)
	{
		$less     = new self;
		// compile all css files
		$files    = array();
		$lesspath = 'templates/' . T3_TEMPLATE . '/less/';
		$csspath  = 'templates/' . T3_TEMPLATE . '/css/';
		
		// get files need to compile
		$lessFiles   = JFolder::files(JPATH_ROOT . '/' . $lesspath, '.less');
		$lessContent = '';
		foreach ($lessFiles as $file) {
			$lessContent .= JFile::read(JPATH_ROOT . '/' . $lesspath . $file) . "\n";
		}
		
		// get files imported in this list
		if (preg_match_all('#^\s*@import\s+"([^"]*)"#im', $lessContent, $matches)) {
			foreach ($lessFiles as $f) {
				if (!in_array($f, $matches[1]))
					$files[] = substr($f, 0, -5);
			}
		}
		
		// build default
		if (!$theme || $theme == 'default') {
			self::buildVars('', 'ltr');
			// compile default
			foreach ($files as $file) {
				$less->compileCss($lesspath . $file . '.less', $csspath . $file . '.css');
			}
		}

		// build themes
		if (!$theme) {
			// get themes
			$themes = JFolder::folders(JPATH_ROOT . '/' . $lesspath . '/themes');
		} else {
			$themes = $theme != 'default' ? (array)($theme) : array();
		}
		
		if (is_array($themes)) {
			foreach ($themes as $t) {
				self::buildVars($t, 'ltr');
				// compile
				foreach ($files as $file) {
					$less->compileCss($lesspath . $file . '.less', $csspath . 'themes/' . $t . '/' . $file . '.css');
				}
			}
		}
		
		// compile rtl css
		$tplparams = T3::getTemplateParams();
		if($tplparams && $tplparams->get('build_rtl', 0)){
			// compile default
			if (!$theme || $theme == 'default') {
				self::buildVars('', 'rtl');
				foreach ($files as $file) {
					$less->compileCss($lesspath . $file . '.less', $csspath . 'rtl/' . $file . '.css');
				}
			}
			
			if (is_array($themes)) {
				// rtl for themes
				foreach ($themes as $t) {
					self::buildVars($t, 'rtl');
					// compile
					foreach ($files as $file) {
						$less->compileCss($lesspath . $file . '.less', $csspath . 'rtl/' . $t . '/' . $file . '.css');
					}
				}
			}
		}
	}
}
