<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: add.inc.php,v 1.6.2 2005/03/09 02:30:27 miko Exp $
//
// Add plugin - Append new text below/above existing page
// Usage: cmd=add&page=pagename

function plugin_add_action()
{
	global $get, $post, $vars;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	$page = isset($vars['page']) ? $vars['page'] : '';
	check_editable($page);

	$get['add'] = $post['add'] = $vars['add'] = TRUE;
	return array(
		'msg'  => _("Add to $1"),
		'body' => '<ul>' . "\n" .
		          ' <li>' . _('Two and the contents of an input are added for a new-line to the contents of a page of present addition.') . '</li>' . "\n" .
		          '</ul>' . "\n" . edit_form($page, '')
	);
}
?>
