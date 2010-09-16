<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: lastmod.php,v 0.1.2 2006/02/16 01:31:00 upk Exp $
//

function plugin_lastmod_inline()
{
	global $vars;
	global $WikiName, $BracketName;

	$args = func_get_args();
	if ($args[0]){
		if (preg_match("/^($WikiName|\[\[$BracketName\]\])$/",$args[0]))
		{
			$_page = get_fullname(strip_bracket($args[0]),$vars["page"]);
		} else {
			return FALSE;
		}
	} else {
		$_page = $vars["page"];
	}
	if (!is_page($_page))
		return FALSE;
	return format_date(get_filetime($_page));
}
?>
