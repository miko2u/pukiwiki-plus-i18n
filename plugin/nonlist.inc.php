<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: nonlist.inc.php,v 0.0.1 2007/04/23 02:21:00 upk Exp $
//
function plugin_nonlist_action()
{
	global $vars;
	$_title_nonlist = _('List of non_list pages');

	if (auth::check_role('role_adm_contents')) return '';
	$env = (isset($vars['env'])) ? 1 : 0;
	$col = (isset($vars['col'])) ? 1 : 0;

	return array(
		'msg'=> $_title_nonlist,
		'body'=>plugin_nonlist_getlist($col,$env));
}

function plugin_nonlist_getlist($col=0,$env=0)
{
	global $non_list, $whatsnew;

        $pages = array_diff(auth::get_existpages(),array($whatsnew));
	$pages = preg_grep('/' . $non_list . '/S', $pages);

	if (empty($pages)) return '';
	if (!$col && !$env) {
		return page_list($pages,'read',false);
	}

	$tmp = array();
	foreach($pages as $file=>$page) {
		if ($col && substr($page,0,1) !== ':') continue;
		if ($env && substr($page,0,8) !== ':config/') continue;
		$tmp[$file] = $page;
	}

	if (empty($tmp)) return '';
	return page_list($tmp,'read',false);
}
?>
