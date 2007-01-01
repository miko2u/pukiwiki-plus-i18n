<?php
/**
 * PageBreak Plugin
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: pagebreak.inc.php,v 0.2 2007/01/02 01:12:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
function plugin_pagebreak_convert()
{
	// page-break-before, page-break-after, page-break-inside
	return '<span style="page-break-before: always;"></span>'."\n";
}
?>
