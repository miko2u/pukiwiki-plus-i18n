<?php
/**
 * I18N - 各言語毎のメッセージを一時的に設定するプラグイン
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: _set.inc.php,v 0.1 2005/05/07 23:38:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

function plugin__set_inline()
{
	global $i18n_temp_msg;
	$argc = func_num_args();
	$argv = func_get_args();
	list($key,$lang,$msg) = i18n_set_param($argc, $argv);
	$i18n_temp_msg[$key][$lang] = strip_htmltag($msg);
	return '';
}

function i18n_set_param($argc, $argv)
{
	$rc = array(0,'en','');
	$rc[2] = $argv[ --$argc ]; // msg
	if ($argc == 0) return $rc;
	$rc[1] = $argv[ --$argc ]; // lang
	if ($argc == 0) return $rc;
	$rc[0] = $argv[ --$argc ]; // no or key
	return $rc;
}

?>
