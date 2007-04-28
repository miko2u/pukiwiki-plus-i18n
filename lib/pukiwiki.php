<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: pukiwiki.php,v 1.19.12 2007/04/15 12:31:02 miko Exp $
//
// PukiWiki Plus! 1.4.*
//  Copyright (C) 2002-2007 by PukiWiki Plus! Team
//  http://pukiwiki.cafelounge.net/plus/
//
// PukiWiki 1.4.*
//  Copyright (C) 2002-2007 by PukiWiki Developers Team
//  http://pukiwiki.sourceforge.jp/
//
// PukiWiki 1.3.*
//  Copyright (C) 2002-2004 by PukiWiki Developers Team
//  http://pukiwiki.sourceforge.jp/
//
// PukiWiki 1.3 (Base)
//  Copyright (C) 2001-2002 by yu-ji <sng@factage.com>
//  http://pukiwiki.sourceforge.jp/
//
// Special thanks
//  YukiWiki by Hiroshi Yuki <hyuki@hyuki.com>
//  http://www.hyuki.com/yukiwiki/
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// Plus!NOTE:(policy)not merge official cvs(1.16->1.17) See Question/181

if (! defined('DATA_HOME')) define('DATA_HOME', '');

/////////////////////////////////////////////////
// Include subroutines

if (! defined('LIB_DIR')) define('LIB_DIR', '');

require(LIB_DIR . 'func.php');
require(LIB_DIR . 'file.php');
require(LIB_DIR . 'funcplus.php');
require(LIB_DIR . 'fileplus.php');
require(LIB_DIR . 'plugin.php');
require(LIB_DIR . 'html.php');
require(LIB_DIR . 'backup.php');

require(LIB_DIR . 'convert_html.php');
require(LIB_DIR . 'make_link.php');
require(LIB_DIR . 'diff.php');
require(LIB_DIR . 'config.php');
require(LIB_DIR . 'link.php');
require(LIB_DIR . 'auth.php');
require(LIB_DIR . 'proxy.php');
require(LIB_DIR . 'lang.php');
require(LIB_DIR . 'timezone.php');
require(LIB_DIR . 'log.php');
require(LIB_DIR . 'spamplus.php');
require(LIB_DIR . 'proxy.cls.php');
require(LIB_DIR . 'auth.cls.php');
require(LIB_DIR . 'netbios.cls.php');
require(LIB_DIR . 'ua/user_agent.cls.php');

if (! extension_loaded('mbstring')) {
	require(LIB_DIR . 'mbstring.php');
}
if (! extension_loaded('gettext')) {
	require(LIB_DIR . 'gettext.php');
} else {
	function N_($message) { return $message; }
	if (! function_exists('bind_textdomain_codeset')) {
		function bind_textdomain_codeset($domain, $codeset) { return; }
	}
}

// Defaults
$notify = $trackback = $referer = 0;

// Load *.ini.php files and init PukiWiki
require(LIB_DIR . 'init.php');

// Load optional libraries
if ($notify) {
	require(LIB_DIR . 'mail.php'); // Mail notification
}
if ($trackback) {
	require(LIB_DIR . 'trackback.php'); // TrackBack
}
if ($referer) {
	require(LIB_DIR . 'referer.php');
}

/////////////////////////////////////////////////
// Main

$retvars = array();
$page  = isset($vars['page'])  ? $vars['page']  : '';
$refer = isset($vars['refer']) ? $vars['refer'] : '';

if (isset($vars['cmd'])) {
	$plugin = & $vars['cmd'];
} else if (isset($vars['plugin'])) {
	$plugin = & $vars['plugin'];
} else {
	$plugin = '';
}

// SPAM
if (SpamCheckBAN($_SERVER['REMOTE_ADDR'])) die();

// Spam filtering
if ($spam && $method != 'GET') {
	// Adjustment
	$_spam   = ! empty($spam);
	$_plugin = strtolower($plugin);
	switch ($_plugin) {
		case 'search': $_spam = FALSE; break;
		case 'edit':
			$_page = & $page;
			if (isset($vars['add']) && $vars['add']) {
				$_plugin = 'add';
			}
			break;
		case 'bugtrack': $_page = & $vars['base'];  break;
		case 'tracker':  $_page = & $vars['_base']; break;
		case 'read':     $_page = & $page;  break;
		default: $_page = & $refer; break;
	}
	if ($_spam) {
		require(LIB_DIR . 'spam.php');
		if (isset($spam['method'][$_plugin])) {
			$_method = & $spam['method'][$_plugin];
		} else if (isset($spam['method']['_default'])) {
			$_method = & $spam['method']['_default'];
		} else {
			$_method = array();
		}
		$exitmode = isset($spam['exitmode']) ? $spam['exitmode'] : '';
		pkwk_spamfilter($method . ' to #' . $_plugin, $_page, $vars, $_method, $exitmode);
	}
}

// Plugin execution
if ($plugin != '') {
	if (exist_plugin_action($plugin)) {
		$retvars = do_plugin_action($plugin);
		if ($retvars === FALSE) exit; // Done
		// Rescan $vars (Some plugins rewrite it)
		if (isset($vars['cmd'])) {
			$base = isset($vars['page'])  ? $vars['page']  : '';
		} else {
			$base = isset($vars['refer']) ? $vars['refer'] : '';
		}
	} else {
		$msg = 'plugin=' . htmlspecialchars($plugin) . ' is not implemented.';
		$retvars = array('msg'=>$msg,'body'=>$msg);
		$base    = & $defaultpage;
	}
}

// If page output, enable session.
// NOTE: if action plugin(command) use session, call pkwk_session_start()
//       in plugin action-API function.
pkwk_session_start();

// Page output
$title = htmlspecialchars(strip_bracket($base));
$page  = make_search($base);
if (isset($retvars['msg']) && $retvars['msg'] != '') {
	$title = str_replace('$1', $title, $retvars['msg']);
	$page  = str_replace('$1', $page,  $retvars['msg']);
}

if (isset($retvars['body']) && $retvars['body'] != '') {
	$body = & $retvars['body'];
} else {
	if ($base == '' || ! is_page($base)) {
		$base  = & $defaultpage;
		$title = htmlspecialchars(strip_bracket($base));
		$page  = make_search($base);
	}

	$vars['cmd']  = 'read';
	$vars['page'] = & $base;

	global $fixed_heading_edited;
	$source = get_source($base);

	// Virtual action plugin(partedit).
	// NOTE: Check wiki source only.(*NOT* call convert_html() function)
	$lines = $source;
	while (! empty($lines)) {
		$line = array_shift($lines);
		if (preg_match("/^\#(partedit)(?:\((.*)\))?/", $line, $matches)) {
			if ( !isset($matches[2]) || $matches[2] == '') {
				$fixed_heading_edited = ($fixed_heading_edited ? 0:1);
			} else if ( $matches[2] == 'on') {
				$fixed_heading_edited = 1;
			} else if ( $matches[2] == 'off') {
				$fixed_heading_edited = 0;
			}
		}
	}

	$body = convert_html($source);

	if ($trackback) $body .= tb_get_rdf($base); // Add TrackBack-Ping URI
	if ($referer) ref_save($base);
	log_write('browse',$vars['page']);
}

// Output
catbody($title, $page, $body);
exit;
?>
