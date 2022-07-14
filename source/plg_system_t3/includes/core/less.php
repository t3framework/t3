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

T3::import('core/path');
T3::import('lessphp/' . T3_BASE_LESS_COMPILER);

/**
 * T3Less class compile less
 *
 * @package T3
 */
class T3Less
{
	static $kfilepath    = 'less-file-path';
	static $kvarsep      = 'less-content-separator';
	static $krtlsep      = 'rtl-less-content';
	static $rsplitbegin  = '@^\s*\#';
	static $rsplitend    = '[^\s]*?\s*{\s*[\r\n]*\s*content:\s*"([^"]*)";\s*[\r\n]*\s*}[\r\n]*@im';
	static $rswitchrtl   = '@/less/(themes/[^/]*/)?@';
	static $rcomment     = '@/\*[^*]*\*+([^/][^*]*\*+)*/@';
	static $rspace       = '@[\r?\n]{2,}@';
	static $rimport      = '@^\s*\@import\s+"([^"]*)"\s*;@im';
	static $rimportvars  = '@^\s*\@import\s+".*(variables-custom|variables|vars|mixins)\.less"\s*;@im';

	static $_path = null;

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
	public static function buildCss ($path, $return = false) {
		$rtpl_check		= '@'.preg_quote(T3_TEMPLATE_REL, '@') . '/@i';
		$rtpl_less_check		= '@'.preg_quote(T3_TEMPLATE_REL, '@') . '/less/@i';

		$app     = JFactory::getApplication();
		$doc		 = JFactory::getDocument();
		$theme   = $app->getUserState('current_theme', '');
		$is_rtl      = ($app->getUserState('current_direction') == 'rtl');
		if(array_key_exists("HTTP_USER_AGENT", $_SERVER)){
			$_SERVER['HTTP_USER_AGENT'] = "";
		}
		$ie8 = preg_match('/MSIE 8\./', $_SERVER['HTTP_USER_AGENT']);

		// get css cached file
		$subdir  = ($is_rtl ? 'rtl/' : '') . ($theme ? $theme . '/' : '');
		$cssdir  = T3_DEV_FOLDER . ($ie8 ? '/ie8' : '') . '/' . $subdir;
		$cssfile = $cssdir . str_replace('/', '.', $path) . '.css';

		// modified time
		$less_lm = @filemtime (JPATH_ROOT . '/' . $path);
		$css_lm = @filemtime ($cssfile);
		$vars_lm = self::getState('vars_last_modified', 0);

		$list = self::parse($path);

		if (empty ($list)) return false;

		// prepare output list
		$split = !$ie8 && !$return && preg_match ($rtpl_less_check, $path) && !preg_match ('/bootstrap/', $path);
		$output_files = array();

		foreach ($list as $f => $import) {
			if ($import) {
				$css = $cssdir . str_replace('/', '.', $f) . '.css';
				if ($split) $output_files[] = $css;
				$less_lm = max ($less_lm, @filemtime(JPATH_ROOT . '/' . $f));
				$css_lm = max ($css_lm, @filemtime(JPATH_ROOT . '/' . $css));
			}
		}

		// itself
		$output_files [] = $cssfile;

		// check modified
		$rebuild = $vars_lm > $css_lm || $less_lm > $css_lm;
		if ($rebuild) {
			if ($split) {
				self::compileCss($path, $cssfile, true, $list);
			} else {
				self::compileCss($path, $cssfile, false, $list);
			}
		}

		if (!$return) {
			// add css
			foreach ($output_files as $css) {
				$doc->addStylesheet($css);
			}
		} else {
			return $cssfile;
		}
	}

	public static function relativePath($topath, $path, $default = null){
		$rel = T3Path::relativePath($topath, $path);
		return $rel ? $rel . '/' : './';
	}

