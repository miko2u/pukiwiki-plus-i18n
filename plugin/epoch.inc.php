<?php
/**
 * PukiWiki Plus! epoch plugin.
 *
 * @copyright   Copyright &copy; 2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: epoch.inc.php,v 0.1 2008/06/17 00:47:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 *  for BugTrack/83
 *
 * &epoch(1234578098);
 * &epoch(1234578098,new);
 * &epoch(1234578098){2006-06-27 (火) 14:10:56};
 * &epoch(1234578098,new){2006-06-27 (火) 14:10:56};
 */

function plugin_epoch_inline()
{
	list($time) = func_get_args();
	return htmlspecialchars(format_date($time));
}

?>
