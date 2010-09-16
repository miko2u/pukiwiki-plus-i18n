<?php
/**
 * Ruby
 */

$switchHash['$'] = PLUGIN_CODE_ESCAPE;            // $ はエスケープ
$switchHash['\''] = PLUGIN_CODE_NONESCAPE_LITERAL; // ' はエスケープしない文字列リテラル
$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義

$switchHash['#'] = PLUGIN_CODE_COMMENT;	// コメントは # から改行まで (例外あり)
$switchHash['='] = PLUGIN_CODE_COMMENT;	// コメントは =begin から =end まで
$switchHash['('] = PLUGIN_CODE_COMMENT;	// コメントは (?# から ) まで (正規表現内)
$code_comment = Array(
					  '#' => Array(
								   Array('/^#[^{]/', "\n", 1),
								   ),
					  '=' => Array(
								   Array('/^=begin/', '=end', 4),
								   ),
					  '(' => Array(
								   Array('/^\(\?#/', ')', 1),
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
  	// 宣言

  	// 制御構文関係
  	'for' => 2,
	'in' => 2,
  	'while' => 2,
  	'do' => 2,
  	'done' => 2,
	'each' => 2,
	'until' => 2,
	'yield' => 2,

	'BEGIN' => 2,
	'END' => 2,
	'begin' => 2,
	'end' => 2,
  	'if' => 2,
	'then' => 2,
  	'else' => 2,
  	'elsif' => 2,
	'unless' => 2,
  	'switch' => 2,
  	
  	'case' => 2,
	'break' => 2,
	'next' => 2,
	'redo' => 2,
	'retry' => 2,
  	'return' => 2,

	'and' => 2,
	'or' => 2,
	'not' => 2,
	'true' => 2,
	'false' => 2,


	// 変数タイプ関係
  	
  	// クラス等
  	'class' => 2,
  	'module' => 2,
  	'def' => 2,
  	'defined' => 2,
  	'undef' => 2,
  	'alias' => 2,
	'self' => 2,
	'super' => 2,
  	
  	// 例外処理 
	'rescue' => 2,
	'ensure' => 2,
	'raise' => 2,

  //'pragma',		// module, import と pragma
  	'include' => 3,
  	'require' => 3,
  //'system',		// 処理系組み込みの奴 __stdcall とか
  );
?>