<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: default.ini.php,v 1.25.13 2009/12/27 15:38:00 upk Exp $
// Copyright (C)
//   2005-2006,2009 PukiWiki Plus! Team
//   2003-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// PukiWiki setting file (user agent:default)
@include(add_homedir('default.usr.ini.php'));
/////////////////////////////////////////////////
// Skin file
if (isset($_COOKIE['tdiary_theme'])) {
	defined('TDIARY_THEME') or define('TDIARY_THEME', $_COOKIE['tdiary_theme']);
}

if (defined('TDIARY_THEME')) {
	defined('SKIN_FILE_DEFAULT') or define('SKIN_FILE_DEFAULT', add_skindir('tdiary'));
} else {
	if (defined('PLUS_THEME')) {
		defined('SKIN_FILE_DEFAULT') or define('SKIN_FILE_DEFAULT', add_skindir(PLUS_THEME));
	} else {
		defined('SKIN_FILE_DEFAULT') or define('SKIN_FILE_DEFAULT', add_skindir('default'));
	}
}
$skin_file = (isset($_COOKIE['skin_file'])) ? $_COOKIE['skin_file'] : SKIN_FILE_DEFAULT;

/////////////////////////////////////////////////
// メニューバー/サイドバーを常に表示する(1:する 0:しない)
$always_menu_displayed = 0;

/////////////////////////////////////////////////
// 雛形とするページの読み込みを可能にする(1:する 0:しない)
$load_template_func = 0;

/////////////////////////////////////////////////
// 元ページのリンクを自動的に先頭につける(1:つける 0:つけない)
$load_refer_related = 0;

/////////////////////////////////////////////////
// 検索文字列を色分けする(1:する 0:しない)
$search_word_color = 1;

/////////////////////////////////////////////////
// 一覧ページに頭文字インデックスをつける(1:つける 0:つけない)
$list_index = 1;

/////////////////////////////////////////////////
// 特殊シンボル
$_symbol_paraedit = '<i class="icon-edit" style="margin-left:4px"></i>';
$_symbol_paraguiedit = '<img src="'. IMAGE_URI.'plus/paraguiedit.png" width="9" height="10" alt="Edit(GUI)" title="Edit(GUI)" />';
$_symbol_extanchor = '<img src="'.IMAGE_URI.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\'$1\', \'$2\');" />';
$_symbol_innanchor = '';

/////////////////////////////////////////////////
// 先頭・最後へジャンプ
$_msg_content_back_to_top = '';
//$_msg_content_back_to_top = '<div class="jumpmenu"><a href="#header">▲</a>&nbsp;<a href="#footer">▼</a></div>';

/////////////////////////////////////////////////
// テキストエリアのカラム数
$cols = 80;

/////////////////////////////////////////////////
// テキストエリアの行数
$rows = 20;

/////////////////////////////////////////////////
// 大・小見出しから目次へ戻るリンクの文字
$top = $_msg_content_back_to_top;

/////////////////////////////////////////////////
// 添付ファイルの一覧を常に表示する (負担がかかります)
$attach_link = 1;

/////////////////////////////////////////////////
// 関連するページのリンク一覧を常に表示する(負担がかかります)
$related_link = 1;

// リンク一覧の区切り文字
$related_str = "\n ";

// (#relatedプラグインが表示する) リンク一覧の区切り文字
$rule_related_str = "</li>\n<li>";

/////////////////////////////////////////////////
// 水平線のタグ
$hr = '<hr class="full_hr" />';

/////////////////////////////////////////////////
// 脚注機能関連

// 脚注のアンカーに埋め込む本文の最大長
define('PKWK_FOOTNOTE_TITLE_MAX', 16); // Characters

// 脚注のアンカーを相対パスで表示する (0 = 絶対パス)
//  * 相対パスの場合、以前のバージョンのOperaで問題になることがあります
//  * 絶対パスの場合、calendar_viewerなどで問題になることがあります
// (詳しくは: BugTrack/698)
define('PKWK_ALLOW_RELATIVE_FOOTNOTE_ANCHOR', 1);

// 文末の脚注の直前に表示するタグ
$note_hr = '<hr class="note_hr" />';

/////////////////////////////////////////////////
// WikiName,BracketNameに経過時間を付加する
$show_passage = 1;

/////////////////////////////////////////////////
// リンク表示をコンパクトにする
// * ページに対するハイパーリンクからタイトルを外す
// * Dangling linkのCSSを外す
$link_compact = 0;

/////////////////////////////////////////////////
// フェイスマークを使用する
$usefacemark = 1;

