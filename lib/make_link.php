<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: make_link.php,v 1.6.3 2004/11/23 09:39:42 miko Exp $
//

// リンクを付加する
function make_link($string, $page = '')
{
	global $vars;
	static $converter;

	if (! isset($converter)) $converter = new InlineConverter();

	$clone = $converter->get_clone($converter);

	return $clone->convert($string, ($page != '') ? $page : $vars['page']);
}

//インライン要素を置換する
class InlineConverter
{
	var $converters; // as array()
	var $pattern;
	var $pos;
	var $result;

	function get_clone($obj) {
		static $clone_func;

		if (! isset($clone_func)) {
			if (version_compare(PHP_VERSION, '5.0.0', '<')) {
				$clone_func = create_function('$a', 'return $a;');
			} else {
				$clone_func = create_function('$a', 'return clone $a;');
			}
		}
		return $clone_func($obj);
	}

	function __clone() {
		$converters = array();
		foreach ($this->converters as $key=>$converter) {
			$converters[$key] = $this->get_clone($converter);
		}
		$this->converters = $converters;
	}

	function InlineConverter($converters = NULL, $excludes = NULL)
	{
		if ($converters === NULL) {
			$converters = array(
				'plugin',        // インラインプラグイン
				'note',          // 注釈
				'url',           // URL
				'url_interwiki', // URL (interwiki definition)
				'mailto',        // mailto:
				'interwikiname', // InterWikiName
				'glossary',	 // AutoGlossary
				'autolink',      // AutoLink
				'bracketname',   // BracketName
				'wikiname',      // WikiName
				'glossary_a',	 // AutoGlossary(アルファベット)
				'autolink_a',    // AutoLink(アルファベット)
			);
		}

		if ($excludes !== NULL)
			$converters = array_diff($converters, $excludes);

		$this->converters = $patterns = array();
		$start = 1;

		foreach ($converters as $name) {
			$classname = "Link_$name";
			$converter = new $classname($start);
			$pattern   = $converter->get_pattern();
			if ($pattern === FALSE) continue;

			$patterns[] = "(\n$pattern\n)";
			$this->converters[$start] = $converter;
			$start += $converter->get_count();
			++$start;
		}
		$this->pattern = join('|', $patterns);
	}

	function convert($string, $page)
	{
		$this->page   = $page;
		$this->result = array();

		$string = preg_replace_callback("/{$this->pattern}/x",
			array(& $this, 'replace'), $string);

		$arr = explode("\x08", make_line_rules(htmlspecialchars($string)));
		$retval = '';
		while (! empty($arr)) {
			$retval .= array_shift($arr) . array_shift($this->result);
		}
		return $retval;
	}

	function replace($arr)
	{
		$obj = $this->get_converter($arr);

		$this->result[] = ($obj !== NULL && $obj->set($arr, $this->page) !== FALSE) ?
			$obj->toString() : make_line_rules(htmlspecialchars($arr[0]));

		return "\x08"; //処理済みの部分にマークを入れる
	}

	function get_objects($string, $page)
	{
		preg_match_all("/{$this->pattern}/x", $string, $matches, PREG_SET_ORDER);

		$arr = array();
		foreach ($matches as $match) {
			$obj = $this->get_converter($match);
			if ($obj->set($match, $page) !== FALSE) {
				$arr[] = $this->get_clone($obj);
				if ($obj->body != '')
					$arr = array_merge($arr, $this->get_objects($obj->body, $page));
			}
		}
		return $arr;
	}

	function & get_converter(& $arr)
	{
		foreach (array_keys($this->converters) as $start) {
			if ($arr[$start] == $arr[0])
				return $this->converters[$start];
		}
		return NULL;
	}
}

//インライン要素集合のベースクラス
class Link
{
	var $start;   // 括弧の先頭番号(0オリジン)
	var $text;    // マッチした文字列全体

	var $type;
	var $page;
	var $name;
	var $body;
	var $alias;

	// constructor
	function Link($start)
	{
		$this->start = $start;
	}

	// マッチに使用するパターンを返す
	function get_pattern() {}

	// 使用している括弧の数を返す ((?:...)を除く)
	function get_count() {}

