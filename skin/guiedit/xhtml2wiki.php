<?php
//
//	guiedit - PukiWiki Plugin
//
//	License:
//	  GNU General Public License Version 2 or later (GPL)
//	  http://www.gnu.org/licenses/gpl.html
//
//	Copyright (C) 2006-2008 garand
//	PukiWiki : Copyright (C) 2001-2006 PukiWiki Developers Team
//	FCKeditor : Copyright (C) 2003-2008 Frederico Caldeira Knabben
//      PukiWiki Plus! : Copyright (C) 2009-2010 Katsumi Saito
//
//
//	File:
//	  xhtml2wiki.php
//	  XHTML を PukiWiki の構文に変換
//


function xhtml2wiki($source)
{
	// 変換クラスのオブジェクト生成とその設定
	$obj = new XHTML2Wiki();
	
	// 変換メソッドの呼び出し
	$body = $obj->Convert($source);
	
	return $body;
}


// 変換クラス
class XHTML2Wiki
{
	var $body;
	var $text;
	var $parent_div;
	var $div_level;
	var $level_array;
	var $protect_data;
	
	//	初期化
	function XHTML2Wiki() {
		$this->parent_div = array('');
		$this->div_level = 0;
		$this->level_array = array(0);
		$this->protect_data = array();
		$this->text = '';
	}
	
	// 変換メソッド
	function Convert($source) {
		$this->body = '';

		// <br /> が行末にならない場合の対処
		// -文字列~ のような場合の挙動に対応
		$source = preg_replace("/<br\s\/>\</", "~\n<", $source);
		
		// １行ずつに分割
		$source = explode("\n", $source);
		
		// 一行ずつ取り出し
		foreach ($source as $line) {
			$this->Div($line);
		}
		
		// 構文を結合
		$body = implode('', $this->body);
		
		// 構文補正
		$body = preg_replace("/___GUIPD(\d+)___/e", '$this->protect_data["$1"-1]', $body);
		$body = preg_replace("/\n\n\n+/", "\n\n", $body);
		
		return $body;
	}

	// ブロック要素
	function Div($line) {
		if ($line == '') {
			return;
		}
		
		if ($this->GetDiv() == 'Table') {
			$this->Table($line);
			return;
		}
		
		// 整形済みテキスト
		if (preg_match("/<pre>/", $line, $matches)) {
			$this->StartDiv('Pre');
		}
		else if ($this->GetDiv() == 'Pre') {
			if (preg_match("/(.*)<\/pre>/", $line, $matches)) {
				$line = $matches[1];
				$this->EndDiv();
			}
			$line = preg_replace("/<br\s\/>/", "\n ", $line);
			$line = strip_tags($line);
			$this->OutputLine(' ' . $this->DecodeSpecialChars($line));
		}
		// 見出し
		else if (preg_match("/<h([2-4])(.*?)>(.*)/", $line, $matches)) {
			$this->StartDiv('Heading');
			$level = $matches[1];
			$line = $matches[3];
			$attribute = $matches[2];
			if (preg_match("/id=\"(\w+)\"/", $attribute, $matches)) {
				$line .= ' [#' . $matches[1] . ']';
			}
			$this->OutputLine(str_repeat("*", --$level), $line);
			$this->EndDiv();
		}
		// ブロック型プラグイン
		else if (preg_match("/<div\s([^>]*?)class=\"(plugin|ref)\"(.*?)>(.*)/", $line)) {
			$line = preg_replace("/<br\s\/>/", "\n", $line);
			$line = strip_tags($line);
			$this->OutputLine($this->DecodeSpecialChars($line));
		}
		// 引用文
		else if (preg_match("/<blockquote.*?>/", $line)) {
			if ($this->GetDiv() != 'Blockquote') {
				$this->StartDiv('Blockquote');
			}
			$this->div_level++;
			$this->OutputLine(str_repeat('>', $this->GetLevel()));
		}
		// リスト
		else if (preg_match("/<(o|u|d)l.*?>/", $line, $matches)) {
			$element = strtoupper($matches[1]) . 'List';
			if ($this->GetDiv() != $element) {
				$this->StartDiv($element);
			}
			$this->div_level++;
		}
		// テーブル
		else if (preg_match("/<table.*?>/", $line)) {
			$this->StartDiv('Table');
		}
		// 水平線
		else if (preg_match("/^<hr(\sclass=\"(full_hr|short_line)\")?\s\/>$/", $line, $matches)) {
			if ($matches[2] == 'short_line') {
				$this->OutputLine('#hr');
			}
			else {
				$this->OutputLine('----');
			}
		}
		// 改頁
		else if (strpos($line,'<div style="page-break-after: always;">') !== false) {
			$this->OutputLine('#pagebreak');
		}
		else {
			switch ($this->GetDiv()) {
				case 'OList':
					$this->OList($line);
					break;
				case 'UList':
					$this->UList($line);
					break;
				case 'DList':
					$this->DList($line);
					break;
				case 'Blockquote':
					$this->Blockquote($line);
					break;
				default:
					$this->Paragraph($line);
					break;
			}
		}
	}