	/**
	 * @param   string  $path    file path of less file to compile
	 * @param   string  $topath  file path of output css file
	 * @return  bool|mixed       compile result or the css compiled content
	 */
	public static function compileCss($path, $topath = '', $split = false, $list = null) {
		$fromdir = dirname($path);
		$app     = JFactory::getApplication();
		$is_rtl      = ($app->getUserState('current_direction') == 'rtl');

		if (empty ($list)) $list = self::parse($path);
		if (empty ($list)) {
			return false;
		}
		// join $list
		$content = '';
		$importdirs = array();
		$todir = $topath ? dirname($topath) : $fromdir;

		if (!is_dir(JPATH_ROOT . '/' . $todir)) {
			JFolder::create(JPATH_ROOT . '/' . $todir);
		}
		$importdirs[JPATH_ROOT . '/' . $fromdir] = './';
		foreach ($list as $f => $import) {
			if ($import) {
				$importdirs[JPATH_ROOT . '/' . dirname($f)] = self::relativePath($fromdir, dirname($f));
				$content .= "\n#".self::$kfilepath."{content: \"{$f}\";}\n";
				// $content .= "@import \"$import\";\n\n";
				if (is_file(JPATH_ROOT . '/' . $f)) {
					$less_content = file_get_contents(JPATH_ROOT . '/' . $f);
					// remove vars/mixins for template & t3 less
					if (preg_match ('@'.preg_quote(T3_TEMPLATE_REL, '@') . '/@i', $f) || preg_match ('@'.preg_quote(T3_REL, '@') . '/@i', $f)) {
						$less_content = preg_replace(self::$rimportvars, '', $less_content);
					}
					self::$_path = T3Path::relativePath($fromdir, dirname($f)) . '/';
					$less_content = preg_replace_callback(self::$rimport, array('T3Less', 'cb_import_path'), $less_content);
					$content .= $less_content;
				}
			} else {
				$content .= "\n#".self::$kfilepath."{content: \"{$path}\";}\n";
				$content .= $f . "\n\n";
			}
		}

		// get vars
		$vars_files = explode('|', self::getVars('urls'));

		// build source
		$source = '';
		// build import vars
		foreach ($vars_files as $var) {
			$var_file = T3Path::relativePath($fromdir, $var);
			$source .= "@import \"" . $var_file . "\";\n";
		}
		
		// less content
		$source .= "\n#" . self::$kvarsep . "{content: \"separator\";}\n" . $content;

		// call Less to compile,
		// temporary compile into source path, then update url to destination later
		try {
			$output = T3LessCompiler::compile($source, $importdirs);
		} catch (Exception $e) {	
			// echo 'Caught exception: ',  $e->getMessage(), "\n";
			throw new Exception($path . "<br />\n" . $e->getMessage());
		}

		// process content
		//use cssjanus to transform the content
		if ($is_rtl) {
			$output = preg_split(self::$rsplitbegin . self::$krtlsep . self::$rsplitend, $output, -1, PREG_SPLIT_DELIM_CAPTURE);
			$rtlcontent = isset($output[2]) ? $output[2] : false;
			$output = $output[0];

			T3::import('jacssjanus/ja.cssjanus');
			$output = JACSSJanus::transform($output, true);

			// join with rtl content
			if($rtlcontent){
				$output = $output . "\n" . $rtlcontent;
			}
		}
		// skip duplicate clearfix
		$arr = preg_split(self::$rsplitbegin . self::$kvarsep . self::$rsplitend, $output, 2);
		if (preg_match ('/bootstrap.less/', $path)) {
			$output = implode ("\n", $arr);
		} else {
			$output = count($arr) > 1 ? $arr[1] : $arr[0];
		}

		//remove comments and clean up
		$output = preg_replace(self::$rcomment, '', $output);
		$output = preg_replace(self::$rspace, "\n\n", $output);

		// update url for output
		$file_contents = self::updateUrl ($output, $path, $todir, $split);

		// split if needed
		if ($split) {
			if(!empty($file_contents)){
				//output the file to content and add to document
				foreach ($file_contents as $file => $content) {
					if ($file) {
						$content = trim($content);
						$filename = str_replace('/', '.', $file) . '.css';
						JFile::write(JPATH_ROOT . '/' . $todir . '/' . $filename, $content);
					}
				}
			}
		} else {
			if ($topath) {
				JFile::write(JPATH_ROOT . '/' . $topath, $file_contents);
			} else {
				return $output;
			}
		}
		// write to path
		return true;
	}

