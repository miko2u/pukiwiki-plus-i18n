<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: interwiki.inc.php,v 1.10.1 2005/03/09 14:48:32 miko Exp $
//
// InterWiki redirection plugin (OBSOLETE)

function plugin_interwiki_action()
{
	global $vars, $InterWikiName;

	if (PKWK_SAFE_MODE) die_message('InterWiki plugin is not allowed');

	$match = array();
	if (! preg_match("/^$InterWikiName$/", $vars['page'], $match))
		return plugin_interwiki_invalid();

	$url = get_interwiki_url($match[2], $match[3]);
	if ($url === FALSE) return plugin_interwiki_invalid();

	pkwk_headers_sent();
	header('Location: ' . $url);
	exit;
}

function plugin_interwiki_invalid()
{
	return array(
		'msg'  => _('This is not a valid InterWikiName'),
		'body' => str_replace(array('$1', '$2'),
			array(htmlspecialchars(''),
			make_pagelink('InterWikiName')),
			_(' $1 is not a valid $2.')));
}
?>
