<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: navititle.inc.php,v 0.1.1 2004/10/19 14:42:29 miko Exp $
//

function plugin_navititle_inline()
{
	global $newtitle;
	global $page;

        $is_read = (arg_check('read') && is_page($vars['page']));

	return '<h1 class="title">' . (($newtitle!='' && $is_read)?$newtitle:$page) . '</h1>';
}
?>
