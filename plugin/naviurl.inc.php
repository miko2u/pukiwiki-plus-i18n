<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: naviurl.inc.php,v 0.1.1 2004/10/19 14:42:29 miko Exp $
//

function plugin_naviurl_inline()
{
	global $_LINK;

	if ($_LINK['reload'] == '') {
		return "&naviurl: not found.\n";
	}
	return '<a href="'. $_LINK['reload'] .'"><span class="small">'. $_LINK['reload'] .'</span></a>';
}
?>
