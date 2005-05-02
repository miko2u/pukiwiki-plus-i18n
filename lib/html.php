<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: html.php,v 1.31.15 2005/04/21 14:27:27 miko Exp $
//
// HTML-publishing related functions

// Show page-content
function catbody($title, $page, $body)
{
	global $script, $vars, $arg, $defaultpage, $whatsnew, $help_page, $hr;
	global $related_link, $cantedit, $function_freeze, $search_word_color;
	global $foot_explain, $note_hr, $head_tags;
	global $trackback, $trackback_javascript, $referer, $javascript;
	global $_LANG, $_LINK, $_IMAGE;

	global $pkwk_dtd;     // XHTML 1.1, XHTML1.0, HTML 4.01 Transitional...
	global $page_title;   // Title of this site
	global $do_backup;    // Do backup or not
	global $modifier;     // Site administrator's  web page
	global $modifierlink; // Site administrator's name

	global $skin_file, $menubar, $sidebar;
	global $_string;

	if (!defined('SKIN_FILE') || ! file_exists(SKIN_FILE) || ! is_readable(SKIN_FILE)) {
		if (! file_exists($skin_file) || ! is_readable($skin_file)) {
			die_message(SKIN_FILE.'(skin file) is not found.');
		} else {
			define(SKIN_FILE,$skin_file);
		}
	}

	$_LINK = $_IMAGE = array();

	// Add JavaScript header when ...
	if ($trackback && $trackback_javascript) $javascript = 1; // Set something If you want
	if (! PKWK_ALLOW_JAVASCRIPT) unset($javascript);

	$_page  = isset($vars['page']) ? $vars['page'] : '';
	$r_page = rawurlencode($_page);

	// Set $_LINK for skin
	$_LINK['add']      = "$script?cmd=add&amp;page=$r_page";
	$_LINK['backup']   = "$script?cmd=backup&amp;page=$r_page";
	$_LINK['copy']     = "$script?plugin=template&amp;refer=$r_page";
	$_LINK['diff']     = "$script?cmd=diff&amp;page=$r_page";
	$_LINK['edit']     = "$script?cmd=edit&amp;page=$r_page";
	$_LINK['filelist'] = "$script?cmd=filelist";
	$_LINK['freeze']   = "$script?cmd=freeze&amp;page=$r_page";
	$_LINK['help']     = "$script?cmd=help";
	$_LINK['list']     = "$script?cmd=list";
	$_LINK['menu']     = "$script?$menubar";
	$_LINK['new']      = "$script?plugin=newpage&amp;refer=$r_page";
	$_LINK['read']     = "$script?cmd=read&amp;page=$r_page";
	$_LINK['rdf']      = "$script?cmd=rss&amp;ver=1.0";
	$_LINK['recent']   = "$script?" . rawurlencode($whatsnew);
	$_LINK['refer']    = "$script?plugin=referer&amp;page=$r_page";
	$_LINK['reload']   = "$script?$r_page";
	$_LINK['rename']   = "$script?plugin=rename&amp;refer=$r_page";
	$_LINK['rss']      = "$script?cmd=rss";
	$_LINK['rss10']    = "$script?cmd=rss&amp;ver=1.0"; // Same as 'rdf'
	$_LINK['rss20']    = "$script?cmd=rss&amp;ver=2.0";
	$_LINK['mixirss']  = "$script?cmd=mixirss";         // Same as 'rdf' for mixi
	$_LINK['search']   = "$script?cmd=search";
	$_LINK['side']     = "$script?$sidebar";
	$_LINK['source']   = "$script?plugin=source&amp;refer=$r_page";
	$_LINK['top']      = "$script?" . rawurlencode($defaultpage);
	if ($trackback) {
		$tb_id = tb_get_id($_page);
		$_LINK['trackback'] = "$script?plugin=tb&amp;__mode=view&amp;tb_id=$tb_id";
	}
	$_LINK['unfreeze'] = "$script?cmd=unfreeze&amp;page=$r_page";
	$_LINK['upload']   = "$script?plugin=attach&amp;pcmd=upload&amp;page=$r_page";

	// Compat: Skins for 1.4.4 and before
	$link_add       = & $_LINK['add'];
	$link_new       = & $_LINK['new'];	// New!
	$link_edit      = & $_LINK['edit'];
	$link_diff      = & $_LINK['diff'];
	$link_top       = & $_LINK['top'];
	$link_list      = & $_LINK['list'];
	$link_filelist  = & $_LINK['filelist'];
	$link_search    = & $_LINK['search'];
	$link_whatsnew  = & $_LINK['recent'];
	$link_backup    = & $_LINK['backup'];
	$link_help      = & $_LINK['help'];
	$link_trackback = & $_LINK['trackback'];	// New!
	$link_rdf       = & $_LINK['rdf'];		// New!
	$link_rss       = & $_LINK['rss'];
	$link_rss10     = & $_LINK['rss10'];		// New!
	$link_rss20     = & $_LINK['rss20'];		// New!
	$link_freeze    = & $_LINK['freeze'];
	$link_unfreeze  = & $_LINK['unfreeze'];
	$link_upload    = & $_LINK['upload'];
	$link_template  = & $_LINK['copy'];
	$link_refer     = & $_LINK['refer'];	// New!
	$link_rename    = & $_LINK['rename'];

	$link_mixirss   = & $_LINK['mixirss'];	// Plus!
	$link_menu      = & $_LINK['menu'];     // Plus!
	$link_read      = & $_LINK['read'];     // Plus!
	$link_reload    = & $_LINK['reload'];   // Plus!
	$link_side      = & $_LINK['side'];     // Plus!
	$link_source    = & $_LINK['source'];   // Plus!

	// Init flags
	$is_page = (is_pagename($_page) && ! arg_check('backup') && $_page != $whatsnew);
	$is_read = (arg_check('read') && is_page($_page));
	$is_freeze = is_freeze($_page);

	// Last modification date (string) of the page
	$lastmodified = $is_read ?  get_date('D, d M Y H:i:s T', get_filetime($_page)) .
		' ' . get_pg_passage($_page, FALSE) : '';

	// List of related pages
	$related = ($is_read && $related_link) ? make_related($_page) : '';

	// List of attached files of the page
	// $attaches = ($is_read && exist_plugin_action('attach')) ? attach_filelist() : '';
	if ($is_read && exist_plugin_action('attach')) {
		do_plugin_init('attach');
		$attaches = attach_filelist();
	}

	// List of footnotes
	ksort($foot_explain, SORT_NUMERIC);
	$notes = ! empty($foot_explain) ? $note_hr . join("\n", $foot_explain) : '';

	// Tags will be inserted into <head></head>
	$head_tag = ! empty($head_tags) ? join("\n", $head_tags) ."\n" : '';

	// 1.3.x compat
	// Last modification date (UNIX timestamp) of the page
	$fmt = $is_read ? get_filetime($_page) + LOCALZONE : 0;

	// Search words
	if ($search_word_color && isset($vars['word'])) {
		$body = '<div class="small">' . $_string['word'] . htmlspecialchars($vars['word']) .
			'</div>' . $hr . "\n" . $body;
		$words = array_flip(array_splice(
			preg_split('/\s+/', $vars['word'], -1, PREG_SPLIT_NO_EMPTY),
			0, 10));
		$keys = array();
		foreach ($words as $word=>$id) $keys[$word] = strlen($word);
		arsort($keys, SORT_NUMERIC);
		$keys = get_search_words(array_keys($keys), TRUE);
		$id = 0;
		foreach ($keys as $key=>$pattern) {
			$s_key    = htmlspecialchars($key);
			$pattern  = '/<[^>]*>|(' . $pattern . ')|&[^;]+;/';
			$callback = create_function(
				'$arr',
				'return (count($arr) > 1) ? \'<strong class="word' .
					$id++ . '">\' . $arr[1] . \'</strong>\' : $arr[0];'
			);
			$body  = preg_replace_callback($pattern, $callback, $body);
			$notes = preg_replace_callback($pattern, $callback, $notes);
		}
	}

	$longtaketime = getmicrotime() - MUTIME;
	$taketime     = sprintf('%01.03f', $longtaketime);

	require(SKIN_FILE);
}

