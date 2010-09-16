<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone
// $Id: pagebreak.inc.php,v 0.1 2007/01/03 18:48:00 upk Exp $
// Copyright (C)
//   2007 PukiWiki Plus! Team
// License: GNU Public License (GPL2)
//
function plugin_pagebreak_convert()
{
	// page-break-before, page-break-after, page-break-inside
	// FIXME: Only IE will operate.
	return '<div style="page-break-before: always;">&nbsp;</div>'."\n";
}
?>