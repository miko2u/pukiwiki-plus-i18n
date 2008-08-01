<?php
/**
 * PukiWiki Plus! ログインプラグイン
 *
 * @copyright   Copyright &copy; 2004-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: login.php,v 0.20 2008/08/02 02:47:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth.cls.php');

// defined('LOGIN_USE_AUTH_DEFAULT') or define('LOGIN_USE_AUTH_DEFAULT', 1);

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
	global $script, $vars, $auth_api, $_login_msg;

	@list($type) = func_get_args();
	$type = (isset($type)) ? htmlspecialchars($type, ENT_QUOTES) : '';

	$auth_key = auth::get_user_info();

	if (!empty($auth_key['key'])) {
		if ($auth_key['api'] == 'plus') {
			return <<<EOD
<div>
        <label>{$_login_msg['msg_username']}</label>:
        {$auth_key['key']}
</div>

EOD;
		} else {
			exist_plugin($auth_key['api']);
			return do_plugin_convert($auth_key['api']);
		}
	}

	$select = '';
	//if (LOGIN_USE_AUTH_DEFAULT) {
	//	$select .= '<option value="plus" selected="selected">Normal</option>';
	//}
	$sw_ext_auth = false;
	foreach($auth_api as $api=>$val) {
		if (! $val['use']) continue;
		if (isset($val['hidden']) && $val['hidden']) continue;
		$displayname = (isset($val['displayname'])) ? $val['displayname'] : $api;
		if ($api !== 'plus') $sw_ext_auth = true;
		$select .= '<option value="'.$api.'">'.$displayname.'</option>';
	}

	if (empty($select)) return ''; // 認証機能が使えない

	if ($sw_ext_auth) {
		// 外部認証がある
		$select = '<select name="api">'.$select.'</select>';
	} else {
		// 通常認証のみなのでボタン
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
	if (PKWK_READONLY != ROLE_AUTH) return '';

	$auth_key = auth::get_user_info();

	// Offline
	if (empty($auth_key['key'])) {
		return plugin_login_auth_guide();
	}

	// Online
	return exist_plugin($auth_key['api']) ? do_plugin_inline($auth_key['api']) : '';
}

function plugin_login_auth_guide()
{
	global $auth_api,$_login_msg;

	$inline = '';
	$sw = true;
	foreach($auth_api as $api=>$val) {
		if ($val['use']) {
			if (isset($val['hidden']) && $val['hidden']) continue;
			if (! exist_plugin($api)) continue;
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
	global $vars;

	$page = (empty($vars['page'])) ? '' : $vars['page'];
	log_write('login','');
	header( 'Location: ' . get_page_location_uri($page));
	die();
}

?>
