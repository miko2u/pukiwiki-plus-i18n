<?php
// $Id: server.inc.php,v 1.6.1 2006/01/11 23:57:00 upk Exp $
//
// Server information plugin
// by Reimy http://pukiwiki.reimy.com/

function plugin_server_convert()
{

	// if (PKWK_SAFE_MODE) return ''; // Show nothing
	if (auth::check_role('safemode')) return ''; // Show nothing

	return '<dl>' . "\n" .
		'<dt>Server Name</dt>'     . '<dd>' . SERVER_NAME . '</dd>' . "\n" .
		'<dt>Server Software</dt>' . '<dd>' . SERVER_SOFTWARE . '</dd>' . "\n" .
		'<dt>Server Admin</dt>'    . '<dd>' .
			'<a href="mailto:' . SERVER_ADMIN . '">' .
			SERVER_ADMIN . '</a></dd>' . "\n" .
		'</dl>' . "\n";
}
?>
