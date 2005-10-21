<?php
/**
 * PukiWiki Plus! 目次プラグイン
 *
 * @copyright	Copyright &copy; 2004-2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: toc.php,v 0.9 2005/10/21 21:46:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link	http://jo1upk.blogdns.net/saito/
 */

/**
 * ブロック型プラグイン処理
 */
function plugin_toc_convert()
{
	global $vars;

	// global $fixed_heading_anchor;
	// if (!$fixed_heading_anchor) return '';	// 固有アンカーの機能が有効か？

	// パラメータの取得
	$argv = func_get_args();
	$argc = func_num_args();
	//$data = $argv[ --$argc ];
	$field = array('lvl','view', 'mode', 'id');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = $argv[$i];
	}

	if (empty($lvl)) $lvl = 3;		// 表示レベル : 3 まで表示
	if (empty($view)) $view = 'toc';	// 表示形式   : toc, tree
	if (empty($mode)) $mode = 'toc';	// 処理対象   : full, toc, part
	if (empty($id)) $id = '';		// #toc 位置識別子

	$src = get_source($vars['page']);	// ページの取得
	if (count($src) == 0) return '';	// 該当ページなし

	if ($mode == 'part') {
		$idx = toc_make_index_part($src, $id, $lvl);
	} else {
		// full, toc
		$idx = toc_make_index($src, $mode, $lvl);
	}

	// 整形処理
	if ($view == 'tree') {
		return toc_mode_contents($idx);
	}
	return toc_mode_toc($idx);
}

function toc_from_to_check($lvl,$dat_lvl)
{
	$chk_lvl = abs($lvl);

	if ($lvl < 0) {
		if ($dat_lvl < $chk_lvl) return 0;
	} else {
		if ($dat_lvl > $chk_lvl) return 0;
	}
	return 1;
}

/*
 * インデックスの把握
 */
function toc_make_index($src, $mode, $lvl)
{
	$rc = array();
	$i = 0;
	$sw = ($mode == 'toc') ? 0 : 1;

	foreach ($src as $_src) {
		// $sw は、#toc 以降の行だと true
		if ($sw == 0 && substr($_src,0,5) == '#toc(') {
			$sw = 1;
			continue;
		}
		if (! $sw) continue;

		// * で開始されない行は読み捨てる
		if (substr($_src,0,1) != '*') continue;
		$dat_lvl = min(3,strspn($_src, '*'));

		if (! toc_from_to_check($lvl,$dat_lvl)) continue;

		// [レコード][#toc前後]
		$rc[$i]['dat'] = $_src;
		$rc[$i]['lvl'] = $dat_lvl;
		$i++;
	}

	return $rc;
}


function toc_make_index_part($src, $id, $lvl)
{
	$rc = array();
	$start = $i = 0;

	foreach ($src as $_src) {
		if (substr($_src,0,5) == '#toc(') {
			if ($id == toc_get_id('#toc',$_src,3)) {
				$start = 1;
				continue;
			}
			if ($start) break;
		}
		if (! $start) continue;

		// * で開始されない行は読み捨てる
		if (substr($_src,0,1) != '*') continue;
		$dat_lvl = min(3,strspn($_src, '*'));

		if (! toc_from_to_check($lvl,$dat_lvl)) continue;

		// [レコード][#toc前後]
		$rc[$i]['dat'] = $_src;
		$rc[$i]['lvl'] = $dat_lvl;
		$i++;
	}

	return $rc;
}

/*
 * パラメータ解析
 */
function toc_get_id($name,$data,$no)
{
	preg_match("'$name\((.*?)\)'si", $data, $regs);
	if (empty($regs[1])) return '';
	$opt = explode(',', $regs[1]);
	if (empty($opt[$no])) return '';
	return trim($opt[$no]);
}

/*
 * 明細編集(階層表示なし)
 */
function toc_mode_toc($idx)
{
	$hed = '#content_1_';
	$sw = 1;
	$rc = '';

	foreach ($idx as $id => $data) {
		list($text,$tag) = toc_trim_pw($data['dat']);
		$link = (empty($tag)) ? $hed.$id : $tag;
		if ($sw) {
			$sw = 0;
		} else {
			$rc .= ' / ';
		}
		$rc .= '<a href="'. $link . '">'. $text . '</a>';
	}

	//      '<div style="text-align:left;margin-left:10px;width:80%;">'.
	return  '<div style="text-align:left;margin-left:20px;width:90%;">'.
		$rc.
		'</div>';
}

/*
 * #contents 互換モード
 * 定義箇所以前は無視し、指定階層まで編集する。
 */
function toc_mode_contents($idx)
{
	static $seq = 0;

	$hed  = '#content_1_';
	$rc = '';

	$top_lvl = toc_get_top_level($idx);
	$format = '<ul class="list%s" style="padding-left:%spx;margin-left:%spx">' .
		  '<li><a href="%s">%s</a></li></ul>'."\n";

	foreach ($idx as $data) {
		list($text,$tag) = toc_trim_pw($data['dat']);
		$link = (empty($tag)) ? $hed.++$seq : $tag;

		$i = $data['lvl'] - $top_lvl + 1;
		$pad_px = $i * 16;
		$rc .= sprintf($format, $i, $pad_px, $pad_px, $link, $text);
	}

	return $rc;
}

function toc_get_top_level($idx)
{
	$top_lvl = 9;
	foreach ($idx as $data) {
		$top_lvl = min($top_lvl, $data['lvl']);
	}
	return $top_lvl;
}

/*
 * PukiWiki 固有の記述を除去
 */
function toc_trim_pw($line)
{
	preg_match("'(.*?)\[(#.*)\]'si",$line,$regs);
	if (isset($regs[1])) $line = $regs[1];
	$tag = (isset($regs[2])) ? $regs[2] : ''; // ID の取得
	$str = trim(strip_htmltag(convert_html($line)));
	return array($str,$tag);
}

?>
