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

class T3MenuMegamenuTpl
{
	static function beginmenu($vars)
	{
		$menu          = $vars['menu'];
		$animation     = $menu->getParam('navigation_animation', '');
		$trigger       = $menu->getParam('navigation_trigger', 'hover');
		$responsive    = $menu->getParam('responsive', 1);
		$anim_duration = $menu->getParam('navigation_animation_duration', 0);

		$cls  = ' class="t3-megamenu' . ($trigger == 'hover' && $animation ? ' animate ' . $animation : '') . '"';
		$data = $animation && $anim_duration ? ' data-duration="' . $anim_duration . '"' : '';
		$data = $data . ($responsive ? ' data-responsive="true"' : '');

		return "<div $cls $data>";
	}

	static function endmenu($vars)
	{
		return '</div>';
	}

	static function beginnav($vars)
	{
		$item = $vars['item'];
		$cls  = '';
		if (!$item) {
			// first nav
			$cls = 'nav navbar-nav level0';
		} else {
			$cls .= ' mega-nav';
			$cls .= ' level' . $item->level;
		}
		if ($cls) $cls = 'class="' . trim($cls) . '"';

		return '<ul itemscope itemtype="http://www.schema.org/SiteNavigationElement" ' . $cls . '>';

	}

	static function endnav($vars)
	{
		return '</ul>';
	}

	static function beginmega($vars)
	{
		$item    = $vars['item'];
		$setting = $item->setting;
		$sub     = $setting['sub'];
		$cls     = 'nav-child ' . ($item->dropdown ? 'dropdown-menu mega-dropdown-menu' : 'mega-group-ct');
		$style   = '';
		$data    = '';
		if (isset($sub['class'])) {
			$data .= " data-class=\"{$sub['class']}\"";
			$cls  .= " {$sub['class']}";
		}
		if (isset($setting['alignsub']) && $setting['alignsub'] == 'justify') {
			$cls  .= ' ' . ($vars['menu']->editmode ? 'span' : T3_BASE_NONRSP_WIDTH_PREFIX) . '12';
		} else {
			if (isset($sub['width'])) {
				if ($item->dropdown) $style = ' style="width: ' . str_replace('px', '', $sub['width']) . 'px"';
				$data .= ' data-width="' . str_replace('px', '', $sub['width']) . '"';
			}
		}

		if ($cls) $cls = 'class="' . trim($cls) . '"';

		return "<div $cls $style $data><div class=\"mega-dropdown-inner\">";
	}

	static function endmega($vars)
	{
		return '</div></div>';
	}

	static function beginrow($vars)
	{
		return '<div class="' . ($vars['menu']->editmode ? 'row-fluid' : T3_BASE_ROW_FLUID_PREFIX) . '">';
	}

	static function endrow($vars)
	{
		return '</div>';
	}

	static function begincol($vars)
	{
		$setting = isset($vars['setting']) ? $vars['setting'] : array();
		$width   = isset($setting['width']) ? $setting['width'] : T3_BASE_MAX_GRID;
		$data    = "data-width=\"$width\"";
		$cls     = ($vars['menu']->editmode ? 'span' : T3_BASE_NONRSP_WIDTH_PREFIX) . $width;

		if (isset($setting['position'])) {
			$cls  .= " mega-col-module";
			$data .= " data-position=\"{$setting['position']}\"";
		} else {
			$cls  .= " mega-col-nav";
		}
		if (isset($setting['class'])) {
			$cls  .= " {$setting['class']}";
			$data .= " data-class=\"{$setting['class']}\"";
		}
		if (isset($setting['hidewcol'])) {
			$cls  .= " hidden-collapse";
			$data .= " data-hidewcol=\"1\"";
		}
		if (isset($setting['groupstyle']) && $setting['groupstyle']) {
			$cls  .= " " . $setting['groupstyle'];
			$data .= " data-groupstyle=\"{$setting['groupstyle']}\"";
		}

		return "<div class=\"$cls\" $data><div class=\"mega-inner\">";
	}

	static function endcol($vars)
	{
		return '</div></div>';
	}

	static function beginitem($vars)
	{
		$item    = $vars['item'];
		$setting = $item->setting;
		$cls     = $item->class;

		if ($item->dropdown) {
			$cls .= $item->level == 1 ? ' dropdown' : ' dropdown-submenu';
		}

		if ($item->mega)  $cls .= ' mega';
		if ($item->group) $cls .= ' mega-group';
		if ($item->type == 'separator' && !$item->group && !$item->mega) $cls .= ' divider';

		$data = "data-id=\"{$item->id}\" data-level=\"{$item->level}\"";
		if ($item->group) $data .= " data-group=\"1\"";
		if (isset($setting['class'])) {
			$cls  .= " {$setting['class']}";
			$data .= " data-class=\"{$setting['class']}\"";
		}
		if (isset($setting['alignsub'])) {
			$cls  .= " mega-align-{$setting['alignsub']}";
			$data .= " data-alignsub=\"{$setting['alignsub']}\"";
		}
		if (isset($setting['hidesub'])) $data .= " data-hidesub=\"1\"";
		if (isset($setting['xicon']))   $data .= " data-xicon=\"{$setting['xicon']}\"";
		if (isset($setting['caption'])) $data .= " data-caption=\"" . htmlspecialchars($setting['caption']) . "\"";
		if (isset($setting['hidewcol'])) {
			$data .= " data-hidewcol=\"1\"";
			$cls  .= " sub-hidden-collapse";
		}

		if ($cls) $cls = 'class="' . trim($cls) . '"';

		return "<li itemprop='name' $cls $data>";
	}

