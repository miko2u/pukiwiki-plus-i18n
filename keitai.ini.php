<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: keitai.ini.php,v 1.25.4 2008/10/03 21:59:00 upk Exp $
// Copyright (C)
//   2005,2008 PukiWiki Plus! Team
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// PukiWiki setting file (Cell phones, PDAs and other thin clients)

/////////////////////////////////////////////////
// 携帯・PDA専用のページを初期ページとして指定する

// $defaultpage = 'm';

/////////////////////////////////////////////////
// スキンファイルの場所
define('SKIN_FILE', SKIN_DIR . 'keitai.skin.php');

/////////////////////////////////////////////////
// 雛形とするページの読み込みを可能にする
$load_template_func = 0;

/////////////////////////////////////////////////
// 検索文字列を色分けする
$search_word_color = 0;

/////////////////////////////////////////////////
// 一覧ページに頭文字インデックスをつける
$list_index = 0;

/////////////////////////////////////////////////
// リスト構造の左マージン
$_ul_left_margin =  0;	// リストと画面左端との間隔(px)
$_ul_margin      = 16;	// リストの階層間の間隔(px)
$_ol_left_margin =  0;	// リストと画面左端との間隔(px)
$_ol_margin      = 16;	// リストの階層間の間隔(px)
$_dl_left_margin =  0;	// リストと画面左端との間隔(px)
$_dl_margin      = 16;	// リストの階層間の間隔(px)
$_list_pad_str   = '';

/////////////////////////////////////////////////
// 大・小見出しから目次へ戻るリンクの文字
$top = '';

/////////////////////////////////////////////////
// 添付ファイルの一覧を常に表示する (負担がかかります)
// ※keitaiスキンにはこの一覧を表示する機能がありません
$attach_link = 0;

/////////////////////////////////////////////////
// 関連するページのリンク一覧を常に表示する(負担がかかります)
// ※keitaiスキンにはこの一覧を表示する機能がありません
$related_link = 0;

// リンク一覧の区切り文字
// ※上同
$related_str = "\n ";

// (#relatedプラグインが表示する) リンク一覧の区切り文字
$rule_related_str = "</li>\n<li>";

/////////////////////////////////////////////////
// 水平線のタグ
$hr = '<hr>';

/////////////////////////////////////////////////
// 脚注機能関連

// 脚注のアンカーに埋め込む本文の最大長
define('PKWK_FOOTNOTE_TITLE_MAX', 0); // Characters

// 脚注のアンカーを相対パスで表示する (0 = 絶対パス)
//  * 相対パスの場合、以前のバージョンのOperaで問題になることがあります
//  * 絶対パスの場合、calendar_viewerなどで問題になることがあります
// (詳しくは: BugTrack/698)
define('PKWK_ALLOW_RELATIVE_FOOTNOTE_ANCHOR', 1);

// 文末の注釈の直前に表示するタグ
$note_hr = '<hr>';

/////////////////////////////////////////////////
// WikiName,BracketNameに経過時間を付加する
$show_passage = 0;

/////////////////////////////////////////////////
// リンク表示をコンパクトにする
// * ページに対するハイパーリンクからタイトルを外す
// * Dangling linkのCSSを外す
$link_compact = 1;

/////////////////////////////////////////////////
// フェイスマークを絵文字に変換する (※i-mode, Vodafone, EzWebなど携帯電話限定)
$usefacemark = 1;

/////////////////////////////////////////////////
// accesskey (SKINで使用)
$accesskey = 'accesskey';

/////////////////////////////////////////////////
// $scriptを短縮
if (preg_match('#([^/]+)$#', $script, $matches)) {
	$script = $matches[1];
}

/////////////////////////////////////////////////
// ブラウザ調整前のデフォルト値

// max_size (SKINで使用)
$max_size = 5;	// SKINで使用, KByte

// cols: テキストエリアのカラム数 rows: 行数
$cols = 22; $rows = 5;	// i_mode


/////////////////////////////////////////////////
// ブラウザに合わせた調整

$ua_name  = $user_agent['name'];
$ua_vers  = $user_agent['vers'];
$ua_agent = $user_agent['agent'];
$matches  = array();

