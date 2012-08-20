<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: headarea.inc.php,v 1.6.6 2004/12/15 14:37:37 miko Exp $
//

// サブメニューを使用する
define('HEAD_ENABLE_SUBHEAD', TRUE);

// サブメニューの名称
define('HEAD_SUBHEADBAR', 'Header');

function plugin_headarea_convert()
{
	global $vars, $headarea, $use_open_uri_in_new_window;
	static $head = NULL;
	static $headhtml = NULL;

//miko patched
	// Cached MenuHTML
	if ($headhtml !== NULL)
		return preg_replace('/<ul class="list[^>]*>/','<ul class="head">', $headhtml);
//miko patched

	if (func_num_args()) {
		$args = func_get_args();
		if (is_page($args[0])) $head = $args[0];
		return '';
	}

	$page = ($head === NULL) ? $headarea : $head;

	if (HEAD_ENABLE_SUBHEAD) {
		$path = explode('/', strip_bracket($vars['page']));
		while(count($path)) {
			$_page = join('/', $path) . '/' . HEAD_SUBHEADBAR;
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
//		return '<!-- #headarea(): You already view ' . htmlspecialchars($page) . ' -->';
	}

	$headtext = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m','$1$2',get_source($page));
	if (function_exists('convert_filter')) {
		$headtext = convert_filter($headtext);
	}
	$save_newwindow = $use_open_uri_in_new_window;
	$use_open_uri_in_new_window = 0;
	$headhtml = convert_html($headtext);
	$use_open_uri_in_new_window = $save_newwindow;
        $headhtml = str_replace("\n",'',$headhtml);
	return preg_replace('/<ul class="list[^>]*>/','<ul class="head">',$headhtml);
}
?>
