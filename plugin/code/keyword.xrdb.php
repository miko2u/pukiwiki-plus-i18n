<?php
/**
 * Xrdb キーワード定義ファイル
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可 
// コメント定義
$switchHash['!'] = PLUGIN_CODE_COMMENT;	// コメントは ! から改行まで
$code_comment = Array(
	'!' => Array(
				 Array('/^!/', "\n", 1),
	)
);

$code_css = Array(
);

$code_keyword = Array(
);
?>