	/**
	 * Update url for background, import according to output path
	 *
	 * @param $css the compiled css
	 * @param $path the source less path
	 * @param $output_dir destination of css file
	 * @param $split split into small files or not
	 * @return if $split then return an array of sub file, else return the whole css
	 */
	public static function updateUrl ($css, $path, $output_dir, $split) {
		//update path and store to files
		$split_contents = preg_split(self::$rsplitbegin . self::$kfilepath . self::$rsplitend, $css, -1, PREG_SPLIT_DELIM_CAPTURE);
		$file_contents  = array();
		$file       		= $path;
		$isfile         = false;
		$output         = '';

		// split
		foreach ($split_contents as $chunk) {
			if ($isfile) {
				$isfile  = false;
				$file = $chunk;
			} else {
				$content = T3Path::updateUrl (trim($chunk), T3Path::relativePath($output_dir, dirname($file)));
				$file_contents[$file] = (isset($file_contents[$file]) ? $file_contents[$file] : '') . "\n" . $content . "\n\n";
				$output .= $content . "\n";
				$isfile = true;
			}
		}

		return $split ? $file_contents : trim($output);
	}

	/**
	 * Get less variables
	 * @return mixed
	 */
	public static function getVars($name = '')
	{
		return self::getState('vars_' . ($name ? $name.'_' : '') . 'content');
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
			'caching'	=> true,
			'cachebase' => JPATH_ROOT.'/'.T3_DEV_FOLDER
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
			'caching'	=> true,
			'cachebase' => JPATH_ROOT.'/'.T3_DEV_FOLDER
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
		if (T3::isAdmin()) {
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
		if ($responsive !== self::getState('current_responsive')) {
			self::setState('current_responsive', $responsive);
			$last_modified = time();
			touch($path, $last_modified);
		} else {
			$last_modified = filemtime($path);
		}

		$vars_content          = file_get_contents($path);
		$vars_urls = array();

		preg_match_all('#^\s*@import\s+"([^"]*)"#im', $vars_content, $matches);
		if (count($matches[0])) {
			foreach ($matches[1] as $url) {
				$path = T3Path::cleanPath(T3_TEMPLATE_PATH . '/less/' . $url);
				if (file_exists($path)) {
					$last_modified = max($last_modified, filemtime($path));
					$vars_urls[] = T3Path::cleanPath(T3_TEMPLATE_REL . '/less/' . $url);
				}
			}
		}

		// add override variables
		$paths = array();
		if ($theme) {
			$paths[] = T3_TEMPLATE_REL . "/less/themes/{$theme}/variables.less";
			$paths[] = T3_TEMPLATE_REL . "/less/themes/{$theme}/variables-custom.less";
		}
		if ($dir == 'rtl') {
			$paths[] = T3_TEMPLATE_REL . "/less/rtl/variables.less";
			if ($theme) $paths[] = T3_TEMPLATE_REL . "/less/rtl/themes/{$theme}/variables.less";
		}
		if (!defined('T3_LOCAL_DISABLED')) {
			$paths[] = T3_LOCAL_REL . "/less/variables.less";
			if ($theme) {
				$paths[] = T3_LOCAL_REL . "/less/themes/{$theme}/variables.less";
				$paths[] = T3_LOCAL_REL . "/less/themes/{$theme}/variables-custom.less";
			}
			if ($dir == 'rtl') {
				$paths[] = T3_LOCAL_REL . "/less/rtl/variables.less";
				if ($theme) $paths[] = T3_LOCAL_REL . "/less/rtl/themes/{$theme}/variables.less";
			}
		}
		if (!$responsive) {
			$paths[] = T3_REL . '/less/non-responsive-variables.less';
			$paths[] = T3_TEMPLATE_REL . '/less/non-responsive-variables.less';
		}

		foreach ($paths as $file) {
			if (is_file(JPATH_ROOT . '/' . $file)) {
				$last_modified = max($last_modified, filemtime(JPATH_ROOT . '/' . $file));
				$vars_urls[] = $file;
			}
		}

		if (self::getState('vars_last_modified') != $last_modified) {
			self::setState('vars_last_modified', $last_modified);
		}
		self::setState('vars_urls_content', implode('|', $vars_urls));
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
				$doc->addScript(T3_URL . '/js/less.js?v=2');

				if($doc->direction == 'rtl'){
					$doc->addScript(T3_URL . '/js/cssjanus.js');
				}

				define('T3_LESS_JS', 1);
			}
		} else {
			self::buildCss(T3Path::cleanPath($lesspath));
		}
	}


	public static function getOutputCssPath ($lessPath, $theme = '', $is_rtl = false) {
		$cssPath = '';
		$extraPath = '';
		$extraPath .= $is_rtl ? 'rtl/' : '';
		$extraPath .= $theme ? ($is_rtl ? '' : 'themes/') . $theme . '/' : '';

		if (preg_match ('/(^|\/)less\//i', $lessPath)) {
			$cssPath = preg_replace ('/(^|\/)less\//i', '\1css/' . $extraPath, $lessPath);
		} else {
			$cssPath = dirname ($lessPath) . '/' . $extraPath . basename($lessPath);
		}
		$cssPath = str_replace('.less', '.css', $cssPath);
		return $cssPath;
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
		$files = array();
		$toPath  = T3Path::getLocalPath('', true);

		// t3 core plugin files
		$t3files  = array('less/frontend-edit.less', 'less/legacy-grid.less', 'less/legacy-navigation.less', 'less/megamenu.less', 'less/off-canvas.less');

		// all less file in the template folder
		$lessFiles    = JFolder::files(T3_TEMPLATE_PATH, '.less', true, true, array('rtl', 'themes', '.svn', 'CVS', '.DS_Store', '__MACOSX'));

		$relLessFiles = array();

		$importedFiles = array();

		foreach ($lessFiles as $file) {
			$file = str_replace('\\', '/', $file);
			$lessContent = file_get_contents($file);
			$rel = ltrim(str_replace(T3_TEMPLATE_PATH, '', $file), '/');
			$reldir = dirname ($rel);
			$ignore = true;
			if (preg_match_all('#^\s*@import\s+"([^"]*)"#im', $lessContent, $matches)) {
				foreach ($matches[1] as $if) {
					$if = T3Path::cleanPath($reldir . '/' . $if);
					if (!in_array($if, $importedFiles)) $importedFiles[] = $if;
					// check if this file import anything in main less folder. if yes, put it in the compile list
					if (preg_match ('@^less/@', $if)) $ignore = false;
				}
			}
			if (!$ignore) $relLessFiles[] = $rel;
		}

		$lessFiles = $relLessFiles;

		// ignore files which are imported in other file
		foreach ($lessFiles as $f) {
			if (!in_array($f, $importedFiles) && !preg_match ('@^less/(themes|rtl)/@i', $f)) {
				$files[] = $f;
			}
		}

		//build t3files
		foreach ($t3files as $key => $file) {
			if(in_array($file, $files)){
				unset($t3files[$key]);
			}
		}

		// build default
		if (!$theme || $theme == 'default') {
			self::buildVars('', 'ltr');

			// compile all less files in template "less" folder
			foreach ($files as $lessPath) {
				$cssPath = self::getOutputCssPath($lessPath);
				self::compileCss(T3_TEMPLATE_REL . '/' . $lessPath, $toPath . $cssPath);
			}

			// if the template not overwrite the t3 core, we will compile those missing files
			if(!empty($t3files)){
				foreach ($t3files as $lessPath) {
					$cssPath = self::getOutputCssPath($lessPath);
					self::compileCss(T3_REL . '/' . $lessPath, $toPath . $cssPath);
				}
			}
		}

		// build themes
		if (!$theme) {
			// get themes
			$themes = JFolder::folders(T3_TEMPLATE_PATH . '/less/themes');
		} else {
			$themes = $theme != 'default' ? (array)($theme) : array();
		}

		if (is_array($themes)) {
			foreach ($themes as $t) {
				self::buildVars($t, 'ltr');

				// compile
				foreach ($files as $lessPath) {
					$cssPath = self::getOutputCssPath($lessPath, $t);
					self::compileCss(T3_TEMPLATE_REL . '/' . $lessPath, $toPath . $cssPath);
				}

				if(!empty($t3files)){
					foreach ($t3files as $lessPath) {
						$cssPath = self::getOutputCssPath($lessPath, $t);
						self::compileCss(T3_REL . '/' . $lessPath, $toPath . $cssPath);
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
				foreach ($files as $lessPath) {
					$cssPath = self::getOutputCssPath($lessPath, '', true);
					self::compileCss(T3_TEMPLATE_REL . '/' . $lessPath, $toPath . $cssPath);
				}

				if(!empty($t3files)){
					foreach ($t3files as $lessPath) {
						$cssPath = self::getOutputCssPath($lessPath, '', true);
						self::compileCss(T3_REL . '/' . $lessPath, $toPath . $cssPath);
					}
				}
			}

			if (is_array($themes)) {
				// rtl for themes
				foreach ($themes as $t) {
					self::buildVars($t, 'rtl');

					// compile
					foreach ($files as $lessPath) {
						$cssPath = self::getOutputCssPath($lessPath, $t, true);
						self::compileCss(T3_TEMPLATE_REL . '/' . $lessPath, $toPath . $cssPath);
					}

					if(!empty($t3files)){
						foreach ($t3files as $lessPath) {
							$cssPath = self::getOutputCssPath($lessPath, $t, true);
							self::compileCss(T3_REL . '/' . $lessPath, $toPath . $cssPath);
						}
					}
				}
			}
		}
	}

	/**
	 * Parse a less file to get all its overrides before compile
	 * @param  string  $path the less file
	 */
	public static function parse($path) {
		$rtpl_check		= '@'.preg_quote(T3_TEMPLATE_REL, '@') . '/@i';
		$rtpl_less_check		= '@'.preg_quote(T3_TEMPLATE_REL, '@') . '/less/@i';

		$app    = JFactory::getApplication();
		$theme  = $app->getUserState('current_theme');
		$dir  = $app->getUserState('current_direction');
		$is_rtl = ($dir == 'rtl');

		$rel_path = preg_replace($rtpl_check, '', $path);
		$rel_dir = dirname($rel_path);
		// check path
		$realpath = realpath(JPATH_ROOT . '/' . $path);
		if (!is_file($realpath)) {
			return false;
		}

		// get file content
		$content = file_get_contents($realpath);

		//remove vars.less
		$content = preg_replace(self::$rimportvars, '', $content);

		// split into array, separated by the import
		$arr = preg_split(self::$rimport, $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		$arr[] = basename($rel_path);
		$arr[] = '';

		$list = array();
		$rtl_list = array();
		$list[$path] = '';
		$import = false;

		foreach ($arr as $chunk) {
			if ($import) {
				$import = false;
				$import_url = T3Path::cleanPath(T3_TEMPLATE_REL . '/' . $rel_dir . '/' .$chunk);
				// if $url in less folder, get all its overrides
				if (preg_match ($rtpl_less_check, $import_url)) {
					$less_rel_url = preg_replace($rtpl_less_check, '', $import_url);
					$array = T3Path::getAllPath('less/' . $less_rel_url, true);
					if ($theme) {
						$array = array_merge($array, T3Path::getAllPath('less/themes/'.$theme.'/'.$less_rel_url, true));
					}

					foreach ($array as $f) {
						// add file in template only
						if (preg_match ($rtpl_check, $f)) {
							$list [$f] = T3Path::relativePath(dirname($path), $f);
						}
					}

					// rtl overrides
					if ($is_rtl) {
						$array = T3Path::getAllPath('less/rtl/'.$less_rel_url, true);
						if ($theme) {
							$array = array_merge($array, T3Path::getAllPath('less/rtl/themes/'.$theme.'/'.$less_rel_url, true));
						}

						foreach ($array as $f) {
							// add file in template only
							if (preg_match ($rtpl_check, $f)) {
								$rtl_list [$f] = T3Path::relativePath(dirname($path), $f);
							}
						}
					}
				} else {
					$list [$import_url] = T3Path::cleanPath($chunk);
					// rtl override
					if ($is_rtl) {
						$rtl_url = preg_replace ('/\/less\//', '/less/rtl/', $import_url);
						if (is_file(JPATH_ROOT.'/'.$rtl_url)) {
							$rtl_list [$rtl_url] = T3Path::relativePath(dirname($path), $rtl_url);
						}
					}
				}
			} else {
				$import = true;
				$list [$chunk] = false;
			}
		}

		// remove itself
		unset($list[$path]);

		// join rtl
		if ($is_rtl) {
			$list ["\n\n#" . self::$krtlsep . "{content: \"separator\";}\n\n"] = false;
			$list = array_merge($list, $rtl_list);
		}

		return $list;
	}

	public static	function cb_import_path ($match) {
		$f = $match[1];
		$newf = T3Path::cleanPath(self::$_path . $f);
		return str_replace($f, $newf, $match[0]);
	}
}
