<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: related.inc.php,v 1.10.1 2008/01/05 18:49:00 upk  Exp $
// Copyright (C)
//   2007-2008 PukiWiki Plus! Developers Team
//   2005, 2007 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Related plugin: Show Backlinks for the page

function plugin_related_convert()
{
	global $vars;

	return make_related($vars['page'], 'p');
}

// Show Backlinks: via related caches for the page
function plugin_related_action()
{
	global $vars, $defaultpage;

	$_page = isset($vars['page']) ? $vars['page'] : '';
	if ($_page == '') $_page = $defaultpage;

	// Get related from cache
	$data = links_get_related_db($_page);
	if (! empty($data)) {
		// Hide by array keys (not values)
		foreach(array_keys($data) as $page) {
			if (is_cantedit($page) || check_non_list($page)) {
				unset($data[$page]);
			}
		}
	}

	// Result
	$s_word = htmlspecialchars($_page);
	$msg = 'Backlinks for: ' . $s_word;
	$retval  = '<a href="' . get_page_uri($_page) . '">' .
		'Return to ' . $s_word .'</a><br />'. "\n";

	if (empty($data)) {
		$retval .= '<ul><li>No related pages found.</li></ul>' . "\n";	
	} else {
		// Show count($data)?
		ksort($data, SORT_STRING);
		$retval .= '<ul>' . "\n";
		foreach ($data as $page=>$time) {
			$s_page  = htmlspecialchars($page);
			$passage = get_passage($time);
			$retval .= ' <li><a href="' . get_page_uri($page) . '">' . $s_page .
				'</a> ' . $passage . '</li>' . "\n";
		}
		$retval .= '</ul>' . "\n";
	}
	return array('msg'=>$msg, 'body'=>$retval);
}
?>
