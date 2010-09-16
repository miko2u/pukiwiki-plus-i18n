<?php
/**
 * D Language
 */

$switchHash['#'] = PLUGIN_CODE_SHARP_IDENTIFIRE; // # から始まる予約語あり
$switchHash['\`'] = NONESCAPE_LITERAL;  // ` はエスケープしない文字列リテラル

// コメント定義
$switchHash['/'] = PLUGIN_CODE_COMMENT;    //  コメントは /* から */ までと // から改行までと、/+ から +/ まで。
$code_comment = Array(
	'/' => Array(
				 Array('/^\/\*/', '*/', 2),
				 Array('/^\/\+/', '+/', 2),
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

    'opNeg' => 1,
    'opCom' => 1,
    'opPostinc' => 1,
    'opPostDec' => 1,

    'opAdd' => 1,
    'opSub' => 1,
    'opSub_r' => 1,
    'opMul' => 1,
    'opDiv' => 1,
    'opDiv_r' => 1,
    'opMod' => 1,
    'opMod_r' => 1,
    'opAnd' => 1,
    'opOr' => 1,
    'opXor' => 1,
    'opShl' => 1,
    'opShl_r' => 1,
    'opShr' => 1,
    'opShr_r' => 1,
    'opUShr' => 1,
    'opUShr_r' => 1,
    'opCat' => 1,
    'opCat_r' => 1,
    'opEquals' => 1,
    'opCmp' => 1,
    'addass' => 1,
    'opAddAssign' => 1,
    'opSubAssign' => 1,
    'opMulAssign' => 1,
    'opDivAssign' => 1,
    'opModAssign' => 1,
    'opAndAssign' => 1,
    'opOrAssign' => 1,
    'opXorAssign' => 1,
    'opShlAssign' => 1,
    'opShrAssign' => 1,
    'opUShrAssign' => 1,
    'opCatAssign' => 1,
    'opCall' => 1,
    'opIndex' => 1,
    'opSlice' => 1,

    'opApply' => 1,

    'abstract' => 2,
    'alias' => 2,
    'align' => 2,
    'asm' => 2,
    'assert' => 2,
    'auto' => 2,

    'bit' => 2,
    'body' => 2,
    'break' => 2,
    'byte' => 2,
    'bswap' => 2,
    'bool' => 2,

    'case' => 2,
    'cast' => 2,
    'catch' => 2,
    'cent' => 2,
    'char' => 2,
    'class' => 2,
    'cfloat' => 2,
    'cdouble' => 2,
    'creal' => 2,
    'const' => 2,
    'continue' => 2,

    'debug' => 2,
    'default' => 2,
    'delegate' => 2,
    'delete' => 2,
    'deprecated' => 2,
    'do' => 2,
    'double' => 2,
    'dchar' => 2,

    'else' => 2,
    'enum' => 2,
    'export' => 2,
    'extern' => 2,

    'false' => 2,
    'final' => 2,
    'finally' => 2,
    'float' => 2,
    'for' => 2,
    'foreach' => 2,
    'function' => 2,

    'super' => 2,
    'null' => 2,
    'new' => 2,
    'short' => 2,
    'int' => 2,
    'long' => 2,
    'ifloat' => 2,
    'idouble' => 2,
    'ireal' => 2,
    'if' => 2,
    'switch' => 2,
    'synchronized' => 2,
    'return' => 2,
    'goto' => 2,
    'struct' => 2,
    'interface' => 2,
    'static' => 2,
    'override' => 2,
    'in' => 2,
    'out' => 2,
    'inout' => 2,
    'private' => 2,
    'protected' => 2,
    'public' => 2,
    'invariant' => 2,
    'real' => 2,
    'instance' => 2,
    'is' => 2,

    'template' => 2,
    'this' => 2,
    'throw' => 2,
    'true' => 2,
    'try' => 2,
    'typedef' => 2,

    'ubyte' => 2,
    'ucent' => 2,
    'uint' => 2,
    'ulong' => 2,
    'union' => 2,
    'ushort' => 2,

    'version' => 2,
    'void' => 2,
    'volatile' => 2,

    'wchar' => 2,
    'while' => 2,
    'with' => 2,

    '#line' => 3,
    'import' => 3,
    'module' => 3,

    'allocate' => 4,
    '_asm' => 4,
    '__asm' => 4,
    '_based' => 4,
    '__based' => 4,
    'cdecl' => 4,
    '_cdecl' => 4,
    '__cdecl' => 4,
    '_declspec' => 4,
    '__declspec' => 4,
    'dllexport' => 4,
    'dllimport' => 4,
    '_except' => 4,
    '__except' => 4,
    '_fastcall' => 4,
    '__fastcall' => 4,
    '_finally' => 4,
    '__finally' => 4,
    '_inline' => 4,
    '__inline' => 4,
    '_int8' => 4,
    '__int8' => 4,
    '_int16' => 4,
    '__int16' => 4,
    '_int32' => 4,
    '__int32' => 4,
    '_int64' => 4,
    '__int64' => 4,
    '_leave' => 4,
    '__leave' => 4,
    '_multiple_inheritance' => 4,
    '__multiple_inheritance' => 4,
    'naked' => 4,
    'nothrow' => 4,
    'property' => 4,
    'selectany' => 4,
    '_single_inheritance' => 4,
    '__single_inheritance' => 4,
    '_stdcall' => 4,
    '__stdcall' => 4,
    'thread' => 4,
    '_try' => 4,
    '__try' => 4,
    'uuid' => 4,
    '_uuidof' => 4,
    '__uuidof' => 4,
    '_virtual_inheritance' => 4,
    '__virtual_inheritance' => 4,
  );
?>