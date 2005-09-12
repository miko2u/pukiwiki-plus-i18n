<?php
/**
 * Hitachi H8/300h specific syntax for GNU Assembler
 * キーワード定義ファイル
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義
$switchHash[';'] = PLUGIN_CODE_COMMENT;        //  コメントは ; から改行まで
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
  'add'  => 2,
  'adds'  => 2,
  'addx'  => 2,
  'and'  => 2,
  'bld'  => 2,
  'ble'  => 2,
  'blo'  => 2,
  'blt'  => 2,
  'bls'  => 2,
  'cmp'  => 2,
  'dec'  => 2,
  'divxu'  => 2,
  'divxs'  => 2,
  'exts'  => 2,
  'extu'  => 2,
  'inc'  => 2,
  'mov'  => 2,
  'mulxs'  => 2,
  'mulxu'  => 2,
  'neg'  => 2,
  'not'  => 2,
  'or'  => 2,
  'pop'  => 2,
  'push'  => 2,
  'rotx'  => 2,
  'rotx=l'  => 2,
  'rotx=r'  => 2,
  'shal'  => 2,
  'shar'  => 2,
  'shll'  => 2,
  'shlr'  => 2,
  'sub'  => 2,
  'xor'  => 2,
  'andc'  => 2,
  'band'  => 2,
  'bcc'  => 2,
  'bclr'  => 2,
  'bcs'  => 2,
  'beq'  => 2,
  'bf'  => 2,
  'bge'  => 2,
  'bgt'  => 2,
  'bhi'  => 2,
  'bhs'  => 2,
  'biand'  => 2,
  'bild'  => 2,
  'bior'  => 2,
  'bist'  => 2,
  'bixor'  => 2,
  'bmi'  => 2,
  'bne'  => 2,
  'bnot'  => 2,
  'bnp'  => 2,
  'bor'  => 2,
  'bpl'  => 2,
  'bpt'  => 2,
  'bra'  => 2,
  'brn'  => 2,
  'bset'  => 2,
  'bsr'  => 2,
  'btst'  => 2,
  'bst'  => 2,
  'bt'  => 2,
  'bvc'  => 2,
  'bvs'  => 2,
  'bxor'  => 2,
  'cmp'  => 2,
  'daa'  => 2,
  'das'  => 2,
  'eepmov'  => 2,
  'eepmovw'  => 2,
  'inc'  => 2,
  'jmp'  => 2,
  'jsr'  => 2,
  'ldc'  => 2,
  'movfpe'  => 2,
  'movtpe'  => 2,
  'mov'  => 2,
  'nop'  => 2,
  'orc'  => 2,
  'rte'  => 2,
  'rts'  => 2,
  'sleep'  => 2,
  'stc'  => 2,
  'sub'  => 2,
  'trapa'  => 2,
  'xorc'  => 2,


  );
?>
