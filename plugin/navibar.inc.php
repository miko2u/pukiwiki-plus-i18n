<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: navibar.php,v 0.1.4 2005/03/15 15:33:43 miko Exp $
//
function plugin_navibar_convert()
{
	global $_LINK;
	global $do_backup, $trackback, $referer;
	global $function_freeze;
	global $vars;

	if ($_LINK['reload'] == '') {
		return '#navibar: plugin called from wikipage. skipped.';
	}

	$is_read = (arg_check('read') && is_page($vars['page']));

	$num = func_num_args();
	$args = $num ? func_get_args() : array();
	$body = '';
	$line = '';

	while(!empty($args)) {
		$name = array_shift($args);
		switch ($name) {
		case 'freeze':
			if ($is_read && $function_freeze) {
				if (!$is_freeze)
					$name = 'freeze';
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case 'upload':
			if ($is_read && (bool)ini_get('file_uploads')) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case 'filelist':
			if (arg_check('list')) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case 'backup':
			if ($do_backup) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case 'trackback':
			if ($trackback) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$tbcount = tb_count($vars['page']);
				if ($tbcount > 0) {
					$body .= _navigator($name, 'Trackback(' . $tbcount . ')');
				} else if (!$is_read) {
					$body .= _navigator($name);
				} else {
					$body .= 'no Trackback';
				}
			}
			break;
		case 'refer':
			if ($referer) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case '|':
			if ( trim($body) != '' ) {
				$line .= '[ ' . $body . ' ]' . "\n\n";
				$body = '';
			}
			break;
		case 'new':
		case 'edit':
		case 'diff':
			if (!$is_read)
				break;
		default:
			if ($body != '' && $oldname != '|') { $body .= ' | '; }
			$body .= _navigator($name);
			break;
		}
		$oldname = $name;
		$body .= ' ';
	}

	if ( trim($body) != '' ) {
		$line .= '[ ' . $body . ' ]' . "\n\n";
		$body = '';
	}
	return '<div id="navigator">'. $line . '</div>';
}

function _navigator($key, $val = '')
{
	global $_LINK, $_LANG, $_IMAGE;

	$link = $_LINK;
	$lang = $_LANG['skin'];
	$image = isset($_IMAGE['skin']) ? $_IMAGE['skin'] : array();

	if (!isset($link[$key])) { return '<!--LINK NOT FOUND-->'; }
	if (!isset($lang[$key])) { return '<!--LANG NOT FOUND-->'; }

	if (!isset($image[$key])) {
		return '<a href="' . $link[$key] . '">' . (($val === '') ? $lang[$key] : $val) . '</a>';
	}
	return '<a href="' . $link[$key] . '"><img src="' . IMAGE_DIR . $image[$key] . '" style="vertical-align:middle;">' . (($val === '') ? $lang[$key] : $val) . '</a>';
}
?>