	// 番号付きリスト
	function OList($line) {
		if (preg_match("/<\/ol>/", $line)) {
			$this->div_level--;
			if ($this->div_level == 0) {
				$this->EndDiv();
				if ($this->GetDiv() == '') {
					$this->OutputLine();
				}
			}
		}
		else if (preg_match("/^(<li>)?(.*?)(<\/li>)?$/", $line, $matches)) {
			$head = '';
			if ($matches[1]) {
				$head = str_repeat("+", $this->GetLevel());
			}
			if (!$matches[1] && isset($matches[3]) && !$matches[3]) {
				$this->Paragraph($line);
			}
			else if ($head || $matches[2]) {
				$this->OutputLine($head, $matches[2]);
			}
		}
	}
	
	// 番号なしリスト
	function UList($line) {
		if (preg_match("/<\/ul>/", $line)) {
			$this->div_level--;
			if ($this->div_level == 0) {
				$this->EndDiv();
				if ($this->GetDiv() == '') {
					$this->OutputLine();
				}
			}
		}
		else if (preg_match("/^(<li>)?(.*?)(<\/li>)?$/", $line, $matches)) {
			$head = '';
			if ($matches[1]) {
				$head = str_repeat("-", $this->GetLevel());
			}
			if (!$matches[1] && isset($matches[3]) && !$matches[3]) {
				$this->Paragraph($line);
			}
			else if ($head || $matches[2]) {
				$this->OutputLine($head, $matches[2]);
			}
		}
	}
	
	// 定義リスト
	function DList($line) {
		if (preg_match("/<\/dl>/", $line)) {
			$this->div_level--;
			if ($this->div_level == 0) {
				$this->EndDiv();
				if ($this->GetDiv() == '') {
					$this->OutputLine();
				}
			}
		}
		else if (preg_match("/^\s*(<d(t|d)>)?(.*?)(<\/d(t|d)>)?\s*$/", $line, $matches)) {
			$text = $matches[3];
			if ($matches[2] == 't') {
				$this->OutputLine(str_repeat(':', $this->GetLevel()), $text, '|');
			}
			else if ($text) {
				$this->OutputLine('', $text);
			}
		}
	}
	
	// 引用文
	function Blockquote($line) {
		if (preg_match("/<\/blockquote>/", $line)) {
			if ($this->div_level <= 3) {
				$this->OutputLine(str_repeat('<', $this->GetLevel()), '');
			}
			$this->div_level--;
			if ($this->div_level == 0) {
				$this->EndDiv();
			}
		}
		else if (preg_match("/(<p.*?>)?(.*?)(<\/p>)?$/", $line, $matches)) {
			if (!$matches[1] && !$matches[3]) {
				$this->Paragraph($line);
			}
			else if ($matches[2]) {
				$head = $matches[1] ? str_repeat('>', $this->GetLevel()) : '';
				$this->OutputLine($head, $matches[2]);
			}
		}
	}
	
