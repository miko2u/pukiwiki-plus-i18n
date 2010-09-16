<?php
/**
 * Scheme
 */

$switchHash['\'']  = PLUGIN_CODE_CHARACTOR;        // Lisp Scheme は ' 文字リテラルではない

// コメント定義
$switchHash[';']  = PLUGIN_CODE_COMMENT;  // コメントは ; から改行まで
$code_comment = Array(
	';' => Array(
				 Array('/^;/', "\n", 1),
	)
);

// アウトライン用
if($mkoutline){
  $switchHash['('] = PLUGIN_CODE_BLOCK_START;
  $switchHash[')'] = PLUGIN_CODE_BLOCK_END;
}

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
'and' => 2,
'begin' => 2,
'call-with-current-continuation' => 2,
'call-with-input-file' => 2,
'call-with-output-file' => 2,
'call/cc' => 2,
'case' => 2,
'cond' => 2,
'define' => 2,
'delay' => 2,
'do' => 2,
'else' => 2,
'for-each' => 2,
'if' => 2,
'lambda' => 2,
'let' => 2,
'let*' => 2,
'let-syntax' => 2,
'letrec' => 2,
'letrec-syntax'	 => 2,
'map' => 2,
'or' => 2,
'syntax' => 2,
'syntax-rules' => 2,
  );
?>