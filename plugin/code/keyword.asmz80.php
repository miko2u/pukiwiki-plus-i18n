<?php
/**
 * Zilog Z80
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
  'adc' => 2,
  'add' => 2,
  'and' => 2,
  'bit' => 2,
  'ccf' => 2,
  'cp' => 2,
  'cpd' => 2,
  'cpdr' => 2,
  'cpi' => 2,
  'cpir' => 2,
  'cpl' => 2,
  'daa' => 2,
  'di' => 2,
  'djnz' => 2,
  'ei' => 2,
  'exx' => 2,
  'halt' => 2,
  'im' => 2,
  'in' => 2,
  'ind' => 2,
  'ini' => 2,
  'indr' => 2,
  'inir' => 2,
  'jp' => 2,
  'jr' => 2,
  'ld' => 2,
  'ldd' => 2,
  'lddr' => 2,
  'ldi' => 2,
  'ldir' => 2,
  'neg' => 2,
  'nop' => 2,
  'or' => 2,
  'otdr' => 2,
  'otir' => 2,
  'out' => 2,
  'outd' => 2,
  'outi' => 2,
  'res' => 2,
  'rl' => 2,
  'rla' => 2,
  'rlc' => 2,
  'rlca' => 2,
  'rld' => 2,
  'rr' => 2,
  'rra' => 2,
  'rrc' => 2,
  'rrca' => 2,
  'rrd' => 2,
  'sbc' => 2,
  'scf' => 2,
  'set' => 2,
  'sla' => 2,
  'sra' => 2,
  'srl' => 2,
  'sub' => 2,
  'xor' => 2,
  'push' => 2,
  'pop' => 2,
  'call' => 2,
  'ret' => 2,
  'reti' => 2,
  'retn' => 2,
  'inc' => 2,
  'dec' => 2,
  'ex' => 2,
  'rst' => 2,
  'push' => 2,
  'pop' => 2,
  'call' => 2,
  'ret' => 2,
  'reti' => 2,
  'retn' => 2,
  'rst' => 2,
  'inc' => 2,
  'dec' => 2,
  'ex' => 2,
  '.org' => 4,
  '.globl' => 4,
  '.db' => 4,
  '.dw' => 4,
  '.ds' => 4,
  '.byte' => 4,
  '.word' => 4,
  '.blkb' => 4,
  '.blkw' => 4,
  '.ascii' => 4,
  '.asciz' => 4,
  '.module' => 4,
  '.title' => 4,
  '.sbttl' => 4,
  '.even' => 4,
  '.odd' => 4,
  '.area' => 4,
  '.page' => 4,
  '.setdp' => 4,
  '.radix' => 4,
  '.include' => 4,
  '.if' => 4,
  '.else' => 4,
  '.endif' => 4,


  );
?>
