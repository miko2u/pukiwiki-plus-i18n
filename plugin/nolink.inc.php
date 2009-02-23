<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: nolink.inc.php,v 1.0.1 2009/02/16 23:33:00 upk Exp $
//

function plugin_nolink_convert()
{
	$argv = func_get_args();
	$argc = func_num_args();

	if ($argc < 1) return '';
	$data = $argv[ --$argc ];
	return strip_a(convert_html(line2array($data)));
}
?>
