<?php
/*
 * PukiWiki �ǿ���?���ɽ������ץ饰����
 *
 * CopyRight 2002 Y.MASUI GPL2
 * http://masui.net/pukiwiki/ masui@masui.net
 *
 * �ѹ�����:
 *  2002.04.08: pat���󡢤ߤΤ뤵��λ�Ŧ�ˤ�ꡢ����褬���ܸ�ξ���
 *              ������Τ���
 *
 *  2002.06.17: plugin_recent_init()������
 *  2002.07.02: <ul>�ˤ����Ϥ��ѹ�����¤��
 *
 * $Id: recent.inc.php,v 1.13.1 2005/03/10 14:26:52 miko Exp $
 */

// RecentChanges�Υ���å���
define('PLUGIN_RECENT_CACHE', CACHE_DIR . 'recent.dat');

function plugin_recent_convert()
{
	global $script, $vars, $date_format;
//	global $_recent_plugin_frame;

	$_recent_plugin_frame_s = _('recent(%d)');
	$_recent_plugin_frame   = sprintf('<h5>%s</h5><div>%%s</div>', $_recent_plugin_frame_s);

	if (! file_exists(PLUGIN_RECENT_CACHE)) return '';

	$recent_lines = 10;
	if (func_num_args()) {
		$args = func_get_args();
		if (is_numeric($args[0]))
			$recent_lines = $args[0];
	}

	// ��Ƭ��N��(��)����Ф�
	$lines = array_splice(file(PLUGIN_RECENT_CACHE), 0, $recent_lines);

	$date = $items = '';
	foreach ($lines as $line) {
		list($time, $page) = explode("\t", rtrim($line));
		$_date = get_date($date_format, $time);
		if ($date != $_date) {
			if ($date != '') $items .= '</ul>';
			$date = $_date;
			$items .= "<strong>$date</strong>\n" .
				"<ul class=\"recent_list\">\n";
		}
		$s_page = htmlspecialchars($page);
		$r_page = rawurlencode($page);
		$pg_passage = get_pg_passage($page, FALSE);
		if($page == $vars['page']) {
			// No need to link itself, notifies where you just read
			$items .= " <li><span title=\"$s_page $pg_passage\">$s_page</span></li>\n";
		} else {
			$items .= " <li><a href=\"$script?$r_page\" title=\"$s_page $pg_passage\">$s_page</a></li>\n";
		}
	}
	if (! empty($lines)) $items .= "</ul>\n";

	return sprintf($_recent_plugin_frame, count($lines), $items);
}
?>
