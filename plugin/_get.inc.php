<?php
/**
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: _get.inc.php,v 0.2 2006/04/30 16:56:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 */

function plugin__get_inline()
{
	switch ( func_num_args() ) {
	case 2:
		list($msg) = func_get_args();
		return strip_htmltag($msg);
	case 3:
		list($name,$msg) = func_get_args();
		return i18n_gettext($name,$msg);
	case 4:
		list($name,$lang,$msg) = func_get_args();
		return i18n_setlocale($name,$lang,$msg);
	}

	return '';
}

function i18n_gettext($name,$msg)
{
	global $plugin_lang_path;
	static $checked = array();

	if (! isset($checked[$name])) {
		$checked[$name] = 1;
		if (empty($plugin_lang_path[$name])) {
			bindtextdomain($name, LANG_DIR);
		} else {
			bindtextdomain($name, $plugin_lang_path[$name]);
		}
		// bindtextdomain($name, LANG_DIR);
		bind_textdomain_codeset($name, SOURCE_ENCODING);
	}

	textdomain($name);
	$text = _( rawurldecode($msg) );
	textdomain(DOMAIN);
	return $text;
}

function i18n_setlocale($name,$lang,$msg)
{
	putenv('LC_ALL=' . $lang);
	setlocale(LC_ALL, $lang);
	$text = i18n_gettext($name,$msg);
	putenv('LC_ALL=' . PO_LANG);
	setlocale(LC_ALL, PO_LANG);
	return $text;
}

?>