	// マッチしたパターンを設定する
	function set($arr,$page) {}

	// 文字列に変換する
	function toString() {}

	// Private
	// マッチした配列から、自分に必要な部分だけを取り出す
	function splice($arr) {
		$count = $this->get_count() + 1;
		$arr   = array_pad(array_splice($arr, $this->start, $count), $count, '');
		$this->text = $arr[0];
		return $arr;
	}

	// 基本パラメータを設定する
	function setParam($page, $name, $body, $type = '', $alias = '')
	{
		static $converter = NULL;

		$this->page = $page;
		$this->name = $name;
		$this->body = $body;
		$this->type = $type;
		if (is_url($alias) && preg_match('/\.(gif|png|jpe?g)$/i', $alias)) {
			$alias = htmlspecialchars($alias);
			$alias = "<img src=\"$alias\" alt=\"$name\" />";
		} else if ($alias != '') {
			if ($converter === NULL)
				$converter = new InlineConverter(array('plugin'));

			$alias = make_line_rules($converter->convert($alias, $page));

			// BugTrack/669: A hack removing anchor tags added by AutoLink
			$alias = preg_replace('#</?a[^>]*>#i', '', $alias);
		}
		$this->alias = $alias;

		return TRUE;
	}
}

// インラインプラグイン
class Link_plugin extends Link
{
	var $pattern;
	var $plain,$param;

	function Link_plugin($start)
	{
		parent::Link($start);
	}

	function get_pattern()
	{
		$this->pattern = <<<EOD
&
(      # (1) plain
 (\w+) # (2) plugin name
 (?:
  \(
   ((?:(?!\)[;{]).)*) # (3) parameter
  \)
 )?
)
EOD;
		return <<<EOD
{$this->pattern}
(?:
 \{
  ((?:(?R)|(?!};).)*) # (4) body
 \}
)?
;
EOD;
	}

	function get_count()
	{
		return 4;
	}

	function set($arr, $page)
	{
		list($all, $this->plain, $name, $this->param, $body) = $this->splice($arr);

		// 本来のプラグイン名およびパラメータを取得しなおす PHP4.1.2 (?R)対策
		if (preg_match("/^{$this->pattern}/x", $all, $matches)
			&& $matches[1] != $this->plain)
		{
			list(, $this->plain, $name, $this->param) = $matches;
		}
		return parent::setParam($page, $name, $body, 'plugin');
	}

	function toString()
	{
		$body = ($this->body == '') ? '' : make_link($this->body);

		// プラグイン呼び出し
		if (exist_plugin_inline($this->name)) {
			$str = do_plugin_inline($this->name, $this->param, $body);
			if ($str !== FALSE) //成功
				return $str;
		}

		// プラグインが存在しないか、変換に失敗
		$body = ($body == '') ? ';' : "\{$body};";
		return make_line_rules(htmlspecialchars('&' . $this->plain) . $body);
	}
}

// 注釈
class Link_note extends Link
{
	function Link_note($start)
	{
		parent::Link($start);
	}

	function get_pattern()
	{
		return <<<EOD
\(\(
 ((?:(?R)|(?!\)\)).)*) # (1) note body
\)\)
EOD;
	}

	function get_count()
	{
		return 1;
	}

	function set($arr, $page)
	{
		global $foot_explain;
		static $note_id = 0;

		list(, $body) = $this->splice($arr);

		$id   = ++$note_id;
		$note = make_link($body);

		$foot_explain[$id] = <<<EOD
<a id="notefoot_$id" href="#notetext_$id" class="note_super">*$id</a>
<span class="small">$note</span>
<br />
EOD;
		$name = "<a id=\"notetext_$id\" href=\"#notefoot_$id\" class=\"note_super\">*$id</a>";

		return parent::setParam($page, $name, $body);
	}

	function toString()
	{
		return $this->name;
	}
}

// url
class Link_url extends Link
{
	function Link_url($start)
	{
		parent::Link($start);
	}

