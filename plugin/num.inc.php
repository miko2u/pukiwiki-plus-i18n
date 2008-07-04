<?php
/**
 * num (ナンバリング) プラグイン
 *
 * @copyright   Copyright &copy; 2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: resize.inc.php,v 0.1 2008/07/05 01:41:00 upk Exp $
 *
 * - 目次作成時にナンバリングがダブルカウントされないようにする
 * - 見出し(*)と箇条書き(-)での使い分けができること
 * - 各見出し内で完結したナンバリングが可能なこと
 *
 * &num(div,depth,mark);
 * 見出し区切り, 深さ, 記号
 *
 */

function plugin_num_inline()
{
	global $plugin_num_proc; // toc などによる初期化制御用
	static $count;
	static $bkup_depth = 0;
	static $sw_count = false;

	// パラメータの手当て
	$argv = func_get_args();
	$argc = func_num_args();

	$data = $argv[ --$argc ]; // インラインの場合のみ
	$field = array('div','depth','mark');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = $argv[$i];
	}
	// default
	if (empty($div)) $div = '';
	if (empty($depth)) $depth = 1;
	if (empty($mark)) $mark = '*';

	// toc プラグインなど、指定された場合には、別途初期化
	$proc = (! empty($plugin_num_proc)) ? $plugin_num_proc : '';

	// カウンタの初期化
	if (! isset($count[$proc][$div])) {
		$bkup_depth = $depth;
		$count[$proc][$div] = array(0,0,0);
	}

	// 下位レベルの初期化
	if ($bkup_depth > $depth) {
		for($i=$depth; $i<3; ++$i) {
			$count[$proc][$div][$i] = 0;
		}
	}

	// 見出し時はダブルカウントされるため回避
	if ($mark == '*') {
		$sw_count = ($sw_count) ? false : true;
	} else {
		$sw_count = true;
	}

	if ($sw_count) {
		$count[$proc][$div][$depth-1]++;
	}

	// 編集処理
	$ret = '';
	for($i=0; $i<$depth; ++$i) {
		if ($count[$proc][$div][$i] == 0) $count[$proc][$div][$i]++;
		$ret .= $count[$proc][$div][$i];
		$ret .= '.';
	}

	$bkup_depth = $depth;
	return $ret;
}

?>
