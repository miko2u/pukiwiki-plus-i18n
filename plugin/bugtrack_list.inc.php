<?php
// $Id: bugtrack_list.inc.php,v 1.6.1 2006/09/07 02:51:07 miko Exp $
//
// PukiWiki BugTrack-list plugin - A part of BugTrack plugin
//
// Copyright
// 2002-2005 PukiWiki Developers Team
// 2002 Y.MASUI GPL2 http://masui.net/pukiwiki/ masui@masui.net

function plugin_bugtrack_list_init()
{
	if (exist_plugin('bugtrack') && function_exists('plugin_bugtrack_init')) {
		plugin_bugtrack_init();
	}
}
?>
