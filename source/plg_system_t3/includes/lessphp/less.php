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

T3::import('lessphp/less/less');

/**
 * T3Less class compile less
 *
 * @package T3
 */
class T3Less
{

	public static function requirement(){
		static $setup;

		if(isset($setup)){

			@ini_set('pcre.backtrack_limit', '2M');

			$mem_limit = @ini_get('memory_limit');
			if (preg_match('@^(\d+)(.)$@', $mem_limit, $matches)) {
				if ($matches[2] == 'M') {
					$mem_limit = $matches[1] * 1024 * 1024;
				} else if ($matches[2] == 'K') {
					$mem_limit = $matches[1] * 1024;
				}
			}

			if((int)$mem_limit < 128 * 1024 * 1024) {
				@ini_set('memory_limit', '128M');
			}

			$setup = true;
		}
	}

	/**
	 * Compile LESS to CSS
	 * @param   $path   the file path of less file
	 * @return  string  the css compiled content
	 */
	public static function getCss($path)
	{
		//build vars once
		self::buildVarsOnce();

		// get vars last-modified
		$vars_lm = self::getState('vars_last_modified', 0);
		
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
		$data = self::compileCss($path) . "\n";
		$cache->store($data, $key, $group);
		
		return $data;
	}

	/**
	 * Compile LESS to CSS
	 * @param   $path   the less file to compile
	 * @return  string  url to css file
	 */
	public static function buildCss($path)
	{
		$app     = JFactory::getApplication();
		$theme   = $app->getUserState('current_theme', '');
		
		// less file last-modified
		$is_rtl      = ($app->getUserState('current_direction') == 'rtl');
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
		$vars_lm = self::getState('vars_last_modified', 0);
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
		if (!self::compileCss($path, $cssfile)) {
			T3::error(JText::sprintf('T3_MSG_DEVFOLDER_NOT_WRITABLE', T3_DEV_FOLDER));
		}
		
		return $cssurl;
	}

	public static function relativePath($topath, $path, $default = null){
		if(defined('T3_DEV_MODE') && T3_DEV_MODE){
			return $topath ? T3Path::relativePath($topath, JUri::root(true) . '/' . $path) . '/' : (!empty($default) ? $default : T3_TEMPLATE_URL . '/css/'); 	
		} else {
			return $topath ? T3Path::relativePath($topath, $path) . '/' : (!empty($default) ? $default : T3_TEMPLATE_URL . '/css/'); 
		}
	}

