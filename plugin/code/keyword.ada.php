<?php
/**
 * Ada
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可 
$switchHash['\''] = PLUGIN_CODE_NONESCAPE_LITERAL;
// コメント定義
$switchHash['-'] = PLUGIN_CODE_COMMENT;    // コメントは -- から改行まで
$code_comment = Array(
	'-' => Array(
				 Array('/^--/', "\n", 1),
	)
);

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
'abort' => 2,
'abs' => 2,
'accept' => 2,
'access' => 2,
'all' => 2,
'and' => 2,
'array' => 2,
'at' => 2,
'begin' => 2,
'body' => 2,
'case' => 2,
'constant' => 2,
'declare' => 2,
'delay' => 2,
'delta' => 2,
'digits' => 2,
'do' => 2,
'else' => 2,
'elsif' => 2,
'end' => 2,
'entry' => 2,
'exception' => 2,
'exit' => 2,
'for' => 2,
'function' => 2,
'generic' => 2,
'goto' => 2,
'if' => 2,
'in' => 2,
'is' => 2,
'limited' => 2,
'loop' => 2,
'mod' => 2,
'new' => 2,
'not' => 2,
'null' => 2,
'of' => 2,
'or' => 2,
'others' => 2,
'out' => 2,
'package' => 2,
'pragma' => 2,
'private' => 2,
'procedure' => 2,
'raise' => 2,
'range' => 2,
'record' => 2,
'rem' => 2,
'renames' => 2,
'return' => 2,
'reverse' => 2,
'select' => 2,
'separate' => 2,
'subtype' => 2,
'task' => 2,
'terminate' => 2,
'then' => 2,
'type' => 2,
'use' => 2,
'when' => 2,
'while' => 2,
'with' => 2,
'xor' => 2,

  );
?>