// Show 'edit' form
function edit_form($page, $postdata, $digest = FALSE, $b_template = TRUE)
{
	global $script, $vars, $rows, $cols, $hr, $function_freeze;
	global $whatsnew, $non_list, $load_template_func;
	global $notimeupdate;
	global $_button, $_string;
	global $ajax;

//	if ($ajax) $rows = $rows / 3;
	if ($digest === FALSE) $digest = md5(join('', get_source($page)));

	$refer = $template = $addtag = $add_top = $add_ajax = '';

	$checked_top  = isset($vars['add_top'])     ? ' checked="checked"' : '';
	$checked_time = isset($vars['notimestamp']) ? ' checked="checked"' : '';

	if(isset($vars['add'])) {
		$addtag  = '<input type="hidden" name="add" value="true" />';
		$add_top = '<input type="checkbox" name="add_top" value="true"' .
			$checked_top . ' /><span class="small">' .
			$_button['addtop'] . '</span>';
	}

	if($load_template_func && $b_template) {
		$pages  = array();
		$non_list_pattern = '/' . $non_list . '/';
		foreach(get_existpages() as $_page) {
			if ($_page == $whatsnew || preg_match($non_list_pattern, $_page))
				continue;
			$s_page = htmlspecialchars($_page);
			$pages[$_page] = '   <option value="' . $s_page . '">' .
				$s_page . '</option>';
		}
		ksort($pages);
		$s_pages  = join("\n", $pages);
		$template = <<<EOD
  <select name="template_page">
   <option value="">-- {$_button['template']} --</option>
$s_pages
  </select>
  <input type="submit" name="template" value="{$_button['load']}" accesskey="r" />
  <br />
EOD;

		if (isset($vars['refer']) && $vars['refer'] != '')
			$refer = '[[' . strip_bracket($vars['refer']) . ']]' . "\n\n";
	}

	$r_page      = rawurlencode($page);
	$s_page      = htmlspecialchars($page);
	$s_digest    = htmlspecialchars($digest);
	$s_postdata  = htmlspecialchars($refer . $postdata);
	$s_original  = isset($vars['original']) ? htmlspecialchars($vars['original']) : $s_postdata;
	$s_id        = isset($vars['id']) ? htmlspecialchars($vars['id']) : '';
	$b_preview   = isset($vars['preview']); // TRUE when preview
	$btn_preview = $b_preview ? $_button['repreview'] : $_button['preview'];

	if ($ajax) {
		$add_ajax = '<input type="button" name="add_ajax" value="'.$btn_preview.'" accesskey="p" onclick="pukiwiki_apx(this.form.page.value)" />';
	}

	$add_notimestamp = '';
	if ( $notimeupdate != 0 ) {
		// enable 'do not change timestamp'
		$add_notimestamp = <<<EOD
  <input type="checkbox" name="notimestamp" id="_edit_form_notimestamp" value="true"$checked_time />
  <label for="_edit_form_notimestamp"><span class="small">{$_button['notchangetimestamp']}</span></label>
EOD;
		if ( $notimeupdate == 2 ) {
			// enable only administrator
			$add_notimestamp .= <<<EOD
  <input type="password" name="pass" size="12" />
EOD;
		}
		$add_notimestamp .= '&nbsp;';
	}
	$refpage = htmlspecialchars($vars['refpage']);
	$add_assistant = edit_form_assistant();

	$body = <<<EOD
<div id="realview_outer" style="z-index:10;margin:1px;padding:0px 20px;height:200px;overflow:auto;display:none"><div id="realview"></div><br /></div>
<form action="$script" method="post">
 <div class="edit_form" onmouseup="pukiwiki_pos()" onkeyup="pukiwiki_pos()">
$template
  $addtag
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" name="id"     value="$s_id" />
  <textarea id="msg" name="msg" rows="$rows" cols="$cols" onselect="pukiwiki_apv(this.form.page.value,this.value)" onfocus="pukiwiki_apv(this.form.page.value,this.value)" onkeyup="pukiwiki_apv(this.form.page.value,this.value)" onchange="pukiwiki_apv(this.form.page.value,this.value)">$s_postdata</textarea>
  <br />
  $add_assistant
  <br />
  <input type="submit" name="write"   value="{$_button['update']}" accesskey="s" />
  $add_top
  $add_ajax
  $add_notimestamp
  <input type="submit" name="cancel"  value="{$_button['cancel']}" accesskey="c" />
  <textarea name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
 </div>
</form>
EOD;

//  <input type="submit" name="preview" value="$btn_preview" accesskey="p" />

//	if (isset($vars['help'])) {
//		$body .= $hr . catrule();
//	} else {
//		$body .= '<ul><li><a href="' .
//			$script . '?cmd=edit&amp;help=true&amp;page=' . $r_page .
//			'">' . $_string['help'] . '</a></li></ul>';
//	}

	if ($ajax) {
		global $head_tags;
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/msxml.js"></script>';
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/textloader.js"></script>';
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/realedit.js"></script>';
	}

	return $body;
}
// Input Assistant
function edit_form_assistant()
{
	global $html_transitional;
	static $assist_loaded = 0;	// for non-reentry

	$html_transitional = TRUE;
	if (!$assist_loaded) {
		$assist_loaded++;
		$map = <<<EOD
<map id="map_button" name="map_button">
<area shape="rect" coords="0,0,22,16" title="URL" alt="URL" href="#" onclick="javascript:pukiwiki_linkPrompt('url'); return false;" />
<area shape="rect" coords="24,0,40,16" title="B" alt="B" href="#" onclick="javascript:pukiwiki_tag('b'); return false;" />
<area shape="rect" coords="43,0,59,16" title="I" alt="I" href="#" onclick="javascript:pukiwiki_tag('i'); return false;" />
<area shape="rect" coords="62,0,79,16" title="U" alt="U" href="#" onclick="javascript:pukiwiki_tag('u'); return false;" />
<area shape="rect" coords="81,0,103,16" title="SIZE" alt="SIZE" href="#" onclick="javascript:pukiwiki_tag('size'); return false;" />
</map>
<map id="map_color" name="map_color">
<area shape="rect" coords="0,0,8,8" title="Black" alt="Black" href="#" onclick="javascript:pukiwiki_tag('Black'); return false;" />
<area shape="rect" coords="8,0,16,8" title="Maroon" alt="Maroon" href="#" onclick="javascript:pukiwiki_tag('Maroon'); return false;" />
<area shape="rect" coords="16,0,24,8" title="Green" alt="Green" href="#" onclick="javascript:pukiwiki_tag('Green'); return false;" />
<area shape="rect" coords="24,0,32,8" title="Olive" alt="Olive" href="#" onclick="javascript:pukiwiki_tag('Olive'); return false;" />
<area shape="rect" coords="32,0,40,8" title="Navy" alt="Navy" href="#" onclick="javascript:pukiwiki_tag('Navy'); return false;" />
<area shape="rect" coords="40,0,48,8" title="Purple" alt="Purple" href="#" onclick="javascript:pukiwiki_tag('Purple'); return false;" />
<area shape="rect" coords="48,0,55,8" title="Teal" alt="Teal" href="#" onclick="javascript:pukiwiki_tag('Teal'); return false;" />
<area shape="rect" coords="56,0,64,8" title="Gray" alt="Gray" href="#" onclick="javascript:pukiwiki_tag('Gray'); return false;" />
<area shape="rect" coords="0,8,8,16" title="Silver" alt="Silver" href="#" onclick="javascript:pukiwiki_tag('Silver'); return false;" />
<area shape="rect" coords="8,8,16,16" title="Red" alt="Red" href="#" onclick="javascript:pukiwiki_tag('Red'); return false;" />
<area shape="rect" coords="16,8,24,16" title="Lime" alt="Lime" href="#" onclick="javascript:pukiwiki_tag('Lime'); return false;" />
<area shape="rect" coords="24,8,32,16" title="Yellow" alt="Yellow" href="#" onclick="javascript:pukiwiki_tag('Yellow'); return false;" />
<area shape="rect" coords="32,8,40,16" title="Blue" alt="Blue" href="#" onclick="javascript:pukiwiki_tag('Blue'); return false;" />
<area shape="rect" coords="40,8,48,16" title="Fuchsia" alt="Fuchsia" href="#" onclick="javascript:pukiwiki_tag('Fuchsia'); return false;" />
<area shape="rect" coords="48,8,56,16" title="Aqua" alt="Aqua" href="#" onclick="javascript:pukiwiki_tag('Aqua'); return false;" />
<area shape="rect" coords="56,8,64,16" title="White" alt="White" href="#" onclick="javascript:pukiwiki_tag('White'); return false;" />
</map>
EOD;
	}
	return <<<EOD
$map
<script type="text/javascript" src="skin/assistant.js"></script>
EOD;
}

