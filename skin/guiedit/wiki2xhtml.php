<?php
//
//	guiedit - PukiWiki Plugin
//
//	License:
//		GNU General Public License Version 2 or later (GPL)
//		http://www.gnu.org/licenses/gpl.html
//
//	Copyright (C) 2006-2008 garand
//	PukiWiki : Copyright (C) 2001-2006 PukiWiki Developers Team
//	FCKeditor : Copyright (C) 2003-2008 Frederico Caldeira Knabben
//      PukiWiki Plus! : Copyright (C) 2009 Katsumi Saito
//
//
//	File: 
//	  wiki2xhtml.php
//	  PukiWiki の構文を XHTML に変換
//


//	設定の読み込み
require_once(GUIEDIT_LIB_PATH . 'guiedit.ini.php');

//	PukiWiki の構文を XHTML に変換
function guiedit_convert_html($lines) {
	if (! is_array($lines)) $lines = explode("\n", $lines);

	$body = & new BodyEx();
	$body->parse($lines);

	return $body->toString();
}

// インライン要素の変換
function guiedit_make_link($line)
{
	$obj = new InlineConverterEx();
	return $obj->convert($line);
}

// 添付ファイルプラグインの変換
function guiedit_convert_ref($args, $div = TRUE) {
	$options = htmlspecialchars(join(',', $args));

	$filename = array_shift($args);
	$_title = array();
	$params = array(
		'left'   => 0, // 左寄せ
		'center' => 0, // 中央寄せ
		'right'  => 0, // 右寄せ
		'wrap'   => 0, // TABLEで囲む
		'nowrap' => 0, // TABLEで囲まない
		'around' => 0, // 回り込み
		'noicon' => 0, // アイコンを表示しない
		'nolink' => 0, // 元ファイルへのリンクを張らない
		'noimg'  => 0, // 画像を展開しない
		'zoom'   => 0, // 縦横比を保持する
		'_w'     => 0,     // 幅
		'_h'     => 0,     // 高さ
		'_size'  => '%'
	);

	// パラメータ解析
	foreach ($args as $arg) {
		$s_arg = strtolower($arg);
		if (array_key_exists($s_arg, $params)) {
			$params[$s_arg] = 1;
		} else if (preg_match('/^([0-9]+)x([0-9]+)$/', $arg, $matches)) {
			$params['_w'] = $matches[1];
			$params['_h'] = $matches[2];
			$params['_size'] = 'px';
		} else if (preg_match('/^([0-9.]+)%$/', $arg, $matches) && $matches[1] > 0) {
			$params['_w'] = $matches[1];
		} else {
			$_title[] = $arg;
		}
	}

	$align = '';
	if ($params['left']) {
		$align = 'left';
	} else if ($params['center']) {
		$align = 'center';
	} else if ($params['right']) {
		$align = 'right';
	}

	$alt = !empty($_title) ? htmlspecialchars(join(',', $_title)) : '';
	$alt = preg_replace("/^,/", '', $alt);

	$attribute = 'class="ref" contenteditable="false"' . ((UA_NAME == MSIE) ? '' : ' style="cursor:default"');
	$attribute .= ' _filename="' . $filename . '"';
	$attribute .= ' _alt="' . $alt . '"';
	$attribute .= ' _width="' . ($params['_w'] ? $params['_w'] : '') . '"';
	$attribute .= ' _height="' . ($params['_h'] ? $params['_h'] : '') . '"';
	$attribute .= ' _size="' . $params['_size'] . '"';
	$attribute .= ' _align="' . $align . '"';
	$attribute .= ' _wrap="' . $params['wrap'] . '"';
	$attribute .= ' _around="' . $params['around'] . '"';
	$attribute .= ' _nolink="' . $params['nolink'] . '"';
	$attribute .= ' _noicon="' . $params['noicon'] . '"';
	$attribute .= ' _noimg="' . $params['noimg'] . '"';
	$attribute .= ' _zoom="' . $params['zoom'] . '"';
	
	if ($div) {
		$tags = "<div $attribute>#ref($options)</div>";
	}
	else {
		$tags = "<span $attribute>&ref($options);</span>";
	}
	
	return $tags;
}



function guiedit_make_line_rules($line) {
	global $guiedit_line_rules, $guiedit_facemark_rules;
	global $usefacemark;
	static $pattern, $replace;
	
	if (!isset($pattern)) {
		if ($usefacemark) {
			$guiedit_line_rules += $guiedit_facemark_rules;
		}
		$pattern = array_map(create_function('$a', 'return \'/\' . $a . \'/\';'), array_keys($guiedit_line_rules));
		$replace = array_values($guiedit_line_rules);
		unset($guiedit_facemark_rules);
		unset($guiedit_line_rules);
	}
	
	return preg_replace($pattern, $replace, $line);
}


