<?php
/**
 * diff キーワード定義ファイル
 * 行指向モード用
 */

$switchHash['!'] = IDENTIFIRE_CHAR;   // changed
$switchHash['|'] = IDENTIFIRE_CHAR;   // changed
$switchHash['+'] = IDENTIFIRE_WORD;   // added
$switchHash['>'] = IDENTIFIRE_CHAR;   // added
$switchHash[')'] = IDENTIFIRE_CHAR;   // added
$switchHash['-'] = IDENTIFIRE_WORD;   // removed
$switchHash['<'] = IDENTIFIRE_CHAR;   // removed
$switchHash['('] = IDENTIFIRE_CHAR;   // removed
$switchHash['*'] = IDENTIFIRE_CHAR;   // control
$switchHash['\\']= IDENTIFIRE_CHAR;   // control
$switchHash['@'] = IDENTIFIRE_CHAR;   // control

$mkoutline = $option["outline"] = false; // アウトラインモード不可 
$mkcomment = $option["comment"] = false; // コメント無し 
$linemode = true; // 行内を解析しない

// 
$code_identifire = array(
	 '-' => Array(
		  '---',
		 ),
	 '+' => Array(
		  '+++',
		 ),
	 );


// コメント定義
$switchHash["#"] = COMMENT;	// コメントは # から改行まで

$code_css = Array(
					   'changed', //
					   'added',   //
					   'removed', //

					   'system', //
);

$code_keyword = Array(
						   '!' => 1,
						   '|' => 1,

						   '+' => 2,
						   '>' => 2,
						   ')' => 2,
						   '/' => 2,

						   '-' => 3,
						   '<' => 3,
						   '(' => 3,
						   '\\' => 3,

						   '*' => 4,
						   '\\' => 4,
						   '@' => 4,
						   '---' => 4,
						   '+++' => 4,
);
?>