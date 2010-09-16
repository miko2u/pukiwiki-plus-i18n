<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: footarea.inc.php,v 1.6.6 2004/12/15 14:37:37 miko Exp $
//

// サブメニューを使用する
define('FOOT_ENABLE_SUBFOOT', TRUE);

// サブメニューの名称
define('FOOT_SUBFOOTBAR', 'Footer');

function plugin_footarea_convert()
{
	global $vars, $footarea, $use_open_uri_in_new_window;
	static $foot = NULL;
	static $foothtml = NULL;

//miko patched
	// Cached MenuHTML
	if ($foothtml !== NULL)
		return preg_replace('/<ul class="list[^>]*>/','<ul class="foot">', $foothtml);
//miko patched

	if (func_num_args()) {
		$args = func_get_args();
		if (is_page($args[0])) $foot = $args[0];
		return '';
	}

	$page = ($foot === NULL) ? $footarea : $foot;

	if (FOOT_ENABLE_SUBFOOT) {
		$path = explode('/', strip_bracket($vars['page']));
		while(count($path)) {
			$_page = join('/', $path) . '/' . FOOT_SUBFOOTBAR;
			if (is_page($_page)) {
				$page = $_page;
				break;
			}
			array_pop($path);
		}
	}

	if (! is_page($page)) {
		return '';
//	} else if ($vars['page'] == $page) {
//		return '<!-- #footarea(): You already view ' . htmlspecialchars($page) . ' -->';
	}

	$foottext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m','$1$2',get_source($page));
	if (function_exists('convert_filter')) {
		$foottext = convert_filter($foottext);
	}
	$save_newwindow = $use_open_uri_in_new_window;
	$use_open_uri_in_new_window = 0;
	$foothtml = convert_html($foottext);
	$use_open_uri_in_new_window = $save_newwindow;
	$foothtml = str_replace("\n",'',$foothtml);
	return preg_replace('/<ul class="list[^>]*>/','<ul class="foot">',$foothtml);
}
?>
