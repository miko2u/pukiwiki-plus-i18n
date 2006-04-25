<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: skin.inc.php,v 1.1.4 2006/04/26 02:23:00 upk Exp $
//
function plugin_skin_convert()
{
	global $skin_file;

	if (func_num_args() != 1) {
		return '';
	}
	list($skin_name) = func_get_args();


	$skin_temp = array(
		SKIN_URI . basename($skin_name) . '.skin.php',
		DATA_HOME . SKIN_DIR . basename($skin_name) . '.skin.php',
		// Back compat for Pukiwiki Plus!(1.4.4plus-u2)
		DATA_HOME . SKIN_DIR . basename($skin_name) . '.skin.' . LANG . '.php',
	);

	foreach($skin_temp as $skin) {
		if (file_exists($skin) && is_readable($skin)) {
			$skin_file = $skin;
			return '';
		}
	}

	return '<!-- Cant replace skin. Use default. -->';
}
?>
