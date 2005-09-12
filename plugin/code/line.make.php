<?php
/**
 * make キーワード定義ファイル
 * 行指向モード用
 */


// 識別子開始文字
for ($i = ord('a'); $i <= ord('z'); $i++)
	 $switchHash[chr($i)] = PLUGIN_CODE_POST_IDENTIFIRE;
for ($i = ord('A'); $i <= ord('Z'); $i++)
	 $switchHash[chr($i)] = PLUGIN_CODE_POST_IDENTIFIRE;
$switchHash['.'] = PLUGIN_CODE_POST_IDENTIFIRE;
$post_identifire = ':';

	 
$switchHash["\t"] = PLUGIN_CODE_IDENTIFIRE_CHAR;   // tab
//$switchHash['.']  = PLUGIN_CODE_IDENTIFIRE_CHAR;   // 
$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義
$switchHash['#'] = PLUGIN_CODE_COMMENT_CHAR;	// コメントは # から改行まで

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  'execute'       // 実行命令
  );

$code_keyword = Array(
  'else' => 2,
  'endif' => 2,
  'if' => 2,
  'ifdef' => 2,
  'ifeq' => 2,
  'ifndef' => 2,
  'ifneq' => 2,
  'include' => 2,
  'sinclude' => 2,

  "\t" => 5,
  '.' => 3,

);
?>