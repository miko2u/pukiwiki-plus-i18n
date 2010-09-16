<?php
/**
 * css キーワード定義ファイル
 */

$switchHash['@'] = PLUGIN_CODE_SPECIAL_IDENTIFIRE;  // @ から始まる予約語あり

// コメント定義
$switchHash['/'] = PLUGIN_CODE_COMMENT;        //  コメントは /* から */ まで
$code_comment = Array(
	'/' => Array(
				 Array('/^\/\*/', '*/', 2),
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
'font-family' => 2 ,
'font-style' => 2 ,
'font-variant' => 2 ,
'font-weight' => 2 ,
'font-size' => 2 ,
'font' => 2 ,
'background-color' => 2 ,
'background-image' => 2 ,
'background-repeat' => 2 ,
'background-attachment' => 2 ,
'background-position' => 2 ,
'color' => 2 ,
'background' => 2 ,
'word-spacing' => 2 ,
'letter-spacing' => 2 ,
'border-top-width' => 2 ,
'border-right-width' => 2 ,
'border-left-width' => 2 ,
'border-bottom-width' => 2 ,
'border-width' => 2 ,
'list-style-type' => 2 ,
'list-style-image' => 2 ,
'list-style-position' => 2 ,
'text-decoration' => 2 ,
'vertical-align' => 2 ,
'text-transform' => 2 ,
'text-align' => 2 ,
'text-indent' => 2 ,
'line-height' => 2 ,
'margin-top' => 2 ,
'margin-right' => 2 ,
'margin-bottom' => 2 ,
'margin-left' => 2 ,
'margin' => 2 ,
'padding-top' => 2 ,
'padding-right' => 2 ,
'padding-bottom' => 2 ,
'padding-left' => 2 ,
'padding' => 2 ,
'border-top' => 2 ,
'border-right' => 2 ,
'border-bottom' => 2 ,
'border-left' => 2 ,
'border' => 2 ,
'width' => 2 ,
'height' => 2 ,
'float' => 2 ,
'clear' => 2 ,
'display' => 2 ,
'list-style' => 2 ,
'white-space' => 2 ,
'border-style' => 2 ,
'border-color' => 2 ,
'azimuth' => 2 ,
'border-bottom-color' => 2 ,
'border-bottom-style' => 2 ,
'border-collapse' => 2 ,
'border-left-color' => 2 ,
'border-left-style' => 2 ,
'border-right-color' => 2 ,
'border-right-style' => 2 ,
'border-top-color' => 2 ,
'border-top-style' => 2 ,
'caption-side' => 2 ,
'cell-spacing' => 2 ,
'clip' => 2 ,
'column-span' => 2 ,
'content' => 2 ,
'cue' => 2 ,
'cue-after' => 2 ,
'cue-before' => 2 ,
'cursor' => 2 ,
'direction' => 2 ,
'elevation' => 2 ,
'font-size-adjust' => 2 ,
'left' => 2 ,
'marks' => 2 ,
'max-height' => 2 ,
'max-width' => 2 ,
'min-height' => 2 ,
'min-width' => 2 ,
'orphans' => 2 ,
'overflow' => 2 ,
'page-break-after' => 2 ,
'page-break-before' => 2 ,
'pause' => 2 ,
'pause-after' => 2 ,
'pause-before' => 2 ,
'pitch' => 2 ,
'pitch-range' => 2 ,
'play-during' => 2 ,
'position' => 2 ,
'richness' => 2 ,
'right' => 2 ,
'row-span' => 2 ,
'size' => 2 ,
'speak' => 2 ,
'speak-date' => 2 ,
'speak-header' => 2 ,
'speak-punctuation' => 2 ,
'speak-time' => 2 ,
'speech-rate' => 2 ,
'stress' => 2 ,
'table-layout' => 2 ,
'text-shadow' => 2 ,
'top' => 2 ,
'visibility' => 2 ,
'voice-family' => 2 ,
'volume' => 2 ,
'widows' => 2 ,
'z-index' => 2 ,
'empty-cells' => 2 ,

'@import' => 3,
'@media' => 3,
'@charset' => 3,
'@page' => 3,
'@font-face' => 3,
  );
?>