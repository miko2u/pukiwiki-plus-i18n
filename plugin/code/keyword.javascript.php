<?php
/**
 * Java Script キーワード定義ファイル
 */

// コメント定義
$switchHash["/"] = COMMENT;        //  コメントは /* から */ までと // から改行まで
$code_comment = Array(
	"/" => Array(
		"/^\/\/.*\\n/",
		"/^\/\*(.|\n)*?\*\//",
	)
);

// アウトライン用
if($mkoutline){
  $switchHash["{"] = BLOCK_START;
  $switchHash["}"] = BLOCK_END;
}

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
);

$code_keyword = Array(
  'if'  => 2,
  'else'  => 2,
  'while'  => 2,
  'for'  => 2,
  'break'  => 2,
  'continue'  => 2,
  'switch'  => 2,
  'case'  => 2,
  'default'  => 2,
  'new'  => 2,
  'in'  => 2,
  'this'  => 2,
  'var'  => 2,
  'const'  => 2,
  'return'  => 2,
  'with'  => 2,
  'true'  => 2,
  'false'  => 2,
  'function'  => 2,

  );
?>