// Browser-name only
switch ($ua_name) {

	// NetFront / Compact NetFront
	//   DoCoMo Net For MOBILE: ｉモード対応HTMLの考え方: ユーザエージェント
	//   http://www.nttdocomo.co.jp/mc-user/i/tag/imodetag.html
	//   DDI POCKET: 機種ラインナップ: AirH"PHONE用ホームページの作成方法
	//   http://www.ddipocket.co.jp/p_s/products/airh_phone/homepage.html
	case 'NetFront':
	case 'CNF':
	case 'DoCoMo':
	case 'Opera': // Performing CNF compatible
		if (preg_match('#\b[cC]([0-9]+)\b#', $ua_agent, $matches)) {
			$max_size = $matches[1];	// Cache max size
		}
		$cols = 22; $rows = 5;	// i_mode
		break;

	// Vodafone (ex. J-PHONE)
	// ボーダフォンライブ！向けウェブコンテンツ開発ガイド [概要編] (Version 1.2.0 P13)
	// http://www.dp.j-phone.com/dp/tool_dl/download.php?docid=110
	// 技術資料: ユーザーエージェントについて
	// http://www.dp.j-phone.com/dp/tool_dl/web/useragent.php
	case 'J-PHONE':
		$matches = array("");
		preg_match('/^([0-9]+)\./', $user_agent['vers'], $matches);
		switch($matches[1]){
		case '3': $max_size =   6; break; // C type: lt   6000bytes
		case '4': $max_size =  12; break; // P type: lt  12Kbytes
		case '5': $max_size = 200; break; // W type: lt 200Kbytes
		}
		$cols = 24; $rows = 20;
		break;

	// UP.Browser
	case 'UP.Browser':
		// UP.Browser for KDDI cell phones
		// http://www.au.kddi.com/ezfactory/tec/spec/xhtml.html ('About 9KB max')
		// http://www.au.kddi.com/ezfactory/tec/spec/4_4.html (User-agent strings)
		if (preg_match('#^KDDI#', $ua_agent)) $max_size =  9;
		break;
}

// Browser-name + version
switch ("$ua_name/$ua_vers") {
	// Restriction For imode:
	//  http://www.nttdocomo.co.jp/mc-user/i/tag/s2.html
	case 'DoCoMo/2.0':	$max_size = min($max_size, 30); break;
}


/////////////////////////////////////////////////
// ユーザ定義ルール
//
//  正規表現で記述してください。?(){}-*./+\$^|など
//  は \? のようにクォートしてください。
//  前後に必ず / を含めてください。行頭指定は ^ を頭に。
//  行末指定は $ を後ろに。

// ユーザ定義ルール(コンバート時に置換)
$line_rules = array(
	'COLOR\(([^\(\)]*)\){([^}]*)}'	=> '<font color="$1">$2</font>',
	'SIZE\(([^\(\)]*)\){([^}]*)}'	=> '$2',	// Disabled
	'COLOR\(([^\(\)]*)\):((?:(?!COLOR\([^\)]+\)\:).)*)'	=> '<font color="$1">$2</font>',
	'SIZE\(([^\(\)]*)\):((?:(?!SIZE\([^\)]+\)\:).)*)'	=> '$2', // Disabled
	'%%%(?!%)((?:(?!%%%).)*)%%%'	=> '<ins>$1</ins>',
	'%%(?!%)((?:(?!%%).)*)%%'	=> '<del>$1</del>',
	"'''(?!')((?:(?!''').)*)'''"	=> '<em>$1</em>',
	"''(?!')((?:(?!'').)*)''"	=> '<strong>$1</strong>',
);


/////////////////////////////////////////////////
// 携帯電話にあわせたフェイスマーク

// $usefacemark = 1ならフェイスマークが置換されます
// 文章内に' XD'などがあった場合にfacemarkに置換されてしまうため、
// 必要のない方は $usefacemarkを0にしてください。

