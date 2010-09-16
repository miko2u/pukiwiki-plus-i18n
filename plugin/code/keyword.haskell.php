<?php
/**
 * Haskell キーワード定義ファイル
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義
$switchHash['-'] = PLUGIN_CODE_COMMENT;    //  コメントは -- から 改行まで -->は含まない。
$switchHash['{'] = PLUGIN_CODE_COMMENT;    //  コメントは {- から -}まで
$code_comment = Array(
	'-' => Array(
				 Array('/^--[^>]/', "\n", 1),
	),
	'{' => Array(
				 Array('/^{-/', '-}', 2),
	),
);

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
'bool'=>1,
'case'=>1,
'catch'=>1,
'class'=>1,
'data'=>1,
'do'=>1,
'else'=>1,
'error'=>1,
'false'=>1,
'if'=>1,
'in'=>1,
'infixl'=>1,
'instance'=>1,
'let'=>1,
'main'=>1,
'of'=>1,
'return'=>1,
'then'=>1,
'type'=>1,
'where'=>1,

'Char'=>2,
'Bool'=>2,
'Branch'=>2,
'False'=>2,
'Float'=>2,
'Integer'=>2,
'Leaf'=>2,
'Tree'=>2,
'True'=>2,

'as'=>3,
'hiding'=>3,
'import'=>3,
'module'=>3,
'qualified'=>3,

  );
?>
