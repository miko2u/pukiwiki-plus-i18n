<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone
// $Id: pukiwiki.ini.php,v 1.148.26 2007/08/16 19:56:00 upk Exp $
// Copyright (C)
//   2005-2007 PukiWiki Plus! Team
//   2002-2007 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// PukiWiki main setting file
// Plus!NOTE:(policy)not merge official cvs(1.139->1.140)
// Plus!NOTE:(policy)not merge official cvs(1.147->1.148) See Question/181

/////////////////////////////////////////////////
// Functionality settings

// PKWK_OPTIMISE - Ignore verbose but understandable checking and warning
//   If you end testing this PukiWiki, set '1'.
//   If you feel in trouble about this PukiWiki, set '0'.
defined('PKWK_OPTIMISE') or define('PKWK_OPTIMISE', 0); // 0,1

/////////////////////////////////////////////////
// Security settings
// 0 - 機能無効
// 1 - 強制モード
// 2 - サイト管理者以上は除く
// 3 - コンテンツ管理者以上は除く
// 4 - 登録者
// 5 - 認証者(未設定時のデフォルト)以上は除く

// 認証せずには閲覧できない
defined('PLUS_PROTECT_MODE') or define('PLUS_PROTECT_MODE', 0); // 0,2,3,4,5

// PKWK_READONLY - Prohibits editing and maintain via WWW
//   NOTE: Counter-related functions will work now (counter, attach count, etc)
defined('PKWK_READONLY') or define('PKWK_READONLY', 0); // 0,1,2,3,4,5

// PKWK_SAFE_MODE - Prohibits some unsafe(but compatible) functions 
defined('PKWK_SAFE_MODE') or define('PKWK_SAFE_MODE', 0); // 0,1,2,3,4,5

// PKWK_CREATE_PAGE - New page making is prohibited.
defined('PKWK_CREATE_PAGE') or define('PKWK_CREATE_PAGE', 0); // 0,1,2,3,4,5

// PKWK_USE_REDIRECT - When linking outside, Referer is removed.
defined('PKWK_USE_REDIRECT') or define('PKWK_USE_REDIRECT', 0); // 0,1

// PKWK_DISABLE_INLINE_IMAGE_FROM_URI - Disallow using inline-image-tag for URIs
//   Inline-image-tag for URIs may allow leakage of Wiki readers' information
//   (in short, 'Web bug') or external malicious CGI (looks like an image's URL)
//   attack to Wiki readers, but easy way to show images.
defined('PKWK_DISABLE_INLINE_IMAGE_FROM_URI') or define('PKWK_DISABLE_INLINE_IMAGE_FROM_URI', 0);

// PKWK_QUERY_STRING_MAX
//   Max length of GET method, prohibits some worm attack ASAP
//   NOTE: Keep (page-name + attach-file-name) <= PKWK_QUERY_STRING_MAX
defined('PKWK_QUERY_STRING_MAX') or define('PKWK_QUERY_STRING_MAX', 640); // Bytes, 0 = OFF

/////////////////////////////////////////////////
// Experimental features

// Multiline plugin hack (See BugTrack2/84)
// EXAMPLE(with a known BUG):
//   #plugin(args1,args2,...,argsN){{
//   argsN+1
//   argsN+1
//   #memo(foo)
//   argsN+1
//   }}
//   #memo(This makes '#memo(foo)' to this)
defined('PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK') or define('PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK', 0); // 1 = Disabled

/////////////////////////////////////////////////
// Language / Encoding settings
// <language>_<territory> = <ISO 639>_<ISO 3166>
// ja_JP, ko_KR, en_US, zh_TW ...
defined('DEFAULT_LANG') or define('DEFAULT_LANG', 'ja_JP');

// It conforms at the time of server installation location (DEFAULT_LANG).
// (1: Conforming, 0: Language dependence)
// サーバ設置場所(DEFAULT_LANG)の時刻に準拠する。(1:準拠, 0:言語依存)
$use_local_time = 0;

// Effective making function switch (2 Then, it becomes a judgment of 1 and 2.)
// 0) Invalidity
// 1) Judgment with COOKIE['lang']
// 2) Judgment with HTTP_ACCEPT_LANGUAGE
// 3) Considering judgment to HTTP_USER_AGENT
// 4) Considering judgment to HTTP_ACCEPT_CHARSET
// 5) Considering judgment to REMOTE_ADDR
// 機能有効化スイッチ (2 なら、1と2の判定となる)
// 0) 無効
// 1) COOKIE['lang'] での判定
// 2) HTTP_ACCEPT_LANGUAGE での判定
// 3) HTTP_USER_AGENT までの見做し判定
// 4) HTTP_ACCEPT_CHARSET までの見做し判定
// 5) REMOTE_ADDR までの見做し判定
$language_considering_setting_level = 2;

