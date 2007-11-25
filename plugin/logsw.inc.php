<?php
/**
 * PukiWiki Plus! ログ有効化プラグイン
 *
 * @copyright	Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: logsw.php,v 0.1 2007/11/25 15:04:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */

// 凍結モードでののみ設置可能かの定義
defined('USE_FREEZE_ONLY') or define('USE_FREEZE_ONLY', '1');

function plugin_logsw_convert()
{
	global $log, $vars;

	if ($log['browse']['use']) return;
	if (USE_FREEZE_ONLY && !is_freeze($vars['page'])) return;
	$log['browse']['use'] = 1;
}

?>