// Related pages
function make_related($page, $tag = '')
{
	global $script, $vars, $rule_related_str, $related_str, $non_list;
	global $_ul_left_margin, $_ul_margin, $_list_pad_str;

	$links = links_get_related($page);

	if ($tag) {
		ksort($links);
	} else {
		arsort($links);
	}

	$_links = array();
	$non_list_pattern = '/' . $non_list . '/';
	foreach ($links as $page=>$lastmod) {
		if (preg_match($non_list_pattern, $page)) continue;

		$r_page   = rawurlencode($page);
		$s_page   = htmlspecialchars($page);
		$passage  = get_passage($lastmod);
		$_links[] = $tag ?
			'<a href="' . $script . '?' . $r_page . '" title="' .
			$s_page . ' ' . $passage . '">' . $s_page . '</a>' :
			'<a href="' . $script . '?' . $r_page . '">' .
			$s_page . '</a>' . $passage;
	}
	if (empty($_links)) return ''; // Nothing

	if ($tag == 'p') { // From the line-head
		$margin = $_ul_left_margin + $_ul_margin;
		$style  = sprintf($_list_pad_str, 1, $margin, $margin);
		$retval =  "\n" . '<ul' . $style . '>' . "\n" .
			'<li>' . join($rule_related_str, $_links) . '</li>' . "\n" .
			'</ul>' . "\n";
	} else if ($tag) {
		$retval = join($rule_related_str, $_links);
	} else {
		$retval = join($related_str, $_links);
	}

	return $retval;
}

