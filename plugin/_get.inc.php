<?php
/**
 * I18N - 一時的に設定された各言語毎のメッセージを取得するプラグイン
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: _get.inc.php,v 0.1 2005/05/07 00:34:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

function plugin__get_inline()
{
	global $language_considering_setting_level;
	global $language;
	global $i18n_temp_msg;

	switch ( func_num_args() ) {
	case 1:
		list($msg) = func_get_args();
		// $parm_lang = DEFAULT_LANG;
		$parm_lang = 'en_US';
		break;
	default:
		list($parm_lang,$msg) = func_get_args();
	}

	// FIXME: level 4
	$view_lang = ($language_considering_setting_level == 0) ? get_language(4) : $language;
	$lang = accept_language::split_locale_str($view_lang); // ja_JP なら ja に分割

	if ($parm_lang == $view_lang || $parm_lang == $lang[1]) return $msg; // 指定言語と同じ

	// 指定文字列が en 以外の場合は、ベース言語に変換後、他言語に変換する
	$def_lang = accept_language::split_locale_str($parm_lang);

	if ($def_lang !== 'en') {
		$key = i18n_TempMsg_GetKey($parm_lang, $def_lang[1], $msg);
	} else {
		$key = i18n_TempMsg_GetKey($view_lang,$lang[1], $msg);
	}

	if ($key === FALSE) return $msg;
	if (!empty($i18n_temp_msg[$key][$view_lang]) ) return $i18n_temp_msg[$key][$view_lang];
	if (!empty($i18n_temp_msg[$key][$lang[1]]) )   return $i18n_temp_msg[$key][$lang[1]];
	return $msg;
}

function i18n_TempMsg_GetKey($l_lang, $s_lang, $msg)
{
	global $i18n_temp_msg;

	// get no(key)
	foreach($i18n_temp_msg as $tmp_no => $tmp_val) {
		foreach($tmp_val as $tmp_lang => $tmp_msg) {
			if ($tmp_lang == $l_lang || $tmp_lang == $s_lang) {
				if ($tmp_msg == $msg) return $tmp_no;
			}
		}
	}
	return FALSE;
}

?>
