<?php
/**
 * PukiWiki Plus! OpenID 認証処理
 *
 * @copyright   Copyright &copy; 2007-2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: openid.inc.php,v 0.15 2009/05/31 04:32:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_api.cls.php');

defined('PLUGIN_OPENID_SIZE_LOGIN')  or define('PLUGIN_OPENID_SIZE_LOGIN', 30);
defined('PLUGIN_OPENID_STORE_PATH')  or define('PLUGIN_OPENID_STORE_PATH', '/tmp/_php_openid_plus');
defined('PLUGIN_OPENID_NO_NICKNAME') or define('PLUGIN_OPENID_NO_NICKNAME', 0); // anonymouse

class auth_openid_plus extends auth_api
{
	function auth_openid_plus()
	{
		$this->auth_name = 'openid';
		// nickname,email,fullname,dob,gender,postcode,country,language,timezone
		$this->field_name = array('author','nickname','email','local_id','identity_url','fullname');
		$this->response = array();
        }
}

class auth_openid_plus_verify extends auth_openid_plus
{
	function auth_openid_plus_verify()
	{
		$this->auth_name = 'openid_verify';
		// $this->field_name = array('openid.server','openid.delegate','ts','page');
		$this->field_name = array('author','server_url','local_id','ts','page');
		$this->response = array();
	}
	function get_host()
	{
		$msg = $this->auth_session_get();
		$arr = parse_url($msg['server_url']);
		return strtolower($arr['host']);
	}
	function get_delegate()
	{
		$msg = $this->auth_session_get();
		return $msg['local_id'];

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
		'msg_anonymouse'	=> _("anonymouse"),
		'btn_login'		=> _("LOGIN"),
		'msg_title'		=> _("OpenID login form."),
		'err_store_path'	=> _("Could not create the FileStore directory %s. Please check the effective permissions."),
		'err_cancel'		=> _("Verification cancelled."),
		'err_failure'		=> _("OpenID authentication failed: "),
		'err_nickname'		=> _("nickname must be set."),
		'err_authentication'	=> _("Authentication error; not a valid OpenID."),
		'err_redirect'		=> _("Could not redirect to server: %s"),
          )
        );
	set_plugin_messages($msg);
}

function plugin_openid_convert()
{
	global $vars, $auth_api, $_openid_msg;

	if (! isset($auth_api['openid']['use'])) return '';
	if (! $auth_api['openid']['use']) return '<p>'.$_openid_msg['msg_invalid'].'</p>';

	$label  = 'OpenID:';
	$logout = $_openid_msg['msg_logout'];
	$msg = plugin_openid_logoff_msg();
	if ($msg === false) return ''; // 他認証
	if (!empty($msg)) return $msg; // ログオン済

	return plugin_openid_login_form();
}

function plugin_openid_logoff_msg($author='openid',$label='OpenID:',$logout_msg='logout')
{
	global $vars;

	if (! function_exists('pkwk_session_start')) return '<p>'.$_openid_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_openid_msg['msg_not_start'].'</p>';

	// 処理済みか？
	$obj = new auth_openid_plus();
	$name = $obj->auth_session_get();

	if (! empty($name['api'])) {
		switch ($name['api']) {
		case 'openid':
			break; // 認証
		case 'openid_verify':
			// ゴミセッションのため削除
			$obj->auth_session_unset();
			return ''; // 未認証
		default:
			return false; // 他で認証済
		}
	}

	if (! empty($name['author']) && $name['author'] !== $author) return false;
	if (! empty($name['nickname'])) {
		$display_name = '<a href="'.$name['local_id'].'">'.$name['nickname'].'</a>';
		$page = (empty($vars['page'])) ? '' : $vars['page'];
		$logout_url = get_cmd_uri('openid',$page).'&amp;logout';
		return <<<EOD
<div>
	<label>$label</label>
	$display_name
	(<a href="$logout_url">$logout_msg</a>)
</div>

EOD;
	}
	return '';
}

function plugin_openid_inline()
{
	global $vars,$auth_api,$_openid_msg;

	if (! isset($auth_api['openid']['use'])) return '';
	if (! $auth_api['openid']['use']) return $_openid_msg['msg_invalid'];

        if (! function_exists('pkwk_session_start')) return $_openid_msg['msg_not_found'];
        if (pkwk_session_start() == 0) return $_openid_msg['msg_not_start'];

        $obj = new auth_openid_plus();
        $name = $obj->auth_session_get();

        if (!empty($name['api']) && $obj->auth_name !== $name['api']) return;

        $page = (empty($vars['page'])) ? '' : $vars['page'];
        $cmd = get_cmd_uri('openid', $page);

        if (! empty($name['nickname'])) {
                if (empty($name['local_id'])) {
                        $link = $name['nickname'];
                } else {
                        $link = '<a href="'.$name['local_id'].'">'.$name['nickname'].'</a>';
                }
                return sprintf($_openid_msg['msg_logined'],$link) .
                        '(<a href="'.$cmd.'&amp;logout'.'">'.$_openid_msg['msg_logout'].'</a>)';
        }

         $auth_key = auth::get_user_name();
        if (! empty($auth_key['nick'])) return $_openid_msg['msg_openid'];

        return '<a href="'.$cmd.'">'.$_openid_msg['msg_openid'].'</a>';
}

function plugin_openid_action()
{
	global $vars,$_openid_msg,$auth_api;

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	// OpenID 関連プラグイン経由の認証がＯＫの場合のみ通過を許可
	if (!isset($auth_api['openid']['use'])) return '';
	if (! $auth_api['openid']['use']) $die_message( $_openid_msg['msg_invalid'] );

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

	ini_set('include_path', LIB_DIR . 'openid/');
	require_once('Auth/OpenID/Consumer.php');
	require_once('Auth/OpenID/FileStore.php');
	require_once('Auth/OpenID/SReg.php');
	require_once('Auth/OpenID/PAPE.php');
	ini_restore('include_path');

	global $pape_policy_uris;
	$pape_policy_uris = array(
		PAPE_AUTH_MULTI_FACTOR_PHYSICAL,
		PAPE_AUTH_MULTI_FACTOR,
		PAPE_AUTH_PHISHING_RESISTANT
	);

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

	$rc = <<<EOD
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
	return $rc;
}

function plugin_openid_verify($consumer)
{
	global $vars,$_openid_msg;

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	$page = (empty($vars['page'])) ? '' : ''.$vars['page'];
	$openid = $vars['openid_url'];
	$return_to = get_location_uri('openid','','action=finish_auth');
	$trust_root = get_script_absuri();

	// FIXME: 不正な文字列の場合は、logoff メッセージを設定できない
	$author = (empty($vars['author'])) ? 'openid' : $vars['author'];

	$auth_request = $consumer->begin($openid);
	if (!$auth_request) {
		$die_message( $_openid_msg['err_authentication'] );
	}

	$sreg_request = Auth_OpenID_SRegRequest::build(
					// Required
					array('nickname'),
					// Optional
					array('fullname', 'email'));
	if ($sreg_request) {
		$auth_request->addExtension($sreg_request);
	}

	$shouldSendRedirect = $auth_request->shouldSendRedirect();
	if ($shouldSendRedirect) {
		$redirect_url = $auth_request->redirectURL($trust_root, $return_to);
		if (Auth_OpenID::isFailure($redirect_url)) {
			$die_mesage( sprintf($_openid_msg['err_redirect'],$redirect_url->message) );
		}
	} else {
		$form_id = 'openid_message';
		$form_html = $auth_request->htmlMarkup($trust_root, $return_to, false, array('id' => $form_id));
		if (Auth_OpenID::isFailure($form_html)) {
			$die_mesage( sprintf($_openid_msg['err_redirect'],$form_html->message) );
		}
	}

	// v1			v2
	// openid.server	openid2.provider	=> $auth_request->endpoint->server_url	ex. http://www.myopenid.com/server
	// openid.delegate	openid2.local_id	=> $auth_request->endpoint->local_id	ex. http://youraccount.myopenid.com/
        $obj = new auth_openid_plus_verify();
        $obj->response = array( 'server_url' => $auth_request->endpoint->server_url,
                                'local_id'   => $auth_request->endpoint->local_id,
                                'page'       => $page,
				'author'     => $author
                        );
        $obj->auth_session_put();

	if ($shouldSendRedirect) {
		header('Location: '.$redirect_url);
	} else {
		//print $form_html;
		die($form_html);
	}
}

function plugin_openid_finish_auth($consumer)
{
	global $vars,$_openid_msg;

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	$obj_verify = new auth_openid_plus_verify();
	$session_verify = $obj_verify->auth_session_get();
	//$session_verify['server_url']
	//$session_verify['local_id']
	$page = (empty($session_verify['page'])) ? '' : rawurldecode($session_verify['page']);
	$author = (empty($session_verify['author'])) ? '' : rawurldecode($session_verify['author']);
	$obj_verify->auth_session_unset();
	$return_to = get_page_location_uri($page);
	$response = $consumer->complete($return_to);

/*
echo '<pre>';
var_dump($response);
die();
*/

	switch($response->status) {
	case Auth_OpenID_CANCEL:
                $die_message( $_openid_msg['err_cancel'] );
	case Auth_OpenID_FAILURE:
                $die_message( $_openid_msg['err_failure'] . $response->message );
	case Auth_OpenID_SUCCESS:
		$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
		$sreg = $sreg_resp->contents();
		// $sreg['email'], $sreg['nickname'], $sreg['fullname']

		if (! isset($sreg['nickname'])) {
			if (PLUGIN_OPENID_NO_NICKNAME) {
				$sreg['nickname'] = 'anonymouse';
			} else {
				$die_message( $_openid_msg['err_nickname'] );
			}
		}

		$obj = new auth_openid_plus();
		$obj->response = $sreg; // その他の項目を引き渡す
		$obj->response['author'] = $author;
		$obj->response['local_id'] = (!empty($response->endpoint->local_id)) ? $response->endpoint->local_id : $response->endpoint->claimed_id;
		$obj->response['identity_url'] = $response->getDisplayIdentifier();
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

	if (empty($msg['local_id'])) {
		$key = '';
		$prof = $msg['nickname'];
	} else {
		$key = $prof = $msg['local_id'];
	}

	$name = plugin_openid_get_call_func($msg['identity_url']);
	if (empty($name) || !exist_plugin($name)) {
		return array('role'=>ROLE_AUTH_OPENID,'nick'=>$msg['nickname'],'profile'=>$prof,'key'=>$key);
	}

	if (function_exists($name . '_get_user_name')) {
		$aryargs = array($msg,$prof,$key);
		return call_user_func_array($name . '_get_user_name', $aryargs);
	}

	return array('role'=>ROLE_AUTH_OPENID,'nick'=>$msg['nickname'],'profile'=>$prof,'key'=>$key);
}

function plugin_openid_jump_url()
{
	global $vars;
	$page = (empty($vars['page'])) ? '' : $vars['page'];
	return get_location_uri('openid',$page);
}

function plugin_openid_get_call_func($openid)
{
	// 今後、OpenID で色々な制限が可能となった場合に、固有判定が行えるような I/F をもっておく
	$sub_api = array(
		'https://id.mixi.jp/'		=> 'auth_mixi',
		'https://openid.excite.co.jp/'	=> 'auth_openid_btn',
	);

	foreach($sub_api as $uri=>$plugin) {
		$chk = strpos($openid, $uri);
		if ($chk === false) continue;
		return $plugin;
	}

	return '';
}

?>