// User-defined rules (convert without replacing source)
function make_line_rules($str)
{
	global $line_rules;
	static $pattern, $replace;

	if (! isset($pattern)) {
		$pattern = array_map(create_function('$a',
			'return \'/\' . $a . \'/\';'), array_keys($line_rules));
		$replace = array_values($line_rules);
		unset($line_rules);
	}

	return preg_replace($pattern, $replace, $str);
}

function strip_htmltag($str)
{
	global $_symbol_noexists;

	$noexists_pattern = '#<span class="noexists">([^<]*)<a[^>]+>' .
		preg_quote($_symbol_noexists, '#') . '</a></span>#';

	$str = preg_replace($noexists_pattern, '$1', $str);
	//$str = preg_replace('/<a[^>]+>\?<\/a>/', '', $str);
	return preg_replace('/<[^>]+>/', '', $str);
}

// Make a backlink. searching-link of the page name, by the page name, for the page name
function make_search($page)
{
	global $script;

	$s_page = htmlspecialchars($page);
	$r_page = rawurlencode($page);

	return '<a href="' . $script . '?plugin=related&amp;page=' . $r_page .
		'">' . $s_page . '</a> ';
}

// Make heading (remove footnotes and HTML tags)
function make_heading(& $str, $strip = TRUE)
{
	global $NotePattern;

	// Cut fixed-anchors
	$id = '';
	$matches = array();
	if (preg_match('/^(\*{0,3})(.*?)\[#([A-Za-z][\w-]+)\](.*?)$/m', $str, $matches)) {
		$str = $matches[2] . $matches[4];
		$id  = $matches[3];
	} else {
		$str = preg_replace('/^\*{0,3}/', '', $str);
	}

	// Cut footnotes and tags
	if ($strip === TRUE)
		$str = strip_htmltag(make_link(preg_replace($NotePattern, '', $str)));

	return $id;
}