// Browser-name only
$facemark_rules = array();
switch ($ua_name) {

    // Graphic icons for imode HTML 4.0, with Shift-JIS text output
    // http://www.nttdocomo.co.jp/mc-user/i/tag/emoji/e1.html
    // http://www.nttdocomo.co.jp/mc-user/i/tag/emoji/list.html
    case 'DoCoMo':

	$facemark_rules = array(
	// Face marks
	'\s(\:\))'	=>	'&#63893;',	// smile
	'\s(\:D)'	=>	'&#63893;',	// bigsmile
	'\s(\:p)'	=>	'&#xE728;',	// huh
	'\s(\:d)'	=>	'&#xE728;',	// huh
	'\s(XD)'	=>	'&#63895;',	// oh
	'\s(X\()'	=>	'&#63895;',	// oh
	'\s(;\))'	=>	'&#xE729;',	// wink
	'\s(;\()'	=>	'&#63894;',	// sad
	'\s(\:\()'	=>	'&#63894;',	// sad
	'&amp;(smile);'	=>	'&#63893;',
	'&amp;(bigsmile);'=>	'&#63893;',
	'&amp;(huh);'	=>	'&#xE728;',
	'&amp;(oh);'	=>	'&#63895;',
	'&amp;(wink);'	=>	'&#xE729;',
	'&amp;(sad);'	=>	'&#63894;',
	'&amp;(heart);'	=>	'&#63889;',
	'&amp;(worried);'=>	'&#xE722;',
	'&amp;(sweat);' =>	'&#xE722;',
	'&amp;(tear);'	=>	'&#xE72E;',

	// Face marks, Japanese style
	'(\(\^\^\))'	=>	'&#63893;',	// smile
	'(\(\^-\^)'	    =>	'&#63893;',	// smile
	'(\(\^Q\^)'	    =>  '&#xE728;', // huh
	'(\(\.\.;)'   	=>	'&#63895;',	// oh
	'(\(\^_-)'	    =>  '&#xE729;',	// wink
	'(\(\^_-\))'	=>	'&#xE729;',	// wink
	'(\(--;)'	    =>	'&#63894;',	// sad
	'(\(\^\^;\))'	=>	'&#xE722;',	// worried
	'(\(\^\^;)'     =>	'&#xE722;',	// worried
	'(\(T-T\))' 	=>	'&#xE72E;',
	'(\(T-T)'   	=>	'&#xE72E;',
	'(\(\;_\;\))'	=>	'&#xE72E;',
	'(\(\;_\;)' 	=>	'&#xE72E;',

	// Push buttons, 0-9 and sharp
	'&amp;(pb1);'	=>	'&#63879;',
	'&amp;(pb2);'	=>	'&#63880;',
	'&amp;(pb3);'	=>	'&#63881;',
	'&amp;(pb4);'	=>	'&#63882;',
	'&amp;(pb5);'	=>	'&#63883;',
	'&amp;(pb6);'	=>	'&#63884;',
	'&amp;(pb7);'	=>	'&#63885;',
	'&amp;(pb8);'	=>	'&#63886;',
	'&amp;(pb9);'	=>	'&#63887;',
	'&amp;(pb0);'	=>	'&#63888;',
	'&amp;(pb#);'	=>	'&#63877;',

	// Others
	'&amp;(zzz);'	=>	'&#63910;',
	'&amp;(man);'	=>	'&#63829;',
	'&amp;(clock);'	=>	'&#63838;',
	'&amp;(mail);'	=>	'&#63863;',
	'&amp;(mailto);'=>	'&#63859;',
	'&amp;(phone);'	=>	'&#63720;',
	'&amp;(phoneto);'=>	'&#63858;',
	'&amp;(faxto);'	=>	'&#63860;',
	);
	break;

    // Graphic icons for Vodafone (ex. J-PHONE) cell phones
    // http://www.dp.j-phone.com/dp/tool_dl/web/picword_top.php
    case 'J-PHONE':

	$facemark_rules = array(
	// Face marks
	'\s(\:\))'	=>	chr(27).'$Gv'.chr(15),	// '&#57430;',	// smile
	'\s(\:D)'	=>	chr(27).'$Gv'.chr(15),	// '&#57430;',	// bigsmile => smile
	'\s(\:p)'	=>	chr(27).'$E%'.chr(15),	// '&#57605;',	// huh
	'\s(\:d)'	=>	chr(27).'$E%'.chr(15),	// '&#57605;',	// huh
	'\s(XD)'	=>	chr(27).'$Gx'.chr(15),	// '&#57432;',	// oh
	'\s(X\()'	=>	chr(27).'$Gx'.chr(15),	// '&#57432;',	// oh
	'\s(;\))'	=>	chr(27).'$E&'.chr(15),	// '&#57606;',	// winkじゃないけどね(^^; (※目がハート)
	'\s(;\()'	=>	chr(27).'$E&'.chr(15),	// '&#57606;',	// sad
	'\s(\:\()'	=>	chr(27).'$Gy'.chr(15),	// '&#57433;',	// sad
	'&amp;(smile);'	=>	chr(27).'$Gv'.chr(15),	// '&#57430;',
	'&amp;(bigsmile);'=>	chr(27).'$Gw'.chr(15),	// '&#57431;',
	'&amp;(huh);'	=>	chr(27).'$E%'.chr(15),	// '&#57605;',
	'&amp;(oh);'	=>	chr(27).'$Gx'.chr(15),	// '&#57432;',
	'&amp;(wink);'	=>	chr(27).'$E&'.chr(15),	// '&#57606;',	// winkじゃないけどね(^^; (※目がハート)
	'&amp;(sad);'	=>	chr(27).'$Gy'.chr(15),	// '&#57433;',
	'&amp;(heart);'	=>	chr(27).'$GB'.chr(15),	// '&#57378;',
	'&amp;(worried);'=>	chr(27).'$E('.chr(15),	// '&#57608;',
	'&amp;(sweat);'	=>	chr(27).'$E('.chr(15),	// '&#57608;',
	'&amp;(tear);'	=>	chr(27).'$P3'.chr(15),

	// Face marks, Japanese style
	'(\(\^\^\))'	=>	chr(27).'$Gv'.chr(15),	// smile
	'(\(\^-\^)'	=>	chr(27).'$Gv'.chr(15),	// smile
	'(\(\.\.;)'	=>	chr(27).'$Gx'.chr(15),	// oh
	'(\(\^_-\))'	=>	chr(27).'$E&'.chr(15),	// winkじゃないけどね(^^; (※目がハート)
	'(\(--;)'	=>	chr(27).'$E&'.chr(15),	// sad
	'(\(\^\^;\))'	=>	chr(27).'$E('.chr(15),	// worried
	'(\(\^\^;)'	=>	chr(27).'$E('.chr(15),	// worried
	'(\(T-T\))'	=>	chr(27).'$P3'.chr(15),
	'(\(T-T)'	=>	chr(27).'$P3'.chr(15),
	'(\(\;_\;\))'	=>	chr(27).'$P3'.chr(15),
	'(\(\;_\;)'	=>	chr(27).'$P3'.chr(15),

	// Push buttons, 0-9 and sharp
	'&amp;(pb1);'	=>	chr(27).'$F<'.chr(15),	// '&#57884;',
	'&amp;(pb2);'	=>	chr(27).'$F='.chr(15),	// '&#57885;',
	'&amp;(pb3);'	=>	chr(27).'$F>'.chr(15),	// '&#57886;',
	'&amp;(pb4);'	=>	chr(27).'$F?'.chr(15),	// '&#57887;',
	'&amp;(pb5);'	=>	chr(27).'$F@'.chr(15),	// '&#57888;',
	'&amp;(pb6);'	=>	chr(27).'$FA'.chr(15),	// '&#57889;',
	'&amp;(pb7);'	=>	chr(27).'$FB'.chr(15),	// '&#57890;',
	'&amp;(pb8);'	=>	chr(27).'$FC'.chr(15),	// '&#57891;',
	'&amp;(pb9);'	=>	chr(27).'$FD'.chr(15),	// '&#57892;',
	'&amp;(pb0);'	=>	chr(27).'$FE'.chr(15),	// '&#57893;',

	// Others
	'&amp;(zzz);'	=>	chr(27).'$E\\'.chr(15),
	'&amp;(man);'	=>	chr(27).'$G!'.chr(15),
	'&amp;(clock);'	=>	chr(27).'$GF'.chr(15),	// '&#xE026;',
	'&amp;(mail);'	=>	chr(27).'$Fv'.chr(15),
	'&amp;(mailto);'=>	chr(27).'$E#'.chr(15),
	'&amp;(phone);'	=>	chr(27).'$G)'.chr(15),
	'&amp;(phoneto);'=>	chr(27).'$E$'.chr(15),
	'&amp;(faxto);'	=>	chr(27).'$G+'.chr(15),
	);
	break;

    case 'UP.Browser':

	// UP.Browser for KDDI cell phones' built-in icons
	// http://www.au.kddi.com/ezfactory/tec/spec/3.html
	if (preg_match('#^KDDI#', $ua_agent)) {
	$facemark_rules = array(
	// Face marks
	'\s(\:\))'	=>	'<img localsrc="68">',	// smile
	'\s(\:D)'	=>	'<img localsrc="257">',	// bigsmile
	'\s(\:p)'	=>	'<img localsrc="264">',	// huh
	'\s(\:d)'	=>	'<img localsrc="264">',	// huh
	'\s(XD)'	=>	'<img localsrc="260">',	// oh
	'\s(X\()'	=>	'<img localsrc="260">',	// oh
	'\s(;\))'	=>	'<img localsrc="348">',	// wink
	'\s(;\()'	=>	'<img localsrc="259">',	// sad
	'\s(\:\()'	=>	'<img localsrc="259">',	// sad
	'&amp;(smile);'	=>	'<img localsrc="68">',
	'&amp;(bigsmile);'=>	'<img localsrc="257">',
	'&amp;(huh);'	=>	'<img localsrc="264">',
	'&amp;(oh);'	=>	'<img localsrc="260">',
	'&amp;(wink);'	=>	'<img localsrc="348">',
	'&amp;(sad);'	=>	'<img localsrc="259">',
	'&amp;(heart);'	=>	'<img localsrc="415">',
	'&amp;(worried);'=>	'<img localsrc="351">',
	'&amp;(sweat);' =>	'<img localsrc="351">',
	'&amp;(tear);'	=>	'<img localsrc="259">',

	// Face marks, Japanese style
	'(\(\^\^\))'	=>	'<img localsrc="68">',	// smile
	'(\(\^-\^)'	=>	'<img localsrc="68">',	// smile
	'(\(\.\.;)'	=>	'<img localsrc="260">',	// oh
	'(\(\^_-\))'	=>	'<img localsrc="348">',	// wink
	'(\(--;)'	=>	'<img localsrc="259">',	// sad
	'(\(\^\^;\))'	=>	'<img localsrc="351">',	// worried
	'(\(\^\^;)'	=>	'<img localsrc="351">',	// worried
	'(\(T-T\))'	=>	'<img localsrc="259">',
	'(\(T-T)'	=>	'<img localsrc="259">',
	'(\(\;_\;\))'	=>	'<img localsrc="259">',
	'(\(\;_\;)'	=>	'<img localsrc="259">',

	// Push buttons, 0-9 and sharp
	'&amp;(pb1);'	=>	'<img localsrc="180">',
	'&amp;(pb2);'	=>	'<img localsrc="181">',
	'&amp;(pb3);'	=>	'<img localsrc="182">',
	'&amp;(pb4);'	=>	'<img localsrc="183">',
	'&amp;(pb5);'	=>	'<img localsrc="184">',
	'&amp;(pb6);'	=>	'<img localsrc="185">',
	'&amp;(pb7);'	=>	'<img localsrc="186">',
	'&amp;(pb8);'	=>	'<img localsrc="187">',
	'&amp;(pb9);'	=>	'<img localsrc="188">',
	'&amp;(pb0);'	=>	'<img localsrc="325">',
	'&amp;(pb#);'	=>	'<img localsrc="818">',

	// Others
	'&amp;(zzz);'	=>	'<img localsrc="261">',
	'&amp;(man);'	=>	'<img localsrc="80">',	// Face of male
	'&amp;(clock);'	=>	'<img localsrc="46">',
	'&amp;(mail);'	=>	'<img localsrc="108">',
	'&amp;(mailto);'=>	'<img localsrc="784">',
	'&amp;(phone);'	=>	'<img localsrc="85">',
	'&amp;(phoneto);'=>	'<img localsrc="155">',	// An ear receiver
	'&amp;(faxto);'	=>	'<img localsrc="166">',	// A FAX
	);
	}
	break;

}

