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


/**
 * T3Less class compile less
 *
 * @package T3
 */
class T3Less
{
	function __construct(){

		$less_lib_path = T3_ADMIN_PATH . '/includes/lessphp/Less';
		$files = JFolder::files($less_lib_path, '.php', false, true);
		foreach($files as $file){
			include_once $file;
		}

		$files = JFolder::files($less_lib_path . '/Node', '.php', false, true);
		usort($files,function($a, $b){
			return strlen($a) - strlen($b);
		});
		foreach($files as $file){
			include_once $file;
		}

		$files = JFolder::files($less_lib_path . '/Node/Mixin', '.php', false, true);
		usort($files,function($a, $b){
			return strlen($a) - strlen($b);
		});
		foreach($files as $file){
			include_once $file;
		}

		foreach(JFolder::files($less_lib_path . '/Exception', '.php', false, true) as $file){
			include_once $file;
		}


		$files = JFolder::files($less_lib_path . '/Visitor', '.php', false, true);
		usort($files,function($a, $b){
			return strlen($a) - strlen($b);
		});
		foreach($files as $file){
			include_once $file;
		}
	}

	/**
	 * Singleton constructor
	 * @return T3Less
	 */

	public static function getInstance()
	{
		static $t3less = null;
		if (!$t3less) {
			$t3less = new T3Less;
		}
		return $t3less;
	}

	/**
	 * Compile LESS to CSS
	 * @param   $path   the file path of less file
	 * @return  string  the css compiled content
	 */
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

	/**
	 * Compile LESS to CSS
	 * @param   $path   the less file to compile
	 * @return  string  url to css file
	 */
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
		$vars_lm = $app->getUserState('vars_last_modified', 0);
		
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

	/**
	 * @param   string  $path    file path of less file to compile
	 * @param   string  $topath  file path of output css file
	 * @return  bool|mixed       compile result or the css compiled content
	 */
	function compileCss($path, $topath = '')
	{
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


		//reset import dirs
		Less_Cache::$import_dirs = array();

		$env    = new Less_Environment();
		$parser = new Less_Parser($env);
		$env->setCompress(false);


		$app    = JFactory::getApplication();
		$tpl    = T3_TEMPLATE;
		$theme  = $app->getUserState('vars_theme');
		$tofile = null;
		$root   = JUri::root(true);

		//pattern
		$rcomment     = '@/\*[^*]*\*+([^/][^*]*\*+)*/@';
		$rspace       = '@[\r?\n]{2,}@';
		$rimport      = '@^\s*\@import\s+"([^"]*)"\s*;@im';
		$rvarscheck   = '@(base|bootstrap|'.preg_quote($tpl).')/less/(vars|variables)\.less@';
		$rexcludepath = '@(base|bootstrap|'.preg_quote($tpl).')/less/@';
		$rimportvars  = '@^\s*\@import\s+".*(variables-custom|variables|vars)\.less"\s*;@im';

		$rsplitbegin  = '@^\s*\#';
		$rsplitend    = '[^\s]*?\s*{\s*[\r\n]*\s*content:\s*"([^"]*)";\s*[\r\n]*\s*}@im';
		$rswitchrtl   = '@/less/(themes/[^/]*/)?@';


		$kfilepath    = 'less-file-path';
		$kvarsep      = 'less-content-separator';
		$krtlsep      = 'rtl-less-content';

		
		if ($topath) {
			$tofile = JPATH_ROOT . '/' . $topath;
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

				$content = $content . "\n@import \"$themepath\"; \n\n";
			}
		}
		
