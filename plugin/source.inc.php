<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: source.inc.php,v 1.14.3 2006/01/11 23:58:00 upk Exp $
//
// Source plugin

// Output source text of the page
function plugin_source_action()
{
	global $vars; //, $_source_messages;

	// if (PKWK_SAFE_MODE) die_message('PKWK_SAFE_MODE prohibits this');
	if (auth::check_role('safemode')) die_message('PKWK_SAFE_MODE prohibits this');

	$page = isset($vars['page']) ? $vars['page'] : '';
	$vars['refer'] = $page;

	if (! is_page($page) || ! check_readable($page, false, false))
		return array(
			'msg' => _(' $1 was not found.'),
			'body' => _('cannot display the page source.')
		);

	return array(
		'msg' => _('Source of  $1'),
		'body' => '<pre id="source">' . htmlspecialchars(join('', get_source($page))) . '</pre>'
	);
}
?>
