<?php
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: ls2.inc.php,v 1.23.1 2005/03/09 07:37:37 miko Exp $
//
// List plugin 2

/*
 * �۲��Υڡ����䡢���θ��Ф�(*,**,***)�ΰ�����ɽ������
 * Usage
 *  #ls2(pattern[,title|include|link|reverse|compact, ...],heading title)
 *
 * pattern  : ��ά����Ȥ��⥫��ޤ�ɬ��
 * 'title'  : ���Ф��ΰ�����ɽ������
 * 'include': ���󥯥롼�ɤ��Ƥ���ڡ����θ��Ф���Ƶ�Ū����󤹤�
 * 'link   ': action�ץ饰�����ƤӽФ���󥯤�ɽ��
 * 'reverse': �ڡ������¤ӽ��ȿž�����߽�ˤ���
 * 'compact': ���Ф���٥��Ĵ������
 *     PLUGIN_LS2_LIST_COMPACT��TRUE�λ���̵��(�Ѳ����ʤ�)
 * heading title: ���Ф��Υ����ȥ����ꤹ�� (link����ꤷ�����Τ�)
 */

// ���Ф����󥫡��ν�
define('PLUGIN_LS2_ANCHOR_PREFIX', '#content_1_');

// ���Ф����󥫡��γ����ֹ�
define('PLUGIN_LS2_ANCHOR_ORIGIN', 0);

// ���Ф���٥��Ĵ������(�ǥե������)
define('PLUGIN_LS2_LIST_COMPACT', FALSE);

function plugin_ls2_action()
{
	global $vars;

	$params = array();
	foreach (array('title', 'include', 'reverse') as $key)
		$params[$key] = isset($vars[$key]);

	$prefix = isset($vars['prefix']) ? $vars['prefix'] : '';
	$body = plugin_ls2_show_lists($prefix, $params);

	return array('body'=>$body,
		'msg'=>str_replace('$1', htmlspecialchars($prefix), _("List of pages which begin with ' $1'")));
}

function plugin_ls2_convert()
{
	global $script, $vars;

	$params = array(
		'link'    => FALSE,
		'title'   => FALSE,
		'include' => FALSE,
		'reverse' => FALSE,
		'compact' => PLUGIN_LS2_LIST_COMPACT,
		'_args'   => array(),
		'_done'   => FALSE
	);

	$args = array();
	$prefix = '';
	if (func_num_args()) {
		$args   = func_get_args();
		$prefix = array_shift($args);
	}
	if ($prefix == '') $prefix = strip_bracket($vars['page']) . '/';

	array_walk($args, 'plugin_ls2_check_arg', & $params);

	$title = (! empty($params['_args'])) ? join(',', $params['_args']) :   // Manual
		str_replace('$1', htmlspecialchars($prefix), _("List of pages which begin with ' $1'")); // Auto

	if (! $params['link'])
		return plugin_ls2_show_lists($prefix, $params);

	$tmp = array();
	$tmp[] = 'plugin=ls2&amp;prefix=' . rawurlencode($prefix);
	if (isset($params['title']))   $tmp[] = 'title=1';
	if (isset($params['include'])) $tmp[] = 'include=1';

	return '<p><a href="' . $script . '?' . join('&amp;', $tmp) . '">' .
		$title . '</a></p>' . "\n";
}

function plugin_ls2_show_lists($prefix, & $params)
{
	global $_ls2_err_nopages;

	$pages = array();
	if ($prefix != '') {
		foreach (get_existpages() as $_page)
			if (strpos($_page, $prefix) === 0)
				$pages[] = $_page;
	} else {
		$pages = get_existpages();
	}

	natcasesort($pages);
	if ($params['reverse']) $pages = array_reverse($pages);

	foreach ($pages as $page) $params["page_$page"] = 0;

	if (empty($pages)) {
		return str_replace('$1', htmlspecialchars($prefix), '<p>' . _("There is no child page in ' $1'") . '</p>');
	} else {
		$params['result'] = $params['saved'] = array();
		foreach ($pages as $page)
			plugin_ls2_get_headings($page, $params, 1);
		return join("\n", $params['result']) . join("\n", $params['saved']);
	}
}

