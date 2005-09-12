<?php
/**
 * Java
 */

$switchHash['#'] = PLUGIN_CODE_SPECIAL_IDENTIFIRE;  // # から始まる予約語あり

// コメント定義
$switchHash['/'] = PLUGIN_CODE_COMMENT;        //  コメントは /* から */ までと // から改行まで
$code_comment = Array(
	'/' => Array(
				 Array('/^\/\*/', '*/', 2),
				 Array('/^\/\//', "\n", 1),
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
  	'instanceof' => 1,

  	// 宣言
  	'abstract' => 2,
  	'extends' => 2,
  	'final' => 2,
  	'implements' => 2,
  	'native' => 2,
  	'private' => 2,
  	'protected' => 2,
  	'public' => 2,
  	'static' => 2,
  	'strictfp' => 2,
  	'synchronized' => 2,
  	'transient' => 2,
  	'volatile' => 2,
  	
  	// 制御構文関係
  	'for' => 2,
  	'while' => 2,
  	'do' => 2,
  	
  	'if' => 2,
  	'else' => 2,
  	'switch' => 2,
  	
  	'goto' => 2,
  	
  	'case' => 2,
  	'default' => 2,
  	'break' => 2,
  	'continue' => 2,
  	'return' => 2,
  	
		// 変数タイプ関係
  	'void' => 2,
  	'boolean' => 2,
  	'char' => 2,
  	'byte' => 2,
  	'short' => 2,
  	'int' => 2,
  	'long' => 2,
  	'float' => 2,
  	'double' => 2,
  	
  	'const' => 2,
  	
  	// クラス等
  	'class' => 2,
  	'interface' => 2,
  	'super' => 2,
  	'this' => 2,
  	
  	// 
  	'try' => 2,
  	'throw' => 2,
  	'throws' => 2,
  	'catch' => 2,
  	'finally' => 2,
  	
  	'new' => 2,

  	'import' => 3,
  	'package' => 3,
  );
?>