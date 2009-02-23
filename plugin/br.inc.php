<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: br.inc.php,v 1.5.1 2009/02/23 23:25:00 upk Exp $
// Copyright (C)
//   2009 PukiWiki Plus! Team
//   2003-2005, 2007 PukiWiki Developers Team
//
// "Forcing one line-break" plugin

// Escape using <br /> in <blockquote> (BugTrack/583)
define('PLUGIN_BR_ESCAPE_BLOCKQUOTE', 1);

// ----

define('PLUGIN_BR_TAG', '<br class="spacer" />');

function plugin_br_convert()
{
	$br = PLUGIN_BR_ESCAPE_BLOCKQUOTE ? '<div class="spacer">&nbsp;</div>' : PLUGIN_BR_TAG;
	if (func_num_args() == 1) {
		list($j) = func_get_args();
	} else {
		$j = 1;
	}
	$rc = '';
	for($i=0;$i<$j;$i++) { $rc .= $br; }
	return $rc;
}

function plugin_br_inline()
{
	return PLUGIN_BR_TAG;
}
?>