// Separate a page-name(or URL or null string) and an anchor
// (last one standing) without sharp
function anchor_explode($page, $strict_editable = FALSE)
{
	$pos = strrpos($page, '#');
	if ($pos === FALSE) return array($page, '', FALSE);

	// Ignore the last sharp letter
	if ($pos + 1 == strlen($page)) {
		$pos = strpos(substr($page, $pos + 1), '#');
		if ($pos === FALSE) return array($page, '', FALSE);
	}

	$s_page = substr($page, 0, $pos);
	$anchor = substr($page, $pos + 1);

	if($strict_editable === TRUE &&  preg_match('/^[a-z][a-f0-9]{7}$/', $anchor)) {
		return array ($s_page, $anchor, TRUE); // Seems fixed-anchor
	} else {
		return array ($s_page, $anchor, FALSE);
	}
}

// Check HTTP header()s were sent already, or
// there're blank lines or something out of php blocks
function pkwk_headers_sent()
{
	if (PKWK_OPTIMISE) return;

	$file = $line = '';
	if (version_compare(PHP_VERSION, '4.3.0', '>=')) {
		if (headers_sent($file, $line))
		    die('Headers already sent at ' .
		    	htmlspecialchars($file) .
			' line ' . $line . '.');
	} else {
		if (headers_sent())
			die('Headers already sent.');
	}
}

