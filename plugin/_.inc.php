<?php
/**
 * Plug-in that achieves gettext in PukiWiki page
 * PukiWiki ページ内で gettext を実現するプラグイン
 *
 * @copyright   Copyright &copy; 2005-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: _.inc.php,v 0.10 2006/02/04 23:42:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * o :config/i18n/text/ja/text or :config/i18n/text/ja_JP is acceptable.
 * o There are zh_TW etc. ,
 *   too and the form of language_country can be selected though the language name is appropriate in principle.
 * o 原則的には、言語名が適切であるものの、zh_TW などもあり 言語_国 の形式も選択可能。
 *
 * o &_(ja){掲示板};
 *   一度、英語の文字列を得て、表示言語に変換する。
 * o &_{BBS};
 *   英語表記であることを信じ、表示言語に変換する。
 *
 * 全てにおいて、未定義語の場合は、指定文字列をそのまま戻す。
 * All, a specified character string is returned as it is for an undefined word.
 *
 */

function plugin___inline()
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

	$msg = strip_htmltag($msg);

        // FIXME: level 5
	$view_lang  = ($language_considering_setting_level == 0) ? get_language(5) : $language;
	$view_lang_split = accept_language::split_locale_str($view_lang); // ja_JP なら ja に分割

	if ($parm_lang == $view_lang || $parm_lang == $view_lang_split[1]) return $msg; // 指定言語と同じ

	// 指定文字列が en 以外の場合は、ベース言語に変換後、他言語に変換する
	$parm_lang_split = accept_language::split_locale_str($parm_lang);

	if (isset($i18n_temp_msg)) {
		$temp_msg = i18n_TempMsg($parm_lang_split, $view_lang_split, $msg);
		if (!empty($temp_msg)) return $temp_msg;
	}

	if ($parm_lang_split[1] !== 'en') {
		$msg = i18n_ConfMsgGet($parm_lang_split, $msg, 1);
	}

	// :config から、単語を検索
	return i18n_ConfMsgGet($view_lang_split, $msg);
}

function i18n_TempMsg($parm_lang_split, $view_lang_split, $msg)
{
	global $i18n_temp_msg;

	$key = i18n_TempMsg_GetKey($parm_lang_split, $msg);

	if ($key === FALSE) return '';
	if (!empty($i18n_temp_msg[$key][$view_lang_split[0]]) ) return $i18n_temp_msg[$key][$view_lang_split[0]];
	if (!empty($i18n_temp_msg[$key][$view_lang_split[1]]) ) return $i18n_temp_msg[$key][$view_lang_split[1]];
	return '';
}

function i18n_TempMsg_GetKey($lang, $msg)
{
	global $i18n_temp_msg;

	// get no(key)
	foreach($i18n_temp_msg as $tmp_no => $tmp_val) {
		foreach($tmp_val as $tmp_lang => $tmp_msg) {
			if ($tmp_lang == $lang[0] || $tmp_lang == $lang[1]) {
				if ($tmp_msg == $msg) return $tmp_no;
			}
		}
	}
	return FALSE;
}

function i18n_ConfMsgGet($lang, $msg, $no = 0)
{
	// ex. :config/i18n/text/zh_TW
	$ConfName = 'i18n/text/'.$lang[0];
	if (! is_page(':config/'.$ConfName)) {
		// ex. :config/i18n/text/zh
		$ConfName = 'i18n/text/'.$lang[1];
		if (! is_page(':config/'.$ConfName)) return $msg;
	}

	$obj = new Config($ConfName);
	$obj->read();
	$i18n_msg = & $obj->get('TEXT');
	unset($obj);

	$ret_no = ($no == 0) ? 1 : 0;

	foreach($i18n_msg as $text) {
		if ($text[$no] == $msg) return $text[$ret_no];
	}

	return $msg;
}

?>
