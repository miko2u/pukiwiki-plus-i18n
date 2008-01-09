<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: topicpath.inc.php,v 1.7.6 2008/01/05 18:54:00 upk Exp $
// Copyright (C)
//   2004-2008 PukiWiki Plus! Team
//   2004-2005 PukiWiki Developers Team
// License: GPL (any version)
//
// 'topicpath' plugin

// Show a link to $defaultpage or not
defined('PLUGIN_TOPICPATH_TOP_DISPLAY') or define('PLUGIN_TOPICPATH_TOP_DISPLAY', 1);
// Label for $defaultpage
defined('PLUGIN_TOPICPATH_TOP_LABEL') or define('PLUGIN_TOPICPATH_TOP_LABEL', 'Top');
// Separetor / of / topic / path
defined('PLUGIN_TOPICPATH_TOP_SEPARATOR') or define('PLUGIN_TOPICPATH_TOP_SEPARATOR', ' &gt; ');
// Show the page itself or not
defined('PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY') or define('PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY', 1);
// If PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY, add a link to itself
defined('PLUGIN_TOPICPATH_THIS_PAGE_LINK') or define('PLUGIN_TOPICPATH_THIS_PAGE_LINK', 0);

function plugin_topicpath_convert()
{
	global $topicpath;
	if (isset($topicpath) && $topicpath == false) return '';
	return '<div id ="topicpath">' . plugin_topicpath_inline() . '</div>';
}

function plugin_topicpath_inline()
{
	global $vars, $defaultpage, $topicpath;

	if (isset($topicpath) && $topicpath == false) return '';

	$page = isset($vars['page']) ? $vars['page'] : '';
	if ($page == '' || $page == $defaultpage) return '';

	$parts = explode('/', $page);

	$b_link = TRUE;
	if (PLUGIN_TOPICPATH_THIS_PAGE_DISPLAY) {
		$b_link = PLUGIN_TOPICPATH_THIS_PAGE_LINK;
	} else {
		array_pop($parts); // Remove the page itself
	}

	$topic_path = array();
	while (! empty($parts)) {
		$_landing = join('/', $parts);
		$element = htmlspecialchars(array_pop($parts));
		if (! $b_link)  {
			// This page ($_landing == $page)
			$b_link = TRUE;
			$topic_path[] = $element;
		// } else if (PKWK_READONLY && ! is_page($_landing)) {
		} else if (auth::check_role('readonly') && ! is_page($_landing)) {
			// Page not exists
			$topic_path[] = $element;
		} else {
			// Page exists or not exists
			$topic_path[] = '<a href="' . get_page_uri($_landing) . '">' .
				$element . '</a>';
		}
	}

	if (PLUGIN_TOPICPATH_TOP_DISPLAY)
		$topic_path[] = make_pagelink($defaultpage, PLUGIN_TOPICPATH_TOP_LABEL);

	return join(PLUGIN_TOPICPATH_TOP_SEPARATOR, array_reverse($topic_path));
}
?>