	// テーブル
	function Table($line) {
		static $cells;
		static $text;
		static $head, $hspace, $fspace;
		static $row, $col;
		static $is_cell = false;
		static $type = '';
		static $delm = '|';

		// セルの開始
		if (preg_match("/<t(d|h)(\s[^>]*?)?>(.*)/", $line, $matches)) {
			$is_cell = true;
			$cell_type = $matches[1]; // d, h
			$attribute = $matches[2];
			$text = '';
			$line = $matches[3];
			$rowspan = 1;
			$colspan = 1;
			$hspace = 0;
			$fspace = 0;
			
			for (; !empty($cells[$row][$col]); $col++);
			
			// セルの連結
			if (preg_match("/rowspan=\"(\d+)\"/", $attribute, $matches)) {
				$rowspan = $matches[1];
			}
			if (preg_match("/colspan=\"(\d+)\"/", $attribute, $matches)) {
				$colspan = $matches[1];
			}
			for ($i = 1; $i < $rowspan; $i++) {
				for ($j = 0; $j < $colspan; $j++) {
					$cells[$row + $i][$col + $j] = '~';
				}
			}
			for ($i = 1; $i < $colspan; $i++) {
				$cells[$row][$col++] = '>';
			}

			// セルの属性
			$head = $this->GetTableAttribute($attribute);
			// 空白
			if (preg_match("/_hspace=\"(\d+)\"/", $attribute, $matches)) {
				$hspace = $matches[1];
			}
			if (preg_match("/_fspace=\"(\d+)\"/", $attribute, $matches)) {
				$fspace = $matches[1];
			}
			// ヘッダセル
			$head .= ($cell_type == 'h') ? '~' : '';
		}
		
		// セル
		if ($is_cell) {
			if (preg_match("/(.*)<\/t(d|h)>/", $line, $matches)) {
				$text .= $matches[1];
				
				$text = $this->Inline($text);
				if (($cell_type == 'h' && $text == '' && $hspace == 0) ||
					(preg_match('/^(?:LEFT|CENTER|RIGHT|(BG)?COLOR\([#\w]+\)|SIZE\(\d+\)):/', $text) && $hspace == 0))
				{
					$head .= ' ';
				}
				
				$cells[$row][$col] = $head . str_repeat(' ', $hspace) . $text . str_repeat(' ', $fspace);
				$col++;
				$is_cell = false;
			}
			else {
				$text .= $line;
			}
		}
		// 行の開始
		// UPK
		else if (preg_match("/<tr.*?>/", $line)) {
			$col = 1;
			$row++;
			$delm = preg_match("/<tr>/", $line) ? '|' : ',';
		}
		// テーブルの終了
		else if (preg_match("/<\/table>/", $line)) {
			$cells = null;
			$this->EndDiv();
			$this->body[] = "\n";
		}
		// 書式設定行
		else if (preg_match("/<colgroup>/", $line)) {
			if (count($cells)) {
				$this->OutputTable($cells, $type);
				$cells = array();
				$row = 0;
			}
			$text = '';
			while (preg_match("/<col\s([^\/]*?)\/>(.*)/", $line, $matches)) {
				$line = $matches[2];
				$text .= '|' . $this->GetTableAttribute($matches[1]);
			}
			$this->body[] = $text . "|c\n";
		}
		// ヘッダ・ボディ・フッタの開始
		else if (preg_match("/<t((h)ead|body|(f)oot)>/", $line, $matches)) {
			$cells = array();
			$type = !empty($matches[2]) ? $matches[2] : (!empty($matches[3]) ? $matches[3] : '');
			$row = 0;
		}
		// ヘッダ・ボディ・フッタの終わり
		else if (preg_match("/<\/t(head|body|foot)>/", $line)) {
			$this->OutputTable($cells, $type, $delm);
			$cells = array();
		}
	}
	