// Please define it when two or more TimeZone such as en_US exists.
// Please refer to lib/timezone.php for the defined character string.
// en_US など、複数のタイムゾーンが存在する場合に定義して下さい。
// 定義する文字列は、lib/timezone.php を参照して下さい。
// defined('DEFAULT_TZ_NAME') or define('DEFAULT_TZ_NAME', 'Asia/Tokyo');

// The view on public holiday applies to installation features.
// 祝日の表示は、設置場所に準ずる (0:設置者視点, 1:閲覧者視点)
$public_holiday_guest_view = 0;

/////////////////////////////////////////////////
// Directory settings I (ended with '/', permission '777')

// You may hide these directories (from web browsers)
// by setting DATA_HOME at index.php.

defined('DATA_DIR')       or define('DATA_DIR',      DATA_HOME . 'wiki/'     ); // Latest wiki texts
defined('DIFF_DIR')       or define('DIFF_DIR',      DATA_HOME . 'diff/'     ); // Latest diffs
defined('BACKUP_DIR')     or define('BACKUP_DIR',    DATA_HOME . 'backup/'   ); // Backups
defined('CACHE_DIR')      or define('CACHE_DIR',     DATA_HOME . 'cache/'    ); // Some sort of caches
defined('UPLOAD_DIR')     or define('UPLOAD_DIR',    DATA_HOME . 'attach/'   ); // Attached files and logs
defined('COUNTER_DIR')    or define('COUNTER_DIR',   DATA_HOME . 'counter/'  ); // Counter plugin's counts
defined('TRACKBACK_DIR')  or define('TRACKBACK_DIR', DATA_HOME . 'trackback/'); // TrackBack logs
defined('REFERER_DIR')    or define('REFERER_DIR',   DATA_HOME . 'trackback/'); // Referer logs
defined('LOG_DIR')        or define('LOG_DIR',       DATA_HOME . 'log/'      ); // Logging file
defined('INIT_DIR')       or define('INIT_DIR',      DATA_HOME . 'init/'     ); // Initial value (Contents)

defined('PLUGIN_DIR')     or define('PLUGIN_DIR',    SITE_HOME . 'plugin/'   ); // Plugin directory
defined('LANG_DIR')       or define('LANG_DIR',      SITE_HOME . 'locale/'   ); // Language file
defined('SITE_INIT_DIR')  or define('SITE_INIT_DIR', SITE_HOME . 'init/'     ); // Initial value (Site)

defined('EXTEND_DIR')     or define('EXTEND_DIR',    SITE_HOME . 'extend/'   ); // Extend directory
defined('EXT_PLUGIN_DIR') or define('EXT_PLUGIN_DIR',EXTEND_DIR. 'plugin/'   ); // Extend Plugin directory
defined('EXT_LANG_DIR')   or define('EXT_LANG_DIR',  EXTEND_DIR. 'locale/'   ); // Extend Language file

/////////////////////////////////////////////////
// Directory settings II (ended with '/')

// Skins / Stylesheets
defined('SKIN_DIR')       or define('SKIN_DIR',  WWW_HOME . 'skin/');	// Skin files (SKIN_DIR/*.skin.php) are needed at
									// ./DATAHOME/SKIN_DIR from index.php.
defined('IMAGE_DIR')      or define('IMAGE_DIR', WWW_HOME . 'image/');	// Static image files

// for Fancy URL
defined('SKIN_URI')       or define('SKIN_URI',  ROOT_URI . 'skin/');	// CSSs(*.css) and JavaScripts(*.js) are needed at
									// ./SKIN_URI from index.php.
defined('IMAGE_URI')      or define('IMAGE_URI', ROOT_URI . 'image/');	// Keep this directory shown via web browsers like
									// ./IMAGE_URI from index.php.

/////////////////////////////////////////////////
// Title of your Wikisite (Name this)
// Also used as RSS feed's channel name etc
$page_title = 'PukiWiki Plus!';

// Specify PukiWiki URL (default: auto)
//$script = 'http://example.com/pukiwiki/';

// Shorten $script: Cut its file name (default: not cut)
//$script_directory_index = 'index.php';

// Site admin's name (CHANGE THIS)
$modifier = 'anonymous';

// Site admin's Web page (CHANGE THIS)
$modifierlink = dirname($_SCRIPT_NAME);

