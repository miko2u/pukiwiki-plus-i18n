<?php
/**
 * JSP (Java Server Pages)
 */

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

  'contained' => 2,
  'include' => 2,
  'forward' => 2,
  'getProperty' => 2,
  'plugin' => 2,
  'setProperty' => 2,
  'useBean' => 2,
  'param' => 2,
  'params' => 2,
  'fallback' => 2,
  'contained' => 2,
  'id' => 2,
  'scope' => 2,
  'class' => 2,
  'type' => 2,
  'beanName' => 2,
  'page' => 2,
  'flush' => 2,
  'name' => 2,
  'value' => 2,
  'property' => 2,
  'contained' => 2,
  'code' => 2,
  'codebase' => 2,
  'name' => 2,
  'archive' => 2,
  'align' => 2,
  'height' => 2,
  'contained' => 2,
  'width' => 2,
  'hspace' => 2,
  'vspace' => 2,
  'jreversion' => 2,
  'nspluginurl' => 2,
  'iepluginurl' => 2,

  );
?>