/////////////////////////////////////////////////
// クッキーを使用できないアドレス
// (通常は、携帯電話のアドレス)
$use_trans_sid_address = array(
	//DoCoMo 2006/10/23 http://www.nttdocomo.co.jp/service/imode/make/content/ip/about/index.html
	'210.153.84.0/24',
	'210.153.86.0/24',
//	'210.153.87.0/24',  // full browser
	'210.136.161.0/24',
	//auKDDI 2006/12/01 http://www.au.kddi.com/ezfactory/tec/spec/ezsava_ip.html
	'210.169.40.0,24',
	'210.196.3.192/26',
	'210.196.5.192/26',
	'210.230.128.0/24',
	'210.230.141.192/26',
	'210.234.105.32/29',
	'210.234.108.64/26',
	'210.251.1.192/26',
	'210.251.2.0/27',
	'211.5.1.0/24',
	'211.5.2.128/25',
	'211.5.7.0/24',
	'218.222.1.0/24',
	'61.117.0.0/24',
	'61.117.1.0/24',
	'61.117.2.0/26',
	'61.202.3.0/24',
	'219.108.158.0/26',
	'219.125.148.0/24',
	'222.5.63.0/24',
	'222.7.56.0/24',
	'222.5.62.128/25',
	'222.7.57.0/24',
	'59.135.38.128/25',
	'219.108.157.0/25',
	// Vodafone 2006/06/02 http://developers.softbankmobile.co.jp/dp/tech_svc/web/ip.php
	'202.179.204.0/24',
	'210.146.7.192/26',
	'210.146.60.192/26',
	'210.151.9.128/26',
	'210.169.176.0/24',
	'210.175.1.128/25',
	'210.228.189.0/24',
	'211.8.159.128/25',
	// Willcom 2006/11/20 http://www.willcom-inc.com/ja/service/contents_service/club_air_edge/for_phone/ip/
	'61.198.129.0/24',
//	'61.198.130.0/24', //del 06/11/20
	'61.198.132.0/24', //add 08/10/02
	'61.198.133.0/24', //add 08/10/02
	'61.198.134.0/24', //add 08/10/02
	'61.198.135.0/24', //add 08/10/02
	'61.198.136.0/24', //add 08/10/02
	'61.198.137.0/24', //add 08/10/02
	'61.198.140.0/24',
	'61.198.141.0/24',
	'61.198.142.0/24',
	'61.198.160.0/24', //add 08/10/02
	'61.198.161.0/24',
	'61.198.162.0/24', //add 08/10/02
	'61.198.164.0/24', //add 08/10/02
	'61.198.165.0/24', //add 06/11/13
	'61.198.166.0/24', //add 06/11/13
	'61.198.168.0/24', //add 06/11/13
	'61.198.169.0/24', //add 06/11/13
	'61.198.170.0/24', //add 06/11/13
	'61.198.171.0/24', //add 08/10/02
	'61.198.174.0/24', //add 08/10/02
	'61.198.175.0/24', //add 08/10/02
	'61.198.248.0/24', //add 06/11/13
	'61.198.249.0/24',
	'61.198.250.0/24',
	'61.198.251.0/24', //add 08/10/02
	'61.198.253.0/24',
	'61.198.254.0/24',
	'61.198.255.0/24',
	'61.198.163.0/24',
	'61.204.0.0/24',
	'61.204.2.0/24',
	'61.204.3.0/25',
	'61.204.4.0/24',
	'61.204.5.0/24',
	'61.204.6.0/25',
	'125.28.0.0/21',
	'125.28.8.0/24',
	'125.28.11.0/24',
	'125.28.12.0/24',
	'125.28.13.0/24',
	'125.28.14.0/24',
	'125.28.16.0/24',  //add 06/11/13
	'125.28.17.0/24',  //add 06/11/13
	'210.168.246.0/24',
	'210.168.247.0/24',
	'210.169.92.0/24', //add 08/10/02
	'210.169.93.0/24', //add 08/10/02
	'210.169.94.0/24', //add 08/10/02
	'210.169.95.0/24', //add 08/10/02
	'210.169.96.0/24', //add 08/10/02
	'210.169.97.0/24', //add 08/10/02
	'210.169.98.0/24', //add 08/10/02
	'210.169.99.0/24', //add 08/10/02
	'211.18.232.0/24',
	'211.18.233.0/24',
	'211.18.234.0/24', //add 06/11/13
	'211.18.235.0/24',
	'211.18.236.0/24',
	'211.18.237.0/24',
	'211.18.238.0/24',
	'211.18.239.0/24',
	'219.108.0.0/21',
	'219.108.8.0/24',  //add 06/11/13
	'219.108.9.0/24',  //add 06/11/13
	'219.108.10.0/24', //add 06/11/13
	'219.108.14.0/24',
//	'219.108.15.0/24', //del 06/11/20
	'221.119.0.0/21',
	'221.119.8.0/24',
	'221.119.9.0/24',
	// jig browser
	'210.143.108.0/24',
);

unset($matches, $ua_name, $ua_vers, $ua_agent, $special_rules);

?>
