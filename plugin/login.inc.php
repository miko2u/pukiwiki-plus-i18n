<?php
/**
 * PukiWiki Plus! ログインプラグイン
 *
 * @copyright	Copyright &copy; 2004-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: login.php,v 0.7 2006/07/31 00:45:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require_once(LIB_DIR . 'auth.cls.php');

/*
 * 初期処理
 */
function plugin_login_init()
{
	$messages = array(
	'_login_msg' => array(
		'msg_username'		=> _('UserName'),
		'btn_login'		=> _('Login'),
		)
	);
	set_plugin_messages($messages);
}

/*
 * ブロック型プラグイン
 */
function plugin_login_convert()
{
	global $script, $vars;
	global $_login_msg;

	@list($type) = func_get_args();
	$type = (isset($type)) ? htmlspecialchars($type, ENT_QUOTES) : '';
	$user = auth::check_auth();

	if (!empty($user)) {
		return <<<EOD
<div>
	<label>{$_login_msg['msg_username']}</label>:
	$user
</div>

EOD;
	}

	// ボタンを表示するだけ
	$rc = <<<EOD
<form action="$script" method="post">
	<div>
		<input type="hidden" name="plugin" value="login" />
		<input type="hidden" name="type" value="$type" />
		<input type="hidden" name="page" value="{$vars['page']}" />
		<input type="submit" value="{$_login_msg['btn_login']}" />
	</div>
</form>

EOD;

	return $rc;
}

/*
 * アクションプラグイン
 */
function plugin_login_action()
{
	global $auth_type, $auth_users, $realm;

	// NTLM, Negotiate 認証 (IIS 4.0/5.0)
	$srv_soft = (defined('SERVER_SOFTWARE'))? SERVER_SOFTWARE : $_SERVER['SERVER_SOFTWARE'];
	if (substr($srv_soft,0,9) == 'Microsoft') {
		auth::auth_ntlm();
		login_return_page();
	}

	if ($auth_type == 2) {
		if (! auth::auth_digest($realm,$auth_users)) {
			return;
		} else {
			login_return_page();
		}
	}

	if (!auth::auth_pw($auth_users))
	{
		unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
		header( 'WWW-Authenticate: Basic realm="'.$realm.'"' );
		header( 'HTTP/1.0 401 Unauthorized' );
	} else {
		// FIXME
		// 認証成功時は、もともとのページに戻れる
		// 下に記述すると認証すら行えないなぁ
		login_return_page();
	}
}

function login_return_page()
{
	global $vars, $script;
	$retloc = (isset($vars['page'])) ? $script.'?'.rawurlencode($vars['page']) : $script;
	header( 'Location: ' . $retloc );
	die();
}

?>
