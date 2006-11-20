<?php
/**
 * PukiWiki Plus! TypeKey 認証処理
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: typekey.inc.php,v 0.3 2006/11/20 21:27:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'typekey.cls.php');

function plugin_typekey_init()
{
	$msg = array(
	  '_typekey_msg' => array(
		'msg_login'		=> _("TypeKey Login"),
		'msg_logout'		=> _("TypeKey Logout"),
		'msg_out'		=> _("logout"),
		'msg_logined'		=> _("%s has been approved by TypeKey."),	// %s さんは、TypeKey によって、承認されています。
		'msg_error'		=> _("site_token must be set."),
		'msg_invalid'		=> _("The function of TypeKey is invalid."),	// TypeKey の機能は、無効です。
		'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),	// pkwk_session_start() が見つかりません。
		'msg_not_start'		=> _("The session is not start."),		// セッションが開始されていません。
		'btn_login'		=> _("LOGIN(TypeKey)"),
	  )
	);
	set_plugin_messages($msg);
}

function plugin_typekey_convert()
{
	global $script,$vars,$_typekey_msg,$auth_api;

	if (! function_exists('pkwk_session_start')) return '<p>'.$_typekey_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_typekey_msg['msg_not_start'].'</p>';

	if ($auth_api['typekey']['use'] != 1) return '<p>'.$_typekey_msg['msg_invalid'].'</p>';
	if (empty($auth_api['typekey']['site_token'])) return '<p>'.$_typekey_msg['msg_error'].'</p>';

	$page  = $script.rawurlencode('?plugin=typekey');
	if (! empty($vars['page'])) {
		$page .= rawurlencode('&page='.$vars['page']);
	}

	$user = typekey::get_profile_link();
	if (! empty($user)) {
		$logout_url = typekey::typekey_logout_url($page).rawurlencode('&logout');
		return <<<EOD
<div>
	<label>TypeKey</label>:
	$user(<a href="$logout_url">{$_typekey_msg['msg_out']}</a>)
</div>

EOD;
	}

        $obj_typekey = new typekey($typekey['site_token']);
        $obj_typekey->set_need_email($typekey['need_email']);

	$login_url = $obj_typekey->typekey_login_url($page);
	// ボタンを表示するだけ
	return <<<EOD
<form action="$login_url" method="post">
	<div>
		<input type="submit" value="{$_typekey_msg['btn_login']}" />
	</div>
</form>

EOD;
}

function plugin_typekey_inline()
{
	global $script,$vars,$_typekey_msg,$auth_api;

	if (! function_exists('pkwk_session_start')) return $_typekey_msg['msg_not_found'];
	if (pkwk_session_start() == 0) return $_typekey_msg['msg_not_start'];

	if ($auth_api['typekey']['use'] != 1) return $_typekey_msg['msg_invalid'];
	if (empty($auth_api['typekey']['site_token'])) return $_typekey_msg['msg_error'];

	$page  = $script.rawurlencode('?plugin=typekey');
	if (! empty($vars['page'])) {
		$page .= rawurlencode('&page='.$vars['page']);
	}

	$link = typekey::get_profile_link();
	if (! empty($link)) {
		// 既に認証済
		return sprintf($_typekey_msg['msg_logined'],$link) .
			'(<a href="'.typekey::typekey_logout_url($page).rawurlencode('&logout').'">' .
			$_typekey_msg['msg_logout'].'</a>)';
	}

	$obj_typekey = new typekey($typekey['site_token']);
	$obj_typekey->set_need_email($typekey['need_email']);
	return '<a href="'.$obj_typekey->typekey_login_url($page).'">'.$_typekey_msg['msg_login'].'</a>';
}

function plugin_typekey_action()
{
	global $script,$vars,$auth_api;

	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	if (empty($auth_api['typekey']['site_token'])) return '';

	$obj_typekey = new typekey($auth_api['typekey']['site_token']);
	$obj_typekey->set_regkeys();
	$obj_typekey->set_need_email($auth_api['typekey']['need_email']);
	$obj_typekey->set_sigKey($vars);

	$r_page = (empty($vars['page'])) ? '' : rawurlencode($vars['page']);

	if (! $obj_typekey->auth()) {
		if (isset($vars['logout'])) {
			typekey::typekey_session_unset();
		}
		header('Location: '.$script.'?'.$r_page);
		die();
		// return array('msg'=>$vars['page'],'body'=>'It failed in the verification of TypeKey.');
	}

	// 認証成功
	$obj_typekey->typekey_session_put();
	header('Location: '.$script.'?'.$r_page);
	die();
}

?>
