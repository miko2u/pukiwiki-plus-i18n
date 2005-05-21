<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: default.ini.php,v 1.23.3 2005/05/16 13:25:43 miko Exp $
// Copyright (C)
//   2005      PukiWiki Plus! Team
//   2003-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// PukiWiki setting file (user agent:default)

/////////////////////////////////////////////////
// Skin file
if (defined('TDIARY_THEME')) { 
//	define('SKIN_FILE', DATA_HOME . SKIN_DIR . 'tdiary.skin.php');
	define('SKIN_FILE_DEFAULT', DATA_HOME . SKIN_DIR . 'tdiary.skin.php'); 
} else {
//	define('SKIN_FILE', DATA_HOME . SKIN_DIR . 'pukiwiki.skin.php');
	define('SKIN_FILE_DEFAULT', DATA_HOME . SKIN_DIR . 'default.skin.php');
}
$skin_file = SKIN_FILE_DEFAULT;

/////////////////////////////////////////////////
// 雛形とするページの読み込みを可能にする
$load_template_func = 0;

/////////////////////////////////////////////////
// 検索文字列を色分けする
$search_word_color = 1;

/////////////////////////////////////////////////
// 一覧ページに頭文字インデックスをつける
$list_index = 1;

/////////////////////////////////////////////////
// 特殊シンボル
$_symbol_paraedit = '<img src="./image/plus/paraedit.png" width="9" height="9" alt="Edit" title="Edit" />';
$_symbol_extanchor = '<img src="./image/plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\'$1\', \'$2\');" />';
$_symbol_innanchor = '<img src="./image/plus/inn.png" alt="" title="" class="inn" onclick="return open_uri(\'$1\', \'$2\');" />';

/////////////////////////////////////////////////
// リスト構造の左マージン
$_ul_left_margin = 0;   // リストと画面左端との間隔(px)
$_ul_margin = 16;       // リストの階層間の間隔(px)
$_ol_left_margin = 0;   // リストと画面左端との間隔(px)
$_ol_margin = 16;       // リストの階層間の間隔(px)
$_dl_left_margin = 0;   // リストと画面左端との間隔(px)
$_dl_margin = 16;        // リストの階層間の間隔(px)
$_list_pad_str = ' class="list%d" style="padding-left:%dpx;margin-left:%dpx"';

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
	'(\(\^\^\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/smile.png" />',
	'(\(\^-\^)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/bigsmile.png" />',
	'(\(\^Q\^)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/huh.png" />',
	'(\(\.\.;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/oh.png" />',
	'(\(\^_-)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/wink.png" />',
	'(\(\^_-\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/wink.png" />',
	'(\(--;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/sad.png" />',
	'(\(\^\^;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/worried.png" />',
	'(\(\^\^;\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/worried.png" />',
	'(\(\T-T)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'(\(\T-T\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'(\(\;_;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'(\(\;_;\))'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/tear.png" />',
	'(\(__;)'	=> ' <img alt="$1" src="' . IMAGE_URI . 'face/umm.png" />',

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

?>
