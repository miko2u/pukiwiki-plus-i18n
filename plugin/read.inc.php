<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: read.inc.php,v 1.8.2 2007/05/30 19:14:00 upk Exp $
//
// Read plugin: Show a page and InterWiki

function plugin_read_action()
{
	global $vars, $_title_invalidwn, $_msg_invalidiwn;

	$page = isset($vars['page']) ? $vars['page'] : '';

	if (is_page($page)) {
		// ページを表示
		check_readable($page, true, true);
		header_lastmod($page);
		return array('msg'=>'', 'body'=>'');

	// } else if (! PKWK_SAFE_MODE && is_interwiki($page)) {
	} else if (! auth::check_role('safemode') && is_interwiki($page)) {
		return do_plugin_action('interwiki'); // InterWikiNameを処理

	} else if (is_pagename($page)) {
		$realpages = get_autoaliases($page);
		if (! empty($realpages)) {
			if (count($realpages) == 1) {
				header('Location: ' . get_script_uri() . '?' . $realpages[0]);
				return;
			}
			$body = '<p>';
			$body .= _('This pagename is an alias to') . '<br />';
			foreach ($realpages as $realpage) {
				$body .= make_pagelink($realpage) . '<br />';
			}
			$body .= '</p>';
			return array('msg'=>_('Redirect'), 'body'=>$body);
		}
		$vars['cmd'] = 'edit';
		return do_plugin_action('edit'); // 存在しないので、編集フォームを表示

	} else {
		// 無効なページ名
		return array(
			'msg'=>$_title_invalidwn,
			'body'=>str_replace('$1', htmlspecialchars($page),
				str_replace('$2', 'WikiName', $_msg_invalidiwn))
		);
	}
}
?>
