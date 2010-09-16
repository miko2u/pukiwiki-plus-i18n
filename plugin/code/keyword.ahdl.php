<?php
/**
 * AHDL キーワード定義ファイル
 */

$capital = true;                      // 予約語の大文字小文字を区別しない
$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義
$switchHash['-'] = PLUGIN_CODE_COMMENT;    // コメントは -- から改行まで
$switchHash['%']  = PLUGIN_CODE_COMMENT;    // コメントは % から改行まで
$code_comment = Array(
	'-' => Array(
				 Array('/^--/', "\n", 1),
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
  'assert' => 2,
  'begin' => 2,
  'bidir' => 2,
  'bits' => 2,
  'buried' => 2,
  'case' => 2,
  'clique' => 2,
  'connected_pins' => 2,
  'constant' => 2,
  'defaults' => 2,
  'define' => 2,
  'design' => 2,
  'device' => 2,
  'else' => 2,
  'elsif' => 2,
  'end' => 2,
  'for' => 2,
  'function' => 2,
  'generate' => 2,
  'gnd' => 2,
  'help_id' => 2,
  'if' => 2,
  'in' => 2,
  'include' => 2,
  'input' => 2,
  'is' => 2,
  'machine' => 2,
  'node' => 2,
  'of' => 2,
  'options' => 2,
  'others' => 2,
  'output' => 2,
  'parameters' => 2,
  'returns' => 2,
  'states' => 2,
  'subdesign' => 2,
  'table' => 2,
  'then' => 2,
  'title' => 2,
  'to' => 2,
  'tri_state_node' => 2,
  'variable' => 2,
  'vcc' => 2,
  'when' => 2,
  'with' => 2,
  'carry' => 2,
  'cascade' => 2,
  'dffe' => 2,
  'dff' => 2,
  'exp' => 2,
  'global' => 2,
  'jkffe' => 2,
  'jkff' => 2,
  'latch' => 2,
  'lcell' => 2,
  'mcell' => 2,
  'memory' => 2,
  'opendrn' => 2,
  'soft' => 2,
  'srffe' => 2,
  'srff' => 2,
  'tffe' => 2,
  'tff' => 2,
  'tri' => 2,
  'wire' => 2,
  'x' => 2,
  'lpm_and' => 2,
  'lpm_bustri' => 2,
  'lpm_clshift' => 2,
  'lpm_constant' => 2,
  'lpm_decode' => 2,
  'lpm_inv' => 2,
  'lpm_mux' => 2,
  'lpm_or' => 2,
  'lpm_xor' => 2,
  'busmux' => 2,
  'mux' => 2,
  'divide' => 2,
  'lpm_abs' => 2,
  'lpm_add_sub' => 2,
  'lpm_compare' => 2,
  'lpm_counter' => 2,
  'lpm_mult' => 2,
  'altdpram' => 2,
  'csfifo' => 2,
  'dcfifo' => 2,
  'scfifo' => 2,
  'csdpram' => 2,
  'lpm_ff' => 2,
  'lpm_latch' => 2,
  'lpm_shiftreg' => 2,
  'lpm_ram_dq' => 2,
  'lpm_ram_io' => 2,
  'lpm_rom' => 2,
  'lpm_dff' => 2,
  'lpm_tff' => 2,
  'clklock' => 2,
  'pll' => 2,
  'ntsc' => 2,
  'not' => 2,
  'and' => 2,
  'nand' => 2,
  'or' => 2,
  'nor' => 2,
  'xor' => 2,
  'xnor' => 2,
  'mod' => 2,
  'div' => 2,
  'log2' => 2,
  'used' => 2,
  'ceil' => 2,
  'floor' => 2,

  );
?>