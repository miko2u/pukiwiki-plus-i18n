<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: navibar2.inc.php,v 0.1.6 2005/05/23 20:42:00 upk Exp $
//
function plugin_navibar2_convert()
{
	global $vars, $hr;

	$page = strip_bracket($vars['page']) . '/Navigation';
	if (is_page($page)) {
		return plugin_navibar2_makehtml($page);
	}

	$page = 'Navigation';
	if (is_page($page)) {
		return plugin_navibar2_makehtml($page);
	}

	exist_plugin('navibar');
	return do_plugin_convert('navibar','top,list,search,recent,help,|,new,edit,upload,|,trackback') . $hr;
}

function plugin_navibar2_makehtml($page)
{
	$menubarcount = -1;

	$lines = get_source($page);
	convert_html( $lines ); // Processing for prior execution of plug-in.

	foreach ($lines as $line) {
		if ($line == '') continue;

		$head  = $line{0};	// The first letter
		$level = strspn($line, $head);

		if ($head == '-') {
			if ($level == 1) {
				$line = substr($line,1);
				list($rc,$interurl,$intername,$conv) = plugin_navibar2_convert_html($line);
				if ($rc) {
					$menubarcount++;
					$rep = '<a href="' . $interurl;
					$rep .= '" class="navimenu" id="NaviMenuLink' . $menubarcount . '">' . $intername;
					$rep .= '</a>';
					$menubar[$menubarcount] = ' <td class="navimenu" id="navimenutd' . $menubarcount . '">' .
								  str_replace('__navibar2__', $rep, $conv) .
								  '</td>';
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
				list($rc,$interurl,$intername,$conv) = plugin_navibar2_convert_html($line);
				if ($rc) {
					$rep = '<a href="' . $interurl;
					$rep .= '" class="MenuItem">' . $intername;
					$rep .= '</a>';
					$menublk[$menubarcount][] = ' <div class="MenuItem">' . str_replace('__navibar2__', $rep, $conv) . '</div>';
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

function plugin_navibar2_convert_html($str)
{
	$conv = preg_replace(
		array("'<p>'si","'</p>'si"),
		array('',''),
		convert_html( array($str) )
	);

	// $regs[0] - HIT Strings
	// $regs[1] - URL String
	// $regs[2] - LinkName
	if ( preg_match("'<a href=\"(.*?)\"[^>]*>(.*?)</a>'si", $conv, $regs) ) {
		return array( TRUE, $regs[1], $regs[2], str_replace($regs[0], '__navibar2__', $conv) );
	}

	if ( preg_match("'<a class=\"ext\" href=\"(.*?)\" .*?>(.*?)<img src=\"./image/plus/ext.png\".*?</a>'si", $conv, $regs) ) {
		return array( TRUE, $regs[1], $regs[2], str_replace($regs[0], '__navibar2__', $conv) );
	}

	// rc, $interurl, $intername, $conv
	return array( FALSE, '', '', $conv );
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
	$_page  = isset($vars['page']) ? $vars['page'] : '';
	$is_read = (arg_check('read') && is_page($_page));
	$is_freeze = is_freeze($_page);

	switch ($name) {
	case 'freeze':
		if ($is_read && $function_freeze) {
			if (!$is_freeze) {
				$name = 'freeze';
				return _navigator2($name);
			}
		}
		break;
	case 'unfreeze':
		if ($is_read && $function_freeze) {
			if ($is_freeze) {
				$name = 'unfreeze';
				return _navigator2($name);
			}
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
	case 'template':
	case 'source':
		if (!empty($_page)) {
			return _navigator2($name);
		}
		break;
	case 'trackback':
		if ($trackback) {
			$tbcount = tb_count($_page);
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