// インライン変換クラス
class InlineConverterEx {
	function convert($line, $link = TRUE, $enc = TRUE) {
		if ($enc) {
			$line = preg_replace("/&amp;/", "&#038;", $line);
			$line = htmlspecialchars($line);
		}

		// インライン・プラグイン
		$pattern = '/&amp;(\w+)(?:\(((?:(?!\)[;{]).)*)\))?(?:\{((?:(?R)|(?!};).)*)\})?;/';
		$line = preg_replace_callback($pattern, array(&$this, 'convert_plugin'), $line);
		
		// ルールの変換
		$line = guiedit_make_line_rules($line);
		
		// 文字サイズの変換
		$pattern = "/<span\s(style=\"font-size:(\d+)px|class=\"size([1-7])).*?>/";
		$line = preg_replace_callback($pattern, array(&$this, 'convert_size'), $line);
		// 色の変換
		$pattern = "/<sapn\sstyle=\"color:([#0-9a-z]+)(; background-color:([#0-9a-z]+))?\">/";
		$line = preg_replace_callback($pattern, array(&$this, 'convert_color'), $line);
		// 注釈
		$line = preg_replace("/\(\(((?:(?R)|(?!\)\)).)*)\)\)/", "<img alt=\"Note\" title=\"$1\" />", $line);
		// 参照文字
		$line = preg_replace('/&amp;(#?[a-z0-9]+);/', "&$1;", $line);

		// 上付き文字
		$line = preg_replace('/SUP{(.*?)}/', "<sup>$1</sup>", $line);
		// 下付き文字・添え字 
		$line = preg_replace('/SUB{(.*?)}/', "<sub>$1</sub>", $line);
		
		// リンク
		if ($link) {
			$pattern = "/\(\(((?:(?R)|(?!\)\)).)*)\)\)/";
			$replace = "<img alt=\"Note\" title=\"$1\" />";
			$line = $this->make_link($line);
		}
		
		if (preg_match("/^<br\s\/>$/", $line)) {
			$line .= "\n&nbsp;";
		}

		return $line;
	}