	// テーブルの属性を取得
	function GetTableAttribute($attribute) {
		$text = '';
		
		$pattern = "/rgb\((\d+),\s(\d+),\s(\d+)\)/ie";
		$attribute = preg_replace($pattern, 'sprintf("#%02x%02x%02x", "$1", "$2", "$3")', $attribute);
		
		// 文字サイズ
		if (preg_match("/font-size:\s?(\d+)px/i", $attribute, $matches)) {
			$text .= 'SIZE(' . $matches[1] . '):';
		}
		// 背景色
		if (preg_match("/background-color:\s?([#0-9a-z]+)/i", $attribute, $matches)) {
			$text .= 'BGCOLOR(' . $matches[1] . '):';
		}
		// 文字色
		if (preg_match("/(\"|\s)color:\s?([#0-9a-z]+)/i", $attribute, $matches)) {
			$text .= 'COLOR(' . $matches[2] . '):';
		}
		// 整列
		if (preg_match("/align=\"(left|center|right)\"/", $attribute, $matches)) {
			$text .= strtoupper($matches[1]) . ':';
		}
		// 横幅
		if (preg_match("/width=\"(\d+)\"/", $attribute, $matches)) {
			$text .= $matches[1];
		}
		
		return $text;
	}
	
	// テーブルを出力
	function OutputTable($cells, $type, $delm='|') {
		$row = count($cells);
		// $col = count($cells[1]);
		$col = isset($cells[1]) ? count($cells[1]) : 0;
		for ($i = 1; $i <= $row; $i++) {
			for ($j = 1; $j <= $col; $j++) {
				$this->body[] = $delm . $cells[$i][$j];
			}
			if ($delm != ',') {
				$this->body[] = '|' . $type . "\n";
			} else {
				$this->body[] = $type . "\n";
			}
		}
	}
	
	// 段落
	function Paragraph($line) {
		if (preg_match("/<(p|div)(\sstyle=\"text-align:\s*(left|center|right);?\s?\")?>(.*)/", $line, $matches)) {
			if ($matches[1] == 'p') {
				$this->OutputLine();
			}
			$line = ($matches[3] ? (strtoupper($matches[3]) . ':') : '') . $matches[4];
		}
		if (preg_match("/(.*)<\/(p|div)>/", $line, $matches)) {
			if ($matches[1]) {
				$this->OutputLine('', $matches[1]);
			}
			if ($matches[2] == 'p') {
				$this->OutputLine();
			}
		}
		else if ($line) {
			$this->OutputLine('', $line);
		}
	}
	