// Default page name
$defaultpage  = 'FrontPage';     // Top / Default page
$whatsnew     = 'RecentChanges'; // Modified page list
$whatsdeleted = 'RecentDeleted'; // Removeed page list
$interwiki    = 'InterWikiName'; // Set InterWiki definition here
$aliaspage    = 'AutoAliasName'; // Set AutoAlias definition here
$glossarypage = 'Glossary';	 // Set Glossary definition here
$menubar      = 'MenuBar';       // Menu
$sidebar      = 'SideBar';       // Side
$navigation   = 'Navigation';
$headarea     = ':Header';
$footarea     = ':Footer';
$protect      = ':login';        // Protect mode

/////////////////////////////////////////////////
// Change default Document Type Definition

// Some web browser's bug, and / or Java apprets may needs not-Strict DTD.
// Some plugin (e.g. paint) set this PKWK_DTD_XHTML_1_0_TRANSITIONAL.

//$pkwk_dtd = PKWK_DTD_XHTML_1_1; // Default
//$pkwk_dtd = PKWK_DTD_XHTML_1_0_STRICT;
//$pkwk_dtd = PKWK_DTD_XHTML_1_0_TRANSITIONAL;
//$pkwk_dtd = PKWK_DTD_HTML_4_01_STRICT;
//$pkwk_dtd = PKWK_DTD_HTML_4_01_TRANSITIONAL;

/////////////////////////////////////////////////
// Always output "nofollow,noindex" attribute

$nofollow = 0; // 1 = Try hiding from search engines

/////////////////////////////////////////////////

// PLUS_ALLOW_SESSION - Allow / Prohibit using Session
defined('PLUS_ALLOW_SESSION') or define('PLUS_ALLOW_SESSION', 1);

// PKWK_ALLOW_JAVASCRIPT - Allow / Prohibit using JavaScript
defined('PKWK_ALLOW_JAVASCRIPT') or define('PKWK_ALLOW_JAVASCRIPT', 1);

// Javascript Async Library Extenstion
$ajax = 1;

// Control of form unloading which you do not intend 
$ctrl_unload = 1;

// LOG
require_once(add_homedir('config-log.ini.php'));

/////////////////////////////////////////////////
// Blocking SPAM
$use_spam_check = array(
	'page_view'		=> 0,
	'page_remote_addr'	=> 0,
	'page_contents'		=> 1,
	'trackback'		=> 1,
	'referer'		=> 0,
);

/////////////////////////////////////////////////
// Spam URI insertion filtering

$spam = 1;	// 1 = On

if ($spam) {
	$spam = array();

	// Threshold and rules for insertion (default)
	$spam['method']['_default'] = array(
		'_comment'    => '_default',
		'quantity'    => 8,
		//'non_uniquri'  =>  3,
		'non_uniqhost' =>  3,
		'area_anchor' => 0,
		'area_bbcode' => 0,
		'uniqhost'    => TRUE,
		'badhost'     => TRUE,
		'asap'         => TRUE, // Stop as soon as possible (quick but less-info)
	);

	// For editing
	// NOTE:
	// Any thresholds may LOCK your contents by
	// "posting one URL" many times.
	// Any rules will lock contents that have NG things already.
	$spam['method']['edit'] = array(
		// Supposed_by_you(n) * Edit_form_spec(2) * Margin(1.5)
		'_comment'    => 'edit',
		//'quantity'    => 60 * 3,
		//'non_uniquri'  =>  5 * 3,
		//'non_uniqhost' => 50 * 3,
		//'area_anchor' => 30 * 3,
		//'area_bbcode' => 15 * 3,
		'uniqhost'    => TRUE,
		'badhost'     => TRUE,
		'asap'        => TRUE,
	);

	//$spam['exitmode'] = 'dump'; // Dump progress
}

/////////////////////////////////////////////////
// TrackBack feature

// Enable Trackback
// 0: off
// 1: on
//    Only the reception of ping.
//    Ping is not transmitted by the automatic operation.
// 2: on
//    Function in the past. Automatic ping transmission.
$trackback = 2;

/////////////////////////////////////////////////
// Referer list feature
// 0: off
// 1: on
// 2: on
//    IGNORE is not having a look displayed.
$referer = 1;

/////////////////////////////////////////////////
// _Disable_ WikiName auto-linking
$nowikiname = 1;

/////////////////////////////////////////////////
// Symbol of not exists WikiName/BracketName
$_symbol_noexists = '?';

/////////////////////////////////////////////////
// AutoLink feature
// Automatic link to existing pages (especially helpful for non-wikiword pages, but heavy)

// Minimum length of page name
// Pukiwiki Plus! Recommended "5"
$autolink = 5; // Bytes, 0 = OFF (try 8)

