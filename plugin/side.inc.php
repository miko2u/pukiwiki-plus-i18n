<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: side.inc.php,v 1.6.6 2004/12/15 14:37:37 miko Exp $
//

// サブメニューを使用する
define('SIDE_ENABLE_SUBMENU', TRUE);

// サブメニューの名称
define('SIDE_SUBMENUBAR', 'SideBar');

function plugin_side_convert()
{
	global $vars, $sidebar;
	static $side = NULL;
	static $sidehtml = NULL;

//miko patched
	// Cached MenuHTML
	if ($sidehtml !== NULL)
		return preg_replace('/<ul class="list[^>]*>/','<ul class="menu">', $sidehtml);
//miko patched

	$num = func_num_args();
	if ($num > 0) {
		// Try to change default 'sideBar' page name (only)
		if ($num > 1)       return '#side(): Zero or One argument needed';
		if ($side !== NULL) return '#side(): Already set: ' . htmlspecialchars($side);
		$args = func_get_args();
		if (! is_page($args[0])) {
			return '#side(): No such page: ' . htmlspecialchars($args[0]);
		} else {
			$side = $args[0]; // Set
			return '';
		}

	}

	$page = ($side === NULL) ? $sidebar : $side;

	if (SIDE_ENABLE_SUBMENU) {
		$path = explode('/', strip_bracket($vars['page']));
		while(count($path)) {
			$_page = join('/', $path) . '/' . SIDE_SUBMENUBAR;
			if (is_page($_page)) {
				$page = $_page;
				break;
			}
			array_pop($path);
		}
	}

	if (! is_page($page)) {
		return '';
	} else if ($vars['page'] == $page) {
		return '<!-- #side(): You already view ' .
			htmlspecialchars($page) . ' -->';
	}

	$sidetext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m','$1$2',get_source($page));
	if (function_exists('convert_filter')) {
		$sidetext = convert_filter($sidetext);
	}
	global $top;
	$tmptop = $top;
	$top = '';
	$sidehtml = convert_html($sidetext);
	$top = $tmptop;
	$sidehtml = str_replace("\n",'',$sidehtml);
	return preg_replace('/<ul class="list[^>]*>/','<ul class="menu">',$sidehtml);
}
?>
