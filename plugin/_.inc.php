<?php
/**
 * PukiWiki ページ内で gettext を実現するプラグイン
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: multilang.inc.php,v 0.1 2005/05/06 00:11:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * o :config/i18n/ja/text または :config/i18n/ja_JP/text でも良い
 * o 原則的には、言語名が適切であるものの、zh_TW などもあり 言語_国 の形式も選択可能。
 *
 * o &_(ja){掲示板};
 *   一度、英語の文字列を得て、表示言語に変換する。
 * o &_{BBS};
 *   英語表記であることを信じ、表示言語に変換する。
 *
 * 全てにおいて、未定義語の場合は、指定文字列をそのまま戻す。
 *
 */

function plugin___inline()
{
	global $language_considering_setting_level;
	global $language;

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
		$msg = _i18n_MsgGet($parm_lang, $def_lang[1], $msg, 1);
	}

	// :config から、単語を検索
	return _i18n_MsgGet($view_lang,$lang[1], $msg);
}

function _i18n_MsgGet($l_lang,$s_lang,$msg, $no = 0)
{
	// ex. :config/i18n/ja_JP/message
	$ConfName = 'i18n/'.$l_lang.'/text';
	if (! is_page(':config/'.$ConfName)) {
		$ConfName = 'i18n/'.$s_lang.'/text';
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