/////////////////////////////////////////////////
// ユーザ定義ルール
//
//  正規表現で記述してください。?(){}-*./+\$^|など
//  は \? のようにクォートしてください。
//  前後に必ず / を含めてください。行頭指定は ^ を頭に。
//  行末指定は $ を後ろに。
//
/////////////////////////////////////////////////
// ユーザ定義ルール(コンバート時に置換)
$line_rules = array(
	'COLOR\(([^\(\)]*)\){([^}]*)}'	=> '<span style="color:$1">$2</span>',
	'SIZE\(([^\(\)]*)\){([^}]*)}'	=> '<span style="font-size:$1px">$2</span>',
	'COLOR\(([^\(\)]*)\):((?:(?!COLOR\([^\)]+\)\:).)*)'	=> '<span style="color:$1">$2</span>',
	'SIZE\(([^\(\)]*)\):((?:(?!SIZE\([^\)]+\)\:).)*)'	=> '<span class="size$1">$2</span>',
	'SUP{([^}]*)}' => '<span style="font-size:60%;vertical-align:super;">$1</span>',
	'SUB{([^}]*)}' => '<span style="font-size:60%;vertical-align:sub;">$1</span>',
	'%%%(?!%)((?:(?!%%%).)*)%%%'	=> '<ins>$1</ins>',
	'%%(?!%)((?:(?!%%).)*)%%'	=> '<del>$1</del>',
	"'''(?!')((?:(?!''').)*)'''"	=> '<em>$1</em>',
	"''(?!')((?:(?!'').)*)''"	=> '<strong>$1</strong>',
);

// PHP5.4対策
//if (function_exists('hsc')) {
//	foreach($line_rules as $src=>$dst) {
//		$dst = str_replace("'", '#039;', $src);
//	}
//}

/////////////////////////////////////////////////
// フェイスマーク定義ルール(コンバート時に置換)

// $usefacemark = 1ならフェイスマークが置換されます
// 文章内にXDなどが入った場合にfacemarkに置換されてしまうので
// 必要のない方は $usefacemarkを0にしてください。

$facemark_rules = array(
	// Face marks
	'\s(\:\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/smile.png" />',
	'\s(\:D)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/bigsmile.png" />',
	'\s(\:p)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/huh.png" />',
	'\s(\:d)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/huh.png" />',
	'\s(XD)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/oh.png" />',
	'\s(X\()'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/oh.png" />',
	'\s(;\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/wink.png" />',
	'\s(;\()'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/sad.png" />',
	'\s(\:\()'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/sad.png" />',
	'&amp;(smile);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/smile.png" />',
	'&amp;(bigsmile);'=>' <img alt="[$1]" src="' . IMAGE_URI . 'face/bigsmile.png" />',
	'&amp;(huh);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/huh.png" />',
	'&amp;(oh);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/oh.png" />',
	'&amp;(wink);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/wink.png" />',
	'&amp;(sad);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/sad.png" />',
	'&amp;(heart);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/heart.png" />',
	'&amp;(worried);'=>' <img alt="[$1]" src="' . IMAGE_URI . 'face/worried.png" />',
	'&amp;(sweat);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/worried.png" />',
	'&amp;(tear);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/tear.png" />',
	'&amp;(umm);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/umm.png" />',
	'&amp;(star);'	=> ' <img alt="[$1]" src="' . IMAGE_URI . 'face/star.gif" />',

	// Face marks, Japanese style
	'\((\^\^\))'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/smile.png" />',
	'\((\^-\^)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/bigsmile.png" />',
	'\((\^Q\^)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/huh.png" />',
	'\((\.\.;)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/oh.png" />',
	'\((\^_-)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/wink.png" />',
	'\((\^_-\))'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/wink.png" />',
	'\((--;)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/sad.png" />',
	'\((\^\^;)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/worried.png" />',
	'\((\^\^;\))'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/worried.png" />', // Plus! not patched BugTrack2/144
	'\((\T-T)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'\((\T-T\))'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'\((\;_;)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'\((\;_;\))'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'\((__;)'	=> ' <img alt="&#40;$1" src="' . IMAGE_URI . 'face/umm.png" />',

	// Push buttons, 0-9 and sharp (Compatibility with cell phones)
	'&amp;(pb1);'	=> '[1]',
	'&amp;(pb2);'	=> '[2]',
	'&amp;(pb3);'	=> '[3]',
	'&amp;(pb4);'	=> '[4]',
	'&amp;(pb5);'	=> '[5]',
	'&amp;(pb6);'	=> '[6]',
	'&amp;(pb7);'	=> '[7]',
	'&amp;(pb8);'	=> '[8]',
	'&amp;(pb9);'	=> '[9]',
	'&amp;(pb0);'	=> '[0]',
	'&amp;(pb#);'	=> '[#]',

	// Other icons (Compatibility with cell phones)
	'&amp;(zzz);'	=> '[zzz]',
	'&amp;(man);'	=> '[man]',
	'&amp;(clock);'	=> '[clock]',
	'&amp;(mail);'	=> '[mail]',
	'&amp;(mailto);'=> '[mailto]',
	'&amp;(phone);'	=> '[phone]',
	'&amp;(phoneto);'=>'[phoneto]',
	'&amp;(faxto);'	=> '[faxto]',
);

/////////////////////////////////////////////////
// クッキーを使用できないアドレス
// (通常、デスクトップでは存在しない)
$use_trans_sid_address = array(
);

/////////////////////////////////////////////////
@include(add_homedir('default.usr.ini.php'));
?>
