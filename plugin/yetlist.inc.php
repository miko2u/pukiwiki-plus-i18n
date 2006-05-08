<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: yetlist.inc.php,v 1.28.2 2006/05/07 03:55:26 miko Exp $
// Copyright (C)
//   2005-2006 PukiWiki Plus! Team
//   2001-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Yet list plugin - Show a list of dangling links (not yet created)

function plugin_yetlist_action()
{
//	global $_title_yetlist, $_err_notexist, $_symbol_noexists, $non_list;
	global $_symbol_noexists, $non_list, $whatsdeleted;

	$retval = array(
		'msg' => _('List of pages which have not yet been created.'),
		'body' => ''
	);

	// Diff
	$pages = array_diff(get_existpages(CACHE_DIR, '.ref'), get_existpages());
	if (empty($pages)) {
		$retval['body'] = _('All pages have been created.');
		return $retval;
	}

	$empty = TRUE;

	// Load .ref files and Output
	$script      = get_script_uri();
	$refer_regex = '/' . $non_list . '|^' . preg_quote($whatsdeleted, '/') . '$/S';
	asort($pages, SORT_STRING);
	foreach ($pages as $file=>$page) {
		$refer = array();
		foreach (file(CACHE_DIR . $file) as $line) {
			list($_page) = explode("\t", rtrim($line));
			$refer[] = $_page;
		}
		// Diff
		$refer = array_diff($refer, preg_grep($refer_regex, $refer));
		if (! empty($refer)) {
			$empty = FALSE;
			$refer = array_unique($refer);
			sort($refer, SORT_STRING);

			$r_refer = '';
			$link_refs = array();
			foreach ($refer as $_refer) {
				$r_refer = rawurlencode($_refer);
				$link_refs[] = '<a href="' . $script . '?' . $r_refer . '">' .
					htmlspecialchars($_refer) . '</a>';
			}
			$link_ref = join(' ', $link_refs);
			unset($link_refs);

			$s_page = htmlspecialchars($page);
//			if (PKWK_READONLY) {
			if (auth::check_role('readonly')) {
				$href = $s_page;
			} else {
				// Dangling link
				$href = '<span class="noexists">' . $s_page . '<a href="' .
					$script . '?cmd=edit&amp;page=' . rawurlencode($page) .
					'&amp;refer=' . $r_refer . '">' . $_symbol_noexists .
					'</a></span>';
			}
			$retval['body'] .= '<li>' . $href . ' <em>(' . $link_ref . ')</em></li>' . "\n";
		}
	}

	if ($empty) {
		$retval['body'] = $_err_notexist;
		return $retval;
	}

	if ($retval['body'] != '')
		$retval['body'] = '<ul>' . "\n" . $retval['body'] . '</ul>' . "\n";

	return $retval;
}
?>