	// 文からリンクを検出し、link_replace を呼び出す
	function make_link($line) {
		$link_rules = "/(
			(?:\[\[((?:(?!\]\]).)+):)? 
			((?:https?|ftp|news)(?::\/\/[!~*'();\/?:\@&=+\$,%#\w.-]+))
			(?(2)\]\])
			|
			 (\[\[
			  (?:
			   (?:((?:(?!\]\]).)+))
			   (?:&gt;)
			  )?
			  (?:
			   (\#(?:[a-zA-Z][\w-]*)?)
			   |
			   ((?:(?!\]\]).)*)
			  )?
			 \]\])
		)/x";

		return preg_replace_callback($link_rules, array(&$this,'link_replace'), $line);
	}

	// make_link で検出したリンクにリンクタグを付加する
	function link_replace($matches) {
		if ($matches[3] != '') {
			if (!$matches[2]) {
				return $matches[3];
			}
			$url = $matches[3];
			$alias = empty($matches[2]) ? $url : $matches[2];
			return '<a href="'.$url.'">'.$alias.'</a>';
		}
		if ($matches[6] != '') {
			$str = empty($matches[5]) ? $matches[6] : $matches[5];
			return '<a href="' . $matches[6] . '">' . $str.'</a>';
		}
		if ($matches[7] != '') {
			$str = empty($matches[5]) ? $matches[7] : $matches[5];
			return '<a href="' . $matches[7] . '">' . $str . '</a>';
		}
		return $matches[0];
	}
	
	// インラインプラグイン処理メソッド
	function convert_plugin($matches) {
		$aryargs = (!empty($matches[2])) ? explode(',', $matches[2]) : array();
		$name = strtolower($matches[1]);
		$body = empty($matches[3]) ? '' : $matches[3];
		
		//	プラグインが存在しない場合はそのまま返す。
		// if (!file_exists(PLUGIN_DIR . $name . '.inc.php')) {
		if (!exist_plugin($name)) {
			return $matches[0];
		}

		switch ($name) {
			case 'aname':
				return '<a name="'.$aryargs[0].'">'.$body.'</a>';
			case 'br':
				return '<br />';
			case 'color':
				$color = $aryargs[0];
				$bgcolor = $aryargs[1];
				if ($body == '')
					return '';
				if ($color != '' && !preg_match('/^(#[0-9a-f]+|[\w-]+)$/i', $color))
					return $body;
				if ($bgcolor != '' && !preg_match('/^(#[0-9a-f]+|[\w-]+)$/i', $bgcolor))
					return $body;
				if ($color != '')
					$color = 'color:'.$color;
				if ($bgcolor != '')
					$bgcolor = ($color ? '; ' : '') . 'background-color:'.$bgcolor;
				return '<span style="'.$color.$bgcolor.'">' . $this->convert($body, TRUE, FALSE) . '</span>';

			case 'sup':
			case 'sub':
				return '<'.$name.'>'.$body.'</'.$name.'>';

			case 'size':
				$size = $aryargs[0];
				if ($size == '' || $body == '')
					return '';
				if (!preg_match('/^\d+$/', $size))
					return $body;
				return '<span style="font-size:' . $size . 'px;line-height:130%">' . 
				       $this->convert($body, TRUE, FALSE) . "</span>";
			case 'ref':
				return guiedit_convert_ref($aryargs, FALSE);
		}
		
		if ($body) {
			$pattern = array("%%", "''", "[[", "]]", "{", "|", "}");
			$replace = array("&#037;&#037;", "&#039;&#039;", "&#091;&#091;",
		 	 	"&#093;&#093;", "&#123;", "&#124;", "&#125;");
			$body = str_replace($pattern, $replace, $body);
		}
		
		$inner = '&' . $matches[1] . ($matches[2] ? '('.$matches[2].')' : '') . ($body ? '{'.$body.'}' : '') . ';';
		$style = (UA_NAME == MSIE) ? '' : ' style="cursor:default"';
		
		return '<span class="plugin" contenteditable="false"'.$style.'>'.$inner.'</span>';
	}
	
	// 色の変換
	function convert_color($matches) {
		$color = $matches[1];
		$bgcolor = $matches[3];
		if ($bgcolor && preg_match("/^#[0-9a-z]{3}$/i", $bgcolor)) {
			$bgcolor = "; background-color:" . preg_replace('/[0-9a-f]/i', "$0$0", $bgcolor);
		}
		if (preg_match("/^#[0-9a-z]{3}$/i", $color)) {
			$color = preg_replace('/[0-9a-f]/i', "$0$0", $color);
		}
		
		// return "<sapn\sstyle=\"color:$color$bgcolor\">";
		// UPK
		return '<sapn style="color:'.$color.$bgcolor.'">';
	}

	// 文字サイズの変換
	function convert_size($matches) {
		if ($matches[2]) {
			$size = $matches[2];
			
			if      ($size <=  8) $size = 8;
			else if ($size <=  9) $size = 9;
			else if ($size <= 10) $size = 10;
			else if ($size <= 11) $size = 11;
			else if ($size <= 12) $size = 12;
			else if ($size <= 14) $size = 14;
			else if ($size <= 16) $size = 16;
			else if ($size <= 18) $size = 18;
			else if ($size <= 22) $size = 20;
			else if ($size <= 26) $size = 24;
			else if ($size <= 30) $size = 28;
			else if ($size <= 36) $size = 32;
			else if ($size <= 44) $size = 40;
			else if ($size <= 52) $size = 48;
			else		      $size = 60;
			
			return '<span style="font-size:' . $size . 'px; line-height:130%">';
		}
		
		switch ($matches[3]) {
			case 1:	$size = 'xx-small';
			case 2: $size = 'x-small';
			case 3:	$size = 'small';
			case 4:	$size = 'medium';
			case 5:	$size = 'large';
			case 6:	$size = 'x-large';
			case 7:	$size = 'xx-large';
		}
		
		return '<span style="font-size:'.$size.'; line-height:130%">';
	}
}


// Block elements
class ElementEx
{
	var $parent;
	var $elements; // References of childs
	var $last;     // Insert new one at the back of the $last

	function ElementEx()
	{
		$this->elements = array();
		$this->last     = & $this;
	}

	function setParent(& $parent)
	{
		$this->parent = & $parent;
	}

	function & add(& $obj)
	{
		if ($this->canContain($obj)) {
			return $this->insert($obj);
		} else {
			return $this->parent->add($obj);
		}
	}

	function & insert(& $obj)
	{
		$obj->setParent($this);
		$this->elements[] = & $obj;

		return $this->last = & $obj->last;
	}

	function canContain($obj)
	{
		return TRUE;
	}

	function wrap($string, $tag, $param = '', $canomit = TRUE)
	{
		return ($canomit && $string == '') ? '' :
			'<' . $tag . $param . '>' . $string . '</' . $tag . '>';
	}

	function toString()
	{
		$ret = array();
		foreach (array_keys($this->elements) as $key)
			$ret[] = $this->elements[$key]->toString();
		return join("\n", $ret);
	}

	function dump($indent = 0)
	{
		$ret = str_repeat(' ', $indent) . get_class($this) . "\n";
		$indent += 2;
		foreach (array_keys($this->elements) as $key) {
			$ret .= is_object($this->elements[$key]) ?
				$this->elements[$key]->dump($indent) : '';
				//str_repeat(' ', $indent) . $this->elements[$key];
		}
		return $ret;
	}
}

// Returns inline-related object
function & Factory_InlineEx($text)
{
	// Check the first letter of the line
	if (substr($text, 0, 1) == '~') {
		// return new ParagraphEx(' ' . substr($text, 1));
		$obj = new ParagraphEx(' ' . substr($text, 1));
	} else {
		// return new InlineEx($text);
		$obj = new InlineEx($text);
	}
	return $obj;
}

