<?php
/**
 * GNU Assembler
 * キーワード定義ファイル
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可
 
$switchHash['.'] = PLUGIN_CODE_SPECIAL_IDENTIFIRE;  // . から始まる予約語あり

// コメント定義
$switchHash[';'] = PLUGIN_CODE_COMMENT;        //  コメントは ;  から改行まで
$code_comment = Array(
	'/' => Array(
				 Array('/^;/', "\n", 1),
	)
);

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
);

$code_keyword = Array(
  '.long'  => 4,
  '.ascii'  => 4,
  '.asciz'  => 4,
  '.byte'  => 4,
  '.double'  => 4,
  '.float'  => 4,
  '.hword'  => 4,
  '.int'  => 4,
  '.octa'  => 4,
  '.quad'  => 4,
  '.short'  => 4,
  '.single'  => 4,
  '.space'  => 4,
  '.string'  => 4,
  '.word'  => 4,
  '.include'  => 4,
  '.if'  => 4,
  '.else'  => 4,
  '.endif'  => 4,
  '.macro'  => 4,
  '.endm'  => 4,


  );
?>
