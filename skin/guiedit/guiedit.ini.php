<?php
//
//	guiedit - PukiWiki Plugin
//
//	License:
//	  GNU General Public License Version 2 or later (GPL)
//	  http://www.gnu.org/licenses/gpl.html
//
//	Copyright (C) 2006-2007 garand
//	PukiWiki : Copyright (C) 2001-2006 PukiWiki Developers Team
//	FCKeditor : Copyright (C) 2003-2007 Frederico Caldeira Knabben
//      PukiWiki Plus! : Copyright (C) 2009 Katsumi Saito
//
//
//	File:
//	  guiedit.ini.php
//	  プラグインの設定ファイル
//


//	PukiWiki のルートディレクトリ（自動設定）
//	（例）"/bluemoon/pukiwiki/"
global $guiedit_pkwk_root;

//	雛形のリストに入れないページ
global $guiedit_non_list;
$guiedit_non_list = array(
	'RecentDeleted', 'Help', 'FormattingRules', 'SandBox', 'PukiWiki/1.4', 'BracketName', 'WikiName',
    'InterWiki', 'InterWikiName', 'InterWikiSandBox', 'WikiEngines', 'WikiWikiWeb'
);

//	HTML 変換ルール
global $guiedit_line_rules;
$guiedit_line_rules = array(
	"\r"          => '<br />',
	'COLOR\(([^\(\)]*)\){([^}]*)}'	=> '<span style="color:$1">$2</span>',
	'SIZE\(([^\(\)]*)\){([^}]*)}'	=> '<span style="font-size:$1px;line-height:130%">$2</span>',
	'COLOR\(([^\(\)]*)\):((?:(?!COLOR\([^\)]+\)\:).)*)'	=> '<span style="color:$1">$2</span>',
	'SIZE\(([^\(\)]*)\):((?:(?!SIZE\([^\)]+\)\:).)*)'	=> '<span class="size$1">$2</span>',
	'%%%(?!%)((?:(?!%%%).)*)%%%'	=> '<u>$1</u>',
	'%%(?!%)((?:(?!%%).)*)%%'	=> '<strike>$1</strike>',
	"'''(?!')((?:(?!''').)*)'''"	=> '<em>$1</em>',
	"''(?!')((?:(?!'').)*)''"	=> '<strong>$1</strong>',
);

// フェイスマークの HTML 変換ルール
global $guiedit_facemark_rules;
$guiedit_facemark_rules = array(
	'\s(\:\))'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/smile.png" />',
	'\s(\:D)'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/bigsmile.png" />',
	'\s(\:p)'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/huh.png" />',
	'\s(\:d)'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/huh.png" />',
	'\s(XD)'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/oh.png" />',
	'\s(X\()'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/oh.png" />',
	'\s(;\))'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/wink.png" />',
	'\s(;\()'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/sad.png" />',
	'\s(\:\()'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/sad.png" />',
	'&amp;(smile);'	=> ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/smile.png" />',
	'&amp;(bigsmile);'=>' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/bigsmile.png" />',
	'&amp;(huh);'	=> ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/huh.png" />',
	'&amp;(oh);'	=> ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/oh.png" />',
	'&amp;(wink);'	=> ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/wink.png" />',
	'&amp;(sad);'	=> ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/sad.png" />',
	'&amp;(heart);'	=> ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/heart.png" />',
	'&amp;(worried);'=>' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/worried.png" />',
	'&amp;(tear);'  => ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/tear.png" />',
	'&amp;(umm);'   => ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/umm.png" />',
	'&amp;(star);'  => ' <img alt="[$1]" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/star.gif" />',

	'\s(\(\^\^\))'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/smile.png" />',
	'\s(\(\^-\^)'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/bigsmile.png" />',
	'\s(\(\.\.;)'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/oh.png" />',
	'\s(\(\^_-\))'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/wink.png" />',
	'\s(\(--;)'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/sad.png" />',
	'\s(\(\^\^;\))'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/worried.png" />',
	'\s(\(\^\^;)'	=> ' <img alt="$1" src="' . $guiedit_pkwk_root . IMAGE_URI . 'face/worried.png" />',
);

?>
