<?php
/**
 *キーワード定義ファイル
 */


$capital = true;                      // 予約語の大文字小文字を区別しない
$mkoutline = $option["outline"] = false; // アウトラインモード不可 

// コメント定義
$switchHash["'"] = COMMENT_WORD;   // コメントは ' から改行まで
$switchHash["R"] = CHAR_COMMENT;   // コメントは REM から改行まで
$commentpattern = 'REM';
/*
$code_comment = Array(
	"'" => Array(
		"/^'.*\n/",
		),
	"R" => Array(
		"/^REM.*\n/",
	)
);
*/
$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
"beep" => 2,
"call" => 2,
"chain" => 2,
"circle" => 2,
"close" => 2,
"com" => 2,
"const" => 2,
"declare" => 2,
"defdbl" => 2,
"deflng" => 2,
"defstr" => 2,
"do" => 2,
"draw" => 2,
"environ" => 2,
"error" => 2,
"field" => 2,
"for" => 2,
"function" => 2,
"gosub" => 2,
"if" => 2,
"then" => 2,
"else" => 2,
"next" => 2,
"end" => 2,
"input" => 2,
"ioctl" => 2,
"kill" => 2,
"line" => 2,
"lock" => 2,
"lprint" => 2,
"lset" => 2,
"name" => 2,
"error" => 2,
"option" => 2,
"out" => 2,
"palette" => 2,
"pen" => 2,
"pmap" => 2,
"preset" => 2,
"print#" => 2,
"pset" => 2,
"randomize" => 2,
"redim" => 2,
"restore" => 2,
"return" => 2,
"rset" => 2,
"seek" => 2,
"case" => 2,
"shell" => 2,
"sound" => 2,
"stop" => 2,
"sub" => 2,
"system" => 2,
"troff" => 2,
"type" => 2,
"view" => 2,
"while" => 2,
"width" => 2,
"write" => 2,

"abs" => 2,
"atn" => 2,
"cint" => 2,
"sin" => 2,
"cos" => 2,
"tan" => 2,
"csrlin" => 2,
"cvdmbf" => 2,
"cvl" => 2,
"cvsmbf" => 2,
"erdev" => 2,
"err" => 2,
"fileattr" => 2,
"fre" => 2,
"inp" => 2,
"int" => 2,
"len" => 2,
"lof" => 2,
"lpos" => 2,
"pen" => 2,
"pos" => 2,
"sadd" => 2,
"seek" => 2,
"sgn" => 2,
"spc" => 2,
"stick" => 2,
"tab" => 2,
"ubound" => 2,
"valptr" => 2,
"varptr" => 2,
"chr" => 2,
"date" => 2,
"erdev" => 2,
"inkey" => 2,
"ioctl" => 2,
"laft" => 2,
"mid" => 2,
"mkd" => 2,
"mkl" => 2,
"mks" => 2,
"right" => 2,
"space" => 2,
"string" => 2,
"ucase" => 2,
"paint" => 2,
"cls" => 2,

  );
?>