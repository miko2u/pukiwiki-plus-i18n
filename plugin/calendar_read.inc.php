<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: calendar_read.inc.php,v 1.7 2004/07/31 03:09:20 henoheno Exp $
//

function plugin_calendar_read_convert()
{
	global $command;

	$command = 'read';

	if (!file_exists(PLUGIN_DIR.'calendar.inc.php'))
	{
		return FALSE;
	}
	require_once PLUGIN_DIR.'calendar.inc.php';
	if (!function_exists('plugin_calendar_convert'))
	{
		return FALSE;
	}

	$args = func_num_args() ? func_get_args() : array();

	bindtextdomain('calendar', LANG_DIR);
	bind_textdomain_codeset('calendar', SOURCE_ENCODING);
	textdomain('calendar');
	return call_user_func_array('plugin_calendar_convert',$args);
}
?>
