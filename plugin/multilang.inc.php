<?php
/**
 * 言語を判定しメッセージを表示
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: multilang.inc.php,v 0.4 2005/03/14 01:01:00 upk Exp $
 *
 */

function plugin_multilang_convert()
{
	global $language_considering_setting_level;
	global $language;

	switch ( func_num_args() ) {
	case 1:
		list($lines) = func_get_args();
		$lang = DEFAULT_LANG;
		break;
	default:
		list($lang,$lines) = func_get_args();
	}

	// FIXME: level 4
	$env = ($language_considering_setting_level == 0) ? get_language(4) : $language;
	$l = accept_language::split_locale_str($env);

	if ($lang == $env || $lang == $l[1]) {
		$lines = preg_replace(array("[\\r|\\n]","[\\r]"), array("\n","\n"), $lines);
		return preg_replace(array("'<p>'si","'</p>'si"), array("",""), convert_html($lines) );
	}

	return "";
}

?>
