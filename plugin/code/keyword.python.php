<?php
/**
 * Python
 */

$switchHash['$'] = PLUGIN_CODE_ESCAPE;            // $ はエスケープ
$switchHash['\''] = PLUGIN_CODE_NONESCAPE_LITERAL; // ' はエスケープしない文字列リテラル
$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義
$switchHash['#'] = PLUGIN_CODE_COMMENT;	// コメントは # から改行まで (例外あり)
$code_comment = Array(
	'#' => Array(
				 Array('/^#[^{]/', "\n", 1),
	)
);

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
  //'operator',		// オペレータ関数
  //'identifier',	// その他の識別子
    'access' => 2,
    'and' => 2,
    'break' => 2,
    'class' => 2,
    'continue' => 2,
    'def' => 2,
    'del' => 2,
    'elif' => 2,
    'else' => 2,
    'expect' => 2,
    'exec' => 2,
    'finally' => 2,
    'for' => 2,
    'form' => 2,
    'global' => 2,
    'if' => 2,
    'import' => 2,
    'in' => 2,
    'is' => 2,
    'lambda' => 2,
    'not' => 2,
    'or' => 2,
    'pass' => 2,
    'print' => 2,
    'raise' => 2,
    'return' => 2,
    'try' => 2,
    'while' => 2,
  //'pragma',		// module, import と pragma
  //'system',		// 処理系組み込みの奴 __stdcall とか
  );
?>