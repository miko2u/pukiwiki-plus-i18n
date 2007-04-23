<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: nonlist.inc.php,v 0.0.2 2007/04/23 22:11:00 upk Exp $
//

function plugin_nonlist_convert()
{
	if (auth::check_role('role_adm_contents')) return '';

	if (func_num_args() == 1) {
		list($cmd) = func_get_args();
		switch($cmd) {
		case 'env':
			$cmd = 2;
			break;
		case 'col':
			$cmd = 1;
			break;
		default:
			$cmd = 0;
		}
	} else {
		$cmd = 0;
	}

	return plugin_nonlist_getlist($cmd);
}

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
	// : のみ抜粋
	$pages = preg_grep('/^\:/S', $pages);
	if ($cmd == 2) {
		$pages = preg_grep('/^\:config\//S', $pages);
	}
	if (empty($pages)) return '';
	return page_list($pages,'read',false);
}
?>