	function get_pattern()
	{
		$s1 = $this->start + 1;
		return <<<EOD
(\[\[             # (1) open bracket
 ((?:(?!\]\]).)+) # (2) alias
 (?:>|:)
)?
(                 # (3) url
 (?:(?:https?|ftp|news):\/\/|mailto:)[\w\/\@\$()!?&%#:;.,~'=*+-]+
)
(?($s1)\]\])      # close bracket
EOD;
	}

	function get_count()
	{
		return 3;
	}

	function set($arr, $page)
	{
		list(, , $alias, $name) = $this->splice($arr);
		return parent::setParam($page, htmlspecialchars($name),
			'', 'url', $alias == '' ? $name : $alias);
	}

	function toString()
	{
//		return "<a href=\"{$this->name}\">{$this->alias}</a>";
		return open_uri_in_new_window("<a href=\"{$this->name}\">{$this->alias}</a>", get_class($this));
	}
}

// url (InterWiki definition type)
class Link_url_interwiki extends Link
{
	function Link_url_interwiki($start)
	{
		parent::Link($start);
	}

	function get_pattern()
	{
		return <<<EOD
\[       # open bracket
(        # (1) url
 (?:(?:https?|ftp|news):\/\/|\.\.?\/)[!~*'();\/?:\@&=+\$,%#\w.-]*
)
\s
([^\]]+) # (2) alias
\]       # close bracket
EOD;
	}

	function get_count()
	{
		return 2;
	}

	function set($arr, $page)
	{
		list(, $name, $alias) = $this->splice($arr);
		return parent::setParam($page, htmlspecialchars($name), '', 'url', $alias);
	}

	function toString()
	{
//		return "<a href=\"{$this->name}\">{$this->alias}</a>";
		return open_uri_in_new_window("<a href=\"{$this->name}\">{$this->alias}</a>", get_class($this));
	}
}

//mailto:
class Link_mailto extends Link
{
	var $is_image, $image;

	function Link_mailto($start)
	{
		parent::Link($start);
	}

	function get_pattern()
	{
		$s1 = $this->start + 1;
		return <<<EOD
(?:
 \[\[
 ((?:(?!\]\]).)+)(?:>|:)  # (1) alias
)?
([\w.-]+@[\w-]+\.[\w.-]+) # (2) mailto
(?($s1)\]\])              # close bracket if (1)
EOD;
	}

	function get_count()
	{
		return 2;
	}

	function set($arr, $page)
	{
		list(, $alias, $name) = $this->splice($arr);
		return parent::setParam($page, $name, '', 'mailto', $alias == '' ? $name : $alias);
	}
	
	function toString()
	{
		return "<a href=\"mailto:{$this->name}\">{$this->alias}</a>";
	}
}

//InterWikiName
class Link_interwikiname extends Link
{
	var $url    = '';
	var $param  = '';
	var $anchor = '';

	function Link_interwikiname($start)
	{
		parent::Link($start);
	}

	function get_pattern()
	{
		$s2 = $this->start + 2;
		$s5 = $this->start + 5;
		return <<<EOD
\[\[                  # open bracket
(?:
 ((?:(?!\]\]).)+)>    # (1) alias
)?
(\[\[)?               # (2) open bracket
((?:(?!\s|:|\]\]).)+) # (3) InterWiki
(?<! > | >\[\[ )      # not '>' or '>[['
:                     # separator
(                     # (4) param
 (\[\[)?              # (5) open bracket
 (?:(?!>|\]\]).)+
 (?($s5)\]\])         # close bracket if (5)
)
(?($s2)\]\])          # close bracket if (2)
\]\]                  # close bracket
EOD;
	}

	function get_count()
	{
		return 5;
	}

	function set($arr, $page)
	{
		global $script;

		list(, $alias, , $name, $this->param) = $this->splice($arr);

		if (preg_match('/^([^#]+)(#[A-Za-z][\w-]*)$/', $this->param, $matches))
			list(, $this->param, $this->anchor) = $matches;

		$url = get_interwiki_url($name, $this->param);
		$this->url = ($url === FALSE) ?
			$script . '?' . rawurlencode('[[' . $name . ':' . $this->param . ']]') :
			htmlspecialchars($url);

		return parent::setParam(
			$page,
			htmlspecialchars($name . ':' . $this->param),
			'',
			'InterWikiName',
			$alias == '' ? $name . ':' . $this->param : $alias
		);
	}

	function toString()
	{
//		return "<a href=\"{$this->url}{$this->anchor}\" title=\"{$this->name}\">{$this->alias}</a>";
		return open_uri_in_new_window("<a href=\"{$this->url}{$this->anchor}\" title=\"{$this->name}\">{$this->alias}</a>", get_class($this));
	}
}

