<?php
/**
 * PukiWiki Plus! XBEL Plugin
 *
 * @copyright   Copyright &copy; 2006,2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: xbel.inc.php,v 0.5 2008/01/19 18:09:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 */

defined('XBEL_PREF_PAGE') or define('XBEL_PREF_PAGE', 'Favorite');

function plugin_xbel_init()
{
	$messages = array(
		'_xbel_msg' => array(
			'btn_exec'	=> _('Exec'),
			'msg_text'	=> _('Please select the page that wants to output as a favorite.'),
			'msg_zero'	=> _('There was no data.'),
		)
	);
	set_plugin_messages($messages);
}

function plugin_xbel_action()
{
	global $vars, $page_title, $rss_description, $whatsnew;

	$adm = (empty($vars['adm'])) ? 'page' : $vars['adm'];

	// ユーザ認証されていない
	$id = auth::check_auth();
	if (empty($id)) {
		$adm = 'recent';
	}

	$data = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xbel PUBLIC "+//IDN python.org//DTD XML Bookmark Exchange Language 1.0//EN//XML" "http://www.python.org/topics/xml/dtds/xbel-1.0.dtd">
<xbel version="1.0">
<title><![CDATA[$page_title]]></title>
<desc><![CDATA[$rss_description]]></desc>


EOD;

	change_uri('',1); // Force absoluteURI.

	switch ($adm)
	{
		case 'list':
			$pages = xbel::get_data();
			break;
		case 'recent':
			$pages = array($whatsnew);
			break;
		// list
		default:
			$page = (empty($vars['page'])) ? $whatsnew : $vars['page'];
			$pages = array($page);
			unset($page);
	}

	foreach($pages as $page) {
		$links = xbel::get_link_list($page);
		$data .= xbel::put_body($links, $page);
	}

	$data .= "</xbel>\n";

	pkwk_common_headers();
	header('Content-type: application/xml');
	print($data);
	exit;
}

function plugin_xbel_convert()
{
	global $script, $vars;
	global $_xbel_msg;

	// ユーザ認証されていない
        $id = auth::check_auth();
        if (empty($id)) return '';

	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('pref');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = $argv[$i];
	}

	if (empty($pref)) {
		$pref = XBEL_PREF_PAGE;
	}

	$page_pref = xbel::get_data_prefix($pref);

	$rc = <<<EOD
<form action="$script" method="post">
<input type="hidden" name="plugin" value="xbel" />
<input type="hidden" name="adm" value="list" />
<div>
{$_xbel_msg['msg_text']}
</div>
<table>
EOD;

	$i = 0;
	foreach($page_pref as $_page) {
		$i++;

		$url = get_page_uri($_page);
		$rc .= <<<EOD
<tr>
 <td><input type="checkbox" name="{$i}_c" /></td>
 <td><input type="hidden" name="{$i}_n" value="$_page" /><a href="$url">$_page</a></td>
</tr>

EOD;
	}

	if ($i == 0) return $_xbel_msg['msg_zero'];

	$rc .= <<<EOD
</table>
<div>
<input type="submit" value="{$_xbel_msg['btn_exec']}" />
</div>
</form>

EOD;

	return $rc;
}

class xbel
{
	function get_link_list($page)
	{
		$links = $tmp = array();
		$data = get_source($page, TRUE, TRUE);
	        $html  = convert_html($data);
		preg_match_all("'href=\"(https?://[^\"]+).*?>(.*?)<'si", $html, $tmp, PREG_PATTERN_ORDER);

		$str_redirect = get_cmd_absuri('redirect','','u=');
		$spos = (PKWK_USE_REDIRECT) ? strlen($str_redirect) : 0;

		$ctr = count($tmp[1]);
		for ($i=0;$i<$ctr;$i++){
			if (xbel::is_ignore($tmp[1][$i])) {
				continue;
			}
			// 名称が無い場合
			if (empty($tmp[2][$i])) {
				continue;
			}
			$aname = trim($tmp[2][$i]);
			if ($aname == '') {
				continue;
			}

			// Redirect を有効にしている場合の対応
			if (strpos($tmp[1][$i],$str_redirect) === FALSE) {
				$href = $tmp[1][$i];
			} else {
				$href = rawurldecode(substr($tmp[1][$i],$spos));
			}
			// HREF でサマリする
			$links[$href] = $aname;
		}

		// GreyBox 対応
		preg_match_all("'GB_showFullScreen\(\'(.*?)\'\, \'(.*?)\'\);\"'si", $html, $tmp, PREG_PATTERN_ORDER);
		$ctr = count($tmp[1]);

		for ($i=0;$i<$ctr;$i++){
			$links[ $tmp[2][$i] ] = $tmp[1][$i];
		}

		return $links;
	}

	function is_ignore($link)
	{
		static $my, $ignore_link;

		if (!isset($my)) $my = get_script_absuri();
		if (!isset($ignore_link)) {
			$ignore_link = array(
				// えんぴつマークのリンクのため迂回
				$my.'?cmd=edit&amp;page=',
				// 添付画像などの迂回
				$my.'?cmd=attach',
				$my.'?plugin=attach',
			);
		}

		foreach($ignore_link as $ignore) {
			if (strpos($link,$ignore) !== FALSE) {
				return TRUE;
			}
		}
		return FALSE;
	}

	function put_body($links, $page)
	{
		$rc = <<<EOD
<folder folded="no">
<title><![CDATA[$page]]></title>

EOD;

		foreach($links as $href=>$aname) {
			$rc .= <<<EOD
<bookmark href="$href">
<title><![CDATA[$aname]]></title>
</bookmark>

EOD;
		}

		$rc .= <<<EOD
</folder>


EOD;
		return $rc;
	}

	function get_data_prefix($pref)
	{
		static $pages;
		if (!isset($pages)) $pages = auth::get_existpages();

		$rc = array();

		foreach($pages as $_page) {
			if (strpos($_page,$pref) === 0) {
				$rc[] = $_page;
			}
		}

		natcasesort($rc);
		return $rc;
	}

	// POSTされたデータを得る
	function get_data()
	{
		global $vars;

		$rc = array();

		foreach ($vars as $key => $data) {
			if (strchr($key,'_') === FALSE) continue;
			preg_match("'(.*)_(.*)'si",$key,$regs);
			switch ($regs[2]) {
			case 'c':
				$rc[] = $vars[$regs[1].'_n'];
				continue;
			}
		}
		return $rc;
	}

}

?>
