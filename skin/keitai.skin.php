<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: keitai.skin.php,v 1.8.11 2005/02/13 03:12:47 miko Exp $
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

// コメント行は無くす
$body = preg_replace('#<!(?:--[^-]*-(?:[^-]+-)*?-(?:[^>-]*(?:-[^>-]+)*?)??)*(?:>|$(?!\n)|--.*$)#', '', $body);

// <del>〜</del>を削除
$body = preg_replace('#(<del>)([\w\W]*)(</del>)#i', '', $body);

// 編集機能をもつ IMG タグ(画像)を絵文字に置換
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]*alt="Edit"[^>]*>(?(2)</a>)(?(1)</div>)#i', '&#63826;', $body);

// ALT="keitai" を持つ IMG タグ(画像)を置換(*1)
$body = preg_replace('#<img([^>]*)title="keitai"[^>]*>#i', '<PWimg $1>', $body);

// ALT option を持つ IMG タグ(画像)を文字列に置換
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]*alt="([^"]+)"[^>]*>(?(2)</a>)(?(1)</div>)#i', '[$3]', $body);

// ALT option の無い IMG タグ(画像)を文字列に置換
$body = preg_replace('#(<div[^>]+>)?(<a[^>]+>)?<img[^>]+>(?(2)</a>)(?(1)</div>)#i', '[img]', $body);

// ALT="keitai" を持つ IMG タグ(画像)を置換(*2)
$body = preg_replace('#<PWimg#', '<img', $body);

// ページ番号
$r_page = isset($vars['page']) ? $vars['page'] : '';
$r_page = rawurlencode($r_page);
$pageno = (isset($vars['p']) and is_numeric($vars['p'])) ? $vars['p'] : 0;
$pagecount = ceil(strlen($body) / $max_size);
$lastpage = $pagecount - 1;

// ナビゲーション
$headnavi = array();
$footnavi = array();
if ($rw) {
	$footnavi[] = '<a href="' . $_LINK['new'] . '"' . $accesskey . '="1">1.New</a>';
	$footnavi[] = '<a href="' . $_LINK['edit'] . '"' . $accesskey . '="2">2.Edit</a>';
	if ($is_read and $function_freeze) {
		if (! $is_freeze) {
			$footnavi[] = '<a href="' . $_LINK['freeze']   . '" ' . $accesskey . '="3">3.Freeze</a>';
		} else {
			$footnavi[] = '<a href="' . $_LINK['unfreeze'] . '" ' . $accesskey . '="3">3.Unfreeze</a>';
		}
	}
}
$footnavi[] = '<a href="' . $_LINK['top'] . '"' . $accesskey . '="0">0.Top</a>';
$headnavi[] = '<a href="' . $_LINK['menu'] . '" ' . $accesskey . '="4">4.Menu</a>';
$headnavi[] = '<a href="' . $_LINK['recent'] . '" ' . $accesskey . '="5">5.Recent</a>';

// Previous / Next block
if ($pagecount > 1) {
	$prev = $pageno - 1;
	$next = $pageno + 1;
	if ($pageno > 0) {
		$headnavi[] = '<a href="' . $_LINK['read'] . '&amp;p=' . $prev . '"' . $accesskey . '="7">7.Prev</a>';
	}
	$headnavi[] = $next . '/' . $pagecount . ' ';
	if ($pageno < $lastpage) {
		$headnavi[] = '<a href="' . $_LINK['read'] . '&amp;p=' . $next . '"' . $accesskey . '="8">8.Next</a>';
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
