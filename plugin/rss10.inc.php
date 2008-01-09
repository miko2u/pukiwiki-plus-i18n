<?php
// RSS 1.0 plugin - had been merged into rss plugin
// $Id: rss10.inc.php,v 1.17.1 2008/01/06 05:11:00 upk Exp $

function plugin_rss10_action()
{
	pkwk_headers_sent();
	header('Status: 301 Moved Permanently');
	header('Location: ' . get_location_uri('rss','','ver=1.0')); // HTTP
	exit;
}
?>
