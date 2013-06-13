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

if(!class_exists('lessc')) {
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
	public static function getInstance () {
		static $t3less = null;
		if (!$t3less) {
			$t3less = new T3Less;
		}
		return $t3less;
	}
	
	function getCss ($path) {
		$app = JFactory::getApplication();
		// get vars last-modified
		$vars_lm = $app->getUserState('vars_last_modified', 0);
		$vars_lm = $app->getUserState('vars_last_modified_rtl', 0);

		// less file last-modified
		$filepath = JPATH_ROOT.'/'.$path;
		$less_lm = filemtime ($filepath);

		// cache key
		$key = md5 ($vars_lm.':'.$less_lm.':'.$path);
		$group = 't3';
		$cache = JCache::getInstance ('output', array('lifetime'=>1440));
		// get cache
		$data = $cache->get ($key, $group);
		if ($data) {
			return $data;
		}

		// not cached, build & store it
		$data = $this->compileCss ($path)."\n";
		$cache->store ($data, $key, $group);

		return $data;
	}

	function buildCss ($path) {
		$app = JFactory::getApplication();
		// get vars last-modified
		$vars_lm = $app->getUserState('vars_last_modified', 0);
		$vars_lm = $app->getUserState('vars_last_modified_rtl', 0);
		$theme = $app->getUserState('vars_theme', '');


		// less file last-modified
		$filepath = JPATH_ROOT.'/'.$path;
		$less_lm = filemtime ($filepath);

		$less_lm_rtl = 0;
		$is_rtl = ($app->getUserState('DIRECTION') == 'rtl');
		if ($is_rtl) {
			$filepath_rtl = preg_replace ('/\/less\/(themes\/)?/', '/less/rtl/', $filepath);
			if (is_file($filepath_rtl))
				$less_lm_rtl = filemtime ($filepath_rtl);
		}

		// get css cached file
		$subdir = ($is_rtl ? 'rtl/' : '') . ($theme ? $theme . '/' : '');
		$cssfile = T3_DEV_FOLDER.'/' . $subdir . str_replace('/', '.', $path) . '.css';
		$cssurl = JURI::base(true).'/'.$cssfile;
		$csspath = JPATH_ROOT.'/'.$cssfile;
		if (is_file ($csspath) && filemtime($csspath) > $less_lm && filemtime($csspath) > $less_lm_rtl && filemtime($csspath) > $vars_lm) {
			return $cssurl;
		}

		// not cached, build & store it
		if (!$this->compileCss ($path, $cssfile)) {
			T3::error(JText::sprintf('T3_MSG_DEVFOLDER_NOT_WRITABLE', T3_DEV_FOLDER));
		}

		return $cssurl;
	}

	function compileCss ($path, $topath = '') {
		$app = JFactory::getApplication();
		$theme = $app->getUserState('vars_theme');
		$tofile = null;
		if ($topath) {
			$tofile = JPATH_ROOT.'/'.$topath;
			if (!is_dir (dirname($tofile))) {
				JFolder::create (dirname($tofile));
			}
		}

		$realpath = realpath(JPATH_ROOT.'/'.$path);
		// check path
		if(!is_file($realpath)){
		//if (!JPath::check ($realpath)){
			return;
		}
		// Get file content
		$content = JFile::read($realpath);
		// remove comments
		$content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);

		// split into array, separated by the import
		$arr = preg_split ('#^\s*@import\s+"([^"]*)"\s*;#im', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
		// check and add theme less if not is theme less
		if ($theme && !preg_match('#themes/#', $path)) {
			$themepath = 'themes/'.$theme.'/'.basename($path);
			if (is_file (T3_TEMPLATE_PATH.'/less/'.$themepath)) {
				$arr[] = $themepath;
				$arr[] = '';
			}
		}

		// variables & mixin
		$vars = $this->getVars();

		// add vars
		//$this->setImportDir (array(dirname($realpath), T3_TEMPLATE_PATH.'/less/'));

		$importdirs = array();

		// compile chuck
		$import = false;
		$output = '';
		foreach ($arr as $s) {
			if ($import) {
				$import = false;
				// ignore variables.less | vars.less
				if (preg_match ('/(vars|variables)\.less/', $s)) continue;
				// process import file
				$url = T3Path::cleanPath (dirname ($path).'/'.$s);
				$importcontent = JFile::read(JPATH_ROOT.'/'.$url);
				// ignore variables.less | vars.less
				$importcontent = preg_replace ('#^\s*@import\s+".*(variables|vars)\.less"\s*;#im', '', $importcontent);
				if(preg_match('#^\s*@import\s+"([^"]*)"\s*;#im', $importcontent)){
					$importdirs[] = dirname(JPATH_ROOT.'/'.$url);
				}

				$output .= "#less-file-path{content: \"$url\";}\n".$importcontent . "\n\n";
			} else {
				$import = true;
				$s = trim ($s);
				if ($s) {
					$output .= "#less-file-path{content: \"$path\";}\n" . $s . "\n\n";
				}
			}
		}

		$importdirs[] = dirname($realpath);
		$importdirs[] = T3_TEMPLATE_PATH.'/less/';
		$this->setImportDir ($importdirs);
		// convert to RTL if using RTL
		if ($app->getUserState('DIRECTION') == 'rtl' && !preg_match('#rtl/#', $path)) {
			// transform LTR to RTL
			T3::import('jacssjanus/ja.cssjanus');
			$output = JACSSJanus::transform ($output, true);
			
			// import rtl override
			// check override for import
			$import = false;
			foreach ($arr as $s) {
				if ($import) {
					$import = false;
					if ($s == 'vars.less') continue;
					// process import file
					$url = T3Path::cleanPath (dirname ($path).'/'.$s);
					$url = preg_replace ('/\/less\/(themes\/)?/', '/less/rtl/', $url);
					if (!is_file (JPATH_ROOT.'/'.$url)) continue;
					$importcontent = JFile::read(JPATH_ROOT.'/'.$url);

					$output .= "#less-file-path-rtl{content: \"$url\";}\n".$importcontent . "\n\n";
				} else {
					$import = true;
				}
			}

			// override in template for this file
			$rtlpath = preg_replace ('/\/less\/(themes\/[^\/]*\/)?/', '/less/rtl/', $path);
			if (is_file (JPATH_ROOT.'/'.$rtlpath)) {
				// process import file
				$importcontent = JFile::read(JPATH_ROOT.'/'.$rtlpath);
				$output .= "#less-file-path-rtl{content: \"$rtlpath\";}\n".$importcontent . "\n\n";
			}
			// rtl theme
			if ($theme) {
				$rtlthemepath = preg_replace ('/\/less\/(themes\/[^\/]*\/)?/', '/less/rtl/'.$theme.'/', $path);
				if (is_file (JPATH_ROOT.'/'.$rtlthemepath)) {
					// process import file
					$importcontent = JFile::read(JPATH_ROOT.'/'.$rtlthemepath);
					$output .= "#less-file-path-rtl{content: \"$rtlthemepath\";}\n".$importcontent . "\n\n";
				}
			}
		}

		$source = $vars ."\n/**** Content ****/\n" . $output;
		// compile less to css using lessphp
		$output = $this->compile ($source);

		$arr = preg_split ('#^\s*\#less-file-path\s*{\s*[\r\n]*\s*content:\s*"([^"]*)";\s*[\r\n]*\s*}#im', $output, -1, PREG_SPLIT_DELIM_CAPTURE);

		$output = '';
		$file = '';
		$isfile = false;
		foreach ($arr as $s) {
			if ($isfile) {
				$isfile = false;
				$file = $s;
				$relpath = $topath ? T3Path::relativePath(dirname($topath), dirname($file)) : JURI::base(true).'/'.dirname($file);
			} else {
				$output .= ($file ? T3Path::updateUrl ($s, $relpath) : $s) . "\n\n";
				$isfile = true;
			}
		}

		// remove the dupliate clearfix at the beggining if not bootstrap.css file
		if (!preg_match ('#bootstrap.less#', $path)) {
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

	function getVars () {
		$app = JFactory::getApplication();
		$rtl = $app->getUserState('DIRECTION') == 'rtl' ? '_rtl':'';
		$vars = $app->getUserState('vars_content'.$rtl);
		return $vars;
	}

	public static function buildVars ($theme=null, $dir=null) {
		$app = JFactory::getApplication();
		// get last modify from import files
		$path = T3_TEMPLATE_PATH.'/less/vars.less';
		$vars = JFile::read($path);
		// get last-modified
		preg_match_all('#^\s*@import\s+"([^"]*)"#im', $vars, $matches);
		$vars = '';
		// get last-modified
		$last_modified = filemtime ($path);
		if (count($matches[0])) {
			foreach ($matches[1] as $url) {
				$path = T3Path::cleanPath(T3_TEMPLATE_PATH.'/less/'.$url);
				if(file_exists($path)) {
					if ($last_modified < filemtime ($path)) $last_modified = filemtime ($path);
					$vars .= JFile::read ($path);
				}
			}
		}
		// theme style
		if ($theme === null) {
			$tpl = $app->getTemplate(true);
			$theme = $tpl->params->get ('theme');
		}
		$app->setUserState('vars_theme', $theme);

		// detect RTL
		if ($dir === null) {
			$doc = JFactory::getDocument();
			$dir = $doc->direction;
		}
		$app->setUserState('DIRECTION', $dir);

		if ($theme) {
			// add theme variables.less
			$path = T3_TEMPLATE_PATH.'/less/themes/'.$theme.'/variables.less';
			if (is_file ($path)) {
				if ($last_modified < filemtime ($path)) $last_modified = filemtime ($path);
				// append theme file into vars
				// $vars .= "\n".'@import "'.'themes/'.$theme.'/variables.less";';
				$vars .= JFile::read ($path);
			}
			// add theme variables-custom.less
			$path = T3_TEMPLATE_PATH.'/less/themes/'.$theme.'/variables-custom.less';
			if (is_file ($path)) {
				if ($last_modified < filemtime ($path)) $last_modified = filemtime ($path);
				// append theme file into vars
				$vars .= JFile::read ($path);
				// $vars .= "\n".'@import "'.'themes/'.$theme.'/variables-custom.less";';
			}
		}

		// RTL variables
		$rtl = '';
		if ($dir == 'rtl') {
			// add rtl variables.less
			$path = T3_TEMPLATE_PATH.'/less/rtl/variables.less';
			if (is_file ($path)) {
				if ($last_modified < filemtime ($path)) $last_modified = filemtime ($path);
				// append rtl file into vars
				$vars .= JFile::read ($path);
			}

			// add rtl theme variables.less
			$path = T3_TEMPLATE_PATH.'/less/rtl/'.$theme.'/variables.less';
			if (is_file ($path)) {
				if ($last_modified < filemtime ($path)) $last_modified = filemtime ($path);
				// append theme file into vars
				$vars .= JFile::read ($path);
			}

			$rtl = '_rtl';
		}

		if ($app->getUserState('vars_last_modified'.$rtl) != $last_modified.$theme) {
			$app->setUserState('vars_last_modified'.$rtl, $last_modified.$theme);
		} else {
			return $app->getUserState('vars_content'.$rtl);
		}

		if ($rtl) {
			// transform LTR to RTL
			T3::import('jacssjanus/ja.cssjanus');
			$vars = JACSSJanus::transform ($vars, true);
		}
		$app->setUserState('vars_content'.$rtl, $vars);
	}

	public static function addStylesheet ($lesspath) {
		// build less vars, once only
		static $vars_built = false;
		$t3less = T3Less::getInstance();
		if (!$vars_built) {
			self::buildVars();
			$vars_built = true;
		}

		$app = JFactory::getApplication();
		$tpl = $app->getTemplate(true);
		$theme = $tpl->params->get ('theme');

		$doc = JFactory::getDocument();
		if (defined ('T3_THEMER')) {
			// in Themer mode, using js to parse less for faster
			$doc->addStylesheet(JURI::base(true).'/'.T3Path::cleanPath($lesspath), 'text/less');
			// Add lessjs to process lesscss
			$doc->addScript (T3_URL.'/js/less-1.3.3.js');
		} else {
			// in development mode, using php to compile less for a better view of development
			if (preg_match('#(template(-responsive)?.less)#',$lesspath)) {
				// Development mode is on, try to include less file inside folder less/
				// get the less content
				$lessContent = JFile::read(JPATH_ROOT . '/' . $lesspath);
				$path = dirname($lesspath);
				// parse less content
				if (preg_match_all('#^\s*@import\s+"([^"]*)"#im', $lessContent, $matches)) {
					foreach ($matches[1] as $url) {
						if ($url == 'vars.less') {
							continue;
						}
						$url = $path.'/'.$url;
						$cssurl = $t3less->buildCss (T3Path::cleanPath($url));
						$doc->addStyleSheet($cssurl);
					}
				}

				// check and add theme, rtl less
				if ($theme) {
					$themepath = str_replace ('/less/', '/less/themes/'.$theme.'/', $lesspath);
					if (is_file (JPATH_ROOT . '/' . $themepath)) {
						$cssurl = $t3less->buildCss (T3Path::cleanPath($themepath));
						$doc->addStyleSheet($cssurl);
					}
				} elseif ($doc->direction == 'rtl' && !preg_match ('/rtl/', $lesspath)) {
					$rtlpath = str_replace ('/less/', '/less/rtl/', $lesspath);
					if (is_file (JPATH_ROOT . '/' . $rtlpath)) {
						$cssurl = $t3less->buildCss (T3Path::cleanPath($rtlpath));
						$doc->addStyleSheet($cssurl);
					}
				}

			} else {
				$cssurl = $t3less->buildCss (T3Path::cleanPath($lesspath));
				$doc->addStyleSheet($cssurl);
			}	
		}	
	}

	public static function compileAll ($theme = null) {

		$less = new self;
		// compile all css files
		$files = array ();
		$lesspath = 'templates/'.T3_TEMPLATE.'/less/';
		$csspath = 'templates/'.T3_TEMPLATE.'/css/';

		// get single files need to compile
		$lessFiles = JFolder::files (JPATH_ROOT.'/'.$lesspath, '.less');
		$lessContent = '';
		foreach ($lessFiles as $file) {
			$lessContent .= JFile::read (JPATH_ROOT.'/'.$lesspath.$file)."\n";
			// get file imported in this list
		}
		if (preg_match_all('#^\s*@import\s+"([^"]*)"#im', $lessContent, $matches)) {
			foreach ($lessFiles as $f) {
				if (!in_array($f, $matches[1])) $files[] = substr($f, 0, -5);
			}
		}

		if (!$theme || $theme == 'default') {
			self::buildVars('', 'ltr');
			// compile default
			foreach ($files as $file) {
				$less->compileCss ($lesspath.$file.'.less', $csspath.$file.'.css');
			}
		}
		// compile themes css
		if (!$theme) {
			// get themes
			$themes = JFolder::folders (JPATH_ROOT.'/'.$lesspath.'/themes');
		} else {
			$themes = $theme != 'default' ? (array) ($theme) : array();
		}
		foreach ($themes as $t) {
			self::buildVars($t, 'ltr');
			// compile
			foreach ($files as $file) {
				$less->compileCss ($lesspath.$file.'.less', $csspath.'themes/'.$t.'/'.$file.'.css');
			}
		}

		// compile rtl css
		self::buildVars('', 'rtl');
		// compile default
		if (!$theme || $theme == 'default') {
			foreach ($files as $file) {
				$less->compileCss ($lesspath.$file.'.less', $csspath.'rtl/'.$file.'.css');
			}
		}
		// rtl for themes
		foreach ($themes as $t) {
			self::buildVars($t, 'rtl');
			// compile
			foreach ($files as $file) {
				$less->compileCss ($lesspath.$file.'.less', $csspath.'rtl/'.$t.'/'.$file.'.css');
			}
		}

	}
}
