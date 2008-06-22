<?php
/**
 * PukiWiki Plus! OpenID 認証処理
 *
 * @copyright   Copyright &copy; 2007-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: openid.inc.php,v 0.10 2008/06/21 23:30:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_api.cls.php');

defined('PLUGIN_OPENID_SIZE_LOGIN') or define('PLUGIN_OPENID_SIZE_LOGIN', 30);
defined('PLUGIN_OPENID_STORE_PATH') or define('PLUGIN_OPENID_STORE_PATH', '/tmp/_php_openid_plus');

class auth_openid_plus extends auth_api
{
	function auth_openid_plus()
	{
		$this->auth_name = 'openid';
		// nickname,email,fullname,dob,gender,postcode,country,language,timezone
		$this->field_name = array('nickname','email','openid_identity');
		$this->response = array();
        }
}

class auth_openid_plus_verify extends auth_openid_plus
{
	function auth_openid_plus_verify()
	{
		$this->auth_name = 'openid_verify';
		$this->field_name = array('openid.server','openid.delegate','ts','page');
		$this->response = array();
	}
	function get_host()
	{
		$msg = $this->auth_session_get();
		$arr = parse_url($msg['openid.server']);
		return strtolower($arr['host']);
	}
	function get_delegate()
	{
		$msg = $this->auth_session_get();
		return $msg['openid.delegate'];

	}
}

function plugin_openid_init()
{
	$msg = array(
          '_openid_msg' => array(
                'msg_logout'		=> _("logout"),
                'msg_logined'		=> _("%s has been approved by openid."),
                'msg_invalid'		=> _("The function of opeind is invalid."),
                'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),
                'msg_not_start'		=> _("The session is not start."),
                'msg_openid'		=> _("OpenID"),
		'msg_openid_url'	=> _("OpenID URL:"),
		'btn_login'		=> _("LOGIN"),
		'msg_title'		=> _("OpenID login form."),
		'err_store_path'	=> _("Could not create the FileStore directory %s. Please check the effective permissions."),
		'err_cancel'		=> _("Verification cancelled."),
		'err_failure'		=> _("OpenID authentication failed: "),
		'err_nickname'		=> _("nickname must be set."),
		'err_authentication'	=> _("Authentication error."),
          )
        );
	set_plugin_messages($msg);
}

function plugin_openid_convert()
{
	global $script,$vars, $auth_api, $_openid_msg;

	if (! isset($auth_api['openid']['use'])) return '';
	if (! $auth_api['openid']['use']) return '<p>'.$_openid_msg['msg_invalid'].'</p>';

	if (! function_exists('pkwk_session_start')) return '<p>'.$_openid_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_openid_msg['msg_not_start'].'</p>';

	// 処理済みか？
	$obj = new auth_openid_plus();
	$name = $obj->auth_session_get();

	if (! empty($name['nickname'])) {
		$logout_url = $script.'?plugin=openid&amp;logout';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']);
		}

		return <<<EOD
<div>
        <label>OpenID</label>:
        {$name['nickname']}
        (<a href="$logout_url">{$_openid_msg['msg_logout']}</a>)
</div>

EOD;
	}

	// 他でログイン
	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return '';

	return plugin_openid_login_form();
}

function plugin_openid_inline()
{
	global $script,$vars,$auth_api,$_openid_msg;

	if (! isset($auth_api['openid']['use'])) return '';
	if (! $auth_api['openid']['use']) return $_openid_msg['msg_invalid'];

	if (! function_exists('pkwk_session_start')) return $_openid_msg['msg_not_found'];
	if (pkwk_session_start() == 0) return $_openid_msg['msg_not_start'];

	$obj = new auth_openid_plus();
	$name = $obj->auth_session_get();

	if (!empty($name['api']) && $obj->auth_name !== $name['api']) return;

	$r_page = (empty($vars['page'])) ? '' : '&amp;page='.rawurlencode($vars['page']);

	if (! empty($name['nickname'])) {
		if (empty($name['openid_identity'])) {
			$link = $name['nickname'];
		} else {
			$link = '<a href="'.$name['openid_identity'].'">'.$name['nickname'].'</a>';
		}
		$logout_url = $script.'?plugin=openid';
		if (! empty($r_page)) {
			$logout_url .= $r_page.'&amp;logout';
		}
		return sprintf($_openid_msg['msg_logined'],$link) .
                        '(<a href="'.$logout_url.'">'.$_openid_msg['msg_logout'].'</a>)';
	}

	 $auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return $_openid_msg['msg_openid'];

	return '<a href="'.$script.'?plugin=openid'.$r_page.'">'.$_openid_msg['msg_openid'].'</a>';
}

function plugin_openid_action()
{
	global $vars,$_openid_msg;

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	if (! function_exists('pkwk_session_start')) $die_message($_openid_msg['msg_not_found']);
	if (pkwk_session_start() == 0) $die_message($_openid_msg['msg_not_start']);

	// LOGOUT
	if (isset($vars['logout'])) {
		$obj = new auth_openid_plus();
		$obj->auth_session_unset();
		$page = (empty($vars['page'])) ? '' : $vars['page'];
		header('Location: '.get_page_location_uri($page));
		die();
	}

	// LOGIN
	if (! isset($vars['action'])) {
		return array('msg'=>$_openid_msg['msg_title'], 'body'=>plugin_openid_login_form() );
	}

	// AUTH
	if (!file_exists(PLUGIN_OPENID_STORE_PATH) && !mkdir(PLUGIN_OPENID_STORE_PATH)) {
		$die_mesage( sprintf($_openid_msg['err_store_path'],PLUGIN_OPENID_STORE_PATH) );
	}

	// function_exists('Auth_OpenID_FileStore') 
	// function_exists('Auth_OpenID_Consumer') 
	ini_set('include_path', LIB_DIR . 'openid/');
	require_once('Auth/OpenID/Consumer.php');
	require_once('Auth/OpenID/FileStore.php');
	ini_restore('include_path');

	$store = new Auth_OpenID_FileStore(PLUGIN_OPENID_STORE_PATH);
	$consumer = new Auth_OpenID_Consumer($store);

	switch($vars['action']) {
	case 'verify':
		if (empty($vars['openid_url'])) {
			return array('msg'=>$_openid_msg['msg_title'], 'body'=>plugin_openid_login_form() );
		}
		return plugin_openid_verify($consumer);
	case 'finish_auth':
		return plugin_openid_finish_auth($consumer);
	}

	// Error.
	header('Location: '.get_location_uri());
}

function plugin_openid_login_form()
{
	global $script,$vars,$_openid_msg;

	$r_page = (empty($vars['page'])) ? '' : rawurlencode($vars['page']);
	$size = PLUGIN_OPENID_SIZE_LOGIN;

        return <<<EOD
<form method="get" action="$script">
  <div class="openid">
    {$_openid_msg['msg_openid_url']}
    <input type="hidden" name="plugin" value="openid" />
    <input type="hidden" name="action" value="verify" />
    <input type="hidden" name="page" value="$r_page" />
    <input type="text" name="openid_url" size="$size" style="background: url(http://www.openid.net/login-bg.gif) no-repeat; padding-left:18px;" value="" />
    <input type="submit" value="{$_openid_msg['btn_login']}" />
  </div>
</form>

EOD;
}

function plugin_openid_verify($consumer)
{
	global $vars,$_openid_msg;

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	$page = (empty($vars['page'])) ? '' : ''.$vars['page'];
	$openid = $vars['openid_url'];
	$process_url = get_location_uri('openid','','action=finish_auth');
	$trust_root = get_script_absuri();

	// Begin the OpenID authentication process.
	$auth_request = $consumer->begin($openid);

	// Handle failure status return values.
	if (!$auth_request) {
		$die_message( $_openid_msg['err_authentication'] );
	}

	//nickname,email,fullname,dob,gender,postcode,country,language,timezone
	$auth_request->addExtensionArg('sreg', 'optional', 'nickname,email');

	// Redirect the user to the OpenID server for authentication.  Store
	// the token for this authentication so we can verify the response.
	$redirect_url = $auth_request->redirectURL($trust_root, $process_url);

	// redirectURL();
	// openid.server	=> $auth_request->endpoint->server_url		ex. http://www.myopenid.com/server
	// openid.delegate	=> $auth_request->endpoint->getServerID()	ex. http://youraccount.myopenid.com/
	$obj = new auth_openid_plus_verify();
	$obj->response = array(	'openid.server'   => $auth_request->endpoint->server_url,
				'openid.delegate' => $auth_request->endpoint->getServerID(),
				'page'            => $page
			);
	$obj->auth_session_put();

	header('Location: '.$redirect_url);
}

function plugin_openid_finish_auth($consumer)
{
	global $vars,$_openid_msg;

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	$obj_verify = new auth_openid_plus_verify();
	$session_verify = $obj_verify->auth_session_get();
	//$session_verify['openid.server']
	//$session_verify['openid.delegate']
	$page = (empty($session_verify['page'])) ? '' : rawurldecode($session_verify['page']);
	$obj_verify->auth_session_unset();

	// Complete the authentication process using the server's response.
	$response = $consumer->complete($vars);

	switch($response->status) {
	case Auth_OpenID_CANCEL:
		// This means the authentication was cancelled.
		$die_message( $_openid_msg['err_cancel'] );
	case Auth_OpenID_FAILURE:
		$die_message( $_openid_msg['err_failure'] . $response->message );
	case Auth_OpenID_SUCCESS:
		// This means the authentication succeeded.
		$openid = $response->identity_url;
		$esc_identity = htmlspecialchars($openid, ENT_QUOTES);
		$sreg = $response->extensionResponse('sreg');

		// FIXME: 認証状態を保持で戻ると、email しか戻ってこないなぁ。
		if (! isset($sreg['nickname'])) $die_message( $_openid_msg['err_nickname'] );

		$obj = new auth_openid_plus();
		$obj->response = $sreg;
		// openid.delegate ?
		$obj->response['openid_identity'] = (empty($vars['openid_identity'])) ? '' : $vars['openid_identity'];
		$obj->auth_session_put();
		break;
	}

	// オリジナルの画面に戻る
	header('Location: '. get_page_location_uri($page));
}

function plugin_openid_get_user_name()
{
	global $auth_api;
	// role,name,nick,profile
	if (! $auth_api['openid']['use']) return array('role'=>ROLE_GUEST,'nick'=>'');
	$obj = new auth_openid_plus();
	$msg = $obj->auth_session_get();
	if (empty($msg['nickname'])) return array('role'=>ROLE_GUEST,'nick'=>'');

	if (empty($msg['openid_identity'])) {
		$key = '';
		$prof = $msg['nickname'];
	} else {
		$key = $prof = $msg['openid_identity'];
	}

	return array('role'=>ROLE_AUTH_OPENID,'nick'=>$msg['nickname'],'profile'=>$prof,'key'=>$key);
}

function plugin_openid_jump_url()
{
	global $vars;
	$page = (empty($vars['page'])) ? '' : $vars['page'];
	return get_location_uri('openid',$page);
}

?>
