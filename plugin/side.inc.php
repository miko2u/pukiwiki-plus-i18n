<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: side.inc.php,v 1.6.2 2004/09/04 05:42:37 miko Exp $
//

// サブメニューを使用する
define('SIDE_ENABLE_SUBMENU', TRUE);

// サブメニューの名称
define('SIDE_SUBMENUBAR', 'SideBar');

function plugin_side_convert()
{
	global $vars, $sidebar;
	static $side = NULL;

	if (func_num_args()) {
		$args = func_get_args();
		if (is_page($args[0])) $side = $args[0];
		return '';
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
	return preg_replace('/<ul class="list[^>]*>/','<ul class="menu">',$sidehtml);
}
?>
