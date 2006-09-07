<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: calendar_edit.inc.php,v 1.10.1 2006/09/07 05:07:51 miko Exp $
// Copyright (C)
//   2005-2006 PukiWiki Plus! Team
//   2003-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// calendar_edit plugin (required calendar plugin)

function plugin_calendar_edit_convert()
{
	global $command;

	if (!exist_plugin_convert('calendar')) {
		return FALSE;
	}

	$command = 'edit';
	$args = func_num_args() ? func_get_args() : array();
	return call_user_func_array('plugin_calendar_convert', $args);
}
?>