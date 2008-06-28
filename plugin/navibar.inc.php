<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: navibar.php,v 0.1.12 2008/06/25 01:50:00 upk Exp $
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

        $_page  = isset($vars['page']) ? $vars['page'] : '';
        $is_read = (arg_check('read') && is_page($_page));
        $is_freeze = is_freeze($_page);

	$num = func_num_args();
	$args = $num ? func_get_args() : array();
	$body = '';
	$line = '';

	while(!empty($args)) {
		$name = array_shift($args);
		switch ($name) {
		case 'freeze':
			if ($is_read && $function_freeze) {
				if (!$is_freeze) {
					$name = 'freeze';
					if ($body != '' && $oldname != '|') { $body .= ' | '; }
					$body .= _navigator($name);
				}
			}
			break;
		case 'unfreeze':
			if ($is_read && $function_freeze) {
				if ($is_freeze) {
					$name = 'unfreeze';
					if ($body != '' && $oldname != '|') { $body .= ' | '; }
					$body .= _navigator($name);
				}
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
		case 'template':
		case 'source':
			if (!empty($_page)) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case 'trackback':
			if ($trackback) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$tbcount = tb_count($_page);
				if ($tbcount > 0) {
					$body .= _navigator($name, 'Trackback(' . $tbcount . ')');
				} else if ($is_read) {
					$body .= 'no Trackback';
				} else if (isset($vars['cmd']) && $vars['cmd'] == 'list') {
					$body .= _navigator($name, 'Trackback list');
				}
			}
			break;
		case 'refer':
		case 'skeylist':
		case 'linklist':
			if ($referer) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;

		case 'log_login':
			if (log_exist('login',$vars['page'])) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case 'log_check':
			if (log_exist('check',$vars['page'])) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case 'log_browse':
			if ($body != '' && $oldname != '|') { $body .= ' | '; }
			$body .= _navigator($name);
//			if (log_exist('browse',$vars['page'])) {
//				return _navigator($name);
//			}
			break;
		case 'log_update':
			if (log_exist('update',$vars['page'])) {
				if ($body != '' && $oldname != '|') { $body .= ' | '; }
				$body .= _navigator($name);
			}
			break;
		case 'log_down':
			if (log_exist('download',$vars['page'])) {
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
		// case 'new':
		case 'newsub':
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

	$text = ($val === '') ? $lang[$key] : $val;
	if (!isset($image[$key])) {
		return '<a href="' . $link[$key] . '">' . $text . '</a>';
	}
	return '<a href="' . $link[$key] . '"><img src="' . IMAGE_URI . $image[$key] . '" style="vertical-align:middle;" alt="' . $text . '"/>' . $text . '</a>';
}
?>
