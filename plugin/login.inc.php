<?php
/**
 * PukiWiki Plus! ログインプラグイン
 *
 * @copyright	Copyright &copy; 2004-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: login.php,v 0.12 2006/12/06 03:10:00 upk Exp $
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
		'msg_auth_guide'	=> _('Please attest it with %s to write the comment.'),
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
	global $script, $vars, $auth_api, $_login_msg, $login_api;

	@list($type) = func_get_args();
	$type = (isset($type)) ? htmlspecialchars($type, ENT_QUOTES) : '';
	$user = auth::check_auth();

	if (!empty($user)) {
		// $role = strval(auth::get_role_level());
		list($role,$name,$nick,$url) = auth::get_user_name();
		$role = strval($role);

		if (isset($login_api[$role])) {
			exist_plugin($login_api[$role]);
			return do_plugin_convert($login_api[$role]);
		}

		return '<div><label>'.$_login_msg['msg_username'].'</label>:'.$user.'</div>';
	}

	$select = '';
	foreach($auth_api as $api=>$val) {
		if ($val['use']) {
			$select .= '<option value="'.$api.'">'.$api.'</option>';
		}
	}

	if (! empty($select)) {
		$select = '<select name="api"><option value="plus" selected>Normal</option>'.$select.'</select>';
	} else {
		$select = '<input type="hidden" name="api" value="plus" />';
	}

	// ボタンを表示するだけ
	$rc = <<<EOD
<form action="$script" method="post">
	<div>
$select
		<input type="hidden" name="plugin" value="login" />
		<input type="hidden" name="type" value="$type" />
		<input type="hidden" name="page" value="{$vars['page']}" />
		<input type="submit" value="{$_login_msg['btn_login']}" />
	</div>
</form>

EOD;

	return $rc;
}

function plugin_login_inline()
{
	global $script, $_login_msg, $login_api;

	if (PKWK_READONLY != ROLE_AUTH) return '';

	$user = auth::check_auth();

	// Offline
	if (empty($user)) {
		return plugin_login_auth_guide();
	}

	// Online
	$role = strval(auth::get_role_level());
	if (isset($login_api[$role])) {
		exist_plugin($login_api[$role]);
		return do_plugin_inline($login_api[$role]);
	}
	return '';
}

function plugin_login_auth_guide()
{
	global $auth_api,$_login_msg;

	$inline = '';
	$sw = true;
	foreach($auth_api as $api=>$val) {
		if ($val['use']) {
			$inline .= ($sw) ? '' : ',';
			$sw = false;
			$inline .= '&'.$api.'();';
		}
	}

	if ($sw) return '';
	return convert_html(sprintf($_login_msg['msg_auth_guide'],$inline));
}

/*
 * アクションプラグイン
 */
function plugin_login_action()
{
	global $vars,$auth_type, $auth_users, $realm;

	$api = (empty($vars['api'])) ? 'plus' : $vars['api'];
	if ($api != 'plus') {
		if (! exist_plugin($vars['api'])) return;
		$call_api = 'plugin_'.$vars['api'].'_jump_url';
		header('Location: '. $call_api());
		die();
	}

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
