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

// No direct access
defined('_JEXEC') or die();

jimport('joomla.filesystem.file');
t3import('minify/csscompressor');
t3import('core/path');

/**
 * T3Template class provides extended template tools used for T3 framework
 *
 * @package T3
 */
class T3Minify
{
	/**
	 * 
	 * Known Valid CSS Extension Types
	 * @var array
	 */
	protected static $cssexts = array(".css", ".css1", ".css2", ".css3");

	/**
	 * 
	 * Check if a css url can be minify or not
	 * @var array
	 */
	public static function minifiable($url) {
		$url = preg_replace('#[?\#]+.*$#', '', $url);
		
		if(substr($url, 0, 2) === '//'){ //check and append if url is omit http
			$url = JURI::getInstance()->getScheme() . ':' . $url; 
		}

		if (preg_match('/^https?\:/', $url)) {
			if (strpos($url, JURI::base()) === false){
				// External css
				return false;
			}
		}

		foreach ( self::$cssexts as $ext ) {
			if (substr_compare($url, $ext, -strlen($ext), strlen($ext)) === 0) {
				return true;
			}
		}

		return false;
	}

	public static function fromUrlToPath($url){
		$root = JURI::root(true);
		$path = '';

		if(substr($url, 0, 2) === '//'){ //check and append if url is omit http
			$url = 'http:' . $url; 
		}

		if(preg_match('/^https?\:/', $url)){ //this is a full link
			$path = JPath::clean(JPATH_ROOT . '/' . substr($url, strlen(JURI::base())));
		} else {
			$path = JPath::clean(JPATH_ROOT . '/' . ($root && strpos($url, $root) == 0 ? substr($url, strlen($root)) : $url));
		}
		
		return $path;
	}

	public static function optimizecss($tpl)
	{
		$outputpath = JPATH_ROOT . '/' . $tpl->getParam('t3-assets', 't3-assets') . '/css';
		$outputurl = JURI::root(true) . '/' . $tpl->getParam('t3-assets', 't3-assets') . '/css';
		
		if (!JFile::exists($outputpath)){
			@JFolder::create($outputpath);
		}

		if (!is_writeable($outputpath)) {
			return false;
		}

		$doc = JFactory::getDocument();

		//======================= Group css ================= //
		$cssgroups = array();
		$stylesheets = array();
		$ielimit = 4096;
		$selcounts = 0;
		$regex = '/\{.+?\}|,/s'; //selector counter

		foreach ($doc->_styleSheets as $url => $stylesheet) {

			if ($stylesheet['mime'] == 'text/css' && self::minifiable($url)) {
				$stylesheet['path'] = self::fromUrlToPath($url);
				$stylesheet['data'] = @JFile::read($stylesheet['path']);

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
			$cssgroups[] = $cssgroup;
		}
		
		//======================= Group css ================= //

		$output = array();
		foreach ($cssgroups as $cssgroup) {
			if(isset($cssgroup['ignore'])){
				
				unset($cssgroup['ignore']);
				foreach ($cssgroup as $furl => $fsheet) {
					$output[$furl] = $fsheet;
				}

			} else {

				$groupname = 'css-' . substr(md5($cssgroup['groupname']), 0, 5) . '.css';
				$groupfile = $outputpath . '/' . $groupname;
				$grouptime = JFile::exists($groupfile) ? @filemtime($groupfile) : -1;
				$rebuild = $grouptime < 0; //filemtime == -1 => rebuild

				unset($cssgroup['groupname']);
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
						
						$cssmin = Minify_CSS_Compressor::process($fsheet['data']);
						$cssmin = T3Path::updateUrl($cssmin, T3Path::relativePath($outputurl, dirname($furl)));

						$cssdata[] = $cssmin;
					}

					@JFile::write($groupfile, implode("\n", $cssdata));
				}

				$output[$outputurl . '/' . $groupname] = array(
					'mime' => 'text/css',
					'media' => null,
					'attribs' => array()
					);
			}
		}

		//apply the change make change
		$doc->_styleSheets = $output;
	}
}
?>