/////////////////////////////////////////////////
// AutoAlias feature
// Automatic link from specified word, to specifiled URI, page or InterWiki

// Minimum length of alias "from" word
// Pukiwiki Plus! Recommended "4"
$autoalias = 4; // Bytes, 0 = OFF (try 8)

// Limit loading valid alias pairs in AutoAliasName page
$autoalias_max_words = 50; // pairs

// AutoBaseAlias - AutoAlias to each page from its basename automatically
$autobasealias = 0;

// nonlist for AutoBaseAlias
$autobasealias_nonlist = '^\:|(^|\/)template$';

/////////////////////////////////////////////////
// AutoGlossary feature
// Automatic tooltip from specified word

// Minimum length of glossary "from" word
// Pukiwiki Plus! Recommended "2"
$autoglossary = 2; // NChars, 0 = OFF

// Limit loading valid glossary pairs
$autoglossary_max_words = 50; // pairs

/////////////////////////////////////////////////
// Enable Freeze / Unfreeze feature
$function_freeze = 1;

/////////////////////////////////////////////////
// Allow to use 'Do not change timestamp' checkbox
// (0:Disable, 1:For everyone,  2:Only for the administrator)
$notimeupdate = 1;

// Authentication
require_once(add_homedir('auth.ini.php'));

/////////////////////////////////////////////////
// Page-reading feature settings
// (Automatically creating pronounce datas, for Kanji-included page names,
//  to show sorted page-list correctly)

// Enable page-reading feature by calling ChaSen or KAKASHI command
// (1:Enable, 0:Disable)
$pagereading_enable = 0;

// Specify converter as ChaSen('chasen') or KAKASI('kakasi') or None('none')
$pagereading_kanji2kana_converter = 'none';

// Specify Kanji encoding to pass data between PukiWiki and the converter
$pagereading_kanji2kana_encoding = 'EUC'; // Default for Unix
//$pagereading_kanji2kana_encoding = 'SJIS'; // Default for Windows

// Absolute path of the converter (ChaSen)
$pagereading_chasen_path = '/usr/local/bin/chasen';
//$pagereading_chasen_path = 'c:\progra~1\chasen21\chasen.exe';

// Absolute path of the converter (KAKASI)
$pagereading_kakasi_path = '/usr/local/bin/kakasi';
//$pagereading_kakasi_path = 'c:\kakasi\bin\kakasi.exe';

// Page name contains pronounce data (written by the converter)
$pagereading_config_page = ':config/PageReading';

// Page name of default pronouncing dictionary, used when converter = 'none'
$pagereading_config_dict = ':config/PageReading/dict';

/////////////////////////////////////////////////
// Exclude plugin for this site-policy.
$exclude_plugin = array(
	'server',
	'version',
	'versionlist',
);

/////////////////////////////////////////////////
// Exclude Link plugin.
//
// When TrackBack Ping and SPAM Check are processed,
// it is substituted for null plugin.
//
// TrackBack Ping および SPAMチェックの処理の際に、
// null プラグインに置換されます。
$exclude_link_plugin = array(
	'showrss',
	'rssreader',
);

/////////////////////////////////////////////////
// Fuzzy Search (for Japanese EUC-JP Version Only)
// 0: Disabled
// 1: Enabled
$search_fuzzy = 0;

/////////////////////////////////////////////////
// Fast Tracker(Sortable Tracker)
//
$sortable_tracker = 1;

/////////////////////////////////////////////////
// $whatsnew: Max number of RecentChanges
$maxshow = 60;

// $whatsdeleted: Max number of RecentDeleted
// (0 = Disabled)
$maxshow_deleted = 60;

/////////////////////////////////////////////////
// Page names can't be edit via PukiWiki
$cantedit = array( $whatsnew, $whatsdeleted );

/////////////////////////////////////////////////
// HTTP: Output Last-Modified header
$lastmod = 0;

/////////////////////////////////////////////////
// Date format
$date_format = 'Y-m-d';

// Time format
$time_format = 'H:i:s';

/////////////////////////////////////////////////
// Max number of RSS feed
$rss_max = 15;
// Description
$rss_description = 'PukiWiki RecentChanges';

/////////////////////////////////////////////////
// Backup related settings

// Enable backup
$do_backup = 1;

// When a page had been removed, remove its backup too?
$del_backup = 0;

// Bacukp interval and generation
$cycle  = 1;    // Wait N hours between backup (0 = no wait)
$maxage = 360; // Stock latest N backups

// NOTE: $cycle x $maxage / 24 = Minimum days to lost your data
//          1   x   360   / 24 = 15

