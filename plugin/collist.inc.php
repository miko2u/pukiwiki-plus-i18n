<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: collist.inc.php,v 0.0.1 2007/04/22 17:22:00 upk Exp $
//
function plugin_collist_action()
{
	global $vars;
	$_title_collist = _('List of Colon pages');
	$_title_collist_env = _('List of Config pages');

	if (auth::check_role('role_adm_contents')) return '';
	$config = (isset($vars['env'])) ? 1 : 0;

	return array(
		'msg'=> ($config) ? $_title_collist_env : $_title_collist,
		'body'=>plugin_collist_getlist($config));
}

function plugin_collist_getlist($config=0)
{
	// ページ名の取得
	$tmp = get_existpages(DATA_DIR, '.txt');
	// ユーザ名取得
	$uname = auth::check_auth();

	$pages = array();
	foreach($tmp as $file=>$page) {
		if (! auth::is_page_readable($uname, $page)) continue;
		if (substr($page,0,1) !== ':') continue;
		if ($config && substr($page,0,8) !== ':config/') continue;
		$pages[$file] = $page;
	}

	if (empty($pages)) return '';
	return page_list($pages,'read',false);
}
?>
