<?php
/**
 * Prolog キーワード定義ファイル
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可 
$switchHash['$'] = PLUGIN_CODE_NONESCAPE_LITERAL;

//$switchHash['['] = PLUGIN_CODE_PAIR_LITERAL;  // ()は文字列リテラル
//$literal_delimiter = ']';

// コメント定義
$switchHash['/'] = PLUGIN_CODE_COMMENT;    //  コメントは /* から */ まで
$switchHash['%'] = PLUGIN_CODE_COMMENT;    // コメントは % から改行まで
$code_comment = Array(
	'/' => Array(
				 Array('/^\/\*/', '*/', 2),
	 ),
	'%' => Array(
				 Array('/^%/', "\n", 1),
	 )
);

$code_css = Array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = Array(
  'abolish' => 2,
  'current_output' => 2,
  'peek_code' => 2,
  'append' => 2,
  'current_predicate' => 2,
  'put_byte' => 2,
  'arg' => 2,
  'current_prolog_flag' => 2,
  'put_char' => 2,
  'asserta' => 2,
  'fail' => 2,
  'put_code' => 2,
  'assertz' => 2,
  'findall' => 2,
  'read' => 2,
  'at_end_of_stream' => 2,
  'float' => 2,
  'read_term' => 2,
  'atom' => 2,
  'flush_output' => 2,
  'repeat' => 2,
  'atom_chars' => 2,
  'functor' => 2,
  'retract' => 2,
  'atom_codes' => 2,
  'get_byte' => 2,
  'set_input' => 2,
  'atom_concat' => 2,
  'get_char' => 2,
  'set_output' => 2,
  'atom_length' => 2,
  'get_code' => 2,
  'set_prolog_flag' => 2,
  'atomic' => 2,
  'halt' => 2,
  'set_stream_position' => 2,
  'bagof' => 2,
  'integer' => 2,
  'setof' => 2,
  'call' => 2,
  'is' => 2,
  'stream_property' => 2,
  'catch' => 2,
  'nl' => 2,
  'sub_atom' => 2,
  'char_code' => 2,
  'nonvar' => 2,
  'throw' => 2,
  'char_conversion' => 2,
  'number' => 2,
  'true' => 2,
  'clause' => 2,
  'number_chars' => 2,
  'unify_with_occurs_check' => 2,
  'close' => 2,
  'number_codes' => 2,
  'var' => 2,
  'compound' => 2,
  'once' => 2,
  'write' => 2,
  'copy_term' => 2,
  'op' => 2,
  'write_canonical' => 2,
  'current_char_conversion' => 2,
  'open' => 2,
  'write_term' => 2,
  'current_input' => 2,
  'peek_byte' => 2,
  'writeq' => 2,
  'current_op' => 2,
  'peek_char' => 2,


  );
?>
