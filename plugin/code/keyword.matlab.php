<?php
/**
 * Matlab キーワード定義ファイル
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義
$switchHash['%']  = PLUGIN_CODE_COMMENT;    // コメントは % から改行まで
$code_comment = Array(
	'%' => Array(
				 Array('/^%/', "\n", 1),
	)
);

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
  'return' => 2,
  'case' => 2,
  'switch' => 2,
  'else' => 2,
  'elseif' => 2,
  'end' => 2,
  'if' => 2,
  'otherwise' => 2,
  'do' => 2,
  'for' => 2,
  'while' => 2,
  'contained' => 2,
  'oneline' => 2,
  'break' => 2,
  'zeros' => 2,
  'default' => 2,
  'margin' => 2,
  'round' => 2,
  'ones' => 2,
  'rand' => 2,
  'ceil' => 2,
  'floor' => 2,
  'size' => 2,
  'clear' => 2,
  'zeros' => 2,
  'eye' => 2,
  'mean' => 2,
  'std' => 2,
  'cov' => 2,
  'error' => 2,
  'eval' => 2,
  'function' => 2,
  'abs' => 2,
  'acos' => 2,
  'atan' => 2,
  'asin' => 2,
  'cos' => 2,
  'cosh' => 2,
  'exp' => 2,
  'log' => 2,
  'prod' => 2,
  'sum' => 2,
  'log10' => 2,
  'max' => 2,
  'min' => 2,
  'sign' => 2,
  'sin' => 2,
  'sqrt' => 2,
  'tan' => 2,
  'reshape' => 2,
  );
?>