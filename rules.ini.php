<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: rules.ini.php,v 1.10.5 2007/06/10 02:08:40 miko Exp $
// Copyright (C)
//   2005-2007 Customized/Patched by Miko.Hoshina
//   2003-2007 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// PukiWiki setting file
if (!defined('DATA_HOME')) { exit; }

/////////////////////////////////////////////////
// フィルタルール
//
//  正規表現で記述してください。?(){}-*./+\$^|など
//  は \? のようにクォートしてください。
//  前後に必ず / を含めてください。行頭指定は ^ を頭に。
//  行末指定は $ を後ろに。
//
/////////////////////////////////////////////////
// フィルタルール(直接ソースを置換)
$filter_rules = array(
	"^(TITLE):(.*)$" => "",
	"#tboff(.*)$" => "",
	"#skin(.*)$" => "",
);

/////////////////////////////////////////////////
// 日時置換ルール (閲覧時に置換)
// $usedatetime = 1なら日時置換ルールが適用されます
// 必要のない方は $usedatetimeを0にしてください。
$datetime_rules = array(
	'&amp;_now;'	=> format_date(UTIME),
	'&amp;_date;'	=> get_date($date_format),
	'&amp;_time;'	=> get_date($time_format),
);

/////////////////////////////////////////////////
// ユーザ定義ルール(保存時に置換)
//  正規表現で記述してください。?(){}-*./+\$^|など
//  は \? のようにクォートしてください。
//  前後に必ず / を含めてください。行頭指定は ^ を頭に。
//  行末指定は $ を後ろに。
//
$str_rules = array(
	// Compat 1.3.x
	//'now\?' 	=> format_date(UTIME),
	//'date\?'	=> get_date($date_format),
	//'time\?'	=> get_date($time_format),

	'&now;' 	=> format_date(UTIME),
	'&date;'	=> get_date($date_format),
	'&time;'	=> get_date($time_format),
	'&page;'	=> get_short_pagename($vars['page']),
	'&fpage;'	=> $vars['page'],
	'&t;'   	=> "\t",
);

?>