	/**
	 * @param   string  $path    file path of less file to compile
	 * @param   string  $topath  file path of output css file
	 * @return  bool|mixed       compile result or the css compiled content
	 */
	public static function compileCss($path, $topath = '')
	{
		//check system
		self::requirement();

		$parser = new Less_Parser();
		$app    = JFactory::getApplication();
		$tpl    = T3_TEMPLATE;
		$theme  = $app->getUserState('current_theme');
		$tofile = null;
		$todir  = null;

		//pattern
		$rcomment     = '@/\*[^*]*\*+([^/][^*]*\*+)*/@';
		$rspace       = '@[\r?\n]{2,}@';
		$rimport      = '@^\s*\@import\s+"([^"]*)"\s*;@im';
		$rvarscheck   = '@(base|base-bs3|bootstrap|'.preg_quote($tpl).')/less/(vars|variables|mixins)\.less@';
		$rexcludepath = '@(base|base-bs3|bootstrap|'.preg_quote($tpl).')/less/@';
		$rimportvars  = '@^\s*\@import\s+".*(variables-custom|variables|vars|mixins)\.less"\s*;@im';

		$rsplitbegin  = '@^\s*\#';
		$rsplitend    = '[^\s]*?\s*{\s*[\r\n]*\s*content:\s*"([^"]*)";\s*[\r\n]*\s*}@im';
		$rswitchrtl   = '@/less/(themes/[^/]*/)?@';

		$kvarsep      = 'less-content-separator';
		$krtlsep      = 'rtl-less-content';

		
		if ($topath) {
			$tofile = JPATH_ROOT . '/' . $topath;
			$todir  = dirname($topath);

			if (!is_dir(dirname($tofile))) {
				JFolder::create(dirname($tofile));
			}
		}
		
		// check path
		$realpath = realpath(JPATH_ROOT . '/' . $path);
		if (!is_file($realpath)) {
			return;
		}

		// get file content
		$content = JFile::read($realpath);

		//remove vars.less
		if (preg_match($rexcludepath, $path)){
			$content = preg_replace($rimportvars, '', $content);
		}
		
		// remove comments? - we should keep comment for rtl flip
		//$content = preg_replace($rcomment, '', $content);

		// split into array, separated by the import
		$arr = preg_split($rimport, $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		// check and add theme less if not is theme less
		if ($theme && strpos($path, 'themes/') === false) {
			$themepath = 'themes/' . $theme . '/' . basename($path);
			if (is_file(T3_TEMPLATE_PATH . '/less/' . $themepath)) {
				$arr[] = $themepath;
				$arr[] = '';
			}
		}
		
		// variables & mixin
		$vars       = self::getVars();
		$output     = '';
		$importdirs = array();
		
		// compile chunk
		$import = false;
		foreach ($arr as $s) {
			if ($import) {
				$import = false;
				
				$url = T3Path::cleanPath(dirname($path) . '/' . $s);
				
				// ignore vars.less and variables.less if they are in template folder
				// cause there may have other css less file with the same name (eg. font awesome)
				if (preg_match($rvarscheck, $url)){
					continue;
				}
				
				// remember this path when lookup for import
				$importdirs[dirname(JPATH_ROOT . '/' . $url)] = self::relativePath($todir, dirname($url));

				$output .= "\n@import \"$s\";\n\n";

				// check and add theme less
				if ($theme && strpos($url, 'themes/') === false) {
					$theme_rel  = 'themes/' . $theme . '/' . basename($url);
					$theme_path = T3_TEMPLATE_PATH . '/less/' . $theme_rel;
					if (is_file($theme_path)) {
						$importdirs[dirname($theme_path)] = self::relativePath($todir, 'templates/' . $tpl . '/less/themes/' . $theme . '/');

						$output .= "\n@import \"$theme_rel\";\n\n";
					}
				}

			} else {
				$import = true;
				$s  = trim($s);
				if ($s) {
					$output .= "\n$s\n\n";
				}
			}
		}
		
		$rtlcontent = '';
		$is_rtl     = $app->getUserState('current_direction') == 'rtl' && strpos($path, 'rtl/') === false;

		// convert to RTL if using RTL
		if ($is_rtl) {

			// import rtl override
			// check override for import
			$import = false;
			foreach ($arr as $s) {
				if ($import) {
					$import = false;
					
					$url = T3Path::cleanPath(dirname($path) . '/' . $s);
					// ignore vars.less and variables.less if they are in template folder
					// cause there may have other css less file with the same name (eg. font awesome)
					if (preg_match($rvarscheck, $url)){
						continue;
					}

					// process import file
					$rtl_url = preg_replace('@/less/(themes/)?@', '/less/rtl/', $url);
					
					if (!is_file(JPATH_ROOT . '/' . $rtl_url)){
						continue;
					}

					// process import file
					$importcontent = JFile::read(JPATH_ROOT . '/' . $rtl_url);
					if (preg_match($rexcludepath, $rtl_url)){
						$importcontent = preg_replace($rimportvars, '', $importcontent);
					}

					// remember this path when lookup for import
					if (preg_match($rimport, $importcontent)) {
						$importdirs[dirname(JPATH_ROOT . '/' . $rtl_url)] = self::relativePath($todir, dirname($rtl_url));
					}

					$rtlcontent .= "\n$importcontent\n\n";

					// rtl theme overwrite
					if ($theme && strpos($url, 'themes/') === false) {
						$rtlthemepath = preg_replace($rswitchrtl, '/less/rtl/' . $theme . '/', $url);

						if (is_file(JPATH_ROOT . '/' . $rtlthemepath)) {
							// process import file
							$importcontent = JFile::read(JPATH_ROOT . '/' . $rtlthemepath);
							$rtlcontent   .= "\n$importcontent\n\n";
							$importdirs[dirname(JPATH_ROOT . '/' . $rtlthemepath)] = self::relativePath($todir,  dirname($rtlthemepath));
						}
					}

				} else {
					$import = true;
				}
			}
			
			// override in template for this file
			$rtlpath = preg_replace($rswitchrtl, '/less/rtl/', $path);
			if (is_file(JPATH_ROOT . '/' . $rtlpath)) {
				// process import file
				$importcontent = JFile::read(JPATH_ROOT . '/' . $rtlpath);
				$rtlcontent   .= "\n$importcontent\n\n";
				$importdirs[dirname(JPATH_ROOT . '/' . $rtlpath)] = self::relativePath($todir, dirname($rtlpath));
			}

			// rtl theme
			if ($theme) {
				$rtlthemepath = preg_replace($rswitchrtl, '/less/rtl/' . $theme . '/', $path);
				if (is_file(JPATH_ROOT . '/' . $rtlthemepath)) {
					// process import file
					$importcontent = JFile::read(JPATH_ROOT . '/' . $rtlthemepath);
					$rtlcontent   .= "\n$importcontent\n\n";
					$importdirs[dirname(JPATH_ROOT . '/' . $rtlthemepath)] = self::relativePath($todir, dirname($rtlthemepath));
				}
			}

			if($rtlcontent){
				$output = $output . "\n#$krtlsep{content: \"separator\";}\n\n$rtlcontent\n\n";
			}
		}

		// common place
		$importdirs[T3_TEMPLATE_PATH . '/less'] = self::relativePath($todir, T3_TEMPLATE_URL);

		// myself
		$importdirs[dirname(JPATH_ROOT . '/' . $path)] = self::relativePath($todir, dirname($path));

		// ignore all these files
		foreach (array(T3_PATH, T3_PATH . '/bootstrap', T3_TEMPLATE_PATH) as $know_path) {
			foreach (array('vars', 'variables', 'mixins') as $know_file) {
				$realfile = realpath($know_path . '/less/' . $know_file . '.less');
				
				if(is_file($realfile) && !Less_Parser::FileParsed($realfile)){
					Less_Parser::AddParsedFile($realfile);
				}
			}
		}

		// compile less to css using lessphp
		$parser->SetImportDirs($importdirs);
		$parser->SetFileInfo(JPATH_ROOT . '/' . $path, self::relativePath($todir, dirname($path)));
		$source = $vars . "\n#$kvarsep{content: \"separator\";}\n" . $output;
		$parser->parse($source);
		$output = $parser->getCss();
		
		// remove the duplicate clearfix at the beginning if not bootstrap.css file
		if (strpos($path, $tpl . '/less/bootstrap.less') === false) {
			$arr = preg_split($rsplitbegin . $kvarsep . $rsplitend, $output);
			// ignore first one, it's clearfix
			if(is_array($arr)){
				array_shift($arr);
			}

			$output = implode("\n", $arr);

		} else {
			$output = preg_replace($rsplitbegin . $kvarsep . $rsplitend, '', $output);
		}

		//update url if needed
		if(defined('T3_DEV_MODE') && T3_DEV_MODE){
			$output = T3Path::updateUrl($output, $topath ? T3Path::relativePath(dirname($topath), dirname($path) . ($theme? '/themes/' . $theme : '')) : T3_TEMPLATE_URL . '/css/');
		}

		if ($is_rtl) {
			
			if($rtlcontent){
				$output = preg_split($rsplitbegin . $krtlsep . $rsplitend, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
				
				$rtlcontent = isset($output[2]) ? $output[2] : false;
				$output = $output[0];
			}

			T3::import('jacssjanus/ja.cssjanus');
			$output = JACSSJanus::transform($output, true);

			//join again
			if($rtlcontent){
				$output = $output . "\n" . $rtlcontent;
			}
		}

		//remove comments and clean up
		$output = preg_replace($rcomment, '', $output);
		$output = preg_replace($rspace, "\n\n", $output);

		if ($tofile) {
			$ret = JFile::write($tofile, $output);
			@chmod($tofile, 0644);
			
			return $ret;
		}
		
		return $output;
	}


	/**
	 * Get less variables
	 * @return mixed
	 */
	public static function getVars()
	{
		return self::getState('vars_content');
	}

	/**
	 * get value from cache
	 */
	public static function getState ($key, $default = null) {
		$app = JFactory::getApplication();
		$keysfx = $app->getUserState('current_key_sufix');
		// cache key
		$ckey   = $key.$keysfx;
		$group = 't3';
		$cache = JCache::getInstance('output', array(
			'lifetime' => 25200,
			'caching'	=> true
		));

		// get cache
		$data  = $cache->get($ckey, $group);
		return $data===false ? $app->getUserState($ckey, $default) : $data;
	}

	/**
	 * store value to cache
	 */
	public static function setState ($key, $value) {
		$app = JFactory::getApplication();
		$keysfx = $app->getUserState('current_key_sufix');
		// cache key
		$ckey   = $key.$keysfx;
		$group = 't3';
		$cache = JCache::getInstance('output', array(
			'lifetime' => 25200,
			'caching'	=> true
		));
		if (!$cache->store($value, $ckey, $group)) {
			$app->setUserState($ckey, $value);
		}
	}

	/**
	 * @param  string  $theme  template theme
	 * @param  string  $dir    direction (ltr or rtl)
	 * @return mixed
	 */
	public static function buildVars($theme = null, $dir = null)
	{
		$app  = JFactory::getApplication();
		$params = null;
		if ($app->isAdmin()) {
			$params = $app->getUserState ('current_template_params');
		} else {
			$tpl   =  $app->getTemplate(true);
			$params = $tpl->params;
		}
		if (!$params) {
			T3::error(JText::_('T3_MSG_CANNOT_DETECT_TEMPLATE'));
			exit;
		}

		$responsive = $params->get('responsive', 1);
		// theme style
		if ($theme === null) {
			$theme = $params->get('theme');
		}
		// detect RTL
		if ($dir === null) {
			$doc = JFactory::getDocument();
			$dir = $doc->direction;
		}
		$app->setUserState('current_theme', $theme);
		$app->setUserState('current_direction', $dir);
		$app->setUserState('current_key_sufix', "_{$theme}_{$dir}");

		$path = T3_TEMPLATE_PATH . '/less/vars.less';
		if(!is_file($path)){
			T3::error(JText::_('T3_MSG_LESS_NOT_VALID'));
			exit;
		}

		// force re-build less if switch responsive mode and get last modified time
		if ($responsive !== $app->getUserState('current_responsive')) {
			$app->setUserState('current_responsive', $responsive);
			$last_modified = time();
			touch($path, $last_modified);
		} else {
			$last_modified = filemtime($path);
		}

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

		// Non-responsive variables
		if (!$responsive) {
			$file = '/less/non-responsive-variables.less';
			$path = T3_PATH . $file;
			if (is_file($path)) {
				$last_modified = max($last_modified, filemtime($path));
				// append rtl file into vars
				$vars .= JFile::read($path);
			}
			$path = T3_TEMPLATE_PATH . $file;
			if (is_file($path)) {
				$last_modified = max($last_modified, filemtime($path));
				// append rtl file into vars
				$vars .= JFile::read($path);
			}
		}

		if (self::getState('vars_last_modified') != $last_modified) {
			self::setState('vars_last_modified', $last_modified);
		}
		self::setState('vars_content', $vars);

	}

	/**
	 * Build vars only one per request
	 */
	public static function buildVarsOnce(){
		// build less vars, once only
		static $vars_built = false;
		if (!$vars_built) {
			self::buildVars();
			$vars_built = true;
		}
	}

	/**
	 * Wrapper function to add a stylesheet to html document
	 * @param  string  $lesspath  the less file to add
	 */
	public static function addStylesheet($lesspath)
	{
		//build vars once
		self::buildVarsOnce();


		$app   = JFactory::getApplication();
		$doc   = JFactory::getDocument();
		$tpl   = $app->getTemplate(true);
		$theme = $tpl->params->get('theme');

		if (defined('T3_THEMER') && $tpl->params->get('themermode', 1)) {
			// in Themer mode, using js to parse less, so we will use 'text/less' content type
			$doc->addStylesheet(JURI::base(true) . '/' . T3Path::cleanPath($lesspath), 'text/less');

			// just to make sure this function is call once
			if(!defined('T3_LESS_JS')){
				// Add lessjs to process lesscss
				$doc->addScript(T3_URL . '/js/less.js');

				if($doc->direction == 'rtl'){
					$doc->addScript(T3_URL . '/js/cssjanus.js');
				}

				define('T3_LESS_JS', 1);
			}
			
		} else {

			// in development mode, using php to compile less for a better view of development
			if (preg_match('#(template(-responsive)?.less)#', $lesspath)) {
				
				self::divide($lesspath, $theme);
				
			} else {
				$cssurl = self::buildCss(T3Path::cleanPath($lesspath));
				$doc->addStyleSheet($cssurl);
			}
		}
	}

	/**
	 * Special compilation for template.css file when development mode is on
	 * @param $path
	 * @return bool
	 */
	public static function divide($path)
	{
		//check system
		self::requirement();

		$parser = new Less_Parser();
		$app    = JFactory::getApplication();
		$doc    = JFactory::getDocument();
		$tpl    = T3_TEMPLATE;
		$theme  = $app->getTemplate(true)->params->get('theme');
		$is_rtl = $doc->direction == 'rtl' && strpos($path, 'rtl/') === false;
		$subdir = ($is_rtl ? 'rtl/' : '') . ($theme ? $theme . '/' : '');
		$topath = T3_DEV_FOLDER . '/' . $subdir;
		$tofile = null;
		$root   = JUri::root(true);

		//pattern
		$rimport      = '@^\s*\@import\s+"([^"]*)"\s*;@im';
		$rvarscheck   = '@(base|base-bs3|bootstrap|'.preg_quote($tpl).')/less/(vars|variables|mixins)\.less@';
		$rexcludepath = '@(base|base-bs3|bootstrap|'.preg_quote($tpl).')/less/@';
		$rimportvars  = '@^\s*\@import\s+".*(variables-custom|variables|vars|mixins)\.less"\s*;@im';

		$rsplitbegin  = '@^\s*\#';
		$rsplitend    = '[^\s]*?\s*{\s*[\r\n]*\s*content:\s*"([^"]*)";\s*[\r\n]*\s*}@im';
		$rswitchrtl   = '@/less/(themes/[^/]*/)?@';

		$kfilepath    = 'less-file-path';
		$kvarsep      = 'less-content-separator';
		$krtlsep      = 'rtl-less-content';

		if ($topath) {
			if (!is_dir(JPATH_ROOT . '/' . $topath)) {
				JFolder::create(JPATH_ROOT . '/' . $topath);
			}
		}

		// check path
		$realpath = realpath(JPATH_ROOT . '/' . $path);
		if (!is_file($realpath)) {
			return;
		}

		// get file content
		$content = JFile::read($realpath);

		//remove vars.less
		if (preg_match($rexcludepath, $path)){
			$content = preg_replace($rimportvars, '', $content);
		}

		// check and add theme less if not is theme less
		if ($theme && strpos($path, 'themes/') === false) {
			$themepath = 'themes/' . $theme . '/' . basename($path);
			if (is_file(T3_TEMPLATE_PATH . '/less/' . $themepath)) {
				$content = $content . "\n@import \"$themepath\"; \n\n";
			}
		}

		// split into array, separated by the import
		$split_contents = preg_split($rimport, $content, -1, PREG_SPLIT_DELIM_CAPTURE);

		//check if we need to rebuild
		$rebuild = false;
		$vars_lm = self::getState('vars_last_modified', 0);
		$file_lm = @filemtime(JPATH_ROOT . '/' . $path);

		//check for this file and rtl
		$cssfile = $topath . str_replace('/', '.', $path) . '.css';
		$css_lm  = is_file(JPATH_ROOT . '/' . $cssfile) ? @filemtime(JPATH_ROOT . '/' . $cssfile) : -1;

		//rebuild?
		if($css_lm < $vars_lm || $css_lm < $file_lm){
			$rebuild = true;
		} else {
			$doc->addStylesheet($root . '/' . $cssfile);
		}

		//check for rebuild if this rtl overwrite file has just modified
		if(!$rebuild && $is_rtl){
			$rtl_url = preg_replace('@/less/(themes/)?@', '/less/rtl/', $path);
			$rtl_lm  = is_file(JPATH_ROOT . '/' . $rtl_url) ? @filemtime(JPATH_ROOT . '/' . $rtl_url) : 0;

			$rebuild = ($css_lm < $rtl_lm);
		}

		if(!$rebuild){
			$import = false;
			foreach ($split_contents as $chunk) {
				if ($import) {
					$import = false;

					$url = T3Path::cleanPath(dirname($path) . '/' . $chunk);

					if(is_file(JPATH_ROOT . '/' . $url)){
						//$css_lm should be the same as templates.css
						$file_lm = @filemtime(JPATH_ROOT . '/' . $url);
						$theme_lm     = -1;
						$rtl_lm       = -1;
						$theme_rtl_lm = -1;

						if ($theme && strpos($url, 'themes/') === false) {
							$themepath = 'themes/' . $theme . '/' . basename($path);
							if (is_file(T3_TEMPLATE_PATH . '/less/' . $themepath)) {
								$theme_lm = @filemtime(T3_TEMPLATE_PATH . '/less/' . $themepath);
							}

							if($is_rtl){
								$rtlthemepath = preg_replace($rswitchrtl, '/less/rtl/' . $theme . '/', $url);
								if (is_file(JPATH_ROOT . '/' . $rtlthemepath)) {
									$theme_rtl_lm = @filemtime(JPATH_ROOT . '/' . $rtlthemepath);
								}
							}
						}

						if($is_rtl){
							$rtl_url = preg_replace('@/less/(themes/)?@', '/less/rtl/', $url);
							if(is_file(JPATH_ROOT . '/' . $rtl_url)){
								$rtl_lm  = @filemtime(JPATH_ROOT . '/' . $rtl_url);
							}
						}



						if(!is_file(JPATH_ROOT . '/' . $cssfile)    //file not exist
							|| $css_lm < $vars_lm                     //variables has changed
							|| $css_lm < $file_lm                     //this import file has changed
							|| $css_lm < $theme_lm                    //theme && theme overwrite has changed
							|| $css_lm < $rtl_lm                      //rtl && rtl overwrite has changed
							|| $css_lm < $theme_rtl_lm                //theme && rtl && theme overwrite has changed
						){
							$rebuild = true;  // rebuild for sure
							break;            //no need further check
						} else {
							$doc->addStylesheet($root . '/' . $topath . str_replace('/', '.', $url) . '.css');
						}
					}

				} else {
					$import = true;
				}
			}
		}

		// so, no need to rebuild?
		if(!$rebuild){
			// add RTL css if needed
			if($is_rtl){
				$cssfile = $topath . str_replace('/', '.', str_replace('.less', '-rtl.less', $path)) . '.css';
				if(is_file(JPATH_ROOT . '/' . $cssfile)){
					$doc->addStylesheet($root . '/' . $cssfile);
				}
			}

			return false;
		}
		
		// variables & mixin
		$vars       = self::getVars();
		$output     = '';
		$importdirs = array();
		
		// iterate to each chunk and add separator mark
		$import = false;
		foreach ($split_contents as $chunk) {
			if ($import) {
				$import = false;
				
				$url = T3Path::cleanPath(dirname($path) . '/' . $chunk);
				
				// ignore vars.less and variables.less if they are in template folder
				// cause there may have other css less file with the same name (eg. font awesome)
				if (preg_match($rvarscheck, $url)){
					continue;
				}
				
				// remember this path when lookup for import
				$importdirs[dirname(JPATH_ROOT . '/' . $url)] = $root . '/' . dirname($url) . '/';

				$output .= "#$kfilepath{content: \"$url\";}\n@import \"$chunk\";\n\n";

				// check and add theme less
				if ($theme && strpos($url, 'themes/') === false) {
					$theme_rel  = 'themes/' . $theme . '/' . basename($url);
					$theme_path = T3_TEMPLATE_PATH . '/less/' . $theme_rel;
					if (is_file($theme_path)) {
						$importdirs[dirname($theme_path)] = T3_TEMPLATE_URL . dirname($theme_rel) . '/';

						$output .= "#$kfilepath{content: \"" . ('templates/' . T3_TEMPLATE . '/less/' . $theme_rel) . "\";}\n@import \"$theme_rel\";\n\n";
					}
				}

			} else {
				$import = true;
				$chunk  = trim($chunk);
				if ($chunk) {
					$output .= "#$kfilepath{content: \"$path\";}\n$chunk\n\n";
				}
			}
		}

		// compile RTL overwrite when in RTL mode
		if ($is_rtl) {

			$rtlcontent = '';

			// import rtl override
			$import = false;
			foreach ($split_contents as $chunk) {
				if ($import) {
					$import = false;
					
					$url = T3Path::cleanPath(dirname($path) . '/' . $chunk);
					// ignore vars.less and variables.less if they are in template folder
					// cause there may have other css less file with the same name (eg. font awesome)
					if (preg_match($rvarscheck, $url)){
						continue;
					}

					// process import file
					$rtl_url = preg_replace('@/less/(themes/)?@', '/less/rtl/', $url);
					
					// is there overwrite file?
					if (!is_file(JPATH_ROOT . '/' . $rtl_url)){
						continue;
					}

					// process import file
					$importcontent = JFile::read(JPATH_ROOT . '/' . $rtl_url);
					if (preg_match($rexcludepath, $rtl_url)){
						$importcontent = preg_replace($rimportvars, '', $importcontent);
					}

					// remember this path when lookup for import
					if (preg_match($rimport, $importcontent)) {
						$importdirs[dirname(JPATH_ROOT . '/' . $rtl_url)] = $root . '/' . dirname($rtl_url) . '/';
					}

					$rtlcontent .= "\n$importcontent\n\n";

					// rtl theme overwrite
					if ($theme && strpos($url, 'themes/') === false) {
						$rtlthemepath = preg_replace($rswitchrtl, '/less/rtl/' . $theme . '/', $url);

						if (is_file(JPATH_ROOT . '/' . $rtlthemepath)) {
							// process import file
							$importcontent = JFile::read(JPATH_ROOT . '/' . $rtlthemepath);
							$rtlcontent   .= "\n$importcontent\n\n";
							$importdirs[dirname(JPATH_ROOT . '/' . $rtlthemepath)] = $root . '/' . dirname($rtlthemepath) . '/';
						}
					}

				} else {
					$import = true;
				}
			}
			
			// override in template for this file
			$rtlpath = preg_replace($rswitchrtl, '/less/rtl/', $path);
			if (is_file(JPATH_ROOT . '/' . $rtlpath)) {
				// process import file
				$importcontent = JFile::read(JPATH_ROOT . '/' . $rtlpath);
				$rtlcontent   .= "\n$importcontent\n\n";
				$importdirs[dirname(JPATH_ROOT . '/' . $rtlpath)] = $root . '/' . dirname($rtlpath) . '/';
			}

			// rtl theme
			if ($theme) {
				$rtlthemepath = preg_replace($rswitchrtl, '/less/rtl/' . $theme . '/', $path);
				if (is_file(JPATH_ROOT . '/' . $rtlthemepath)) {
					// process import file
					$importcontent = JFile::read(JPATH_ROOT . '/' . $rtlthemepath);
					$rtlcontent   .= "\n$importcontent\n\n";
					$importdirs[dirname(JPATH_ROOT . '/' . $rtlthemepath)] = $root . '/' . dirname($rtlthemepath) . '/';
				}
			}

			if($rtlcontent){
				//rtl content will be treat as a new file
				$rtlfile = str_replace('.less', '-rtl.less', $path);
				$output = $output . "\n#$kfilepath{content: \"$rtlfile\";}\n\n#$krtlsep{content: \"separator\";}\n\n$rtlcontent\n\n";
			}
		}

		// common place
		$importdirs[T3_TEMPLATE_PATH . '/less'] = T3_TEMPLATE_URL . '/less/';

		// myself
		$importdirs[dirname(JPATH_ROOT . '/' . $path)] = $root . '/' . dirname($path) . '/';

		// compile less to css using lessphp
		$parser->SetImportDirs($importdirs);
		$parser->SetFileInfo(JPATH_ROOT . '/' . $path, $root . '/' . dirname($path) . '/');
		$source = $vars . "\n#$kvarsep{content: \"separator\";}\n" . $output;
		$parser->parse($source);
		$output = $parser->getCss();

		//use cssjanus to transform the content
		if ($is_rtl) {
			
			if($rtlcontent){
				$output = preg_split($rsplitbegin . $krtlsep . $rsplitend, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
				
				$rtlcontent = isset($output[2]) ? $output[2] : false;
				$output = $output[0];
			}

			T3::import('jacssjanus/ja.cssjanus');
			$output = JACSSJanus::transform($output, true);
			
			if($rtlcontent){
				$output = $output . "\n" . $rtlcontent;
			}
		}

		//update path and store to files
		$split_contents = preg_split($rsplitbegin . $kfilepath . $rsplitend, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
		$file_contents  = array();
		$file           = $path; //default
		$relpath        = JURI::base(true) . '/' . dirname($file);
		$isfile         = false;

		foreach ($split_contents as $chunk) {
			if ($isfile) {
				$isfile  = false;
				$file    = $chunk;
				$relpath = $topath ? T3Path::relativePath($topath, dirname($file)) :
									JURI::base(true) . '/' . dirname($file);
			} else {
				$file_contents[$file] = (isset($file_contents[$file]) ? $file_contents[$file] : '') . "\n" . 
																($file ? T3Path::updateUrl($chunk, $relpath) : $chunk) . "\n\n";
				$isfile = true;
			}
		}

		if(!empty($file_contents)){
			
			// remove the duplicate clearfix at the beginning
			$split_contents = preg_split($rsplitbegin . $kvarsep . $rsplitend, reset($file_contents));
			// ignore first one, it's clearfix
			if(is_array($split_contents)){
				array_shift($split_contents);
			}

			$file_contents[key($file_contents)] = implode("\n", $split_contents);

			//output the file to content and add to document
			foreach ($file_contents as $file => $content) {
				$cssfile = $topath . str_replace('/', '.', $file) . '.css';
				JFile::write(JPATH_ROOT . '/' . $cssfile, $content);

				$doc->addStylesheet($root . '/' . $cssfile);
			}
		}
	}

	/**
	 * Compile LESS to CSS for a specific theme or all themes
	 * @param  string  $theme  the specific theme
	 */
	public static function compileAll($theme = null)
	{
		$params   = T3::getTplParams();
		JFactory::getApplication()->setUserState ('current_template_params', $params);

		// get files need to compile
		$files    = array();
		$lesspath = 'templates/' . T3_TEMPLATE . '/less/';
		$csspath  = 'templates/' . T3_TEMPLATE . '/css/';
		$fullpath = JPath::clean(JPATH_ROOT . '/' . $lesspath);

		// t3 core plugin files
		$t3files  = array('frontend-edit', 'legacy-grid', 'legacy-form', 'legacy-navigation', 'megamenu', 'off-canvas');
		
		// all less file in less folders
		$lessFiles    = JFolder::files($fullpath, '.less', true, true, array('rtl', 'themes', '.svn', 'CVS', '.DS_Store', '__MACOSX'));

		$lessContent  = '';
		$relLessFiles = array();

		foreach ($lessFiles as $file) {
			$lessContent .= JFile::read($file) . "\n";
			$relLessFiles[] = ltrim(str_replace($fullpath, '', $file), '/\\');
		}

		$lessFiles = $relLessFiles;
		
		// get files imported in this list
		if (preg_match_all('#^\s*@import\s+"([^"]*)"#im', $lessContent, $matches)) {
			foreach ($lessFiles as $f) {
				if (!in_array($f, $matches[1])) {
					$files[] = substr($f, 0, -5);
				}
			}

			//build t3files
			foreach ($t3files as $key => $file) {
				if(in_array($file, $files)){
					unset($t3files[$key]);
				}
			}
		}
		
		// build default
		if (!$theme || $theme == 'default') {
			self::buildVars('', 'ltr');

			// compile all less files in template "less" folder
			foreach ($files as $file) {
				self::compileCss($lesspath . $file . '.less', $csspath . $file . '.css');
			}

			// if the template not overwrite the t3 core, we will compile those missing files
			if(!empty($t3files)){
				foreach ($t3files as $file) {
					self::compileCss(T3_REL . '/less/' . $file . '.less', $csspath . $file . '.css');
				}
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
					self::compileCss($lesspath . $file . '.less', $csspath . 'themes/' . $t . '/' . $file . '.css');
				}

				if(!empty($t3files)){
					foreach ($t3files as $file) {
						self::compileCss(T3_REL . '/less/' . $file . '.less', $csspath . 'themes/' . $t . '/' . $file . '.css');
					}
				}
			}
		}
		
		// compile rtl css
		if($params && $params->get('build_rtl', 0)){
			// compile default
			if (!$theme || $theme == 'default') {
				self::buildVars('', 'rtl');
				
				// compile
				foreach ($files as $file) {
					self::compileCss($lesspath . $file . '.less', $csspath . 'rtl/' . $file . '.css');
				}

				if(!empty($t3files)){
					foreach ($t3files as $file) {
						self::compileCss(T3_REL . '/less/' . $file . '.less', $csspath . 'rtl/' . $file . '.css');
					}
				}
			}
			
			if (is_array($themes)) {
				// rtl for themes
				foreach ($themes as $t) {
					self::buildVars($t, 'rtl');
					
					// compile
					foreach ($files as $file) {
						self::compileCss($lesspath . $file . '.less', $csspath . 'rtl/' . $t . '/' . $file . '.css');
					}

					if(!empty($t3files)){
						foreach ($t3files as $file) {
							self::compileCss(T3_REL . '/less/' . $file . '.less', $csspath . 'rtl/' . $t . '/' . $file . '.css');
						}
					}
				}
			}
		}
	}
}
