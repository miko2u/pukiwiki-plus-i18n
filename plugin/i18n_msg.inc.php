<?php
/**
 * ����𔻒肵���b�Z�[�W��\��
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: i18n_msg.inc.php,v 0.2 2005/03/08 23:09:00 upk Exp $
 *
 */

function plugin_i18n_msg_convert()
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
	if ($lang != $env) return "";

	$lines = preg_replace(array("[\\r|\\n]","[\\r]"), array("\n","\n"), $lines);
	return preg_replace(array("'<p>'si","'</p>'si"), array("",""), convert_html($lines) );

}

?>
