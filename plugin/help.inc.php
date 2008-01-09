<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: help.inc.php,v 0.4 2008/01/05 18:17:00 upk Exp $
//
function plugin_help_action()
{
	global $help_page;
	header('Location: '. get_page_location_uri($help_page));
	die();
}
?>