// BracketName
class Link_bracketname extends Link
{
	var $anchor, $refer;

	function Link_bracketname($start)
	{
		parent::Link($start);
	}

	function get_pattern()
	{
		global $WikiName, $BracketName;

		$s2 = $this->start + 2;
		return <<<EOD
\[\[                     # open bracket
(?:((?:(?!\]\]).)+)>)?   # (1) alias
(\[\[)?                  # (2) open bracket
(                        # (3) PageName
 (?:$WikiName)
 |
 (?:$BracketName)
)?
(\#(?:[a-zA-Z][\w-]*)?)? # (4) anchor
(?($s2)\]\])             # close bracket if (2)
\]\]                     # close bracket
EOD;
	}

	function get_count()
	{
		return 4;
	}

	function set($arr, $page)
	{
		global $WikiName;

		list(, $alias, , $name, $this->anchor) = $this->splice($arr);

		if ($name == '' && $this->anchor == '') return FALSE;

		if ($name == '' || ! preg_match("/^$WikiName$/", $name)) {

			if ($alias == '') $alias = $name . $this->anchor;

			if ($name != '') {
				$name = get_fullname($name, $page);
				if (! is_pagename($name)) return FALSE;
			}
		}

		return parent::setParam($page, $name, '', 'pagename', $alias);
	}

	function toString()
	{
//miko
		global $fancyurl;
		if ($fancyurl) {
			$link_result =  make_pagelink(
				$this->name,
				$this->alias,
				$this->anchor,
				$this->page
			);
			$link_result = preg_replace("/href=\"([^\?]+)\?([^\"]+)\" title=/", "href=\"".get_fancy_uri()."/$2\" title=", $link_result);
			return $link_result;
		} else {
//miko
		return make_pagelink(
			$this->name,
			$this->alias,
			$this->anchor,
			$this->page
		);
//miko
		}
//miko
	}
}

// WikiName
class Link_wikiname extends Link
{
	function Link_wikiname($start)
	{
		parent::Link($start);
	}

	function get_pattern()
	{
		global $WikiName, $nowikiname;

		return $nowikiname ? FALSE : '(' . $WikiName . ')';
	}

	function get_count()
	{
		return 1;
	}

	function set($arr, $page)
	{
		list($name) = $this->splice($arr);
		return parent::setParam($page, $name, '', 'pagename', $name);
	}

	function toString()
	{
		return make_pagelink(
			$this->name,
			$this->alias,
			'',
			$this->page
		);
	}
}

// AutoLink
class Link_autolink extends Link
{
	var $forceignorepages = array();
	var $auto;
	var $auto_a; // alphabet only

	function Link_autolink($start)
	{
		global $autolink;

		parent::Link($start);

		if (! $autolink || ! file_exists(CACHE_DIR . 'autolink.dat'))
			return;

		@list($auto, $auto_a, $forceignorepages) = file(CACHE_DIR . 'autolink.dat');
		$this->auto   = $auto;
		$this->auto_a = $auto_a;
		$this->forceignorepages = explode("\t", trim($forceignorepages));
	}

	function get_pattern()
	{
		return isset($this->auto) ? "({$this->auto})" : FALSE;
	}

	function get_count()
	{
		return 1;
	}

	function set($arr, $page)
	{
		global $WikiName;

		list($name) = $this->splice($arr);

		// 無視リストに含まれている、あるいは存在しないページを捨てる
		if (in_array($name, $this->forceignorepages) || ! is_page($name))
			return FALSE;

		return parent::setParam($page, $name, '', 'pagename', $name);
	}

