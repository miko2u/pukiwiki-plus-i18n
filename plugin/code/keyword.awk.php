<?php
/**
 * AWK
 */

$switchHash['$'] = PLUGIN_CODE_ESCAPE;            // $ はエスケープ
$switchHash['\''] = PLUGIN_CODE_NONESCAPE_LITERAL; // ' はエスケープしない文字列リテラル

// コメント定義
$switchHash['#'] = PLUGIN_CODE_COMMENT;	// コメントは # から改行まで (例外あり)
$code_comment = Array(
	'#' => Array(
				 Array('/^#[^{]/', "\n", 1),
	)
);

// アウトライン用
if($mkoutline){
  $switchHash['{'] = PLUGIN_CODE_BLOCK_START;
  $switchHash['}'] = PLUGIN_CODE_BLOCK_END;
}

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
'ARGC' => 2,
'ARGIND' => 2,
'ARGV' => 2,
'BEGIN' => 2,
'CONVFMT' => 2,
'END' => 2,
'ENVIRON' => 2,
'ERRNO' => 2,
'FIELDWIDTHS' => 2,
'FILENAME' => 2,
'FNR' => 2,
'FS' => 2,
'IGNORECASE' => 2,
'NF' => 2,
'NR' => 2,
'OFMT' => 2,
'OFS' => 2,
'ORS' => 2,
'RLENGTH' => 2,
'RS' => 2,
'RSTART' => 2,
'SUBSEP' => 2,
'atan2' => 2,
'break' => 2,
'close' => 2,
'continue' => 2,
'cos' => 2,
'ctime' => 2,
'delete' => 2,
'else' => 2,
'exit' => 2,
'exp' => 2,
'for' => 2,
'gsub' => 2,
'if' => 2,
'index' => 2,
'int' => 2,
'length' => 2,
'log' => 2,
'match' => 2,
'next' => 2,
'print' => 2,
'printf' => 2,
'rand' => 2,
'return' => 2,
'sin' => 2,
'split' => 2,
'sprintf' => 2,
'sqrt' => 2,
'srand' => 2,
'sub' => 2,
'substr' => 2,
'system' => 2,
'time' => 2,
'tolower' => 2,
'toupper' => 2,
'while' => 2,

  );
?>