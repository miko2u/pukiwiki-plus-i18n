<?php
/**
 * Delphi
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可 
$switchHash['\''] = PLUGIN_CODE_NONESCAPE_LITERAL;

// コメント定義
$switchHash['('] = PLUGIN_CODE_COMMENT;       // コメントは (* から *) まで
$switchHash['{'] = PLUGIN_CODE_COMMENT;       // コメントは { から } まで
$switchHash['/'] = PLUGIN_CODE_COMMENT;       // コメントは // から改行まで

$code_comment = Array(
	'(' => Array(
				 Array('/^\(\*/', '*)', 2),
		),
	'{' => Array(
				 Array('/^{/', '}', 1),
		),
	'/' => Array(
				 Array('/^\/\//', "\n", 1),
		),
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
    // 制御
    'begin' => 2,
    'case' => 2,
    'do' => 2,
    'downto' => 2,
    'else' => 2,
    'end' => 2,
    'except' => 2,
    'finally' => 2,
    'for' => 2,
    'goto' => 2,
    'label' => 2,
    'if' => 2,
    'of' => 2,
    'on' => 2,
    'raise' => 2,
    'at' => 2,
    'repeat' => 2,
    'then' => 2,
    'to' => 2,
    'try' => 2,
    'until' => 2,
    'while' => 2,
    'with' => 2,

    // 型
    'array' => 2,
    'class' => 2,
    'const' => 2,
    'constructor' => 2,
    'destructor' => 2,
    'function' => 2,
    'packed' => 2,
    'procedure' => 2,
    'property' => 2,
    'record' => 2,
    'set' => 2,
    'string' => 2,
    'var' => 2,

    // 変数
    'inherited' => 2,
    'nil' => 2,

    // 演算
    'and' => 2,
    'as' => 2,
    'div' => 2,
    'in' => 2,
    'is' => 2,
    'mod' => 2,
    'not' => 2,
    'or' => 2,
    'shl' => 2,
    'shr' => 2,
    'xor' => 2,

    // pragma
    'abstract' => 2,
    'default' => 2,
    'dynamic' => 2,
    'external' => 2,
    'forward' => 2,
    'overload' => 2,
    'override' => 2,
    'read' => 2,
    'reintroduce' => 2,
    'stdcall' => 2,
    'stored' => 2,
    'virtual' => 2,
    'write' => 2,

    // system
    'asm' => 2,
    'assembler' => 2,
    'exports' => 2,
    'name' => 2,

    'interface' => 2,
    'implementation' => 2,
    'initialization' => 2,
    'finalization' => 2,

    'pascal' => 2,
    'program' => 2,
    'library' => 2,
    'unit' => 2,
    'uses' => 2,

    'type' => 2,
    'private' => 2,
    'protected' => 2,
    'public' => 2,
    'published' => 2,

    // dfm
    'object' => 2,

    // 見たことない
    'absolute' => 2,
    'automated' => 2,
    'cdecl' => 2,
    'dispid' => 2,
    'file' => 2,
    'index' => 2,
    'inline' => 2,
    'message' => 2,
    'nodefault' => 2,
    'register' => 2,
    'resident' => 2,
    'threadvar' => 2,
  //'pragma',		// module, import と pragma
  //'system',		// 処理系組み込みの奴 __stdcall とか
  );
?>