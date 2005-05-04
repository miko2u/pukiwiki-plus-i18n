<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: navibar2.inc.php,v 0.1.4 2005/03/15 15:33:43 miko Exp $
//
function plugin_navibar2_convert()
{
	global $hr;

	$menubarcount = -1;
	$page = 'Navigation';

	if (!is_page($page)) {
		exist_plugin('navibar');
		return do_plugin_convert('navibar','top,list,search,recent,help,|,new,edit,upload,|,trackback') . $hr;
	}

	foreach (get_source($page) as $line) {
		if ($line == '') continue;

		$head  = $line{0};	// The first letter
		$level = strspn($line, $head);

		if ($head == '-') {
			if ($level == 1) {
				$line = substr($line,1);
				if (preg_match('/\[(' . '(?:(?:https?|ftp|news):\/\/|\.\.?\/)' .
					'[!~*\'();\/?:\@&=+\$,%#\w.-]*)\s([^\]]+)\]\s?([^\s]*)/', $line, $intermatch)) {
					$interurl = $intermatch[1];
					$intername = $intermatch[2];
					$interparam = $intermatch[3];
					if ($interurl !== FALSE && is_url($interurl)) {
						$menubarcount++;
						$menubar[$menubarcount] = ' <td class="navimenu" id="navimenutd' . $menubarcount . '">' .
						                          '<a href="' . $interurl . '" class="navimenu" id="NaviMenuLink' . $menubarcount . '">' . $intername . '</a></td>';
					}
				} else {
					$name = trim($line);
					$interkey = plugin_navibar2_keyword($name);
					if (isset($interkey['url'])) {
						$menubarcount++;
						$menubar[$menubarcount] = ' <td class="navimenu" id="navimenutd' . $menubarcount . '">' .
						                          '<a href="' . $interkey['url'] . '" class="navimenu" id="NaviMenuLink' . $menubarcount . '">' . $interkey['text'] . '</a></td>';
					}
				}
			} else if ($level == 2) {
				$line = substr($line,2);
				if (preg_match('/\[(' . '(?:(?:https?|ftp|news):\/\/|\.\.?\/)' .
					'[!~*\'();\/?:\@&=+\$,%#\w.-]*)\s([^\]]+)\]\s?([^\s]*)/', $line, $intermatch)) {
					$interurl = $intermatch[1];
					$intername = $intermatch[2];
					$interparam = $intermatch[3];
					if ($interurl !== FALSE && is_url($interurl)) {
						$menublk[$menubarcount][] = ' <div class="MenuItem"><a href="' . $interurl . '" class="MenuItem">' . $intername . '</a></div>';
					}
				} else {
					$interkey = plugin_navibar2_keyword(trim($line));
					if (isset($interkey['url'])) {
						$menublk[$menubarcount][] = ' <div class="MenuItem"><a href="' . $interkey['url'] . '" class="MenuItem">' . $interkey['text'] . '</a></div>';
					}
				}
			}
		}
	}
	for ($i=0;$i<=$menubarcount;$i++) {
		$menublkstr = join("\n",$menublk[$i]);
		if ($menublkstr != '') {
			$naviblk[$i] = <<<EOD
<div class="naviblock" id="naviblock{$i}">
 {$menublkstr}
</div>
EOD;
		} else {
			$naviblk[$i] = '';
		}
	}
	$menubarstr = join("\n",$menubar);
	$menublkstr = join("\n",$naviblk);

	return <<<EOD
<div id="navigator2"><table border="0" cellspacing="0" cellpadding="0"><tbody><tr>
{$menubarstr}
</tr></tbody></table></div>
{$menublkstr}
<script type="text/javascript" src="skin/navibar.js"></script>
<script type="text/javascript">
<!-- <![CDATA[
startNaviMenu( "navigator2", "navimenutd", "navimenu", "NaviMenuLink", "naviblock", "MenuItem");
//]]>-->
</script>
EOD;
}

function plugin_navibar2_keyword($name)
{
	global $_LINK;
	global $do_backup, $trackback, $referer;
	global $function_freeze;
	global $vars;

	if ($_LINK['reload'] == '') {
		return array();
	}
	$is_read = (arg_check('read') && is_page($vars['page']));

	switch ($name) {
	case 'freeze':
		if ($is_read && $function_freeze) {
			if (!$is_freeze)
				$name = 'freeze';
			return _navigator2($name);
		}
		break;
	case 'upload':
		if ($is_read && (bool)ini_get('file_uploads')) {
			return _navigator2($name);
		}
		break;
	case 'filelist':
		if (arg_check('list')) {
			return _navigator2($name);
		}
		break;
	case 'backup':
		if ($do_backup) {
			return _navigator2($name);
		}
		break;
	case 'trackback':
		if ($trackback) {
			$tbcount = tb_count($vars['page']);
			if ($tbcount > 0) {
				return _navigator2($name, 'Trackback(' . $tbcount . ')');
			} else if ($is_read) {
				return array('text' => 'no Trackback');
			} else if ($vars['cmd'] == 'list') {
				return _navigator2($name, 'Trackback list');
			}
		}
		break;
	case 'refer':
		if ($referer) {
			return _navigator2($name);
		}
		break;
	case 'new':
	case 'edit':
	case 'diff':
		if (!$is_read)
			break;
	default:
		return _navigator2($name);
	}
	return array();
}

function _navigator2($key, $val = '')
{
	global $_LINK, $_LANG, $_IMAGE;

	$link = $_LINK;
	$lang = $_LANG['skin'];
	$image = isset($_IMAGE['skin']) ? $_IMAGE['skin'] : array();

	if (!isset($link[$key])) { return array('text'=>'<!--LINK NOT FOUND-->'); }
	if (!isset($lang[$key])) { return array('text'=>'<!--LANG NOT FOUND-->'); }

	return array(
		'url' => $link[$key],
		'img' => '<img src="' . IMAGE_DIR . $image[$key] . '" style="vertical-align:middle;" />',
		'text' => (($val === '') ? $lang[$key] : $val),
	);
}
?>