<?php
/**
 * PukiWiki Plus! TypeKey 認証処理
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: typekey.inc.php,v 0.10 2006/11/25 02:27:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_typekey.cls.php');

function plugin_typekey_init()
{
	$msg = array(
	  '_typekey_msg' => array(
		'msg_typekey'		=> _("TypeKey"),
		'msg_logout'		=> _("logout"),
		'msg_logined'		=> _("%s has been approved by TypeKey."),
		'msg_error'		=> _("site_token must be set."),
		'msg_invalid'		=> _("The function of TypeKey is invalid."),
		'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),
		'msg_not_start'		=> _("The session is not start."),
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

	$user = auth_typekey::get_profile_link();
	if (! empty($user)) {
		$page  = $script.rawurlencode('?plugin=typekey');
		if (! empty($vars['page'])) {
			$page .= rawurlencode('&page='.$vars['page']);
		}
		$logout_url = auth_typekey::typekey_logout_url($page).rawurlencode('&logout');
		return <<<EOD
<div>
	<label>TypeKey</label>:
	$user(<a href="$logout_url">{$_typekey_msg['msg_logout']}</a>)
</div>

EOD;
	}

	$login_url = plugin_typekey_jump_url();
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

	$link = auth_typekey::get_profile_link();
	if (! empty($link)) {
		// 既に認証済
		$page  = $script.rawurlencode('?plugin=typekey');
		if (! empty($vars['page'])) {
			$page .= rawurlencode('&page='.$vars['page']);
		}
		return sprintf($_typekey_msg['msg_logined'],$link) .
			'(<a href="'.auth_typekey::typekey_logout_url($page).rawurlencode('&logout').'">' .
			$_typekey_msg['msg_logout'].'</a>)';
	}

	return '<a href="'.plugin_typekey_jump_url().'">'.$_typekey_msg['msg_typekey'].'</a>';
}

function plugin_typekey_action()
{
	global $script,$vars,$auth_api;

	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	if (empty($auth_api['typekey']['site_token'])) return '';

	$obj_typekey = new auth_typekey($auth_api['typekey']['site_token']);
	$obj_typekey->set_regkeys();
	$obj_typekey->set_need_email($auth_api['typekey']['need_email']);
	$obj_typekey->set_sigKey($vars);

	$r_page = (empty($vars['page'])) ? '' : rawurlencode($vars['page']);

	if (! $obj_typekey->auth()) {
		if (isset($vars['logout'])) {
			auth_typekey::typekey_session_unset();
		}
		header('Location: '.$script.'?'.$r_page);
		die();
	}

	// 認証成功
	$obj_typekey->typekey_session_put();
	header('Location: '.$script.'?'.$r_page);
	die();
}

function plugin_typekey_jump_url()
{
	global $auth_api,$script,$vars;

	$page  = $script.rawurlencode('?plugin=typekey');
	if (! empty($vars['page'])) {
		$page .= rawurlencode('&page='.$vars['page']);
	}

	$obj = new auth_typekey($auth_api['typekey']['site_token']);
	$obj->set_need_email($auth_api['typekey']['need_email']);
	return $obj->typekey_login_url($page);
}

function plugin_typekey_get_user_name()
{
	global $auth_api;
	// role,name,nick,profile
	if (! $auth_api['typekey']['use']) return array(ROLE_GUEST,'','','');
	$msg = auth_typekey::typekey_session_get();
	if (! empty($msg['nick']) && ! empty($msg['name'])) {
		return array(ROLE_AUTH_TYPEKEY,$msg['name'],$msg['nick'],TYPEKEY_URL_PROFILE.$msg['name']);
	}
	return array(ROLE_GUEST,'','','');
}

?>
