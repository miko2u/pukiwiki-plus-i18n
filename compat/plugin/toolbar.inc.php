<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: toolbar.php,v 0.2.14 2009/04/20 21:57:00 upk Exp $
// Copyright (C) 2005,2007-2009 PukiWiki Plus! Team
// License: GPL v2
//

function plugin_toolbar_convert()
{
	global $do_backup, $trackback, $referer;
	global $function_freeze;
	global $vars;

	// $is_read = (arg_check('read') && is_page($vars['page']));
	$is_read = is_page($vars['page']);
	$is_readonly = auth::check_role('readonly');
	$is_safemode = auth::check_role('safemode');
	$is_createpage = auth::is_check_role(PKWK_CREATE_PAGE);

	$num = func_num_args();
	$args = $num ? func_get_args() : array();
	$body = '';

	while(!empty($args)) {
		$name = array_shift($args);
		switch ($name) {
		case 'freeze':
			if ($is_readonly) break;
			if (!$is_read) break;
			if ($function_freeze) {
				if (!is_freeze($vars['page'])) {
					$name = 'freeze';
				} else {
					$name = 'unfreeze';
				}
				if ($body != '') { $body .= "\n"; }
				$body .= _toolbar($name);
			}
			break;
		case 'upload':
			if ($is_readonly) break;
			if (!$is_read) break;
			if ($function_freeze && is_freeze($vars['page'])) break;
			if ((bool)ini_get('file_uploads')) {
				if ($body != '') { $body .= "\n"; }
				$body .= _toolbar($name);
			}
			break;
		case 'filelist':
			if (arg_check('list')) {
				if ($body != '') { $body .= "\n"; }
				$body .= _toolbar($name);
			}
			break;
		case 'backup':
			if ($do_backup) {
				if ($body != '') { $body .= "\n"; }
				$body .= _toolbar($name);
			}
			break;
		case 'trackback':
			if ($trackback) {
				if ($body != '') { $body .= "\n"; }
				$tbcount = tb_count($vars['page']);
				if ($tbcount > 0) {
					$body .= _toolbar($name);
				} else if (!$is_read) {
					$body .= _toolbar($name);
				}
			}
			break;
		case 'refer':
			if ($referer) {
				if ($body != '') { $body .= "\n"; }
				$body .= _toolbar($name);
			}
			break;
		case 'rss':
		case 'mixirss':
			if ($body != '') { $body .= "\n"; }
			$body .= _toolbar($name);
			break;
		case '|':
			$body .= "\n&nbsp;\n";
			break;
		case 'diff':
			if (!$is_read) break;
			if ($is_safemode) break;
			if ($body != '') { $body .= "\n"; }
			$body .= _toolbar($name);
			break;
		case 'edit':
		case 'guiedit':
			if (!$is_read) break;
			if ($is_readonly) break;
			if ($function_freeze && is_freeze($vars['page'])) break;
			if ($body != '') { $body .= "\n"; }
			$body .= _toolbar($name);
			break;
		case 'new':
		case 'newsub':
			if ($is_createpage) break;
		case 'rename':
		case 'copy':
			if ($is_readonly) break;
		case 'reload':
		case 'print':
		case 'full':
			if (!$is_read) break;
		default:
			if ($body != '') { $body .= "\n"; }
			$body .= _toolbar($name);
			break;
		}
	}

	return '<div id="toolbar">'. $body . '</div>';
}

function _toolbar($key, $x = 20, $y = 20)
{
	global $_LANG, $_LINK, $_IMAGE;

$_IMAGE['skin']['logo']       = 'pukiwiki.png';
$_IMAGE['skin']['reload']     = 'reload.png';
$_IMAGE['skin']['new']        = 'new.png';
$_IMAGE['skin']['newsub']     = 'new_sub.png';
$_IMAGE['skin']['edit']       = 'edit.png';
$_IMAGE['skin']['guiedit']    = 'plus/guiedit.png';
$_IMAGE['skin']['freeze']     = 'freeze.png';
$_IMAGE['skin']['unfreeze']   = 'unfreeze.png';
$_IMAGE['skin']['diff']       = 'diff.png';
$_IMAGE['skin']['upload']     = 'file.png';
$_IMAGE['skin']['copy']       = 'copy.png';
$_IMAGE['skin']['rename']     = 'rename.png';
$_IMAGE['skin']['top']        = 'plus/home.png';
$_IMAGE['skin']['list']       = 'list.png';
$_IMAGE['skin']['search']     = 'search.png';
$_IMAGE['skin']['recent']     = 'recentchanges.png';
$_IMAGE['skin']['backup']     = 'backup.png';
$_IMAGE['skin']['refer']      = 'plus/referer.png';
$_IMAGE['skin']['help']       = 'help.png';
$_IMAGE['skin']['rss']        = 'feed.png';
$_IMAGE['skin']['rssplus']    = 'feed.png';
$_IMAGE['skin']['mixirss']    = 'feed.png';
$_IMAGE['skin']['skeylist']   = 'plus/skeylist.png';
$_IMAGE['skin']['linklist']   = 'plus/linklist.png';
$_IMAGE['skin']['brokenlink'] = 'plus/brokenlink.png';
$_IMAGE['skin']['log']        = 'log.png';
$_IMAGE['skin']['print']      = 'plus/print.png';
$_IMAGE['skin']['full']       = 'plus/full.png';

	$lang  = $_LANG['skin'];
	$link  = $_LINK;
	$image = $_IMAGE['skin'];

	if (!isset($lang[$key])) { return '<!--LANG NOT FOUND-->'; }
	if (!isset($link[$key])) { return '<!--LINK NOT FOUND-->'; }
	if (!isset($image[$key])) { return  '<!--IMAGE NOT FOUND-->'; }

	return '<a href="' . $link[$key] . '" rel="nofollow">' .
	       '<img src="' . IMAGE_URI . $image[$key] . '" width="' . $x . '" height="' . $y . '" ' .
	                'alt="' . $lang[$key] . '" title="' . $lang[$key] . '" />' .
	       '</a>';
}
?>
