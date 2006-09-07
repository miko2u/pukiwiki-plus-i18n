<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: tracker_list.inc.php,v 1.2.1 2006/09/07 08:30:14 miko Exp $
//
// Issue tracker list plugin (a part of tracker plugin)

function plugin_tracker_list_init()
{
	if (exist_plugin('tracker') && function_exists('plugin_tracker_init')) {
		plugin_tracker_init();
	}
}
?>