		// variables & mixin
		$vars       = $this->getVars();
		$output     = $content;
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
				$importdirs[dirname(JPATH_ROOT . '/' . $url)] = $root . '/' . dirname($url) . '/';

			} else {
				$import = true;
			}
		}
		
		$rtlcontent = '';
		$is_rtl     = $app->getUserState('DIRECTION') == 'rtl' && strpos($path, 'rtl/') === false;

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
					$url = preg_replace('@/less/(themes/)?@', '/less/rtl/', $url);
					
					if (!is_file(JPATH_ROOT . '/' . $url)){
						continue;
					}

					// process import file
					$importcontent = JFile::read(JPATH_ROOT . '/' . $url);
					if (preg_match($rexcludepath, $url)){
						$importcontent = preg_replace($rimportvars, '', $importcontent);
					}

					// remember this path when lookup for import
					if (preg_match($rimport, $importcontent)) {
						$importdirs[dirname(JPATH_ROOT . '/' . $url)] = $root . '/' . dirname($url) . '/';
					}

					$rtlcontent .= "\n$importcontent\n\n";
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
				$output = $output . "\n#$krtlsep{content: \"separator\";}\n\n$rtlcontent\n\n";
			}
		}


		// common place
		$importdirs[T3_TEMPLATE_PATH . '/less'] = T3_TEMPLATE_URL . '/css/';

		// myself
		$importdirs[dirname(JPATH_ROOT . '/' . $path)] = $root . '/' . dirname($path) . '/';


		// compile less to css using lessphp
		$parser->SetImportDirs($importdirs);
		$source = $vars . "\n#$kvarsep{content: \"separator\";}\n" . $output;
		$parser->parse($source);
		$output = $parser->getCss();
		
		// remove the dupliate clearfix at the beggining if not bootstrap.css file
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

		//update url of needed
		$output = T3Path::updateUrl($output, $topath ? T3Path::relativePath(dirname($topath), T3_TEMPLATE_URL . '/css') : T3_TEMPLATE_URL . '/css/');


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
	function getVars()
	{
		$app  = JFactory::getApplication();
		$rtl  = $app->getUserState('DIRECTION') == 'rtl' ? '_rtl' : '';
		$vars = $app->getUserState('vars_content' . $rtl);

		return $vars;
	}

	/**
	 * @param  string  $theme  template theme
	 * @param  string  $dir    direction (ltr or rtl)
	 * @return mixed
	 */
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
		
		$app->setUserState('vars_content' . $rtl, $vars);
	}


	/**
	 * Wrapper function to add a stylesheet to html document
	 * @param  string  $lesspath  the less file to add
	 */
	public static function addStylesheet($lesspath)
	{
		// build less vars, once only
		static $vars_built = false;
		if (!$vars_built) {
			self::buildVars();
			$vars_built = true;
		}
		
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

			$t3less = T3Less::getInstance();

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

	/**
	 * Compile LESS to CSS for a specific theme or all themes
	 * @param  string  $theme  the specific theme
	 */
	public static function compileAll($theme = null)
	{
		$less     = T3Less::getInstance();
		$params   = T3::getTemplateParams();

		// get files need to compile
		$files    = array();
		$lesspath = 'templates/' . T3_TEMPLATE . '/less/';
		$csspath  = 'templates/' . T3_TEMPLATE . '/css/';

		// t3 core plugin files
		$t3files  = array('megamenu', 'off-canvas');
		if($params->get('bs2compat', 0)){
			$t3files[] = 'compat';
		}
		
		// all less file in less folders
		$lessFiles   = JFolder::files(JPATH_ROOT . '/' . $lesspath, '.less');
		$lessContent = '';
		foreach ($lessFiles as $file) {
			$lessContent .= JFile::read(JPATH_ROOT . '/' . $lesspath . $file) . "\n";
		}
		
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
				$less->compileCss($lesspath . $file . '.less', $csspath . $file . '.css');
			}

			// if the template not overwrite the t3 core, we will compile those missing files
			if(!empty($t3files)){
				foreach ($t3files as $file) {
					$less->compileCss(T3_REL . '/less/' . $file . '.less', $csspath . $file . '.css');
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
					$less->compileCss($lesspath . $file . '.less', $csspath . 'themes/' . $t . '/' . $file . '.css');
				}

				if(!empty($t3files)){
					foreach ($t3files as $file) {
						$less->compileCss(T3_REL . '/less/' . $file . '.less', $csspath . $file . '.css');
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
					$less->compileCss($lesspath . $file . '.less', $csspath . 'rtl/' . $file . '.css');
				}

				if(!empty($t3files)){
					foreach ($t3files as $file) {
						$less->compileCss(T3_REL . '/less/' . $file . '.less', $csspath . $file . '.css');
					}
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

					if(!empty($t3files)){
						foreach ($t3files as $file) {
							$less->compileCss(T3_REL . '/less/' . $file . '.less', $csspath . $file . '.css');
						}
					}
				}
			}
		}
	}
}
