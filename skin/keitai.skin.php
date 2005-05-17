<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: keitai.skin.php,v 1.9.11 2005/05/01 02:43:27 miko Exp $
// Copyright (C)
//   2005      Customized/Patched by Miko.Hoshina
//   2003-2005 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Skin for Embedded devices

// ----
// Prohibit direct access
if (! defined('UI_LANG')) die('UI_LANG is not set');

global $vars, $page_title;
global $max_size, $accesskey, $menubar;
$link = $_LINK;
$rw = ! PKWK_READONLY;

// Output HTTP headers
pkwk_headers_sent();
if(TRUE) {
	// Force Shift JIS encode for Japanese embedded browsers and devices
	header('Content-Type: text/html; charset=Shift_JIS');
	$title = mb_convert_encoding($title, 'SJIS', SOURCE_ENCODING);
	$body  = mb_convert_encoding($body,  'SJIS', SOURCE_ENCODING);
} else {
	header('Content-Type: text/html; charset=' . CONTENT_CHARSET);
}

// Make 1KByte spare (for header, etc)
$max_size = --$max_size * 1024;

// Replace IMG tags (= images) with character strings
// STEP1: Delete comment lines
$body = preg_replace('#<!(?:--[^-]*-(?:[^-]+-)*?-(?:[^>-]*(?:-[^>-]+)*?)??)*(?:>|$(?!\n)|--.*$)#', '', $body);
// STEP2: Delete <del> tag
$body = preg_replace('#(<del>)([\w\W]*)(</del>)#i', '', $body);
// STEP3: paraedit-symbol to pen-emoji(for DoCoMo)
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]*alt="Edit"[^>]*>(?(2)</a>)(?(1)</div>)#i', '&#63826;', $body);
// STEP4: <img ... title="keitai"> => change to <PWimg ...>
$body = preg_replace('#<img([^>]*)title="keitai"[^>]*>#i', '<PWimg $1>', $body);
// With ALT option
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]*alt="([^"]+)"[^>]*>(?(2)</a>)(?(1)</div>)#i', '[$3]', $body);
// Without ALT option
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]+>(?(2)</a>)(?(1)</div>)#i', '[img]', $body);
// STEP5: change to <PWimg ...> to <img
$body = preg_replace('#<PWimg#', '<img', $body);

// Page numbers, divided by this skin
$pageno = (isset($vars['p']) and is_numeric($vars['p'])) ? $vars['p'] : 0;
$pagecount = ceil(strlen($body) / $max_size);
$lastpage = $pagecount - 1;

// Top navigation (text) bar
$headnavi = array();
$footnavi = array();
if ($rw) {
	$footnavi[] = '<a href="' . $link['new']  . '" ' . $accesskey . '="1">1.New</a>';
	$footnavi[] = '<a href="' . $link['edit'] . '" ' . $accesskey . '="2">2.Edit</a>';
	if ($is_read and $function_freeze) {
		if (! $is_freeze) {
			$footnavi[] = '<a href="' . $link['freeze']   . '" ' . $accesskey . '="3">3.Freeze</a>';
		} else {
			$footnavi[] = '<a href="' . $link['unfreeze'] . '" ' . $accesskey . '="3">3.Unfreeze</a>';
		}
	}
}
$footnavi[] = '<a href="' . $link['top']  . '" ' . $accesskey . '="0">0.Top</a>';
$headnavi[] = '<a href="' . $script . '?' . $menubar . '" ' . $accesskey . '="4">4.Menu</a>';
$headnavi[] = '<a href="' . $link['recent'] . '" ' . $accesskey . '="5">5.Recent</a>';

// Previous / Next block
if ($pagecount > 1) {
	$prev = $pageno - 1;
	$next = $pageno + 1;
	if ($pageno > 0) {
		$headnavi[] = '<a href="' . $script . '?cmd=read&amp;page=' . $r_page . '&amp;p=' . $prev . '" ' . $accesskey . '="7">7.Prev</a>';
	}
	$navi[] = $next . '/' . $pagecount . ' ';
	if ($pageno < $lastpage) {
		$headnavi[] = '<a href="' . $script . '?cmd=read&amp;page=' . $r_page . '&amp;p=' . $next . '" ' . $accesskey . '="8">8.Next</a>';
	}
}
$headnavi[] = '<a href="' . $_LINK['reload'] . '"' . $accesskey . '="9">9.Reload</a>';

$headnavi = join(' | ', $headnavi);
$footnavi = join(' | ', $footnavi);
$body = substr($body, $pageno * $max_size, $max_size);

// Output
?><html><head><title><?php
	echo $title
?></title></head><body><?php
	echo $headnavi
?><hr><?php
	echo $body
?><hr><?php
	echo $footnavi
?></body></html>
