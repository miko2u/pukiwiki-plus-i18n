<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: help.inc.php,v 0.3 2004/08/09 15:09:20 miko Exp $
//
function plugin_help_action()
{
	global $script;
	global $help_page;

	$url = "$script?".rawurlencode($help_page);
	header("Location: $url");
	die();
}
?>
