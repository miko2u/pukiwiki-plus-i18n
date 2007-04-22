<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: nonlist.inc.php,v 0.0.1 2007/04/23 02:21:00 upk Exp $
//
function plugin_nonlist_action()
{
	global $vars;
	$_title_nonlist = _('List of non_list pages');

	if (auth::check_role('role_adm_contents')) return '';

	if (isset($vars['env'])) {
		$cmd = 2;
	} elseif (isset($vars['col'])) {
		$cmd = 1;
	} else {
		$cmd = 0;
	}

	return array(
		'msg'=> $_title_nonlist,
		'body'=>plugin_nonlist_getlist($cmd));
}

function plugin_nonlist_getlist($cmd=0)
{
	global $non_list, $whatsnew;

	if ($cmd == 0) {
        	$pages = array_diff(auth::get_existpages(),array($whatsnew));
		$pages = preg_grep('/' . $non_list . '/S', $pages);
		if (empty($pages)) return '';
		return page_list($pages,'read',false);
	}

	$pages = array_diff(auth::get_existpages(),array($whatsnew));
	$tmp = array();
	foreach($pages as $file=>$page) {
		if (substr($page,0,1) !== ':') continue;
		if ($cmd == 2 && substr($page,0,8) !== ':config/') continue;
		$tmp[$file] = $page;
	}

	if (empty($tmp)) return '';
	return page_list($tmp,'read',false);
}
?>
