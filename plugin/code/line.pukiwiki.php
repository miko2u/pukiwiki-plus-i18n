<?php
/**
 *キーワード定義ファイル
 */

$switchHash["\n"] = CARRIAGERETURN;  // 行頭の空白行の整形済出力抑制
$switchHash['#'] = SPECIAL_IDENTIFIRE;  // # から始まる予約語あり
$switchHash['&'] = SPECIAL_IDENTIFIRE;  // & から始まる予約語あり
$switchHash['*'] = IDENTIFIRE_CHAR;  // 見出し
$switchHash[','] = IDENTIFIRE_CHAR;  // 表
$switchHash['|'] = IDENTIFIRE_CHAR;  // 表
$switchHash[' '] = IDENTIFIRE_CHAR;  // 整形済出力
$switchHash['-'] = MULTILINE;        // 箇条書
$switchHash['+'] = MULTILINE;        // 箇条書
$switchHash[':'] = MULTILINE;        // 箇条書
$switchHash['<'] = MULTILINE;        // 引用
$switchHash['>'] = MULTILINE;        // 引用
// 複数行の終端記号
$multilineEOL = Array(
'#','*',',','|',' ','-','+',':','>','<',"\n");

/*
// 識別子開始文字
for ($i = ord("a"); $i <= ord("z"); $i++)
	 $switchHash[chr($i)] = 0;
for ($i = ord("A"); $i <= ord("Z"); $i++)
	 $switchHash[chr($i)] = 0;
	 $switchHash["_"] = 0;
*/

$capital = true;                        // 予約語の大文字小文字を区別しない
$mkoutline = $option["outline"] = false; // アウトラインモード不可 

// コメント定義
$switchHash["/"] = COMMENT_WORD;        //  コメントは // から改行まで
$commentpattern = '//';
/*
$code_comment = Array(
	"/" => Array(
		"/^\/\/.*\\n/",
	)
);
*/

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  'header',       // 見出し
  'table',        // 表
  'list',         // 箇条書
  'pre',          // 整形済出力
  'quote',        // 引用
  );

$code_keyword = Array(
"#contents" => 2,
"#related" => 2,
"#amazon" => 2,
"#aname" => 2,
"#article" => 2,
"#attach" => 2,
"#back" => 2,
"#br" => 2,
"#bugtrack" => 2,
"#bugtrack_list" => 2,
"#calendar" => 2,
"#calendar2" => 2,
"#calendar_edit" => 2,
"#calendar_read" => 2,
"#calendar_viewer" => 2,
"#clear" => 2,
"#comment" => 2,
"#counter" => 2,
"#freeze" => 2,
"#hr" => 2,
"#img" => 2,
"#include" => 2,
"#includesubmenu" => 2,
"#insert" => 2,
"#lookup" => 2,
"#ls" => 2,
"#ls2" => 2,
"#memo" => 2,
"#menu" => 2,
"#navi" => 2,
"#newpage" => 2,
"#norelated" => 2,
"#online" => 2,
"#paint" => 2,
"#pcomment" => 2,
"#popular" => 2,
"#random" => 2,
"#recent" => 2,
"#ref" => 2,
"#server" => 2,
"#setlinebreak" => 2,
"#showrss" => 2,
"#topicpath" => 2,
"#tracker" => 2,
"#tracker_list" => 2,
"#version" => 2,
"#versionlist" => 2,
"#vote" => 2,
"#code" => 2,
"&amazon" => 2,
"&aname" => 2,
"&color" => 2,
"&counter" => 2,
"&new" => 2,
"&online" => 2,
"&ref" => 2,
"&ruby" => 2,
"&size" => 2,
"&topicpath" => 2,
"&tracker" => 2,
"&version" => 2,


 '*' => 5,     // 見出し
 ',' => 6,     // 表
 '|' => 6,     // 表
 '-' => 7,     // 箇条書
 '+' => 7,     // 箇条書
 ':' => 7,     // 箇条書
 ' ' => 8,     // 整形済出力
 '<' => 9,     // 引用
 '>' => 9,     // 引用

  );
?>