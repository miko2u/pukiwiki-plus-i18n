<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: skin.inc.php,v 1.1.2 2004/10/12 05:38:46 miko Exp $
//
function plugin_skin_convert()
{
	global $skin_file;

	if (func_num_args() != 1) {
		return '';
	}
	list($skin_name) = func_get_args();

	$skin_temp = DATA_HOME . SKIN_DIR . $skin_name . '.skin.php';
	if (file_exists($skin_temp) && is_readable($skin_temp)) {
		$skin_file = $skin_temp;
		return '';
	}

	// Back compat for Pukiwiki Plus!(1.4.4plus-u2)
	$skin_temp = DATA_HOME . SKIN_DIR . $skin_name . '.skin.' . LANG . '.php';
	if (file_exists($skin_temp) && is_readable($skin_temp)) {
		$skin_file = $skin_temp;
		return '';
	}

	return '<!-- Cant replace skin. Use default. -->';
}
?>