	function toString()
	{
		global $autolink;
		if (!$autolink) return $this->name;
//miko
		global $fancyurl;
		if ($fancyurl) {
			$link_result =  make_pagelink(
				$this->name,
				$this->alias,
				'',
				$this->page
			);
			$link_result = preg_replace("/href=\"([^\?]+)\?([^\"]+)\" title=/", "href=\"".get_fancy_uri()."/$2\" title=", $link_result);
			return $link_result;
		} else {
//miko
		return make_pagelink(
			$this->name,
			$this->alias,
			'',
			$this->page
		);
//miko
		}
//miko
	}
}

class Link_autolink_a extends Link_autolink
{
	function Link_autolink_a($start)
	{
		parent::Link_autolink($start);
	}

	function get_pattern()
	{
		return isset($this->auto_a) ? '(' . $this->auto_a . ')' : FALSE;
	}
}

// Glossary
class Link_glossary extends Link
{
	var $forceignorepages = array();
	var $auto;
	var $auto_a; // alphabet only

	function Link_glossary($start)
	{
		global $autoglossary;

		parent::Link($start);

		if (!$autoglossary or !file_exists(CACHE_DIR.'glossary.dat'))
		{
			return;
		}
		@list($auto,$auto_a,$forceignorepages) = file(CACHE_DIR.'glossary.dat');
		$this->auto = $auto;
		$this->auto_a = $auto_a;
		$this->forceignorepages = explode("\t",trim($forceignorepages));
	}
	function get_pattern()
	{
		return isset($this->auto) ? "({$this->auto})" : FALSE;
	}
	function get_count()
	{
		return 1;
	}
	function set($arr,$page)
	{
		list($name) = $this->splice($arr);
		// 無視リストに含まれている、あるいは存在しないページを捨てる
		if (in_array($name,$this->forceignorepages))
		{
			return FALSE;
		}
		return parent::setParam($page,$name,'','pagename',$name);
	}
	function toString()
	{
		global $autoglossary;
		if (!$autoglossary) return $this->name;
		return make_tooltips(
			$this->name
		);
	}
}
class Link_glossary_a extends Link_glossary
{
	function Link_glossary_a($start)
	{
		parent::Link_glossary($start);
	}
	function get_pattern()
	{
		return isset($this->auto_a) ? "({$this->auto_a})" : FALSE;
	}
}

// ツールチップの展開
function make_tooltips($term,$glossarypage='')
{
	global $script;
	static $tooltip_initialized = FALSE;
	if (!exist_plugin('tooltip')) { return FALSE; }
	if (!$tooltip_initialized) {
		do_plugin_init('tooltip');
		$tooltip_initialized = TRUE;
	}

	$glossary = plugin_tooltip_get_glossary($term,$glossary_page);
	if ( $glossary === FALSE ) {
		$glossary = plugin_tooltip_get_page_title($term);
		if ( $glossary === FALSE ) $glossary = "";
	}
        $s_glossary = htmlspecialchars($glossary);

        $page = strip_bracket($term);
        if ( is_page($page) ) {
                $f_page = rawurlencode($page);
                $passage = get_pg_passage($page,FALSE);
                return <<<EOD
<a href="$script?$f_page" class="linktip" title="$s_glossary$passage">$term</a>
EOD;
        }
        else {
        return <<<EOD
<span class="tooltip" title="$s_glossary" onmouseover="javascript:this.style.backgroundColor='#ffe4e1';" onmouseout="javascript:this.style.backgroundColor='transparent';">$term</span>
EOD;
        }
}

