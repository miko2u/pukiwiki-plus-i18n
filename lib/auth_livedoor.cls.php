<?php
/**
 * PukiWiki Plus! livedoor 認証処理
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_livedoor.cls.php,v 0.2 2007/07/13 01:05:00 upk Exp $
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
		$this->field_name = array('livedoor_id','ts');
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

?>
