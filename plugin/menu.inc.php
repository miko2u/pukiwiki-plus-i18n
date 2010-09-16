<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: menu.inc.php,v 1.8.6 2004/12/15 14:23:02 miko Exp $
//

// サブメニューを使用する
define('MENU_ENABLE_SUBMENU', TRUE);

// サブメニューの名称
define('MENU_SUBMENUBAR', 'MenuBar');

function plugin_menu_convert()
{
	global $vars, $menubar;
	static $menu = NULL;
	static $menuhtml = NULL;

//miko patched
	// Cached MenuHTML
	if ($menuhtml !== NULL)
		return preg_replace('/<ul class="list[^>]*>/','<ul class="menu">', $menuhtml);
//miko patched

	$num = func_num_args();
	if ($num > 0) {
		// Try to change default 'MenuBar' page name (only)
		if ($num > 1)       return '#menu(): Zero or One argument needed';
		if ($menu !== NULL) return '#menu(): Already set: ' . htmlspecialchars($menu);
		$args = func_get_args();
		if (! is_page($args[0])) {
			return '#menu(): No such page: ' . htmlspecialchars($args[0]);
		} else {
			$menu = $args[0]; // Set
			return '';
		}

	} else {
		// Output menubar page data
		$page = ($menu === NULL) ? $menubar : $menu;

		if (MENU_ENABLE_SUBMENU) {
			$path = explode('/', strip_bracket($vars['page']));
			while(! empty($path)) {
				$_page = join('/', $path) . '/' . MENU_SUBMENUBAR;
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
			return '<!-- #menu(): You already view ' . htmlspecialchars($page) . ' -->';
		} else {
			// Cut fixed anchors
			$menutext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m', '$1$2', get_source($page));
//miko patched
			if (function_exists('convert_filter')) {
				$menutext = convert_filter($menutext);
			}
			global $top, $use_open_uri_in_new_window;
			$tmptop = $top;
			$top = '';
			$save_newwindow = $use_open_uri_in_new_window;
			$use_open_uri_in_new_window = 0;
			$menuhtml = convert_html($menutext);
			$top = $tmptop;
			$use_open_uri_in_new_window = $save_newwindow;
			$menuhtml = str_replace("\n",'',$menuhtml);
			return preg_replace('/<ul class="list[^>]*>/','<ul class="menu">',$menuhtml);
//miko patched
		}
	}
}
?>
