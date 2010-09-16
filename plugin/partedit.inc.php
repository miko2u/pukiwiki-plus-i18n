<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: partedit.inc.php,v 1.1.2 2004/09/18 20:27:00 miko Exp $
//

// 凍結時は強制的にオフにする
define(PARTEDIT_FREEZE_OFF,TRUE);

function plugin_partedit_inline()
{
        return plugin_partedit_convert();
}

function plugin_partedit_convert()
{
	global $vars, $fixed_heading_edited;
	list($arg) = func_get_args();

	// 強制オン・オフ
	if ($arg == 'on') {
		$fixed_heading_edited = 1;
	}
	if ($arg == 'off') {
		$fixed_heading_edited = 0;
	}
	if ($arg == 'default' || $arg == '' || !isset($arg) ) {
//		$fixed_heading_edited = 0;
	}

	// 凍結時のみ強制的にオフ
	if (PARTEDIT_FREEZE_OFF) {
		if (is_freeze($vars['page'])) {
			$fixed_heading_edited = 0;
		}
	}
	return '';
}
?>
