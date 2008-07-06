<?php
/**
 * PukiWiki Plus! 目次プラグイン
 *
 * @copyright	Copyright &copy; 2004-2006,2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: toc.php,v 0.15 2008/07/06 12:22:00 upk Exp $
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

	$idx = toc_convert_index($idx);

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
	$i = -1;
	$sw = ($mode == 'toc') ? 0 : 1;

	foreach ($src as $_src) {
		// $sw は、#toc 以降の行だと true
		if ($sw == 0) {
			if (substr($_src,0,5) == '#toc(' || trim($_src) == '#toc') {
				$sw = 1;
				continue;
			}
		}

		// * で開始されない行は読み捨てる
		if (substr($_src,0,1) != '*') continue;
		$dat_lvl = min(3,strspn($_src, '*'));

		$i++;
		$rc[$i]['dat'] = $_src;
		$rc[$i]['sw']  = false;

		if (! $sw) continue;
		if (! toc_from_to_check($lvl,$dat_lvl)) continue;

		// [レコード][#toc前後]
		$rc[$i]['sw'] = true;
	}

	return $rc;
}


function toc_make_index_part($src, $id, $lvl)
{
	$rc = array();
	$start = 0;
	$i = -1;

	foreach ($src as $_src) {
		// if (substr($_src,0,5) == '#toc(') {
		if (substr($_src,0,5) == '#toc(' || trim($_src) == '#toc') {
			if ($id == toc_get_id('#toc',$_src,3)) {
				$start = 1;
				continue;
			}
			if ($start) break;
		}

		// * で開始されない行は読み捨てる
		if (substr($_src,0,1) != '*') continue;
		$dat_lvl = min(3,strspn($_src, '*'));

		$i++;
		$rc[$i]['dat'] = $_src;
		$rc[$i]['sw'] = false;

		if (! $start) continue;
		if (! toc_from_to_check($lvl,$dat_lvl)) continue;

		// [レコード][#toc前後]
		$rc[$i]['sw'] = true;
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
		$text = $data['dat'];
		$tag = $data['tag'];
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
		$text = $data['dat'];
		$tag = $data['tag'];
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

function toc_convert_index($idx)
{
	global $plugin_num_proc;
	global $fixed_heading_edited;
	static $num = 0;

	// 不要な行のカウント
	$off = 0;
	foreach($idx as $id=>$data) {
		if ($data['sw']) break;
		$off++;
	}

	// 全行を変換
	$lines = array();
	foreach($idx as $id=>$data) {
		$lines[] = $data['dat'];
	}

	$plugin_num_proc = 'toc'.$num++;
	$bkup_fixed_heading_edited = $fixed_heading_edited;
	$fixed_heading_edited = 0;
	$html = convert_html($lines);
	$fixed_heading_edited = $bkup_fixed_heading_edited;

	$rc = array();
	$i = 0;
	foreach (explode("\n", $html) as $line) {
		$line = trim($line);
		if (empty($line)) continue;
		$matches = array();
		preg_match("'<h(.?)(.*?)>(.*?)</h.?>'si", $line, $matches);
		if (!empty($matches[2])) {
			preg_match("'^id=\"(.*?)\"'si", trim($matches[2]), $mat);
			$matches[2] = $mat[1];
		}

		if ($off > $i) {
			$i++;
			continue;
		}

		$rc[$i]['tag'] = empty($matches[2]) ? '' : '#'.$matches[2];
		$rc[$i]['dat'] = strip_htmltag($matches[3]);
		$rc[$i]['lvl'] = $matches[1]-1;
		$i++;
	}
	return $rc;
}

?>
