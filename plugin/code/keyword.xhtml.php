<?php
/**
 * xHTML キーワード定義ファイル
 */

$mkoutline = $option['outline'] = false; // アウトラインモード不可 

// コメント定義
$switchHash['<'] = PLUGIN_CODE_COMMENT;
$code_comment = Array(
	'<' => Array(
				 Array('/^<\!--/', '-->', 3),
				 Array('/^<\?/', '?>', 2),
	)
);

$code_css = array(
  'operator',		// オペレータ関数
  'identifier',	// その他の識別子
  'pragma',		// module, import と pragma
  'system',		// 処理系組み込みの奴 __stdcall とか
  );

$code_keyword = array(
  'html' => 2,
  'head' => 2,
  'body' => 2,
  'title' => 2,
  'base' => 2,
  'link' => 2,
  'meta' => 2,
  'p' => 2,
  'pre' => 2,
  'h1' => 2,
  'h2' => 2,
  'h3' => 2,
  'h4' => 2,
  'h5' => 2,
  'h6' => 2,
  'br' => 2,
  'hr' => 2,
  'address' => 2,
  'a' => 2,
  'bdo' => 2,
  'frameset' => 2,
  'frame' => 2,
  'ifreme' => 2,
  'noframes' => 2,
  'abbr' => 2,
  'acronym' => 2,
  'q' => 2,
  'blockquote' => 2,
  'cite' => 2,
  'em' => 2,
  'strong' => 2,
  'code' => 2,
  'dfn' => 2,
  'kbd' => 2,
  'samp' => 2,
  'var' => 2,
  'del' => 2,
  'ins' => 2,
  'style' => 2,
  'div' => 2,
  'span' => 2,
  'center' => 2,
  'ul' => 2,
  'ol' => 2,
  'li' => 2,
  'dl' => 2,
  'dt' => 2,
  'dd' => 2,
  'dir' => 2,
  'menu' => 2,
  'img' => 2,
  'map' => 2,
  'area' => 2,
  'font' => 2,
  'basefont' => 2,
  'big' => 2,
  'small' => 2,
  'b' => 2,
  'i' => 2,
  's' => 2,
  'strike' => 2,
  'u' => 2,
  'tt' => 2,
  'sub' => 2,
  'sup' => 2,
  'form' => 2,
  'input' => 2,
  'button' => 2,
  'textarea' => 2,
  'select' => 2,
  'option' => 2,
  'optgroup' => 2,
  'fieldset' => 2,
  'legend' => 2,
  'label' => 2,
  'inindex' => 2,
  'table' => 2,
  'caption' => 2,
  'thead' => 2,
  'tbody' => 2,
  'tfoot' => 2,
  'tr' => 2,
  'th' => 2,
  'td' => 2,
  'col' => 2,
  'colgroup' => 2,
  'object' => 2,
  'param' => 2,
  'applet' => 2,
  'script' => 2,
  'noscript' => 2,
  'ruby' => 2,
  'rb' => 2,
  'rbc' => 2,
  'rt' => 2,
  'rtc' => 2,
  );
?>
