<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: keitai.skin.php,v 1.16.16 2008/01/05 19:02:00 upk Exp $
// Copyright (C)
//   2005-2006,2008 PukiWiki Plus! Team
//   2003-2006 PukiWiki Developers Team
// License: GPL v2 or (at your option) any later version
//
// Skin for Embedded devices

// ----
// Prohibit direct access
if (! defined('UI_LANG')) die('UI_LANG is not set');

$pageno = (isset($vars['p']) && is_numeric($vars['p'])) ? $vars['p'] : 0;
$edit = (isset($vars['cmd'])    && $vars['cmd']    == 'edit') ||
	(isset($vars['plugin']) && $vars['plugin'] == 'edit');

global $max_size, $accesskey, $menubar, $_symbol_anchor;
$max_size = --$max_size * 1024; // Make 1KByte spare (for $navi, etc)
$link = $_LINK;
$rw = ! PKWK_READONLY;

// ----
// Modify

// Ignore &dagger;s
$body = preg_replace('#<a[^>]+>' . preg_quote($_symbol_anchor, '#') . '</a>#', '', $body);

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

// ----

// Check content volume, Page numbers, divided by this skin
$pagecount = ceil(strlen($body) / $max_size);

// Too large contents to edit
if ($edit && $pagecount > 1)
   	die('Unable to edit: Too large contents for your device');

// Get one page
$body = substr($body, $pageno * $max_size, $max_size);

// Navigation resource string
if (TRUE) {
	$navistr = array(
	  'start'    => '[u]',
	  'final'    => '[b]',
	  'new'      => 'New',
	  'edit'     => 'Edit',
	  'freeze'   => 'Freeze',
	  'unfreeze' => 'Unfreeze',
	  'top'      => 'Top',
	  'menu'     => 'Menu',
	  'recent'   => 'Recent',
	  'prev'     => 'Prev',
	  'next'     => 'Next',
	  'reload'   => 'Reload',
	);
} else {
	$navistr = array(
	  'start'    => _('[u]'),
	  'final'    => _('[b]'),
	  'new'      => _('New'),
	  'edit'     => _('Edit'),
	  'freeze'   => _('Freeze'),
	  'unfreeze' => _('Unfreeze'),
	  'top'      => _('Top'),
	  'menu'     => _('Menu'),
	  'recent'   => _('Recent'),
	  'prev'     => _('Prev'),
	  'next'     => _('Next'),
	  'reload'   => _('Reload'),
	);
}

// ----
// Top navigation (text) bar
$headnavi = array();
$footnavi = array();
$headnavi[] = '<a id="pstart" name="pstart" href="#pfinal">' . $navistr['final'] . '</a>';
$footnavi[] = '<a id="pfinal" name="pfinal" href="#pstart">' . $navistr['start'] . '</a>';

if ($rw) {
	$footnavi[] = '<a href="' . $link['new']  . '" ' . $accesskey . '="1">1.New</a>';
	$footnavi[] = '<a href="' . $link['edit'] . '" ' . $accesskey . '="2">2.Edit</a>';
	if ($is_read && $function_freeze) {
		if (! $is_freeze) {
			$footnavi[] = '<a href="' . $link['freeze']   . '" ' . $accesskey . '="3">3.Freeze</a>';
		} else {
			$footnavi[] = '<a href="' . $link['unfreeze'] . '" ' . $accesskey . '="3">3.Unfreeze</a>';
		}
	}
}
$footnavi[] = '<a href="' . $link['top']  . '" ' . $accesskey . '="0">0.Top</a>';
$headnavi[] = '<a href="' . $link['menu'] . '" ' . $accesskey . '="4">4.Menu</a>';
$headnavi[] = '<a href="' . $link['recent'] . '" ' . $accesskey . '="5">5.Recent</a>';

// Previous / Next block
if ($pagecount > 1) {
	$prev = $pageno - 1;
	$next = $pageno + 1;
	if ($pageno > 0) {
		$headnavi[] = '<a href="' . get_page_uri($_page, 'p=' . $prev) .
			'" ' . $accesskey . '="7">7.Prev</a>';
	}
	$navi[] = $next . '/' . $pagecount . ' ';
	if ($pageno < $pagecount - 1) {
		$headnavi[] = '<a href="' . get_page_uri($_page, 'p=' . $next) .
			'" ' . $accesskey . '="8">8.Next</a>';
	}
}
$headnavi[] = '<a href="' . $_LINK['reload'] . '"' . $accesskey . '="9">9.Reload</a>';

$headnavi = join(' ', $headnavi);
$footnavi = join(' ', $footnavi);

// ----
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
