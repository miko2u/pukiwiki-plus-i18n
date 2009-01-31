<?php
/**
 * PukiWiki Plus! Homeページジャンププラグイン
 *
 * @copyright   Copyright &copy; 2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: mypage.php,v 0.1 2009/02/01 01:46:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth.cls.php');

function plugin_mypage_init()
{
	$msg = array(
                '_mypage_msg' => array(
                        'err_jump'	=> _('Jump Fail.'),
			'msg_no_page'	=> _('The page cannot be prepared.'),
                )
	);
	set_plugin_messages($msg);
}

function plugin_mypage_convert()
{
	global $_mypage_msg;

	@list($is_page) = func_get_args();
	$is_page = empty($is_page) ? false : true;

	$auth_key = auth::get_user_info();
	// 認証確認
	if (empty($auth_key['key'])) return '';
	// マイページ利用の確認
	if (empty($auth_key['mypage'])) return '';

	// マイページの作成により制御
	// マイページ未作成の場合
	// is_page : true  -> プラグイン利用ページに遷移 -> #mypage(1)
	//         : false -> 新規作成画面に遷移         -> #mypage
	// The page cannot be prepared. -> ページの準備ができていません。
	if ($is_page && !is_page($auth_key['mypage'])) return $_mypage_msg['msg_no_page'];

	// 画面に誘導
	header('Location: '. get_page_location_uri($auth_key['mypage']));
	// 誘導失敗時の対処(ブラウザによる)
	die_message($_mypage_msg['err_jump']);
}

?>
