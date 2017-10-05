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
use Joomla\Registry\Registry;
/**
 * T3AB class
 *
 * @package T3
 * 
 * Make A/B testing for a template style. Distribute user access to alternative style.
 */

class T3AB
{

	public static function checkAltStyle () {
		$alt_style_id = self::getAltStyle();
		if (!$alt_style_id) return;
		self::applyAltStyle($alt_style_id);
	}

	public static function getAltStyle() {
		static $alt_style_id = NULL;
		if ($alt_style_id === NULL) {
			// detect if available alternative template style
			$app = JFactory::getApplication();
			$params = $app->getTemplate(true)->params;
			$alt_style_id = $params->get('ab_altstyle', 0);
		}
		return $alt_style_id;
	}

	public static function switchStyle($alt_style_id) {
		$app = JFactory::getApplication();
		$template = $app->getTemplate (true);
		if (!$alt_style_id || $alt_style_id == $template->id) return;
		$currentparams = $template->params;
		// use alternative theme
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('home, template, params')
			->from('#__template_styles')
			->where('client_id = 0 AND id= ' . (int)$alt_style_id)
			->order('id ASC');
		$db->setQuery($query);
		$tm = $db->loadObject();

		if (is_object($tm) && file_exists(JPATH_THEMES . '/' . $tm->template)) {
			$tplparams = new Registry();
			$tplparams->loadString ($tm->params);

			$app->setTemplate($tm->template, $tplparams);
			// setTemplate is buggy, we need to update more info
			// update the template
			$template = $app->getTemplate(true);
			$template->id = $alt_style_id;
			$template->home = $tm->template;
		}

	}

	public static function getCookieName () {
		return 't3ab-alt-style-' . JFactory::getApplication()->getTemplate(true)->id;
	}

	public static function generateMode () {
		$template = JFactory::getApplication()->getTemplate(true);
		$params = $template->params;
		$mode = $params->get('nextmode', 0);

		// update lastmode
		$params->set('nextmode', 1 - $mode);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->update('#__template_styles')
			->set('params =' . $db->quote($params->toString()))
			->where('id =' . (int)$template->id);

		$db->setQuery($query);
		$db->execute();

		// store cookie
		setcookie(self::getCookieName(), $mode,  strtotime( '+365 days' ) , '/');
		// update to current cookie
		JFactory::getApplication()->input->cookie->set(self::getCookieName(), $mode);
		return $mode;
	}

	public static function getMode () {
		static $mode = NULL;

		if ($mode === NULL) {
			$app = JFactory::getApplication();
			$cookie = $app->input->cookie;
			$mode = $cookie->get (self::getCookieName());

			if ($mode === NULL) {
				$mode = self::generateMode();
			}

			$mode = (int)$mode;
		}

		return $mode;		
	}

	public static function applyAltStyle ($alt_style_id) {
		// detect cookie
		$app = JFactory::getApplication();
		$mode = self::getMode();

		// apply alternative style
		if ($mode == 1) {
			self::switchStyle ($alt_style_id);
		}

		// add script to tracking
		$doc = JFactory::getDocument();

		$template = $app->getTemplate(true);
		$params = $template->params;
		$tracking_type = $params->get('ab_tracking_type', 'ga');
		$tracking_code = '';

		switch ($tracking_type) {
			case 'ga':
				$trackingId = $params->get('ab_tracking_ga_id');
				$expId = $params->get('ab_tracking_ga_expid');
				if ($trackingId && $expId) {
					$tracking_code = self::getGATrackingCode ($trackingId, $expId, $mode);
				}
				break;
			
			case 'meta':
				$metaname = $params->get('ab_tracking_meta_name');
				if ($metaname) {
					$tracking_code = '<meta name="' . $metaname . '" content="' . $mode . '" />';
				}
				break;
		}

		if ($tracking_code) {
			$params->set('snippet_open_head', $tracking_code . "\n" . $params->get('snippet_open_head', ''));
		}

	}

	public static function getGATrackingCode ($trackingId, $expId, $variant) {

		$script = <<<SCRIPT
		<script>
		// 1. Load the analytics.js library.
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

		// 2. Create a tracker.
		ga('create', '$trackingId', 'auto');

		// 3. Set the experiment ID and variation ID.
		ga('set', 'exp', '$expId.$variant');

		// 4. Send a pageview hit to Google Analytics.
		ga('send', 'pageview');
		</script>
SCRIPT;
		return $script;
	}

	public static function getPageCacheKey () {
		$alt_style_id = self::getAltStyle();
		if (!$alt_style_id) return NULL;
		$mode = self::getMode();
		$name = self::getCookieName();
		return $name . '-' . $mode;
	}
}
