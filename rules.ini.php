<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: rules.ini.php,v 1.3.2 2004/07/13 13:12:15 miko Exp $
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
	'&amp;_now;'    => format_date(UTIME),
	'&amp;_date;'   => get_date($date_format),
	'&amp;_time;'   => get_date($time_format),
);

/////////////////////////////////////////////////
// ユーザ定義ルール
//
//  正規表現で記述してください。?(){}-*./+\$^|など
//  は \? のようにクォートしてください。
//  前後に必ず / を含めてください。行頭指定は ^ を頭に。
//  行末指定は $ を後ろに。
//
/////////////////////////////////////////////////
// ユーザ定義ルール(直接ソースを置換)
$str_rules = array(
	'now\?' 	=> format_date(UTIME),
	'date\?'	=> get_date($date_format),
	'time\?'	=> get_date($time_format),
	'&now;' 	=> format_date(UTIME),
	'&date;'	=> get_date($date_format),
	'&time;'	=> get_date($time_format),
	'&page;'	=> array_pop(explode('/', $vars['page'])),
	'&fpage;'	=> $vars['page'],
	'&t;'   	=> "\t",
);

?>
