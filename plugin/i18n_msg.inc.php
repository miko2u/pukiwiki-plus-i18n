<?php
/**
 * 言語を判定しメッセージを表示
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: i18n_msg.inc.php,v 0.1 2005/03/06 23:45:00 upk Exp $
 *
 */

function plugin_i18n_msg_convert()
{
	global $language;

	switch ( func_num_args() ) {
	case 1:
		list($lines) = func_get_args();
		$lang = DEFAULT_LANG;
		break;
	default:
		list($lang,$lines) = func_get_args();
	}

	if ($lang != $language) return "";

	$lines = explode("\r", $lines);
	$rc = "";
	foreach($lines as $_lines) {
		$rc .= preg_replace(array("'<p>'si","'</p>'si"), array("",""), make_line_rules( convert_html($_lines) ));
	}
	return $rc;
}

?>
