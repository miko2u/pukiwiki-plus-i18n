<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: version.inc.php,v 1.8.1 2006/01/11 23:59:00 upk Exp $
//
// Show PukiWiki version

function plugin_version_convert()
{
	// if (PKWK_SAFE_MODE) return ''; // Show nothing
	if (auth::check_role('safemode')) return ''; // Show nothing

	return '<p>' . S_VERSION . '</p>';
}

function plugin_version_inline()
{
	// if (PKWK_SAFE_MODE) return ''; // Show nothing
	if (auth::check_role('safemode')) return ''; // Show nothing

	return S_VERSION;
}
?>