// Splitter of backup data (NOTE: Too dangerous to change)
defined('PKWK_SPLITTER') or define('PKWK_SPLITTER', '>>>>>>>>>>');

/////////////////////////////////////////////////
// Command executed per update

defined('PKWK_UPDATE_EXEC') or define('PKWK_UPDATE_EXEC', '');
$update_exec = PKWK_UPDATE_EXEC;

// Sample: Namazu (Search engine)
//$target     = '/var/www/wiki/';
//$mknmz      = '/usr/bin/mknmz';
//$output_dir = '/var/lib/namazu/index/';
//define('PKWK_UPDATE_EXEC',
//	$mknmz . ' --media-type=text/pukiwiki' .
//	' -O ' . $output_dir . ' -L ja -c -K ' . $target);

/////////////////////////////////////////////////
// HTTP proxy setting (for TrackBack etc)

// Use HTTP proxy server to get remote data
$use_proxy = 0;

$proxy_host = 'proxy.example.com';
$proxy_port = 8080;

// Do Basic authentication
$need_proxy_auth = 0;
$proxy_auth_user = 'username';
$proxy_auth_pass = 'password';

// Hosts that proxy server will not be needed
$no_proxy = array(
	'localhost',	// localhost
	'127.0.0.0/8',	// loopback
//	'10.0.0.0/8'	// private class A
//	'172.16.0.0/12'	// private class B
//	'192.168.0.0/16'	// private class C
//	'no-proxy.com',
);

////////////////////////////////////////////////
// Mail related settings

// Send mail per update of pages
$notify = 0;

// Send diff only
$notify_diff_only = 1;

// SMTP server (Windows only. Usually specified at php.ini)
$smtp_server = 'localhost';

// Mail recipient (To:) and sender (From:)
$notify_to   = 'to@example.com';	// To:
$notify_from = 'from@example.com';	// From:

// Subject: ($page = Page name wll be replaced)
$notify_subject = '[PukiWiki] $page';

// Mail header
// NOTE: Multiple items must be divided by "\r\n", not "\n".
$notify_header = '';

// No Mail for Remote Host.
$notify_exclude = array(
//	'192.168.0.',
);

/////////////////////////////////////////////////
// Mail: POP / APOP Before SMTP

// Do POP/APOP authentication before send mail
$smtp_auth = 0;

$pop_server = 'localhost';
$pop_port   = 110;
$pop_userid = '';
$pop_passwd = '';

// Use APOP instead of POP (If server uses)
//   Default = Auto (Use APOP if possible)
//   1       = Always use APOP
//   0       = Always use POP
// $pop_auth_use_apop = 1;

/////////////////////////////////////////////////
// Ignore list

// Regex of ignore pages
$non_list = '^\:';

// Search ignored pages
$search_non_list = 1;

/////////////////////////////////////////////////
// Template setting

$auto_template_func = 1;
$auto_template_rules = array(
	'((.+)\/([^\/]+))' => '\2/template'
);

/////////////////////////////////////////////////
// Automatically add fixed heading anchor
$fixed_heading_anchor = 1;

/////////////////////////////////////////////////
// Remove the first spaces from Preformatted text
$preformat_ltrim = 1;

/////////////////////////////////////////////////
// Convert linebreaks into <br />
$line_break = 0;

/////////////////////////////////////////////////
// Use date-time rules (See rules.ini.php)
$usedatetime = 1;

/////////////////////////////////////////////////
// 見出しごとの編集を可能にする 
//
// 見出し行の固有のアンカ自動挿入されているとき
// のみ有効です
$fixed_heading_edited = 0;

/////////////////////////////////////////////////
// ページを任意のフレームに開く時に使う設定
$use_open_uri_in_new_window  = 1;

// 同一サーバーとしてみなすホストのURI
$open_uri_in_new_window_servername = array(
      'http://localhost/',
      'http://localhost.localdomain/',
);
// URIの種類によって開く動作を設定。
// "_blank"で別窓へ表示、falseを指定すると無効
$open_uri_in_new_window_opis  = '_blank';     // pukiwikiの外で同一サーバー内
$open_uri_in_new_window_opisi = false;        // pukiwikiの外で同一サーバー内(InterWikiLink)
$open_uri_in_new_window_opos  = '_blank';     // pukiwikiの外で外部サーバー
$open_uri_in_new_window_oposi = '_blank';     // pukiwikiの外で外部サーバー(InterWikiLink)
// (注意：あえて拡張しやすいようにしていますが、'_blank'以外は指定しないでください)

// User-Agent settings
require_once(add_homedir('profile.ini.php'));

?>