	// インライン要素
	function Inline($line) {
		$line = $this->EncodeSpecialChars($line);
		$line = preg_replace("/\n/", "", $line);
		
		// 水平線
		if ($this->GetDiv() != 'Heading' && $this->GetDiv() != 'Table') {
			$line = preg_replace("/<hr(\sclass=\"full_hr\")?\s\/>/", "\n----\n", $line);
			$line = preg_replace("/<hr\sclass=\"short_line\"\s\/>/", "\n#hr\n", $line);
		}
		// プラグイン
		$pattern = "/<span\s[^>]*?class=\"(plugin|ref)\".*?>(.*?);<\/span>/";
		$line = preg_replace_callback($pattern, array(&$this, 'InlinePlugin'), $line);
		// リンク
		$line = preg_replace_callback("/<a\shref=\"(.*?)\">(.*?)<\/a>/", array(&$this, 'Link'), $line);
		// アンカー
		$line = preg_replace("/<a\sname=\"(.*?)\"><\/a>/", "&aname($1);", $line);
		$line = preg_replace("/<a\sname=\"(.*?)\">(.*?)<\/a>/", "&aname($1){" . "$2" . "};", $line);
		// 顔文字・注釈・コメント
		$line = preg_replace_callback("/\s?<img\s(.*?)>/", array(&$this, 'Image'), $line);
		// 太字
		$line = preg_replace("/<\/?strong>/", "''", $line);
		// 斜体
		$line = preg_replace("/<\/?em>/", "'''", $line);
		// 下線
		$line = preg_replace("/<\/?u>/", "%%%", $line);
		// 取消線
		$line = preg_replace("/<\/?strike>/", "%%", $line);
		// 上付き文字
		$line = preg_replace("/<sup>(.*?)<\/sup>/", "&sup{"."$1"."};", $line);
		// 下付き文字・添え字
		$line = preg_replace("/<sub>(.*?)<\/sub>/", "&sub{"."$1"."};", $line);
		// 文字のサイズ・色
		$line = preg_replace_callback("/<(\/)?span(.*?)>/", array(&$this, 'Font'), $line);
		// 改行
		global $line_break;
		if ($this->GetDiv() == "Heading" || $this->GetDiv() == "Table") {
			$line = preg_replace("/<br\s\/>/", "&br;", $line);
		}
		else if ($line_break) {
			$line = preg_replace("/<br\s\/>(<br\s\/>)?/e", '("$1" ? "~" : "") . "\n"', $line);
		}
		else {
			$line = preg_replace("/<br\s\/>/", "~\n", $line);
		}
		
		// 無駄な改行を削除
		$line = preg_replace("/\n\n+/", "\n", $line);
		$line = preg_replace("/(^\n|\n$)/", "", $line);

		// タグの除去
		$line = strip_tags($line);
		// スペース
		$line = preg_replace("/&nbsp;/", " ", $line);
		// 特殊文字
		$line = preg_replace("/&quot;/", '"', $line);
		if ($this->GetDiv() == 'Heading' || $this->GetDiv() == 'Table') {
			$line = preg_replace("/\n/", '', $line);
			$line = preg_replace("/&lt;/", "<", $line);
			$line = preg_replace("/&gt;/", ">", $line);
		}
		else {
			$line = preg_replace("/\s+$/", '', $line);
			$line = preg_replace("/^\s+/m", '', $line);
		}
		
		$line = $this->DecodeSpecialChars($line);
		return $line;
	}

	// リンク
	function Link($matches) {
		$url = $matches[1];
		$alias = strip_tags($matches[2]);
		return '[[' . (($url == $alias) ? '' : $alias.'>') . $url.']]';
	}
	
	// 顔文字・注釈・コメント
	function Image($matches) {
		$attribute = $matches[1];
		if (preg_match("/alt=\"(.*?)\"/", $attribute, $matches)) {
			$alt = $matches[1];
			// 注釈
			if ($alt == 'Note' && preg_match("/title=\"(.+?)\"/", $attribute, $matches)) {
				return '((' . $this->DecodeSpecialChars($matches[1]) . '))';
			}
			// コメント
			else if ($alt == 'Comment' && preg_match("/title=\"(.+?)\"/", $attribute, $matches)) {
				$comment = $matches[1];
				$comment = str_replace("___br___", "\n//", $comment);
				$comment = "//" . $this->DecodeSpecialChars($comment);
				array_push($this->protect_data, $comment);
				return "\n___GUIPD" . count($this->protect_data) . "___\n";
			}
			// 顔文字
			// JO1UPK
			// else if (preg_match("/^\[.+\]$/", $alt, $matches)) {
			else if (preg_match("/^\[(.+)\]$/", $alt, $matches)) {
				return '&' . $matches[1].';';
			}
			return ' '.$alt.' ';
		}
		return '';
	}
	