function & Factory_DListEx(& $root, $text)
{
	$out = explode('|', ltrim($text), 2);
	if (count($out) < 2) {
		return Factory_InlineEx($text);
	} else {
		return new DListEx($out);
	}
}

// '|'-separated table
function & Factory_TableEx(& $root, $text)
{
	if (! preg_match('/^\|(.+)\|([hHfFcC]?)$/', $text, $out)) {
		return Factory_InlineEx($text);
	} else {
		// return new TableEx($out);
		$obj = new TableEx($out);
		return $obj;
	}
}

// Comma-separated table
function & Factory_YTableEx(& $root, $text)
{
	if ($text == ',') {
		return Factory_InlineEx($text);
	} else {
		// return new YTableEx(csv_explode(',', substr($text, 1)));
		$obj = new YTableEx(csv_explode(',', substr($text, 1)));
		return $obj;
	}
}

function & Factory_DivEx(& $root, $text)
{
	$matches = array();

	// Seems block plugin?
	if (PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK) {
		// Usual code
		if (preg_match('/^\#([^\(]+)(?:\((.*)\))?/', $text, $matches)) {
			// return new DivEx($matches);
			$obj = new DivEx($matches);
			return $obj;
		}
	} else {
		// Hack code
		if (preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(\{*)/', $text, $matches)) {
			$len  = strlen($matches[3]);
			$body = array();
			if ($len == 0) {
				// return new DivEx($matches); // Seems legacy block plugin
				$obj = new DivEx($matches);
				return $obj;
			} else if (preg_match('/\{{' . $len . '}\s*\r(.*)\r\}{' . $len . '}/', $text, $body)) { 
				$matches[3] .= "\r" . $body[1] . "\r" . str_repeat('}', $len);
				// return new DivEx($matches); // Seems multiline-enabled block plugin
				$obj = new DivEx($matches);
				return $obj;
			}
		}
	}

	// return new ParagraphEx($text);
	$obj = new ParagraphEx($text);
	return $obj;
}

// InlineEx elements
class InlineEx extends ElementEx
{
	function InlineEx($text)
	{
		parent::ElementEx();
		$this->elements[] = trim((substr($text, 0, 1) == "\n") ?
			$text : guiedit_make_link($text));
	}

	function & insert(& $obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function canContain($obj)
	{
		return is_a($obj, 'InlineEx');
	}

	function toString()
	{
		global $line_break;
		return join(($line_break ? '<br />' . "\n" : "\n"), $this->elements);
	}

	function & toPara($class = '')
	{
		$obj = & new ParagraphEx('', $class);
		$obj->insert($this);
		return $obj;
	}
}

// ParagraphEx: blank-line-separated sentences
class ParagraphEx extends ElementEx
{
	var $param;

	function ParagraphEx($text, $param = '')
	{
		parent::ElementEx();
		$this->param = $param;
		if ($text == '') return;

		if (substr($text, 0, 1) == '~')
			$text = ' ' . substr($text, 1);

		$this->insert(Factory_InlineEx($text));
	}

	function canContain($obj)
	{
		return is_a($obj, 'InlineEx');
	}

	function toString()
	{
		return $this->wrap(parent::toString(), 'p', $this->param);
	}
}

// * HeadingEx1
// ** HeadingEx2
// *** HeadingEx3
class HeadingEx extends ElementEx
{
	var $level;
	var $id;
	var $msg_top;

	function HeadingEx(& $root, $text)
	{
		parent::ElementEx();

		$this->level = min(3, strspn($text, '*'));
		
		$text = substr($text, $this->level);
		if (preg_match('/\s*\[#(\w+)\]/', $text, $matches)) {
			$this->id = $matches[1];
		}
		$text = preg_replace('/\s*\[#\w+\]/', '', $text);
		
		$this->insert(Factory_InlineEx($text));
		$this->level++; // h2,h3,h4
	}

	function & insert(& $obj)
	{
		parent::insert($obj);
		return $this->last = & $this;
	}

	function canContain(& $obj)
	{
		return FALSE;
	}

	function toString()
	{
		return $this->wrap(parent::toString(),
			'h' . $this->level, ' id="' . $this->id . '"');
	}
}

// ----
// Horizontal Rule
class HRuleEx extends ElementEx
{
	function HRuleEx(& $root, $text)
	{
		parent::ElementEx();
	}

	function canContain(& $obj)
	{
		return FALSE;
	}

	function toString()
	{
		global $hr;
		return $hr;
	}
}

// Lists (UL, OL, DL)
class ListContainerEx extends ElementEx
{
	var $tag;
	var $tag2;
	var $level;
	var $style;
	var $margin;
	var $left_margin;

	function ListContainerEx($tag, $tag2, $head, $text)
	{
		parent::ElementEx();

		$var_margin      = '_' . $tag . '_margin';
		$var_left_margin = '_' . $tag . '_left_margin';
		global $$var_margin, $$var_left_margin;

		$this->margin      = $$var_margin;
		$this->left_margin = $$var_left_margin;

		$this->tag   = $tag;
		$this->tag2  = $tag2;
		$this->level = min(3, strspn($text, $head));
		$text = ltrim(substr($text, $this->level));

		parent::insert(new ListElementEx($this->level, $tag2));
		if ($text != '')
			$this->last = & $this->last->insert(Factory_InlineEx($text));
	}

	function canContain(& $obj)
	{
		return (! is_a($obj, 'ListContainerEx')
			|| ($this->tag == $obj->tag && $this->level == $obj->level));
	}

	function setParent(& $parent)
	{
		global $_list_pad_str;

		parent::setParent($parent);

		$step = $this->level;
		if (isset($parent->parent) && is_a($parent->parent, 'ListContainerEx'))
			$step -= $parent->parent->level;

		$margin = $this->margin * $step;
		if ($step == $this->level)
			$margin += $this->left_margin;

		$this->style = sprintf($_list_pad_str, $this->level, $margin, $margin);
	}

	function & insert(& $obj)
	{
		if (! is_a($obj, get_class($this)))
			return $this->last = & $this->last->insert($obj);

		// Break if no elements found (BugTrack/524)
		if (count($obj->elements) == 1 && empty($obj->elements[0]->elements))
			return $this->last->parent; // up to ListElementEx

		// Move elements
		foreach(array_keys($obj->elements) as $key)
			parent::insert($obj->elements[$key]);

		return $this->last;
	}

	function toString()
	{
		return $this->wrap(parent::toString(), $this->tag, $this->style);
	}
}

class ListElementEx extends ElementEx
{
	function ListElementEx($level, $head)
	{
		parent::ElementEx();
		$this->level = $level;
		$this->head  = $head;
	}

	function canContain(& $obj)
	{
		return (! is_a($obj, 'ListContainerEx') || ($obj->level > $this->level));
	}

	function toString()
	{
		return $this->wrap(parent::toString(), $this->head);
	}
}

// - One
// - Two
// - Three
class UListEx extends ListContainerEx
{
	function UListEx(& $root, $text)
	{
		parent::ListContainerEx('ul', 'li', '-', $text);
	}
}

// + One
// + Two
// + Three
class OListEx extends ListContainerEx
{
	function OListEx(& $root, $text)
	{
		parent::ListContainerEx('ol', 'li', '+', $text);
	}
}

// : definition1 | description1
// : definition2 | description2
// : definition3 | description3
class DListEx extends ListContainerEx
{
	function DListEx($out)
	{
		parent::ListContainerEx('dl', 'dt', ':', $out[0]);
		$this->last = & ElementEx::insert(new ListElementEx($this->level, 'dd'));
		if ($out[1] != '')
			$this->last = & $this->last->insert(Factory_InlineEx($out[1]));
	}
}

// > Someting cited
// > like E-mail text
class BQuoteEx extends ElementEx
{
	var $level;

	function BQuoteEx(& $root, $text)
	{
		parent::ElementEx();

		$head = substr($text, 0, 1);
		$this->level = min(3, strspn($text, $head));
		$text = ltrim(substr($text, $this->level));

		if ($head == '<') { // Blockquote close
			$level       = $this->level;
			$this->level = 0;
			$this->last  = & $this->end($root, $level);
			if ($text != '')
				$this->last = & $this->last->insert(Factory_InlineEx($text));
		} else {
			$this->insert(Factory_InlineEx($text));
		}
	}

	function canContain(& $obj)
	{
		return (! is_a($obj, get_class($this)) || $obj->level >= $this->level);
	}

	function & insert(& $obj)
	{
		// BugTrack/521, BugTrack/545
		if (is_a($obj, 'InlineEx'))
			return parent::insert($obj->toPara(' class="quotation"'));

		if (is_a($obj, 'BQuoteEx') && $obj->level == $this->level && count($obj->elements)) {
			$obj = & $obj->elements[0];
			if (is_a($this->last, 'ParagraphEx') && count($obj->elements))
				$obj = & $obj->elements[0];
		}
		return parent::insert($obj);
	}

	function toString()
	{
		return $this->wrap(parent::toString(), 'blockquote');
	}

	function & end(& $root, $level)
	{
		$parent = & $root->last;

		while (is_object($parent)) {
			if (is_a($parent, 'BQuoteEx') && $parent->level == $level)
				return $parent->parent;
			$parent = & $parent->parent;
		}
		return $this;
	}
}

class TableCellEx extends ElementEx
{
	var $tag = 'td'; // {td|th}
	var $colspan = 1;
	var $rowspan = 1;
	var $style; // is array('width'=>, 'align'=>...);
	var $is_template;
	var $hspace = 0;
	var $fspace = 0;

	function TableCellEx($text, $is_template = FALSE)
	{
		parent::ElementEx();
		$this->style = $matches = array();
		$this->is_template = $is_template;

		while (preg_match('/^(?:(LEFT|CENTER|RIGHT)|(BG)?COLOR\(([#\w]+)\)|SIZE\((\d+)\)):(.*)$/',
		    $text, $matches)) {
			if ($matches[1]) {
				$this->style['align'] = ' align="' . strtolower($matches[1]) . '"';
				$text = $matches[5];
			} else if ($matches[3]) {
				$name = $matches[2] ? 'background-color' : 'color';
				$color = $matches[3];
				if (preg_match("/^#[0-9a-f]{3}$/i", $color)) {
					$color = preg_replace("/[0-9a-f]/i", "$0$0", $color);
				}
				$this->style[$name] = $name . ':' . htmlspecialchars($color) . ';';
				$text = $matches[5];
			} else if ($matches[4]) {
				$this->style['size'] = 'font-size:' . htmlspecialchars($matches[4]) . 'px;';
				$text = $matches[5];
			}
		}
		
		if ($is_template && is_numeric($text))
			$this->style['width'] = ' width="' . $text . '"';

		if ($text == '>') {
			$this->colspan = 0;
		}
		
		if ($is_template) {
			$this->tag = 'col';
		}
		else if ($text == '~') {
			$this->rowspan = 0;
		} else if (substr($text, 0, 1) == '~') {
			$this->tag = 'th';
			$text      = substr($text, 1);
		}

		if ($text != '' && $text{0} == '#') {
			// Try using DivEx class for this $text
			$obj = & Factory_DivEx($this, $text);
			if (is_a($obj, 'ParagraphEx'))
				$obj = & $obj->elements[0];
		} else {
			if (preg_match("/^(\s+)?.+?(\s+)?$/", $text, $matches)) {
				$this->hspace = isset($matches[1]) ? strlen($matches[1]) : 0;
				$this->fspace = isset($matches[2]) ? strlen($matches[2]) : 0;
			}
			$obj = & Factory_InlineEx($text);
		}

		$this->insert($obj);
	}

	function setStyle(& $style)
	{
		foreach ($style as $key=>$value)
			if (! isset($this->style[$key]))
				$this->style[$key] = $value;
	}

	function toString()
	{
		if ($this->is_template) {
			$param = '';
		}
		else {
			if ($this->rowspan == 0 || $this->colspan == 0) return '';

			$param = ' class="style_' . $this->tag . '"'
				   . ' _hspace="' . $this->hspace . '"'
				   . ' _fspace="' . $this->fspace . '"';
			if ($this->rowspan > 1)
				$param .= ' rowspan="' . $this->rowspan . '"';
			if ($this->colspan > 1) {
				$param .= ' colspan="' . $this->colspan . '"';
				unset($this->style['width']);
			}
		}

		if (! empty($this->style)) {
			foreach($this->style as $key=>$value) {
				if ($key == 'align' || $key == 'width') {
					$param .= $value;
					unset($this->style[$key]);
				}
			}
			$param .= ' style="' . join(' ', $this->style) . '"';
		}
		
		return $this->wrap($this->is_template ? '' : parent::toString(), $this->tag, $param, FALSE);
	}
}

// | title1 | title2 | title3 |
// | cell1  | cell2  | cell3  |
// | cell4  | cell5  | cell6  |
class TableEx extends ElementEx
{
	var $type;
	var $types;
	var $col; // number of column

	function TableEx($out)
	{
		parent::ElementEx();

		$cells       = explode('|', $out[1]);
		$this->col   = count($cells);
		$this->type  = strtolower($out[2]);
		$this->types = array($this->type);
		$is_template = ($this->type == 'c');
		$row = array();
		foreach ($cells as $cell)
			$row[] = & new TableCellEx($cell, $is_template);
		$this->elements[] = $row;
	}

	function canContain(& $obj)
	{
		return is_a($obj, 'TableEx') && ($obj->col == $this->col);
	}

	function & insert(& $obj)
	{
		$this->elements[] = $obj->elements[0];
		$this->types[]    = $obj->type;
		return $this;
	}

	function toString()
	{
		static $parts = array('h'=>'thead', 'f'=>'tfoot', ''=>'tbody');

		// Set rowspan (from bottom, to top)
		for ($ncol = 0; $ncol < $this->col; $ncol++) {
			$rowspan = 1;
			foreach (array_reverse(array_keys($this->elements)) as $nrow) {
				$row = & $this->elements[$nrow];
				if ($row[$ncol]->rowspan == 0) {
					++$rowspan;
					continue;
				}
				$row[$ncol]->rowspan = $rowspan;
				// Inherits row type
				while (--$rowspan)
					$this->types[$nrow + $rowspan] = $this->types[$nrow];
				$rowspan = 1;
			}
		}

		// Set colspan and style
		$stylerow = NULL;
		foreach (array_keys($this->elements) as $nrow) {
			$row = & $this->elements[$nrow];
			$colspan = 1;
			
			if ($this->types[$nrow] == 'c') {
				for ($i = count($row) - 2; $i >= 0; $i--) {
					if ($row[$i]->colspan == 0) {
						$row[$i]->setStyle($row[$i + 1]->style);
					}
				}
			}
			
			foreach (array_keys($row) as $ncol) {
				if ($row[$ncol]->colspan == 0) {
					++$colspan;
					continue;
				}
				$row[$ncol]->colspan = $colspan;
				if ($stylerow !== NULL) {
					$row[$ncol]->setStyle($stylerow[$ncol]->style);
					// Inherits column style
					while (--$colspan)
						$row[$ncol - $colspan]->setStyle($stylerow[$ncol]->style);
				}
				$colspan = 1;
			}
		}

		// toString
		$string = '';
		$part_string = '';
		$old_type = '';
		foreach (array_keys($this->elements) as $nrow) {
			if (($old_type != $this->types[$nrow]) && ($part_string != '')) {
				$string .= ($old_type == 'c') ? $part_string : $this->wrap($part_string, $parts[$old_type]);
				$part_string = '';
			}
			$row        = & $this->elements[$nrow];
			$row_string = '';
			foreach (array_keys($row) as $ncol) {
				$row_string .= $row[$ncol]->toString();
			}
			$part_string .= $this->wrap($row_string, (($this->types[$nrow] == 'c') ? 'colgroup' : 'tr'));
			$old_type = $this->types[$nrow];
		}
		$string .= ($old_type == 'c') ? $part_string : $this->wrap($part_string, $parts[$old_type]);

		return $this->wrap($string, 'table', ' class="style_table" cellspacing="1" border="0" align="center"');
	}
}

// , title1 , title2 , title3
// , cell1  , cell2  , cell3
// , cell4  , cell5  , cell6
class YTableEx extends ElementEx
{
	var $col;

	function YTableEx($_value)
	{
		parent::ElementEx();

		$align = $value = $matches = array();
		foreach($_value as $val) {
			if (preg_match('/^(\s+)?(.+?)(\s+)?$/', $val, $matches)) {
				$align[] =($matches[1] != '') ?
					((isset($matches[3]) && $matches[3] != '') ?
						' align="center"' :
						' align="right"'
					) : '';
				$value[] = $matches[2];
			} else {
				$align[] = '';
				$value[] = $val;
			}
		}
		$this->col = count($value);
		$colspan = array();
		foreach ($value as $val)
			$colspan[] = ($val == '==') ? 0 : 1;
		$str = '';
		$count = count($value);
		for ($i = 0; $i < $count; $i++) {
			if ($colspan[$i]) {
				while ($i + $colspan[$i] < $count && $value[$i + $colspan[$i]] == '==')
					$colspan[$i]++;
				$colspan[$i] = ($colspan[$i] > 1) ? ' colspan="' . $colspan[$i] . '"' : '';
				$str .= '<td class="style_td"' . $align[$i] . $colspan[$i] . '>' . guiedit_make_link($value[$i]) . '</td>';
			}
		}
		$this->elements[] = $str;
	}

	function canContain(& $obj)
	{
		return is_a($obj, 'YTableEx') && ($obj->col == $this->col);
	}

	function & insert(& $obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function toString()
	{
		$rows = '';
		foreach ($this->elements as $str)
			$rows .= "\n" . '<tr class="style_tr">' . $str . '</tr>' . "\n";
		$rows = $this->wrap($rows, 'table', ' class="style_table" cellspacing="1" border="0"');
		return $this->wrap($rows, 'div', ' class="ie5"');
	}
}

// ' 'Space-beginning sentence
// ' 'Space-beginning sentence
// ' 'Space-beginning sentence
class PreEx extends ElementEx
{
	function PreEx(& $root, $text)
	{
		global $preformat_ltrim;
		parent::ElementEx();
		$this->elements[] = htmlspecialchars(
			(! $preformat_ltrim || $text == '' || $text{0} != ' ') ? $text : substr($text, 1));
	}

	function canContain(& $obj)
	{
		return is_a($obj, 'PreEx');
	}

	function & insert(& $obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function toString()
	{
		return $this->wrap(join("<br />", $this->elements), 'pre');
	}
}

// Block plugin: #something (started with '#')
class DivEx extends ElementEx
{
	var $text;
	var $name;
	var $param;

	function DivEx($out)
	{
		parent::ElementEx();
		list(, $this->name, $this->param, $this->text) = array_pad($out, 4, '');
	}

	function canContain(& $obj)
	{
		return FALSE;
	}

	function toString()
	{
		switch ($this->name) {
			case 'br':
				return "<br />\n&nbsp;";
			case 'hr':
				return '<hr class="short_line" />';
			case 'pagebreak':
				return '<div style="page-break-after: always;"><span style="display: none;">&nbsp;</span></div>';
			case 'ref':
				$param = ($this->param != '') ? explode(',', $this->param) : array();
				return guiedit_convert_ref($param);
		}
		
		if ($this->text) {
			$this->text = preg_replace("/\r/", "<br />", $this->text);
		}
		
		$inner = "#$this->name" . ($this->param ? "($this->param)" : '') . $this->text;
		$style = (UA_NAME == MSIE) ? '' : ' style="cursor:default"';
		
		return $this->wrap($inner, 'div', ' class="plugin" contenteditable="false"' . $style);
	}
}

// LEFT:/CENTER:/RIGHT:
class AlignEx extends ElementEx
{
	var $align;

	function AlignEx($align)
	{
		parent::ElementEx();
		$this->align = $align;
	}

	function canContain(& $obj)
	{
		return is_a($obj, 'InlineEx');
	}

	function toString()
	{
		return $this->wrap(parent::toString(), 'div', ' style="text-align: ' . $this->align . '"');
	}
}

// BodyEx
class BodyEx extends ElementEx
{
	var $classes = array(
		'-' => 'UListEx',
		'+' => 'OListEx',
		'>' => 'BQuoteEx',
		'<' => 'BQuoteEx');
	var $factories = array(
		':' => 'DListEx',
		'|' => 'TableEx',
		',' => 'YTableEx',
		'#' => 'DivEx');
	
	var $comments = array();

	function BodyEx()
	{
		parent::ElementEx();
	}

	function parse(& $lines)
	{
		$this->last = & $this;
		$matches = array();

		while (! empty($lines)) {
			$line = array_shift($lines);

			// Escape comments
			if (substr($line, 0, 2) == '//') {
				$this->comments[] = substr($line, 2);
				$line = '___COMMENT___';
			}

			if (preg_match('/^(LEFT|CENTER|RIGHT):(.*)$/', $line, $matches)) {
				// <div style="text-align:...">
				$this->last = & $this->last->add(new AlignEx(strtolower($matches[1])));
				if ($matches[2] == '') continue;
				$line = $matches[2];
			}

			$line = rtrim($line, "\r\n");

			// Empty
			if ($line == '') {
				$this->last = & $this;
				continue;
			}

			// Horizontal Rule
			if (substr($line, 0, 4) == '----') {
				$this->insert(new HRuleEx($this, $line));
				continue;
			}

			// Multiline-enabled block plugin
			if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
			    preg_match('/^#[^{]+(\{\{+)\s*$/', $line, $matches)) {
				$len = strlen($matches[1]);
				$line .= "\r"; // Delimiter
				while (! empty($lines)) {
					$next_line = preg_replace("/[\r\n]*$/", '', array_shift($lines));
					// UPK
					$next_line = htmlspecialchars($next_line);
					if (preg_match('/\}{' . $len . '}/', $next_line)) {
						$line .= $next_line;
						break;
					} else {
						$line .= $next_line .= "\r"; // Delimiter
					}
				}
			}

			// The first character
			$head = $line{0};

			// HeadingEx
			if ($head == '*') {
				$this->insert(new HeadingEx($this, $line));
				continue;
			}

			// PreEx
			if ($head == ' ' || $head == "\t") {
				$this->last = & $this->last->add(new PreEx($this, $line));
				continue;
			}

			// Line Break
			if (substr($line, -1) == '~')
				$line = substr($line, 0, -1) . "\r";
			
			// Other Character
			if (isset($this->classes[$head])) {
				$classname  = $this->classes[$head];
				$this->last = & $this->last->add(new $classname($this, $line));
				continue;
			}

			// Other Character
			if (isset($this->factories[$head])) {
				$factoryname = 'Factory_' . $this->factories[$head];
				$this->last  = & $this->last->add($factoryname($this, $line));
				continue;
			}

			// Default
			$this->last = & $this->last->add(Factory_InlineEx($line));
		}
	}

	function & insert(& $obj)
	{
		if (is_a($obj, 'InlineEx')) $obj = & $obj->toPara();
		return parent::insert($obj);
	}

	function toString()
	{
		global $vars;

		$text = parent::toString();
		
		$text = preg_replace_callback("/___COMMENT___(\n___COMMENT___)*/", array(&$this, 'comment'), $text);

		return $text . "\n";
	}
	
	function comment($matches)
	{
		$comments = explode("\n", $matches[0]);
		foreach ($comments as $key=>$comment) {
			$comments[$key] = array_shift($this->comments);
		}
		$comment = join("\n", $comments);
		return '<img alt="Comment" title="' . htmlspecialchars($comment) . '" />';
	}
}

?>
