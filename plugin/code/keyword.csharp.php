<?php
/**
 * C#
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
	'abstract' => 2,
	'as' => 2,
	'base' => 2,
	'bool' => 2,
	'break' => 2,
	'byte' => 2,
	'case' => 2,
	'catch' => 2,
	'char' => 2,
	'checked' => 2,
	'class' => 2,
	'const' => 2,
	'continue' => 2,
	'decimal' => 2,
	'default' => 2,
	'delegate' => 2,
	'do' => 2,
	'double' => 2,
	'else' => 2,
	'enum' => 2,
	'event' => 2,
	'explicit' => 2,
	'extern' => 2,
	'false' => 2,
	'finally' => 2,
	'fixed' => 2,
	'float' => 2,
	'for' => 2,
	'foreach' => 2,
	'goto' => 2,
	'if' => 2,
	'implicit' => 2,
	'in' => 2,
	'int' => 2,
	'interface' => 2,
	'internal' => 2,
	'is' => 2,
	'lock' => 2,
	'long' => 2,
	'namespace' => 2,
	'new' => 2,
	'null' => 2,
	'object' => 2,
	'operator' => 2,
	'out' => 2,
	'override' => 2,
	'params' => 2,
	'private' => 2,
	'protected' => 2,
	'public' => 2,
	'readonly' => 2,
	'ref' => 2,
	'return' => 2,
	'sbyte' => 2,
	'sealed' => 2,
	'short' => 2,
	'sizeof' => 2,
	'stackalloc' => 2,
	'static' => 2,
	'string' => 2,
	'struct' => 2,
	'switch' => 2,
	'this' => 2,
	'throw' => 2,
	'true' => 2,
	'try' => 2,
	'typeof' => 2,
	'uint' => 2,
	'ulong' => 2,
	'unchecked' => 2,
	'unsafe' => 2,
	'ushort' => 2,
	'using' => 2,
	'virtual' => 2,
	'volatile' => 2,
	'void' => 2,
	'while' => 2,

	'#define'=>3,
	'#undef'=>3,
	'#if'=>3,
	'#elif'=>3,
	'#else'=>3,
	'#endif'=>3,
	'#error'=>3,
	'#warning'=>3,
	'#region'=>3,
	'#endregion'=>3,
	'#line'=>3,
  );
?>
