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

/**
 * T3Minify class provides extended template tools used for T3 framework
 *
 * @package T3
 */
class T3Minify
{
	/**
	 * Known Valid CSS Extension Types
	 * @var array
	 */
	public static $cssexts = array('.css', '.css1', '.css2', '.css3');

	/**
	 * Known valid js extension
	 * @var array
	 */
	public static $jsexts = array('.js');

	public static $jstools = array(
		'jsmin' => 'JSMin',
		'closurecompiler' => 'Minify_JS_ClosureCompiler'
		);

	public static $jstool = 'jsmin';

	public static $exclude = '';

	public static function prepare($tpl){
		//set the compress tool
		self::$exclude = $tpl->getParam('minify_exclude', '');
		self::$jstool  = $tpl->getParam('minify_js_tool', 'jsmin');

		if(self::$exclude){
			self::$exclude = '@' . preg_replace('@[,]+@', '|', preg_quote(self::$exclude)) . '@';
		}
	}

	/**
	 * @param $css
	 * @return string
	 */
	public static function minifyCss( $css ) {
		//T3::import('minify/csscompressor');

		$css = preg_replace( '#\s+#', ' ', $css );
		$css = preg_replace( '#/\*.*?\*/#s', '', $css );
		$css = str_replace( '; ', ';', $css );
		$css = str_replace( ': ', ':', $css );
		$css = str_replace( ' {', '{', $css );
		$css = str_replace( '{ ', '{', $css );
		$css = str_replace( ', ', ',', $css );
		$css = str_replace( '} ', '}', $css );
		$css = str_replace( ';}', '}', $css );

		return trim( $css );
	}

	/**
	 * @param $js
	 * @return string
	 */
	public static function minifyJs( $js ){

		T3::import('minify/' . self::$jstool);
		return call_user_func_array(array(self::$jstools[self::$jstool], 'minify'), array($js));
	}

	/**
	 * 
	 * Check and convert to css real path
	 * @param  string  $url  url to check
	 * @return  mixed  the css file path or false if not exist in server
	 */
	public static function cssPath($url = '') {
		
		//exclude
		if(self::$exclude && preg_match(self::$exclude, $url)){
			return false;
		}

		$url = preg_replace('#[?\#]+.*$#', '', $url);
		$base = JURI::base();
		$root = JURI::root(true);
		$ret = false;

		if(substr($url, 0, 2) === '//'){ //check and append if url is omit http
			$url = JURI::getInstance()->getScheme() . ':' . $url; 
		}

		//check for css file extensions
		foreach ( self::$cssexts as $ext ) {
			if (strlen($ext) <= strlen($url) && substr_compare($url, $ext, -strlen($ext), strlen($ext)) === 0) {
				$ret = true;
				break;
			}
		}

		if($ret){
			if (preg_match('/^https?\:/', $url)) { //is full link
				if (strpos($url, $base) === false){
					// external css
					return false;
				}

				$path = JPath::clean(JPATH_ROOT . '/' . substr($url, strlen($base)));
			} else {
				$path = JPath::clean(JPATH_ROOT . '/' . ($root && strpos($url, $root) === 0 ? substr($url, strlen($root)) : $url));
			}

			return is_file($path) ? $path : false;
		}

		return false;
	}

	/**
	 * 
	 * Check and convert to css real path
	 * @param  string  $url  url to check
	 * @return  mixed  the css file path or false if not exist in server
	 */
	public static function jsPath($url = '') {

		//leave any javascript file that have parameter (K2 is an example)
		if(preg_match('@[?#]+.*$@', $url)){
			return false;
		}

		//exclude
		if(self::$exclude && preg_match(self::$exclude, $url)){
			return false;
		}

		//clean
		$url = preg_replace('@[?#]+.*$@', '', $url);
		$base = JURI::base();
		$root = JURI::root(true);
		$ret = false;

		if(substr($url, 0, 2) === '//'){ //check and append if url is omit http
			$url = JURI::getInstance()->getScheme() . ':' . $url; 
		}

		//check for css file extensions
		foreach ( self::$jsexts as $ext ) {
			if (strlen($ext) <= strlen($url) && substr_compare($url, $ext, -strlen($ext), strlen($ext)) === 0) {
				$ret = true;
				break;
			}
		}

		if($ret){
			if (preg_match('/^https?\:/', $url)) { //is full link
				if (strpos($url, $base) === false){
					// external css
					return false;
				}

				$path = JPath::clean(JPATH_ROOT . '/' . substr($url, strlen($base)));
			} else {
				$path = JPath::clean(JPATH_ROOT . '/' . ($root && strpos($url, $root) === 0 ? substr($url, strlen($root)) : $url));
			}

			return is_file($path) ? $path : false;
		}

		return false;
	}

	/**
	 * @param   string  $url  url to refine
	 * @return  string  the refined url
	 */
	public static function fixUrl($url = ''){
		return ($url[0] === '/' || strpos($url, '://') !== false) ? $url : JURI::base(true) . '/' . $url;
	}

