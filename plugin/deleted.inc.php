<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: deleted.inc.php,v 1.6.1 2005/03/09 04:22:01 miko Exp $
//
// Show deleted (= Exists in BACKUP_DIR or DIFF_DIR but not in DATA_DIR)
// page list to clean them up
//
// Usage:
//   index.php?plugin=deleted[&file=on]
//   index.php?plugin=deleted&dir=diff[&file=on]

function plugin_deleted_action()
{
	global $vars;
	$_deleted_plugin_title = _('The list of deleted pages');
	$_deleted_plugin_title_withfilename = _('The list of deleted pages (with filename)');

	$dir = isset($vars['dir']) ? $vars['dir'] : 'backup';
	$withfilename  = isset($vars['file']);

	$_DIR['diff'  ]['dir'] = DIFF_DIR;
	$_DIR['diff'  ]['ext'] = '.txt';
	$_DIR['backup']['dir'] = BACKUP_DIR;
	$_DIR['backup']['ext'] = BACKUP_EXT; // .gz or .txt
	//$_DIR['cache' ]['dir'] = CACHE_DIR; // No way to delete them via web browser now
	//$_DIR['cache' ]['ext'] = '.ref';
	//$_DIR['cache' ]['ext'] = '.rel';

	if (! isset($_DIR[$dir]))
		return array('msg'=>'Deleted plugin', 'body'=>'No such setting: Choose backup or diff');

	$deleted_pages  = array_diff(
		get_existpages($_DIR[$dir]['dir'], $_DIR[$dir]['ext']),
		get_existpages());

	if ($withfilename) {
		$retval['msg'] = $_deleted_plugin_title_withfilename;
	} else {
		$retval['msg'] = $_deleted_plugin_title;
	}
	$retval['body'] = page_list($deleted_pages, $dir, $withfilename);

	return $retval;
}
?>
