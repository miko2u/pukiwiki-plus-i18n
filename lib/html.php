<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: html.php,v 1.59.16 2007/07/09 23:42:00 upk Exp $
// Copyright (C)
//   2005-2007 PukiWiki Plus! Team
//   2002-2006 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// HTML-publishing related functions
// Plus!NOTE:(policy)not merge official cvs(1.49->1.54)
// Plus!NOTE:(policy)not merge official cvs(1.58->1.59) See Question/181

// Show page-content
function catbody($title, $page, $body)
{
	global $script, $vars, $arg, $defaultpage, $whatsnew, $help_page, $hr;
	global $attach_link, $related_link, $cantedit, $function_freeze;
	global $search_word_color, $foot_explain, $note_hr, $head_tags, $foot_tags;
	global $trackback, $referer, $javascript;
	global $newtitle, $newbase, $language, $use_local_time; // Plus! skin extension
	global $nofollow;
	global $_LANG, $_LINK, $_IMAGE;

	global $pkwk_dtd;     // XHTML 1.1, XHTML1.0, HTML 4.01 Transitional...
	global $page_title;   // Title of this site
	global $do_backup;    // Do backup or not
	global $modifier;     // Site administrator's  web page
	global $modifierlink; // Site administrator's name

	global $skin_file, $menubar, $sidebar;
	global $_string;

	if (! defined('SKIN_FILE') || ! file_exists(SKIN_FILE) || ! is_readable(SKIN_FILE)) {
		if (! file_exists($skin_file) || ! is_readable($skin_file)) {
			die_message(SKIN_FILE . '(skin file) is not found.');
		} else {
			define('SKIN_FILE', $skin_file);
		}
	}

	$_LINK = $_IMAGE = array();

	// Add JavaScript header when ...
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
	$_LINK['newsub']   = "$script?plugin=newpage_subdir&amp;directory=$r_page";
	$_LINK['read']     = "$script?cmd=read&amp;page=$r_page";
	$_LINK['rdf']      = "$script?cmd=rss&amp;ver=1.0";
	$_LINK['recent']   = "$script?" . rawurlencode($whatsnew);
	$_LINK['refer']    = "$script?plugin=referer&amp;page=$r_page";
	$_LINK['reload']   = "$script?$r_page";
	$_LINK['rename']   = "$script?plugin=rename&amp;refer=$r_page";
	$_LINK['print']    = "$script?plugin=print&amp;page=$r_page";
	$_LINK['rss']      = "$script?cmd=rss";
	$_LINK['rss10']    = "$script?cmd=rss&amp;ver=1.0"; // Same as 'rdf'
	$_LINK['rss20']    = "$script?cmd=rss&amp;ver=2.0";
	$_LINK['mixirss']  = "$script?cmd=mixirss";         // Same as 'rdf' for mixi
	$_LINK['skeylist']   = "$script?cmd=skeylist&amp;page=$r_page";
	$_LINK['linklist']   = "$script?cmd=linklist&amp;page=$r_page";
	$_LINK['log_browse'] = "$script?cmd=logview&amp;kind=browse&amp;page=$r_page";
	$_LINK['log_update'] = "$script?cmd=logview&amp;page=$r_page";
	$_LINK['log_down']   = "$script?cmd=logview&amp;kind=download&amp;page=$r_page";
	$_LINK['search']   = "$script?cmd=search";
	$_LINK['side']     = "$script?$sidebar";
	$_LINK['source']   = "$script?plugin=source&amp;page=$r_page";
	$_LINK['template'] = "$script?plugin=template&amp;refer=$r_page";
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
	$link_newsub    = & $_LINK['newsub'];
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

	// List of attached files to the page
	$attaches = '';
	if ($attach_link && $is_read && exist_plugin_action('attach')) {
		if (do_plugin_init('attach') !== FALSE) {
			$attaches = attach_filelist();
		}
	}

	// List of related pages
	$related  = ($related_link && $is_read) ? make_related($_page) : '';

	// List of footnotes
	ksort($foot_explain, SORT_NUMERIC);
	$notes = ! empty($foot_explain) ? $note_hr . join("\n", $foot_explain) : '';

	// Tags will be inserted into <head></head>
	$head_tag = ! empty($head_tags) ? join("\n", $head_tags) ."\n" : '';
	$foot_tag = ! empty($foot_tags) ? join("\n", $foot_tags) ."\n" : '';

	// 1.3.x compat
	// Last modification date (UNIX timestamp) of the page
	$fmt = $is_read ? get_filetime($_page) : 0;

	// Search words
	if ($search_word_color && isset($vars['word'])) {
		$body = '<div class="small">' . $_string['word'] . htmlspecialchars($vars['word']) .
			'</div>' . $hr . "\n" . $body;

		// BugTrack2/106: Only variables can be passed by reference from PHP 5.0.5
		$words = preg_split('/\s+/', $vars['word'], -1, PREG_SPLIT_NO_EMPTY);
		$words = array_splice($words, 0, 10); // Max: 10 words
		$words = array_flip($words);

		$keys = array();
		foreach ($words as $word=>$id) $keys[$word] = strlen($word);
		arsort($keys, SORT_NUMERIC);
		$keys = get_search_words(array_keys($keys), TRUE);
		$id = 0;
		foreach ($keys as $key=>$pattern) {
			$s_key    = htmlspecialchars($key);
			$pattern  = '/' .
				'<textarea[^>]*>.*?<\/textarea>' .	// Ignore textareas
				'|' . '<[^>]*>' .			// Ignore tags
				'|' . '&[^;]+;' .			// Ignore entities
				'|' . '(' . $pattern . ')' .		// $matches[1]: Regex for a search word
				'/sS';
			$decorate_Nth_word = create_function(
				'$matches',
				'return (isset($matches[1])) ? ' .
					'\'<strong class="word' .
						$id .
					'">\' . $matches[1] . \'</strong>\' : ' .
					'$matches[0];'
			);
			$body  = preg_replace_callback($pattern, $decorate_Nth_word, $body);
			$notes = preg_replace_callback($pattern, $decorate_Nth_word, $notes);
			++$id;
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
	global $whatsnew, $load_template_func, $load_refer_related;
	global $notimeupdate;
	global $_button, $_string;
	global $ajax, $ctrl_unload;

	// Newly generate $digest or not
	if ($digest === FALSE) $digest = md5(get_source($page, TRUE, TRUE));

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
		foreach(get_existpages() as $_page) {
			if ($_page == $whatsnew || check_non_list($_page))
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
		if ($load_refer_related) {
			if (isset($vars['refer']) && $vars['refer'] != '')
				$refer = '[[' . strip_bracket($vars['refer']) . ']]' . "\n\n";
		}
	}

	$r_page      = rawurlencode($page);
	$s_page      = htmlspecialchars($page);
	$s_digest    = htmlspecialchars($digest);
	$s_postdata  = htmlspecialchars($refer . $postdata);
	$s_original  = isset($vars['original']) ? htmlspecialchars($vars['original']) : $s_postdata;
	$s_id        = isset($vars['id']) ? htmlspecialchars($vars['id']) : '';
	$b_preview   = isset($vars['preview']); // TRUE when preview
	$btn_preview = $b_preview ? $_button['repreview'] : $_button['preview'];

	$s_ticket = md5(MUTIME);
	if (function_exists('pkwk_session_start') && pkwk_session_start() != 0) {
		// BugTrack/95 fix Problem: browser RSS request with session
		$_SESSION[$s_ticket] = md5(get_ticket() . $digest);
		$_SESSION['origin' . $s_ticket] = md5(get_ticket() . str_replace("\r", '', $s_original));
	}

	if ($ajax && ! is_mobile()) {
		$add_ajax = '<input type="button" name="add_ajax" value="' . $btn_preview . '" accesskey="p" onclick="pukiwiki_apx(this.form.page.value)" />';
	} else {
		$add_ajax = '<input type="submit" name="preview" value="' . $btn_preview . '" accesskey="p" />';
	}


	$add_notimestamp = '';
	if ($notimeupdate != 0 && is_page($page)) {
		// enable 'do not change timestamp'
		$add_notimestamp = <<<EOD
  <input type="checkbox" name="notimestamp" id="_edit_form_notimestamp" value="true"$checked_time />
  <label for="_edit_form_notimestamp"><span class="small">{$_button['notchangetimestamp']}</span></label>
EOD;
		if ($notimeupdate == 2 && auth::check_role('role_adm_contents')) {
			// enable only administrator
			$add_notimestamp .= <<<EOD
  <input type="password" name="pass" size="12" />
EOD;
		}
		$add_notimestamp .= '&nbsp;';
	}
	$refpage = isset($vars['refpage']) ? htmlspecialchars($vars['refpage']) : '';
	$add_assistant = edit_form_assistant();

	$body = <<<EOD
<div id="realview_outer"><div id="realview"></div><br /></div>
<form action="$script" method="post" id="form">
 <div class="edit_form" onmouseup="pukiwiki_pos()" onkeyup="pukiwiki_pos()">
$template
  $addtag
  <input type="hidden" name="cmd"    value="edit" />
  <input type="hidden" name="page"   value="$s_page" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" name="ticket" value="$s_ticket" />
  <input type="hidden" name="id"     value="$s_id" />
  <textarea id="msg" name="msg" rows="$rows" cols="$cols" onselect="pukiwiki_apv(this.form.page.value,this)" onfocus="pukiwiki_apv(this.form.page.value,this)" onkeyup="pukiwiki_apv(this.form.page.value,this)" onmouseup="pukiwiki_apv(this.form.page.value,this)">$s_postdata</textarea>
  <br />
  $add_assistant
  <br />
  <input type="submit" name="write"   value="{$_button['update']}" accesskey="s" />
  $add_top
  $add_ajax
  $add_notimestamp
  <input type="submit" id="cancel" name="cancel"  value="{$_button['cancel']}" accesskey="c" />
  <textarea id="original" name="original" rows="1" cols="1" style="display:none">$s_original</textarea>
 </div>
</form>
EOD;

	if ($ajax) {
		global $head_tags;
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/msxml.js"></script>';
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/realedit.js"></script>';
	}
	if ($ctrl_unload) {
		global $head_tags;
		$head_tags[] = ' <script type="text/javascript" charset="utf-8" src="' . SKIN_URI . 'ajax/ctrl_unload.js"></script>';
	}

	return $body;
}
// Input Assistant
function edit_form_assistant()
{
	global $pkwk_dtd;
	static $assist_loaded = FALSE;	// for non-reentry

	// if Mobile-Phone, do not use.
	if (defined('UA_PROFILE') && is_mobile())
		return;

	// XHTML 1.0 Transitional
	if (! isset($pkwk_dtd) || $pkwk_dtd == PKWK_DTD_XHTML_1_1)
		$pkwk_dtd = PKWK_DTD_XHTML_1_0_TRANSITIONAL;

	if ($assist_loaded === FALSE) {
		$assist_loaded = TRUE;
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
	} else {
		$map = '';
	}

	return $map . "\n" . '<script type="text/javascript" src="' . SKIN_URI . 'assistant.js"></script>' . "\n";
}

// Related pages
function make_related($page, $tag = '')
{
	global $script, $vars, $rule_related_str, $related_str;
	global $_ul_left_margin, $_ul_margin, $_list_pad_str;

	$links = links_get_related($page);

	if ($tag) {
		ksort($links);
	} else {
		arsort($links);
	}

	$_links = array();
	foreach ($links as $page=>$lastmod) {
		if (check_non_list($page)) continue;

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

// Remove all HTML tags(or just anchor tags), and WikiName-speific decorations
function strip_htmltag($str, $all = TRUE)
{
	global $_symbol_noexists;
	static $noexists_pattern;

	if (! isset($noexists_pattern))
		$noexists_pattern = '#<span class="noexists">([^<]*)<a[^>]+>' .
			preg_quote($_symbol_noexists, '#') . '</a></span>#';

	// Strip Dagnling-Link decoration (Tags and "$_symbol_noexists")
	$str = preg_replace($noexists_pattern, '$1', $str);

	if ($all) {
		// All other HTML tags
		return preg_replace('#<[^>]+>#', '', $str);
	} else {
		// All other anchor-tags only
		return preg_replace('#<a[^>]+>|</a>#i', '', $str);
	}
}

// Remove AutoLink marker with AutoLink itself
function strip_autolink($str)
{
	return preg_replace('#<!--autolink--><a [^>]+>|</a><!--/autolink-->#', '', $str);
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

// Make heading string (remove heading-related decorations from Wiki text)
function make_heading(& $str, $strip = TRUE)
{
	global $NotePattern;

	// Cut fixed-heading anchors
	$id = '';
	$matches = array();
	if (preg_match('/^(\*{0,3})(.*?)\[#([A-Za-z][\w-]+)\](.*?)$/m', $str, $matches)) {
		$str = $matches[2] . $matches[4];
		$id  = & $matches[3];
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

	$vary = get_language_header_vary();

	if(defined('PKWK_ZLIB_LOADABLE_MODULE')) {
		$matches = array();
		if(ini_get('zlib.output_compression') &&
		    preg_match('/\b(gzip|deflate)\b/i', $_SERVER['HTTP_ACCEPT_ENCODING'], $matches)) {
		    	// Bug #29350 output_compression compresses everything _without header_ as loadable module
		    	// http://bugs.php.net/bug.php?id=29350
			header('Content-Encoding: ' . $matches[1]);
			if (! empty($vary)) $vary .= ',';
			$vary .= 'Accept-Encoding';
		}
	}

	if (! empty($vary)) {
		header('Vary: '.$vary);
	}
}

// DTD definitions
define('PKWK_DTD_XHTML_1_1',              17); // Strict only
define('PKWK_DTD_XHTML_1_0',              16); // Strict
define('PKWK_DTD_XHTML_1_0_STRICT',       16);
define('PKWK_DTD_XHTML_1_0_TRANSITIONAL', 15);
define('PKWK_DTD_XHTML_1_0_FRAMESET',     14);
define('PKWK_DTD_XHTML_BASIC_1_0',        11);
define('PKWK_DTD_HTML_4_01',               3); // Strict
define('PKWK_DTD_HTML_4_01_STRICT',        3);
define('PKWK_DTD_HTML_4_01_TRANSITIONAL',  2);
define('PKWK_DTD_HTML_4_01_FRAMESET',      1);

define('PKWK_DTD_TYPE_XHTML',        1);
define('PKWK_DTD_TYPE_HTML',         0);

// Output HTML DTD, <html> start tag. Return content-type.
function pkwk_output_dtd($pkwk_dtd = PKWK_DTD_XHTML_1_1, $charset = CONTENT_CHARSET)
{
	static $called;
	if (isset($called)) die('pkwk_output_dtd() already called. Why?');
	$called = TRUE;

	$type = PKWK_DTD_TYPE_XHTML;
	$option = '';
	switch($pkwk_dtd){
	case PKWK_DTD_XHTML_1_1             :
		$version = '1.1' ;
		$dtd     = 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd';
		break;
	case PKWK_DTD_XHTML_1_0_STRICT      :
		$version = '1.0' ;
		$option  = 'Strict';
		$dtd     = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd';
		break;
	case PKWK_DTD_XHTML_1_0_TRANSITIONAL:
		$version = '1.0' ;
		$option  = 'Transitional';
		$dtd     = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd';
		break;

	case PKWK_DTD_XHTML_BASIC_1_0       :
		$version = '1.0' ;
		$option  = 'Basic';
		$dtd     = 'http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd';
		break;

	case PKWK_DTD_HTML_4_01_STRICT      :
		$type    = PKWK_DTD_TYPE_HTML;
		$version = '4.01';
		$dtd     = 'http://www.w3.org/TR/html4/strict.dtd';
		break;
	case PKWK_DTD_HTML_4_01_TRANSITIONAL:
		$type    = PKWK_DTD_TYPE_HTML;
		$version = '4.01';
		$option  = 'Transitional';
		$dtd     = 'http://www.w3.org/TR/html4/loose.dtd';
		break;

	default: die('DTD not specified or invalid DTD');
		break;
	}

	$charset = htmlspecialchars($charset);

	// Output XML or not
	if ($type == PKWK_DTD_TYPE_XHTML) {
		// for IEPatch: for W3C standard rendering
//		if (!(CONTENT_CHARSET == 'UTF-8' && UA_NAME == 'MSIE')) {
			echo '<?xml version="1.0" encoding="' . CONTENT_CHARSET . '" ?' . '>' . "\n";
//		}
	}

	// Output doctype
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD ' .
		($type == PKWK_DTD_TYPE_XHTML ? 'XHTML' : 'HTML') . ' ' .
		$version .
		($option != '' ? ' ' . $option : '') .
		'//EN" "' .
		$dtd .
		'">' . "\n";

	// Output <html> start tag
	$lang_code = str_replace('_','-',LANG); // RFC3066
	echo '<html';
	if ($type == PKWK_DTD_TYPE_XHTML) {
		echo ' xmlns="http://www.w3.org/1999/xhtml"'; // dir="ltr" /* LeftToRight */
		echo ' xml:lang="' . $lang_code . '"';
		if ($version == '1.0') echo ' lang="' . $lang_code . '"'; // Only XHTML 1.0
	} else {
		echo ' lang="' . $lang_code . '"'; // HTML
	}
	echo '>' . "\n"; // <html>
	unset($lang_code);

	// Return content-type (with MIME type)
	if ($type == PKWK_DTD_TYPE_XHTML) {
		// NOTE: XHTML 1.1 browser will ignore http-equiv
		return '<meta http-equiv="content-type" content="application/xhtml+xml; charset=' . $charset . '" />' . "\n";
	} else {
		return '<meta http-equiv="content-type" content="text/html; charset=' . $charset . '" />' . "\n";
	}
}
?>