	/**
	 * @param   $tpl  template object
	 * @return  bool  optimize success or not
	 */
	public static function optimizecss($tpl)
	{
		$outputpath = JPATH_ROOT . '/' . $tpl->getParam('t3-assets', 't3-assets') . '/css';
		$outputurl = JURI::root(true) . '/' . $tpl->getParam('t3-assets', 't3-assets') . '/css';
		
		if (!JFile::exists($outputpath)){
			JFolder::create($outputpath);
			@chmod($outputpath, 0755);
		}

		if (!is_writeable($outputpath)) {
			return false;
		}
		
		//prepare config
		self::prepare($tpl);

		$doc = JFactory::getDocument();

		//======================= Group css ================= //
		$mediagroup = array();
		$cssgroups = array();
		$stylesheets = array();
		$ielimit = 4095;
		$selcounts = 0;
		$regex = '/\{.+?\}|,/s'; //selector counter
		$csspath = '';

		// group css into media
		$mediagroup['all'] = array();
		$mediagroup['screen'] = array();
		foreach ($doc->_styleSheets as $url => $stylesheet) {
			$media = $stylesheet['media'] ? $stylesheet['media'] : 'all';
			if (empty($mediagroup[$media])) {
				$mediagroup[$media] = array();
			}
			$mediagroup[$media][$url] = $stylesheet;
		}

		foreach ($mediagroup as $media => $group) {
			$stylesheets = array(); // empty - begin a new group
			foreach ($group as $url => $stylesheet) {
				$url = self::fixUrl($url);

				if ($stylesheet['mime'] == 'text/css' && ($csspath = self::cssPath($url))) {
					$stylesheet['path'] = $csspath;
					$stylesheet['data'] = JFile::read($csspath);

					$selcount = preg_match_all($regex, $stylesheet['data'], $matched);
					if(!$selcount) {
						$selcount = 1; //just for sure
					}

					//if we found an @import rule or reach IE limit css selector count, break into the new group
					if (preg_match('#@import\s+.+#', $stylesheet['data']) || $selcounts + $selcount >= $ielimit) {
						if(count($stylesheets)){
							$cssgroup = array();
							$groupname = array();
							foreach ( $stylesheets as $gurl => $gsheet ) {
								$cssgroup[$gurl] = $gsheet;
								$groupname[] = $gurl;
							}

							$cssgroup['groupname'] = implode('', $groupname);
							$cssgroup['media'] = $media;
							$cssgroups[] = $cssgroup;
						}

						$stylesheets = array($url => $stylesheet); // empty - begin a new group
						$selcounts = $selcount;
					} else {

						$stylesheets[$url] = $stylesheet;
						$selcounts += $selcount;
					}

				} else {
					// first get all the stylsheets up to this point, and get them into
					// the items array
					if(count($stylesheets)){
						$cssgroup = array();
						$groupname = array();
						foreach ( $stylesheets as $gurl => $gsheet ) {
							$cssgroup[$gurl] = $gsheet;
							$groupname[] = $gurl;
						}

						$cssgroup['groupname'] = implode('', $groupname);
						$cssgroups[] = $cssgroup;
					}

					//mark ignore current stylesheet
					$cssgroup = array($url => $stylesheet, 'ignore' => true);
					$cssgroups[] = $cssgroup;

					$stylesheets = array(); // empty - begin a new group
				}
			}

			if(count($stylesheets)){
				$cssgroup = array();
				$groupname = array();
				foreach ( $stylesheets as $gurl => $gsheet ) {
					$cssgroup[$gurl] = $gsheet;
					$groupname[] = $gurl;
				}

				$cssgroup['groupname'] = implode('', $groupname);
				$cssgroup['media'] = $media;
				$cssgroups[] = $cssgroup;
			}
		}

		//======================= Group css ================= //

		$output = array();
		foreach ($cssgroups as $cssgroup) {
			if(isset($cssgroup['ignore'])){

				unset($cssgroup['ignore']);
				unset($cssgroup['groupname']);
				unset($cssgroup['media']);
				foreach ($cssgroup as $furl => $fsheet) {
					$output[$furl] = $fsheet;
				}

			} else {
				$media = $cssgroup['media'];
				$groupname = 'css-' . substr(md5($cssgroup['groupname']), 0, 5) . '.css';
				$groupfile = $outputpath . '/' . $groupname;
				$grouptime = JFile::exists($groupfile) ? @filemtime($groupfile) : -1;
				$rebuild = $grouptime < 0; //filemtime == -1 => rebuild

				unset($cssgroup['groupname']);
				unset($cssgroup['media']);
				foreach ($cssgroup as $furl => $fsheet) {
					if(!$rebuild && @filemtime($fsheet['path']) > $grouptime){
						$rebuild = true;
					}
				}

				if($rebuild){

					$cssdata = array();
					foreach ($cssgroup as $furl => $fsheet) {
						$cssdata[] = "\n\n/*===============================";
						$cssdata[] = $furl;
						$cssdata[] = "================================================================================*/";

						$cssmin = self::minifyCss($fsheet['data']);
						$cssmin = T3Path::updateUrl($cssmin, T3Path::relativePath($outputurl, dirname($furl)));

						$cssdata[] = $cssmin;
					}

					$cssdata = implode("\n", $cssdata);
					JFile::write($groupfile, $cssdata);
					$grouptime = @filemtime($groupfile);
					@chmod($groupfile, 0644);
				}

				$output[$outputurl . '/' . $groupname.'?t='.($grouptime % 1000)] = array(
					'mime' => 'text/css',
					'media' => $media == 'all' ? NULL : $media,
					'attribs' => array()
					);
			}
		}

		//apply the change make change
		$doc->_styleSheets = $output;
	}