	static function enditem($vars)
	{
		return '</li>';
	}

	static function item($vars)
	{
		$item    = $vars['item'];
		$setting = $item->setting;

		// Note. It is important to remove spaces between elements.
		$vars['class']    = $item->anchor_css ? $item->anchor_css : '';
		$vars['title']    = $item->anchor_title ? ' title="' . $item->anchor_title . '" ' : '';
		$vars['rel']    = $item->anchor_rel ? ' rel="' . $item->anchor_rel . '" ' : '';
		$vars['dropdown'] = ' data-target="#"';
		$vars['caret']    = '';
		$vars['icon']     = '';
		$vars['caption']  = '';

		if ($item->dropdown && $item->level < 2) {
			$vars['class']    .= ' dropdown-toggle';
			$vars['dropdown'] .= ' data-toggle="dropdown"'; // Note: data-target for JomSocial old bootstrap lib
			$vars['caret']     = '<em class="caret"></em>';
		}

		if($item->group){
			$vars['class']    .= ' dropdown-header mega-group-title';
		}

		if ($item->menu_image) {
			$item->params->get('menu_text', 1) ?
				$vars['linktype'] = '<img class="' . $item->menu_image_css . '" src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
				$vars['linktype'] = '<img class="' . $item->menu_image_css . '" src="' . $item->menu_image . '" alt="' . $item->title . '" />';
		} else {
			$vars['linktype'] = $item->title;
		}

		if (isset($setting['xicon']) && $setting['xicon']) {
			$vars['icon'] = '<span class="' . $setting['xicon'] . '"></span>';
		}
		if (isset($setting['caption']) && $setting['caption']) {
			$vars['caption'] = '<span class="mega-caption">' . $setting['caption'] . '</span>';
		} else if ($item->level == 1 && $vars['menu']->get('top_level_caption')) {
			$vars['caption'] = '<span class="mega-caption mega-caption-empty">&nbsp;</span>';
		}

		switch ($item->type) {
			case 'separator':
			case 'heading':
				$html = self::_('item_separator', $vars);
				break;
			case 'component':
				$html = self::_('item_component', $vars);
				break;
			case 'url':
			default:
				$html = self::_('item_url', $vars);
		}

		return $html;
	}

	static function item_url($vars)
	{
		$item     = $vars['item'];
		$class    = $vars['class'];
		$rel	  = $vars['rel'];
		$title    = $vars['title'];
		$dropdown = $vars['dropdown'];
		$caret    = $vars['caret'];
		$linktype = $vars['linktype'];
		$icon     = $vars['icon'];
		$caption  = $vars['caption'];

		$flink = $item->flink;
		$flink = JFilterOutput::ampReplace(htmlspecialchars($flink));

		switch ($item->browserNav) :
			default:
			case 0:
				$link = "<a itemprop='url' class=\"$class\" $rel href=\"$flink\" $title $dropdown>$icon$linktype$caret$caption</a>";
				break;
			case 1:
				// _blank
				$link = "<a itemprop='url' class=\"$class\" $rel href=\"$flink\" target=\"_blank\" $title $dropdown>$icon$linktype$caret$caption</a>";
				break;
			case 2:
				// window.open
				$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes';
				$link = "<a itemprop='url' class=\"$class\" $rel href=\"$flink\"" . (!$vars['menu']->editmode ? " onclick=\"window.open(this.href,'targetWindow','$options');return false;\"" : "") . " $title $dropdown>$icon$linktype$caret$caption</a>";
				break;
		endswitch;

		return $link;
	}

	static function item_separator($vars)
	{
		$item     = $vars['item'];
		$class    = $vars['class'];
		$title    = $vars['title'];
		$dropdown = $vars['dropdown'];
		$caret    = $vars['caret'];
		$linktype = $vars['linktype'];
		$icon     = $vars['icon'];
		$caption  = $vars['caption'];
		// Note. It is important to remove spaces between elements.

		$class .= " separator";

		return "<span class=\"$class\" $title $dropdown>$icon$title $linktype$caret$caption</span>";
	}

	static function item_component($vars)
	{
		$item     = $vars['item'];
		$class    = $vars['class'];
		$rel	  = $vars['rel'];
		$title    = $vars['title'];
		$dropdown = $vars['dropdown'];
		$caret    = $vars['caret'];
		$linktype = $vars['linktype'];
		$icon     = $vars['icon'];
		$caption  = $vars['caption'];
		// Note. It is important to remove spaces between elements.

		switch ($item->browserNav) :
			default:
			case 0:
				$link = "<a itemprop='url' class=\"$class\" $rel href=\"{$item->flink}\" $title $dropdown>$icon$linktype $caret$caption</a>";
				break;
			case 1:
				// _blank
				$link = "<a itemprop='url' class=\"$class\" $rel href=\"{$item->flink}\" target=\"_blank\" $title $dropdown>$icon$linktype $caret$caption</a>";
				break;
			case 2:
				// window.open
				$link = "<a itemprop='url' class=\"$class\" $rel href=\"{$item->flink}\"" . (!$vars['menu']->editmode ? " onclick=\"window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;\"" : "") . " $title $dropdown>$icon$linktype $caret$caption</a>";
				break;
		endswitch;

		return $link;
	}

	static function _($tmpl, $vars) {
		if (function_exists($func = 'T3MenuMegamenuTpl_'.$tmpl)) {
			return $func($vars) . "\n";
		} else if (method_exists('T3MenuMegamenuTpl', $tmpl)) {
			return T3MenuMegamenuTpl::$tmpl($vars) . "\n";
		} else {
			return "$tmpl\n";
		}
	}
}