// ページ名のリンクを作成
function make_pagelink($page, $alias = '', $anchor = '', $refer = '')
{
	global $script, $vars, $show_title, $show_passage, $link_compact, $related;
	global $_symbol_noexists;

	$s_page = htmlspecialchars(strip_bracket($page));
	$s_alias = ($alias == '') ? $s_page : $alias;

	if ($page == '') return "<a href=\"$anchor\">$s_alias</a>";
//	if ($page == '') return open_uri_in_new_window("<a href=\"$anchor\">$s_alias</a>", "make_pagelink");

	$r_page  = rawurlencode($page);
	$r_refer = ($refer == '') ? '' : '&amp;refer=' . rawurlencode($refer);

	if (! isset($related[$page]) && $page != $vars['page'] && is_page($page))
		$related[$page] = get_filetime($page);

	if (is_page($page)) {
		$passage = get_pg_passage($page, FALSE);
		$title   = $link_compact ? '' : " title=\"$s_page$passage\"";
		return "<a href=\"$script?$r_page$anchor\"$title>$s_alias</a>";
//		return open_uri_in_new_window("<a href=\"$script?$r_page$anchor\"$title>$s_alias</a>", "make_pagelink");
	} else {
		$retval = "$s_alias<a href=\"$script?cmd=edit&amp;page=$r_page$r_refer\">$_symbol_noexists</a>";
		if (! $link_compact)
			$retval = "<span class=\"noexists\">$retval</span>";
		return $retval;
//		return open_uri_in_new_window($retval, "make_pagelink_e");
	}
}

// 相対参照を展開
function get_fullname($name, $refer)
{
	global $defaultpage;

	if ($name == '') return $refer;

	if ($name{0} == '/') {
		$name = substr($name, 1);
		return ($name == '') ? $defaultpage : $name;
	}

	if ($name == './') return $refer;
	if (substr($name, 0, 2) == './') {
		$arrn = preg_split('/\//', $name, -1, PREG_SPLIT_NO_EMPTY);
		$arrn[0] = $refer;
		return join('/', $arrn);
	}

	if (substr($name, 0, 3) == '../') {
		$arrn = preg_split('/\//', $name,  -1, PREG_SPLIT_NO_EMPTY);
		$arrp = preg_split('/\//', $refer, -1, PREG_SPLIT_NO_EMPTY);

		while (! empty($arrn) && $arrn[0] == '..') {
			array_shift($arrn);
			array_pop($arrp);
		}
		$name = ! empty($arrp) ? join('/', array_merge($arrp, $arrn)) :
			(! empty($arrn) ? "$defaultpage/" . join('/', $arrn) : $defaultpage);
	}

	return $name;
}

// InterWikiNameを展開
function get_interwiki_url($name, $param)
{
	global $WikiName, $interwiki;
	static $interwikinames;
	static $encode_aliases = array('sjis'=>'SJIS', 'euc'=>'EUC-JP', 'utf8'=>'UTF-8');

	if (! isset($interwikinames)) {
		$interwikinames = $matches = array();
		foreach (get_source($interwiki) as $line) {
			if (preg_match('/\[((?:(?:https?|ftp|news):\/\/|\.\.?\/)[!~*\'();\/?:\@&=+\$,%#\w.-]*)\s([^\]]+)\]\s?([^\s]*)/', $line, $matches))
				$interwikinames[$matches[2]] = array($matches[1], $matches[3]);
		}
	}

	if (! isset($interwikinames[$name])) return FALSE;

	list($url, $opt) = $interwikinames[$name];

	// 文字エンコーディング
	switch ($opt) {

	case '':
	case 'std': // 内部文字エンコーディングのままURLエンコード
		$param = rawurlencode($param);
		break;

	case 'asis':
	case 'raw':
		// $param = htmlspecialchars($param);
		break;

	case 'yw': // YukiWiki
		if (! preg_match("/$WikiName/", $param))
			$param = '[[' . mb_convert_encoding($param, 'SJIS', SOURCE_ENCODING) . ']]';
		// $param = htmlspecialchars($param);
		break;

	case 'moin': // MoinMoin
		$param = str_replace('%', '_', rawurlencode($param));
		break;

	default:
		// エイリアスの変換
		if (isset($encode_aliases[$opt])) $opt = $encode_aliases[$opt];
		// 指定された文字コードへエンコードしてURLエンコード
		$param = rawurlencode(mb_convert_encoding($param, $opt, 'auto'));
	}

	// パラメータを置換
	if (strpos($url, '$1') !== FALSE) {
		$url = str_replace('$1', $param, $url);
	} else {
		$url .= $param;
	}

	$len = strlen($url);
	if ($len > 512) die_message('InterWiki URL too long: ' . $len . ' characters');

	return $url;
}
?>