	// 文字のサイズ・色
	function Font($matches) {
		static $foot_array = array();
		$attribute = $matches[2];
		
		if (!$matches[1]) {
			if (preg_match("/font-size:\s?((\d+)px|[a-z\-]+)/", $attribute, $matches)) {
				if ($matches[2]) {
					array_unshift($foot_array, '};');
					return "&size($matches[2]){";
				}
				switch ($matches[1]) {
					case 'xx-small':	$size = '1'; break;
					case 'x-small':		$size = '2'; break;
					case 'small':		$size = '3'; break;
					case 'medium':		$size = '4'; break;
					case 'large':		$size = '5'; break;
					case 'x-large':		$size = '6'; break;
					case 'xx-large':	$size = '7'; break;
				}
				if ($this->GetDiv() == 'Heading' || $this->GetDiv() == 'Table') {
					array_unshift($foot_array, '');
				}
				else {
					array_unshift($foot_array, "\n");
				}
				return 'SIZE('.$size.'):';
			}
			
			$pattern = "/rgb\((\d+),\s(\d+),\s(\d+)\)/e";
			$attribute = preg_replace($pattern, 'sprintf("#%02x%02x%02x", "$1", "$2", "$3")', $attribute);

			if (preg_match("/background-color:\s?([#0-9a-z]+)/i", $attribute, $matches)) {
				$bgcolor = $matches[1];
			}
			if (preg_match("/[^-]color:\s?([#0-9a-z]+)/i", $attribute, $matches)) {
				$color = $matches[1];
			}
			if (!empty($color) || !empty($bgcolor)) {
				array_unshift($foot_array, '};');
				return '&color(' . ($color ? $color : '') . ($bgcolor ? (',' . $bgcolor) : '') . '){';
			}

			return '';
		}
		else {
			return array_shift($foot_array);
		}
	}

	// インライン型プラグイン
	function InlinePlugin($matches) {
		static $pattern, $replace;

		if (!isset($pattern)) {
			$rule = array(
				"/&amp;/"		=> "&",
				"/&#037;&#037;/"	=> "%%",
				"/&#039;&#039;/"	=> "''",
				"/&#091;&#091;/"	=> "[[",
				"/&#093;&#093;/"	=> "]]",
				"/&#123;/"		=> "{",
				"/&#124;/"		=> "|",
				"/&#125;/"		=> "}"
			);
			$pattern = array_keys($rule);
			$replace = array_values($rule);
		}

		return preg_replace($pattern, $replace, $matches[2] . ';');
	}
	
	// ブロック要素の開始
	function StartDiv($element) {
		array_unshift($this->parent_div, $element);
		array_unshift($this->level_array, $this->div_level);
		$this->div_level = 0;
	}
	
	// ブロック要素の終了
	function EndDiv() {
		array_shift($this->parent_div);
		$this->div_level = array_shift($this->level_array);
		$this->text = '';
	}
	
	// 親のブロック要素を取得
	function GetDiv() {
		return $this->parent_div[0];
	}
	
	// １行出力
	function OutputLine($head = '', $line = '', $foot = '') {
		if ($line != '') {
			$line = $this->Inline($line);
		}
		$this->body[] = $head . $line . $foot . "\n";
		$this->text = '';
	}

	// 引用などのレベルは３までなので４以上の時は３を返す
	function GetLevel() {
		return ($this->div_level <= 3) ? $this->div_level : 3;
	}

	// エンコード
	function EncodeSpecialChars($line) {
		static $pattern = array("/\%\%/", "/\'\'/", "/\[\[/", "/\]\]/", "/\{/", "/\|/", "/\}/");
		static $replace = array("&#037;&#037;", "&#039;&#039;", "&#091;&#091;",
								"&#093;&#093;", "&#123;", "&#124;", "&#125;");

		return preg_replace($pattern, $replace, $line);
	}

	// 特殊な HTML エンティティを文字に戻す
	function DecodeSpecialChars($line) {
		static $pattern = array("/&amp;/", "/&lt;/", "/&gt;/", "/&quot;/", "/&nbsp;/","/&#091;/","/&#093;/");
		static $replace = array('&', '<', '>', '"', ' ','[',']');
		
		return preg_replace($pattern, $replace, $line);
	}
}

?>