function plugin_ls2_get_headings($page, & $params, $level, $include = FALSE)
{
	global $script;
	static $_ls2_anchor = 0;

	// �ڡ�����̤ɽ���ΤȤ�
	$is_done = (isset($params["page_$page"]) && $params["page_$page"] > 0);
	if (! $is_done) $params["page_$page"] = ++$_ls2_anchor;

	$r_page = rawurlencode($page);
	$s_page = htmlspecialchars($page);
	$title  = $s_page . ' ' . get_pg_passage($page, FALSE);
	$href   = $script . '?cmd=read&amp;page=' . $r_page;

	plugin_ls2_list_push($params, $level);
	$ret = $include ? '<li>include ' : '<li>';

	if ($params['title'] && $is_done) {
		$ret .= '<a href="' . $href . '" title="' . $title . '">' . $s_page . '</a> ';
		$ret .= '<a href="#list_' . $params["page_$page"] . '"><sup>&uarr;</sup></a>';
		array_push($params['result'], $ret);
		return;
	}

	$ret .= '<a id="list_' . $params["page_$page"] . '" href="' . $href .
		'" title="' . $title . '">' . $s_page . '</a>';
	array_push($params['result'], $ret);

	$anchor = PLUGIN_LS2_ANCHOR_ORIGIN;
	$matches = array();
	foreach (get_source($page) as $line) {
		if ($params['title'] && preg_match('/^(\*{1,3})/', $line, $matches)) {
			$id    = make_heading($line);
			$level = strlen($matches[1]);
			$id    = PLUGIN_LS2_ANCHOR_PREFIX . $anchor++;
			plugin_ls2_list_push($params, $level + strlen($level));
			array_push($params['result'],
				'<li><a href="' . $href . $id . '">' . $line . '</a>');
		} else if ($params['include'] &&
			preg_match('/^#include\((.+)\)/', $line, $matches) &&
			is_page($matches[1]))
		{
			plugin_ls2_get_headings($matches[1], $params, $level + 1, TRUE);
		}
	}
}

//�ꥹ�ȹ�¤���ۤ���
function plugin_ls2_list_push(& $params, $level)
{
	global $_ul_left_margin, $_ul_margin, $_list_pad_str;

	$result = & $params['result'];
	$saved  = & $params['saved'];
	$cont   = TRUE;
	$open   = '<ul%s>';
	$close  = '</li></ul>';

	while (count($saved) > $level || (! empty($saved) && $saved[0] != $close))
		array_push($result, array_shift($saved));

	$margin = $level - count($saved);

	// count($saved)�����䤹
	while (count($saved) < ($level - 1)) array_unshift($saved, '');

	if (count($saved) < $level) {
		$cont = FALSE;
		array_unshift($saved, $close);

		$left = ($level == $margin) ? $_ul_left_margin : 0;
		if ($params['compact']) {
			$left  += $_ul_margin;   // �ޡ���������
			$level -= ($margin - 1); // ��٥����
		} else {
			$left += $margin * $_ul_margin;
		}
		$str = sprintf($_list_pad_str, $level, $left, $left);
		array_push($result, sprintf($open, $str));
	}

	if ($cont) array_push($result, '</li>');
}

// ���ץ�������Ϥ���
function plugin_ls2_check_arg($value, $key, & $params)
{
	if ($value == '') {
		$params['_done'] = TRUE;
		return;
	}

	if (! $params['_done']) {
		foreach (array_keys($params) as $param) {
			if (strtolower($value)  == $param &&
			    preg_match('/^[a-z]/', $param)) {
				$params[$param] = TRUE;
				return;
			}
		}
		$params['_done'] = TRUE;
	}

	$params['_args'][] = htmlspecialchars($value); // Link title
}
?>