	/**
	 * Optimize javascript
	 * @param $tpl
	 * @return bool
	 */
	public static function optimizejs($tpl){
		$outputpath = JPATH_ROOT . '/' . $tpl->getParam('t3-assets', 't3-assets') . '/js';
		$outputurl = JURI::root(true) . '/' . $tpl->getParam('t3-assets', 't3-assets') . '/js';

		if (!JFile::exists($outputpath)){
			JFolder::create($outputpath);
			@chmod($outputpath, 0755);
		}

		if (!is_writeable($outputpath)) {
			return false;
		}

		//prepare config
		self::prepare($tpl);

		$doc = JFactory::getDocument();

		//======================= Group css ================= //
		$jsgroups = array();
		$scripts = array();
		
		foreach ($doc->_scripts as $url => $script) {

			$url = self::fixUrl($url);

			if ($script['mime'] == 'text/javascript' && ($jspath = self::jsPath($url))) {
				
				$script['path'] = $jspath;
				$script['data'] = JFile::read($jspath);

				$scripts[$url] = $script;

			} else {
				// first get all the stylsheets up to this point, and get them into
				// the items array
				if(count($scripts)){
					$jsgroup = array();
					$groupname = array();
					foreach ( $scripts as $gurl => $gsheet ) {
						$jsgroup[$gurl] = $gsheet;
						$groupname[] = $gurl;
					}

					$jsgroup['groupname'] = implode('', $groupname);
					$jsgroups[] = $jsgroup;
				}

				//mark ignore current script
				$jsgroup = array($url => $script, 'ignore' => true);
				$jsgroups[] = $jsgroup;

				$scripts = array(); // empty - begin a new group
			}
		}

		if(count($scripts)){
			$jsgroup = array();
			$groupname = array();
			foreach ( $scripts as $gurl => $gsheet ) {
				$jsgroup[$gurl] = $gsheet;
				$groupname[] = $gurl;
			}

			$jsgroup['groupname'] = implode('', $groupname);
			$jsgroups[] = $jsgroup;
		}

		//======================= Group js ================= //

		$output = array();
		foreach ($jsgroups as $jsgroup) {
			if(isset($jsgroup['ignore'])){

				unset($jsgroup['ignore']);
				foreach ($jsgroup as $furl => $fsheet) {
					$output[$furl] = $fsheet;
				}

			} else {

				$groupname = 'js-' . substr(md5($jsgroup['groupname']), 0, 5) . '.js';
				$groupfile = $outputpath . '/' . $groupname;
				$grouptime = JFile::exists($groupfile) ? @filemtime($groupfile) : -1;
				$rebuild = $grouptime < 0; //filemtime == -1 => rebuild

				unset($jsgroup['groupname']);
				foreach ($jsgroup as $furl => $fsheet) {
					if(!$rebuild && @filemtime($fsheet['path']) > $grouptime){
						$rebuild = true;
					}
				}

				if($rebuild){

					$jsdata = array();
					foreach ($jsgroup as $furl => $fsheet) {
						$jsdata[] = "\n\n/*===============================";
						$jsdata[] = $furl;
						$jsdata[] = "================================================================================*/;";

						$jsmin    = $fsheet['data'];

						//already minify?
						if(!preg_match('@.*\.min\.js.*@', $furl)){
							$jsmin = self::minifyJs($fsheet['data']);
							//$jsmin = T3Path::updateUrl($jsmin, T3Path::relativePath($outputurl, dirname($furl)));
						}

						$jsdata[] = $jsmin;
					}

					$jsdata = implode("\n", $jsdata);
					JFile::write($groupfile, $jsdata);
					$grouptime = @filemtime($groupfile);
					@chmod($groupfile, 0644);
				}

				$output[$outputurl . '/' . $groupname.'?t='.($grouptime % 1000)] = array(
					'mime' => 'text/javascript',
					'defer' => false,
					'async' => false
				);
			}
		}

		//apply the change make change
		$doc->_scripts = $output;
	}
}
?>