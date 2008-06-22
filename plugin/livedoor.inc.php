<?php
/**
 * PukiWiki Plus! livedoor 認証処理
 *
 * @copyright   Copyright &copy; 2007-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: livedoor.inc.php,v 0.7 2008/06/21 23:56:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'hash.php');
require_once(LIB_DIR . 'auth_api.cls.php');

defined('LIVEDOOR_URL_AUTH')  or define('LIVEDOOR_URL_AUTH','http://auth.livedoor.com/login/');
defined('LIVEDOOR_VERSION')   or define('LIVEDOOR_VERSION','1.0');
defined('LIVEDOOR_PERMS')     or define('LIVEDOOR_PERMS','id');	// userhash or id
defined('LIVEDOOR_URL_GETID') or define('LIVEDOOR_URL_GETID','http://auth.livedoor.com/rpc/auth');
defined('LIVEDOOR_TIMEOUT')   or define('LIVEDOOR_TIMEOUT', 10*60); // 10min

class auth_livedoor extends auth_api
{
	var $sec_key,$app_key;

	function auth_livedoor()
	{
		global $auth_api;
		$this->auth_name = 'livedoor';
		$this->sec_key = $auth_api[$this->auth_name]['sec_key'];
		$this->app_key = $auth_api[$this->auth_name]['app_key'];
		$this->field_name = array('livedoor_id');
		$this->response = array();
	}

	function make_login_link($return)
	{
		$userdata = (empty($return)) ? '' : encode($return);

		$query = array(
			'app_key' => $this->app_key,
			'perms' => LIVEDOOR_PERMS,
			't' => UTIME,
			'v' => LIVEDOOR_VERSION,
			'userdata' => $userdata,
		);

		$api_sig = $this->make_hash($query);

		$q_str = '';
		foreach($query as $key=>$val) {
			$q_str .= (empty($q_str)) ? '?' : '&amp;';
			$q_str .= $key.'='.rawurlencode($val);
		}

		return LIVEDOOR_URL_AUTH.$q_str.'&amp;sig='.$api_sig;
	}

	function make_hash($array)
	{
		ksort($array);
		$x = '';
		foreach($array as $key=>$val) {
			$x .= $key.$val;
		}
		return hmac_sha1($this->sec_key, $x);
	}

	function auth($vars)
	{
		if (! isset($vars['sig'])) return array('has_error'=>'true','message'=>'Signature is not found.');
		if (! isset($vars['token'])) return array('has_error'=>'true','message'=>'Token is not found.');

		if (isset($vars['userdata'])) {
			$this->response['userdata'] = decode($vars['userdata']);
		}

		$query = array();
		static $keys = array('app_key','userhash','token','t','v','userdata');
		foreach($keys as $key) {
			if (!isset($vars[$key])) continue;
			$query[$key] = $vars[$key];
		}

		$api_sig = $this->make_hash($query);
		if ($api_sig !== $vars['sig']) return array('has_error'=>'true','message'=>'Comparison error of signature.');

		// ログオンしてから 10分経過している場合には、タイムアウトとする
		$time_out = UTIME - LIVEDOOR_TIMEOUT;
		if ($vars['t'] < $time_out) return array('has_error'=>'true','message'=>'The time-out was done.');

		if (LIVEDOOR_PERMS !== 'id') {
			return array('has_error'=>'false','message'=>'');
		}

		$post = array(
			'app_key' => $this->app_key,
			'format' => 'xml',
			'token' => $vars['token'],
			't' => UTIME,
			'v' => LIVEDOOR_VERSION,
		);
		$post['sig'] = $this->make_hash($post);

		$data = http_request(LIVEDOOR_URL_GETID,'POST','',$post);
		if ($data['rc'] != 200) return array('has_error'=>'true','message'=>$data['rc']);

		$this->responce_xml_parser($data['data']);

		$has_error = ($this->response['error'] == 0) ? 'false' : 'true';
		return array('has_error'=>$has_error,'message'=>$this->response['message']);
	}

	function get_return_page()
	{
		return $this->response['userdata'];
	}
}

function plugin_livedoor_init()
{
	$msg = array(
	  '_livedoor_msg' => array(
		'msg_logout'		=> _("logout"),
		'msg_logined'		=> _("%s has been approved by livedoor."),
		'msg_invalid'		=> _("The function of livedoor is invalid."),
		'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),
		'msg_not_start'		=> _("The session is not start."),
		'msg_livedoor'		=> _("livedoor"),
		'btn_login'		=> _("LOGIN(livedoor)"),
          )
        );
        set_plugin_messages($msg);
}

function plugin_livedoor_convert()
{
        global $script,$vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return '<p>'.$_livedoor_msg['msg_invalid'].'</p>';

	if (! function_exists('pkwk_session_start')) return '<p>'.$_livedoor_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_livedoor_msg['msg_not_start'].'</p>';

	$obj = new auth_livedoor();
	$name = $obj->auth_session_get();
	if (isset($name['livedoor_id'])) {
		$logout_url = $script.'?plugin=livedoor';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}

		return <<<EOD
<div>
	<label>livedoor</label>:
	{$name['livedoor_id']}
	(<a href="$logout_url">{$_livedoor_msg['msg_logout']}</a>)
</div>

EOD;
	}

	// 他でログイン
	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return '';

	// ボタンを表示するだけ
	$login_url = $script.'?plugin=livedoor';
	if (! empty($vars['page'])) {
		$login_url .= '&amp;page='.rawurlencode($vars['page']);
	}
	$login_url .= '&amp;login';

	return <<<EOD
<form action="$login_url" method="post">
	<div>
		<input type="submit" value="{$_livedoor_msg['btn_login']}" />
	</div>
</form>

EOD;

}

function plugin_livedoor_inline()
{
	global $script,$vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return $_livedoor_msg['msg_invalid'];

	if (! function_exists('pkwk_session_start')) return $_livedoor_msg['msg_not_found'];
	if (pkwk_session_start() == 0) return $_livedoor_msg['msg_not_start'];

	$obj = new auth_livedoor();
	$name = $obj->auth_session_get();

	if (!empty($name['api']) && $obj->auth_name !== $name['api']) return;

	if (isset($name['livedoor_id'])) {
		$logout_url = $script.'?plugin=livedoor';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}
		return sprintf($_livedoor_msg['msg_logined'],$name['livedoor_id']) .
			'(<a href="'.$logout_url.'">'.$_livedoor_msg['msg_logout'].'</a>)';
	}

	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return $_livedoor_msg['msg_livedoor'];

	$login_url = plugin_livedoor_jump_url(1);
	return '<a href="'.$login_url.'">'.$_livedoor_msg['msg_livedoor'].'</a>';
}

function plugin_livedoor_action()
{
	global $vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return '';
	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	// LOGIN
	if (isset($vars['login'])) {
		header('Location: '. plugin_livedoor_jump_url());
		die();
        }

	$obj = new auth_livedoor();

	// LOGOUT
	if (isset($vars['logout'])) {
		$obj->auth_session_unset();
		$page = (empty($vars['page'])) ? '' : decode($vars['page']);
		header('Location: '.get_page_location_uri($page));
		die();
	}

	// AUTH
	$rc = $obj->auth($vars);

	if (! isset($rc['has_error']) || $rc['has_error'] == 'true') {
		// ERROR
		$body = (isset($rc['message'])) ? $rc['message'] : 'unknown error.';
		$die_message($body);
	}

	$obj->auth_session_put();
	header('Location: '. get_page_location_uri($obj->get_return_page()));
	die();
}

function plugin_livedoor_jump_url($inline=0)
{
	global $vars;
	$obj = new auth_livedoor();
	$url = $obj->make_login_link($vars['page']);
	return ($inline) ? $url : str_replace('&amp;','&',$url);
}

function plugin_livedoor_get_user_name()
{
	global $auth_api;
	// role,name,nick,profile
	if (! $auth_api['livedoor']['use']) return array('role'=>ROLE_GUEST,'nick'=>'');
	$obj = new auth_livedoor();
	$msg = $obj->auth_session_get();
	$info = 'http://www.livedoor.com/';
	if (! empty($msg['livedoor_id']))
		return array('role'=>ROLE_AUTH_LIVEDOOR,'nick'=>$msg['livedoor_id'],'key'=>$msg['livedoor_id'],'profile'=>$info);
	return array('role'=>ROLE_GUEST,'nick'=>'');
}

?>
