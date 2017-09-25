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

class T3BootstrapTpl
{
	static function render($list)
	{
		$base      = T3Bootstrap::getBase();
		$active    = T3Bootstrap::getActive();
		$active_id = $active->id;
		$path      = $base->tree;
		?>
		<ul class="nav navbar-nav">
			<?php
			foreach ($list as &$item) :
				//intergration with new params joomla 3.6.x (menu_show)
				$menu_show = (int)$item->params->get('menu_show', 0);
				if ($menu_show!=1)
					continue;
				$class = 'item-' . $item->id;
				if ($item->id == $active_id) {
					$class .= ' current';
				}

				if (in_array($item->id, $path)) {
					$class .= ' active';
				} elseif ($item->type == 'alias') {
					$aliasToId = $item->params->get('aliasoptions');
					if (count($path) > 0 && $aliasToId == $path[count($path) - 1]) {
						$class .= ' active';
					} elseif (in_array($aliasToId, $path)) {
						$class .= ' alias-parent-active';
					}
				}

				if ($item->type == 'separator' || $item->type == 'heading') {
					$class .= ' divider';
				}

				if ($item->deeper) {
					if ($item->level > 1) {
						$class .= ' dropdown-submenu';
					} else {
						$class .= ' deeper dropdown';
					}
				}

				if ($item->parent) {
					$class .= ' parent';
				}

				if (!empty($class)) {
					$class = ' class="' . trim($class) . '"';
				}

				echo '<li' . $class . '>';

				// Render the menu item.
				switch ($item->type) :
					case 'separator':
					case 'url':
					case 'component':
					case 'heading':
						echo self::item($item->type, $item);
						break;

					default:
						echo self::item('url', $item);
						break;
				endswitch;

				// The next item is deeper.
				if ($item->deeper) {
					echo '<ul class="dropdown-menu" role="menu">';
				} // The next item is shallower.
				elseif ($item->shallower) {
					echo '</li>';
					echo str_repeat('</ul></li>', $item->level_diff);
				} // The next item is on the same level.
				else {
					echo '</li>';
				}
			endforeach;
			?>
		</ul>
	<?php
	}

	public static function item($type, $item)
	{
		if (method_exists(__CLASS__, $type)) {
			return self::$type($item);
		} else {
			return $type;
		}
	}

	public static function component($item)
	{
		// Note. It is important to remove spaces between elements.
		$class    = $item->anchor_css ? $item->anchor_css : '';
		$title    = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';
		$caret    = '';
		$dropdown = '';

		if($item->deeper && $item->level < 2){
			$class    .= ' dropdown-toggle';
			$dropdown  = ' data-toggle="dropdown"';
			$caret     = '<em class="caret"></em>';
		}

		if(!empty($class)){
			$class = 'class="'. trim($class) .'" ';
		}

		if ($item->menu_image) {
			$item->params->get('menu_text', 1) ?
				$linktype = '<img class="' . $item->menu_image_css . '"  src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
				$linktype = '<img class="' . $item->menu_image_css . '"  src="' . $item->menu_image . '" alt="' . $item->title . '" />';
		} else {
			$linktype = $item->title;
		}

		switch ($item->browserNav) :
			default:
			case 0:
				?>
				<a <?php echo $class; ?>href="<?php echo $item->flink; ?>" <?php echo $title, $dropdown; ?>><?php echo $linktype, $caret; ?></a>
				<?php
				break;
			case 1:
				// _blank
				?>
				<a <?php echo $class; ?>href="<?php echo $item->flink; ?>" target="_blank" <?php echo $title, $dropdown; ?>><?php echo $linktype, $caret; ?></a>
				<?php
				break;
			case 2:
				// window.open
				?>
				<a <?php echo $class; ?>href="<?php echo $item->flink; ?>" onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;" <?php echo $title, $dropdown; ?>><?php echo $linktype, $caret; ?></a>
				<?php
				break;
		endswitch;
	}

	public static function heading($item)
	{
		?>
		<span class="nav-header"><?php echo $item->title; ?></span>
		<?php
	}

	public static function separator($item)
	{
		// Note. It is important to remove spaces between elements.
		$title = $item->anchor_title ? ' title="' . $item->anchor_title . '" ' : '';
		if ($item->menu_image) {
			$item->params->get('menu_text', 1) ?
				$linktype = '<img class="' . $item->menu_image_css . '"  src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
				$linktype = '<img class="' . $item->menu_image_css . '"  src="' . $item->menu_image . '" alt="' . $item->title . '" />';
		} else {
			$linktype = $item->title;
		}

		?>
		<span class="separator" <?php echo $title; ?>><?php echo $linktype; ?></span>
		<?php
	}

	public static function url($item)
	{

		// Note. It is important to remove spaces between elements.
		$class    = $item->anchor_css ? $item->anchor_css : '';
		$title    = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';
		$caret    = '';
		$dropdown = '';

		if($item->deeper && $item->level < 2){
			$class    .= ' dropdown-toggle';
			$dropdown  = ' data-toggle="dropdown"';
			$caret     = '<em class="caret"></em>';
		}

		if(!empty($class)){
			$class = 'class="'. trim($class) .'" ';
		}

		if ($item->menu_image) {
			$item->params->get('menu_text', 1) ?
				$linktype = '<img class="' . $item->menu_image_css . '"  src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
				$linktype = '<img class="' . $item->menu_image_css . '"  src="' . $item->menu_image . '" alt="' . $item->title . '" />';
		} else {
			$linktype = $item->title;
		}
		$flink = $item->flink;
		$flink = JFilterOutput::ampReplace(htmlspecialchars($flink));

		switch ($item->browserNav) :
			default:
			case 0:
				?>
				<a <?php echo $class; ?>href="<?php echo $flink; ?>" <?php echo $title, $dropdown; ?>><?php echo $linktype, $caret; ?></a>
				<?php
				break;
			case 1:
				// _blank
				?>
				<a <?php echo $class; ?>href="<?php echo $flink; ?>" target="_blank" <?php echo $title, $dropdown; ?>><?php echo $linktype, $caret; ?></a>
				<?php
				break;
			case 2:
				// window.open
				$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,' . $params->get('window_open');
				?>
				<a <?php echo $class; ?>href="<?php echo $flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options; ?>');return false;" <?php echo $title, $dropdown; ?>><?php echo $linktype, $caret; ?></a>
				<?php
				break;
		endswitch;
	}
}
