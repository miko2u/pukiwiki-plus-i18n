<?php
/**
 * PageBreak Plugin
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: pagebreak.inc.php,v 0.4 2007/01/02 01:38:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
function plugin_pagebreak_convert()
{
	global $head_tags;
	static $sw;

	// page-break-before, page-break-after, page-break-inside
	// return '<div style="page-break-before: always;">&nbsp;</div>'."\n";
	if (! isset($sw)) {
		$sw = true;
		$head_tags[] = <<<EOD
 <style type="text/css">
 <!--
 div.pagebreak { page-break-before:always; }
 -->
 </style>
EOD;
	}

	return '<div class="pagebreak">&nbsp;</div>'."\n";
}
?>
