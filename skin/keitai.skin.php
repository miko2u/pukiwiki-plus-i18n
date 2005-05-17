<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: keitai.skin.php,v 1.9.15 2005/05/01 02:43:27 miko Exp $
// Copyright (C)
//   2005      PukiWiki Plus! Team
//   2003-2005 PukiWiki Developers Team
//
// Skin for Embedded devices

// ----
// Prohibit direct access
if (! defined('UI_LANG')) die('UI_LANG is not set');

global $script, $vars, $page_title;
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

// Page Number
$r_page = isset($vars['page']) ? $vars['page'] : '';
$r_page = rawurlencode($r_page);
$pageno = (isset($vars['p']) and is_numeric($vars['p'])) ? $vars['p'] : 0;
$pagecount = ceil(strlen($body) / $max_size);
$lastpage = $pagecount - 1;

// Navigatino resource string
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

// Navigation
$headnavi = array();
$footnavi = array();
$headnavi[] = '<a id="pstart" name="pstart" href="#pfinal">' . $navistr['final'] . '</a>';
$footnavi[] = '<a id="pfinal" name="pfinal" href="#pstart">' . $navistr['start'] . '</a>';
if ($rw) {
	$footnavi[] = '<a href="' . $_LINK['new'] . '"' . $accesskey . '="1">1.' . $navistr['new'] . '</a>';
	$footnavi[] = '<a href="' . $_LINK['edit'] . '"' . $accesskey . '="2">2.' . $navistr['edit'] . '</a>';
	if ($is_read and $function_freeze) {
		if (! $is_freeze) {
			$footnavi[] = '<a href="' . $_LINK['freeze']   . '" ' . $accesskey . '="3">3.' . $navistr['freeze'] . '</a>';
		} else {
			$footnavi[] = '<a href="' . $_LINK['unfreeze'] . '" ' . $accesskey . '="3">3.' . $navistr['unfreeze'] . '</a>';
		}
	}
}
$footnavi[] = '<a href="' . $_LINK['top'] . '"' . $accesskey . '="0">0.' . $navistr['top'] . '</a>';
$headnavi[] = '<a href="' . $_LINK['menu'] . '" ' . $accesskey . '="4">4.' . $navistr['menu'] . '</a>';
$headnavi[] = '<a href="' . $_LINK['recent'] . '" ' . $accesskey . '="5">5.' . $navistr['recent'] . '</a>';

// Previous / Next block
if ($pagecount > 1) {
	$prev = $pageno - 1;
	$next = $pageno + 1;
	if ($pageno > 0) {
		$headnavi[] = '<a href="' . $_LINK['read'] . '&amp;p=' . $prev . '"' . $accesskey . '="7">7.' . $navistr['prev'] . '</a>';
	}
	$headnavi[] = $next . '/' . $pagecount . ' ';
	if ($pageno < $lastpage) {
		$headnavi[] = '<a href="' . $_LINK['read'] . '&amp;p=' . $next . '"' . $accesskey . '="8">8.' . $navistr['next'] . '</a>';
	}
}
$headnavi[] = '<a href="' . $_LINK['reload'] . '"' . $accesskey . '="9">9.' . $navistr['reload'] . '</a>';

$headnavi = join(' ', $headnavi);
$footnavi = join(' ', $footnavi);
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
