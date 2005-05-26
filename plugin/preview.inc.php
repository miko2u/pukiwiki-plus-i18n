<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: preview.inc.php,v 1.8 2005/05/26 13:57:07 miko Exp $
//
// Read plugin: Show a page and InterWiki

function plugin_preview_action()
{
	global $vars;

	$page = isset($vars['page']) ? $vars['page'] : '';

	if (is_page($page)) {
		check_readable($page, true, true);
		$source = get_source($page);
		array_splice($source, 10);
		$body = convert_html($source);

		pkwk_common_headers();
		header('Content-type: text/xml');
		print '<' . '?xml version="1.0" encoding="UTF-8"?' . ">\n";
		print $body;
	}
	exit;
}
?>
