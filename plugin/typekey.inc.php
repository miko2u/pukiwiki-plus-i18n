<?php
/**
 * PukiWiki Plus! TypeKey 認証処理
 *
 * @copyright   Copyright &copy; 2006-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: typekey.inc.php,v 0.15 2008/06/21 23:56:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_api.cls.php');

defined('TYPEKEY_URL_LOGIN')	or define('TYPEKEY_URL_LOGIN',	 'https://www.typekey.com/t/typekey/login');
defined('TYPEKEY_URL_LOGOUT')	or define('TYPEKEY_URL_LOGOUT',	 'https://www.typekey.com/t/typekey/logout');
defined('TYPEKEY_URL_PROFILE')	or define('TYPEKEY_URL_PROFILE', 'http://profile.typekey.com/');
defined('TYPEKEY_REGKEYS')	or define('TYPEKEY_REGKEYS',	 'http://www.typekey.com/extras/regkeys.txt');
defined('TYPEKEY_VERSION')	or define('TYPEKEY_VERSION',	 '1.1');
defined('TYPEKEY_CACHE_TIME')	or define('TYPEKEY_CACHE_TIME',	 60*60*24*2); // 2 day

class auth_typekey extends auth_api
{
	var $siteToken, $need_email, $regkeys, $version;

	function auth_typekey()
	{
		global $auth_api;
		$this->auth_name = 'typekey';
		$this->siteToken = trim( $auth_api[$this->auth_name]['site_token']);
		$this->field_name = array('ts','email','name','nick','site_token');
		$this->need_email = 0;
		$this->version = TYPEKEY_VERSION;
	}

	function set_need_email($x) { $this->need_email = $x; }
	function set_version($x) { $this->version = $x; }
	function set_regkeys() { $this->regkeys = $this->get_regkeys(); }
	function set_sigKey($sigKey)
	{
		foreach($this->field_name as $key) {
			if ($key == 'site_token') {
				$this->response[$key] = $this->siteToken;
			} else {
				$this->response[$key] = (empty($sigKey[$key])) ? '' : trim($sigKey[$key]);
			}
		}

		// FIXME: DSA署名中に + が混入されると空白に変換される場合があるための対応
		$this->response['sig'] = (empty($sigKey['sig'])) ? '' : str_replace(' ', '+', $sigKey['sig']);
	}

	function get_regkeys()
	{
		$rc = array();

		$regkeys = CACHE_DIR . 'regkeys.txt';
		$now = time();
		if (file_exists($regkeys)) {
			$time_regkeys = filemtime($regkeys) + TYPEKEY_CACHE_TIME;
		} else {
			$time_regkeys = $now;
		}

		if ($now < $time_regkeys) {
			$idx = 0;
			$data = file($regkeys);
		} else {
			$data = http_request(TYPEKEY_REGKEYS);
			// if ($data['rc'] != 200) return $rc;
			if ($data['timeout'] && file_exists($regkeys)) {
				// タイムアウト時でキャッシュがあれば、再利用する。
				$idx = 0;
				$data = file($regkeys);
			} else {
				$idx = 'data';
				$fp = fopen($regkeys, 'w');
				@flock($fp, LOCK_EX);
				rewind($fp);
				fputs($fp, $data[$idx]);
				@flock($fp, LOCK_UN);
				fclose($fp);
			}
		}

		foreach(explode(' ',$data[$idx]) as $x) {
			list($key,$val) = explode('=',$x);
			$rc[$key] = trim($val);
		}
		return $rc;
	}

	function get_profile($field='nick')
	{
		$message = $this->auth_session_get();
		return (empty($message[$field])) ? '' : $message[$field];
	}

	function get_profile_link()
	{
		$message = $this->auth_session_get();
		if (! empty($message['api']) && $this->auth_name !== $message['api']) return false;
		if (empty($message['nick'])) return '';
		return '<a class="ext" href="'.auth_typekey::typekey_profile_url($message['name']).'" rel="nofollow">'.
			$message['nick'].
			'<img src="'.IMAGE_URI.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\''.
			auth_typekey::typekey_profile_url($message['name']).'\',\'_blank\');" /></a>';
	}

	function gen_message()
	{
		$message = $delm = '';
		// <email>::<name>::<nick>::<ts>::<site-token>
		foreach(array('email','name','nick','ts','site_token') as $key) {
			$message .= $delm.$this->response[$key];
			if (empty($delm)) $delm = '::';
		}
		return $message;
	}

	function typekey_login_url($return='')
	{
		if (empty($return)) {
			$return = get_script_absuri();
		}
		$rc = TYPEKEY_URL_LOGIN.'?t='.$this->siteToken.'&amp;v='.$this->version;
		if ($this->need_email != 0) {
			$rc .= '&amp;need_email=1';
		}
		return $rc.'&amp;_return='.$return;
	}

	function typekey_logout_url($return='')
	{
		if (empty($return)) {
			$return = get_script_absuri();
		}
		return TYPEKEY_URL_LOGOUT.'?_return='.$return;
        }

	function typekey_login($return)
	{
		header('Location: '.$this->typekey_login_url($return));
		die();
	}

	function typekey_profile_url($name)
	{
		return TYPEKEY_URL_PROFILE.rawurlencode($name).'/';
	}

	function auth()
	{
		if (empty($this->response['email'])) return false;
		// FIXME: どの程度までチェックするのか？
		if ($this->need_email) {
			if (! strpos($this->response['email'],'@')) return false;
		}
		$message = $this->gen_message();

		require_once(LIB_DIR.'DSA.php');
		return Security_DSA::verify($message, $this->response['sig'], $this->regkeys);
	}

}

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
	global $vars,$_typekey_msg,$auth_api;

	if (! function_exists('pkwk_session_start')) return '<p>'.$_typekey_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_typekey_msg['msg_not_start'].'</p>';

	if ($auth_api['typekey']['use'] != 1) return '<p>'.$_typekey_msg['msg_invalid'].'</p>';
	if (empty($auth_api['typekey']['site_token'])) return '<p>'.$_typekey_msg['msg_error'].'</p>';

	$obj = new auth_typekey();

	$user = $obj->get_profile_link();
	if (! empty($user)) {
		$page  = get_script_absuri().rawurlencode('?plugin=typekey');
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

	// 他でログイン
	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return '';

	// ボタンを表示するだけ
	$login_url = plugin_typekey_jump_url();
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
	global $vars,$_typekey_msg,$auth_api;

	if (! function_exists('pkwk_session_start')) return $_typekey_msg['msg_not_found'];
	if (pkwk_session_start() == 0) return $_typekey_msg['msg_not_start'];

	if ($auth_api['typekey']['use'] != 1) return $_typekey_msg['msg_invalid'];
	if (empty($auth_api['typekey']['site_token'])) return $_typekey_msg['msg_error'];

	$obj = new auth_typekey();
	$link = $obj->get_profile_link();
	if ($link === false) return '';

	if (! empty($link)) {
		// 既に認証済
		$page  = get_script_absuri().rawurlencode('?plugin=typekey');
		if (! empty($vars['page'])) {
			$page .= rawurlencode('&page='.$vars['page']);
		}
		return sprintf($_typekey_msg['msg_logined'],$link) .
			'(<a href="'.auth_typekey::typekey_logout_url($page).rawurlencode('&logout').'">' .
			$_typekey_msg['msg_logout'].'</a>)';
	}

	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return $_typekey_msg['msg_typekey'];

	return '<a href="'.plugin_typekey_jump_url().'">'.$_typekey_msg['msg_typekey'].'</a>';
}

function plugin_typekey_action()
{
	global $vars,$auth_api;

	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	if (empty($auth_api['typekey']['site_token'])) return '';

	$obj = new auth_typekey();
	$obj->set_regkeys();
	$obj->set_need_email($auth_api['typekey']['need_email']);
	$obj->set_sigKey($vars);

	$page = (empty($vars['page'])) ? '' : $vars['page'];

	if (! $obj->auth()) {
		if (isset($vars['logout'])) {
			$obj->auth_session_unset();
		}
		header('Location: '.get_page_location_uri($page));
		die();
	}

	// 認証成功
	$obj->auth_session_put();
	header('Location: '.get_page_location_uri($page));
	die();
}

function plugin_typekey_jump_url()
{
	global $auth_api,$vars;

	$page  = get_script_absuri().rawurlencode('?plugin=typekey');
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
	if (! $auth_api['typekey']['use']) return array('role'=>ROLE_GUEST,'nick'=>'');
	$obj = new auth_typekey();
	$msg = $obj->auth_session_get();
	if (! empty($msg['nick']) && ! empty($msg['name'])) {
		return array('role'=>ROLE_AUTH_TYPEKEY,'name'=>$msg['name'],'nick'=>$msg['nick'],'profile'=>TYPEKEY_URL_PROFILE.$msg['name'],'key'=>$msg['name']);
	}
	return array('role'=>ROLE_GUEST,'nick'=>'');
}

?>
