<?php
// $Id: miximixirecent.inc.php,v 1.14.2 2008/01/05 18:30:00 upk Exp $
// Copyright (C)
//   2005,2008 PukiWiki Plus! Team
//   2002-2005 PukiWiki Developers Team
//   2002      Y.MASUI http://masui.net/pukiwiki/ masui@masui.net
// License: GPL version 2
//
// Recent plugin -- Show RecentChanges list
//   * Usually used at 'MenuBar' page
//   * Also used at special-page, without no #recnet at 'MenuBar'

// Default number of 'Show latest N changes'
define('PLUGIN_MIXIRECENT_DEFAULT_LINES', 10);

// ----

define('PLUGIN_MIXIRECENT_USAGE', '#mixirecent(number-to-show)');

// Place of the cache of 'RecentChanges'
define('PLUGIN_MIXIRECENT_CACHE', CACHE_DIR . 'recent.dat');

// Hide title, if Pickup Headings
define('PLUGIN_MIXIRECENT_NOTITLE', TRUE);

function plugin_mixirecent_convert()
{
	global $vars, $date_format; // $_mixirecent_plugin_frame;
	static $done;

	$_mixirecent_plugin_frame_s = _('recent(%d)');
	$_mixirecent_plugin_frame   = sprintf('<h5>%s</h5><div>%%s</div>', $_mixirecent_plugin_frame_s);

	$mixirecent_lines = PLUGIN_MIXIRECENT_DEFAULT_LINES;
	if (func_num_args()) {
		$args = func_get_args();
		if (! is_numeric($args[0]) || isset($args[1])) {
			return PLUGIN_MIXIRECENT_USAGE . '<br />';
		} else {
			$mixirecent_lines = $args[0];
		}
	}

	// Show only the first one
	if (isset($done)) return '<!-- #mixirecent(): You already view changes -->';

	// Get latest N changes
	if (file_exists(PLUGIN_MIXIRECENT_CACHE)) {
		$source = file(PLUGIN_MIXIRECENT_CACHE);
		$lines = array_splice($source, 0, $mixirecent_lines);
	} else {
		return '#mixirecent(): Cache file of RecentChanges not found' . '<br />';
	}

	$date = $items = '';
	foreach ($lines as $line) {
		list($time, $page) = explode("\t", rtrim($line));

		$_date = get_date($date_format, $time);
		if ($date != $_date) {
			// End of the day
			if ($date != '') $items .= '</ul>' . "\n";

			// New day
			$date = $_date;
			$items .= '<strong>' . $date . '</strong>' . "\n" .
				'<ul class="mixirecent_list">' . "\n";
		}

		$s_page = htmlspecialchars($page);
		$pg_passage = get_pg_passage($page, FALSE);
		if (plugin_mixirecent_isValidDate(substr($page,-10)) && check_readable($page,false,false)) {
			// for Calendar/MiniCalendar
			$savepage = $vars['page'];
			$title = $page;
			$source = get_source($page);
			$itemhx = '';
			$itemlx = '';
			while(!empty($source)) {
				$line = array_shift($source);
				if (preg_match('/^(\*{1,3})(.*)\[#([A-Za-z][\w-]+)\](.*)$/m', $line, $matches)) {
					$anchortitle = strip_htmltag(convert_html($matches[2]));
					$anchortitle = preg_replace("/[\r\n]/", ' ', $anchortitle);
					$anchortitle = PLUGIN_MIXIRECENT_NOTITLE ? $anchortitle : $anchortitle . '(' . $title . ')';
					$sharp = '#';
					$itemhx .= "<li><a href=\"" . get_page_uri($page) . "{$sharp}{$matches[3]}\" title=\"$s_page $pg_passage\">{$anchortitle}</a></li>\n";
				}
			}
			if ($itemhx != '') {
				$items .= $itemhx;
			} else if($page == $vars['page']) {
				// No need to link to the page now you read, notifies where you just read
				$items .= ' <li>' . $s_page . '</li>' . "\n";
			} else {
				$items .= ' <li><a href="' . get_page_uri($page) . '" title="' .
					$s_page . ' ' . $pg_passage . '">' . $s_page . '</a></li>' . "\n";
			}
			$vars['page'] = $savepage;
		} else {
			if($page == $vars['page']) {
				// No need to link to the page now you read, notifies where you just read
				$items .= ' <li>' . $s_page . '</li>' . "\n";
			} else {
				$items .= ' <li><a href="' . get_page_uri($page) . '" title="' .
					$s_page . ' ' . $pg_passage . '">' . $s_page . '</a></li>' . "\n";
			}
		}
	}
	// End of the day
	if ($date != '') $items .= '</ul>' . "\n";

	$done = TRUE;

	return sprintf($_mixirecent_plugin_frame, count($lines), $items);
}

function plugin_mixirecent_isValidDate($aStr, $aSepList="-/ .")
{
	if ($aSepList == "") {
		return checkdate(substr($aStr,4,2),substr($aStr,6,2),substr($aStr,0,4));
	}
	if ( ereg("^([0-9]{2,4})[$aSepList]([0-9]{1,2})[$aSepList]([0-9]{1,2})$", $aStr, $m) ) {
		return checkdate($m[2], $m[3], $m[1]);
	}
	return false;
}
?>