// Output common HTTP headers
function pkwk_common_headers()
{
	if (! PKWK_OPTIMISE) pkwk_headers_sent();

	if(defined('PKWK_ZLIB_LOADABLE_MODULE')) {
		$matches = array();
		if(ini_get('zlib.output_compression') &&
		    preg_match('/\b(gzip|deflate)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)) {
		    	// Bug #29350 output_compression compresses everything _without header_ as loadable module
		    	// http://bugs.php.net/bug.php?id=29350
			header('Content-Encoding: ' . $matches[1]);
			header('Vary: Accept-Encoding');
		}
	}
}

// DTD definitions
define('PKWK_DTD_XHTML_1_1',              17); // Strict only
define('PKWK_DTD_XHTML_1_0',              16); // Strict
define('PKWK_DTD_XHTML_1_0_STRICT',       16);
define('PKWK_DTD_XHTML_1_0_TRANSITIONAL', 15);
define('PKWK_DTD_XHTML_1_0_FRAMESET',     14);
define('PKWK_DTD_HTML_4_01',               3); // Strict
define('PKWK_DTD_HTML_4_01_STRICT',        3);
define('PKWK_DTD_HTML_4_01_TRANSITIONAL',  2);
define('PKWK_DTD_HTML_4_01_FRAMESET',      1);

// Output HTML DTD, <html> start tag. Return content-type.
function pkwk_output_dtd($pkwk_dtd = PKWK_DTD_XHTML_1_1)
{
	static $called;
	if (isset($called)) die('pkwk_output_dtd() already called. Why?');
	$called = TRUE;

	$type = 'XHTML';
	$option = '';
	switch($pkwk_dtd){
	case PKWK_DTD_XHTML_1_1             : $version = '1.1' ; $dtd = 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'; break;
	case PKWK_DTD_XHTML_1_0_STRICT      : $version = '1.0' ; $option = 'Strict';       $dtd = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd';      break;
	case PKWK_DTD_XHTML_1_0_TRANSITIONAL: $version = '1.0' ; $option = 'Transitional'; $dtd = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'; break;
	case PKWK_DTD_HTML_4_01_STRICT      : $type = 'HTML'; $version = '4.01'; $dtd = 'http://www.w3.org/TR/html4/strict.dtd';   break;
	case PKWK_DTD_HTML_4_01_TRANSITIONAL: $type = 'HTML'; $version = '4.01'; $option = 'Transitional'; $dtd = 'http://www.w3.org/TR/html4/loose.dtd';    break;
	default: die('DTD not specified or invalid DTD'); break;
	}

	// Output XML or not
	if ($type == 'XHTML') echo '<?xml version="1.0" encoding="' . CONTENT_CHARSET . '" ?>' . "\n";

	// Output doctype
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD ' . $type . ' ' . $version . ($option != '' ? ' ' . $option : '') . '//EN" "' . $dtd . '">' . "\n";

	// Output <html> start tag
	echo '<html';
	if ($type == 'XHTML') {
		echo ' xmlns="http://www.w3.org/1999/xhtml"'; // dir="ltr" /* LeftToRight */
		echo ' xml:lang="' . LANG . '"';
		if ($version == '1.0') echo ' lang="' . LANG . '"'; // Only XHTML 1.0
	} else {
		echo ' lang="' . LANG . '"'; // HTML
	}
	echo '>' . "\n"; // <html>

	// Return content-type (with MIME type)
	if ($type == 'XHTML') {
		// NOTE: XHTML 1.1 browser will ignore http-equiv
		return '<meta http-equiv="content-type" content="application/xhtml+xml; charset=' . CONTENT_CHARSET . '" />' . "\n";
	} else {
		return '<meta http-equiv="content-type" content="text/html; charset=' . CONTENT_CHARSET . '" />' . "\n";
	}
}
?>
