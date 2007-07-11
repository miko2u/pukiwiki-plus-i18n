<?php
/**
 * PukiWiki Plus! livedoor 認証処理
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_livedoor.cls.php,v 0.1 2007/07/11 21:53:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'hash.php');

defined('LIVEDOOR_URL_AUTH')  or define('LIVEDOOR_URL_AUTH','http://auth.livedoor.com/login/');
defined('LIVEDOOR_VERSION')   or define('LIVEDOOR_VERSION','1.0');
defined('LIVEDOOR_PERMS')     or define('LIVEDOOR_PERMS','id');	// userhash or id
defined('LIVEDOOR_URL_GETID') or define('LIVEDOOR_URL_GETID','http://auth.livedoor.com/rpc/auth');
defined('LIVEDOOR_TIMEOUT')   or define('LIVEDOOR_TIMEOUT', 10*60); // 10min

class auth_livedoor
{
	var $sec_key,$app_key,$response;

	function auth_livedoor($sec_key,$app_key)
	{
		$this->sec_key = $sec_key;
		$this->app_key = $app_key;
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

		$api_sig = auth_livedoor::make_hash($this->sec_key,$query);

		$q_str = '';
		foreach($query as $key=>$val) {
			$q_str .= (empty($q_str)) ? '?' : '&amp;';
			$q_str .= $key.'='.rawurlencode($val);
		}

		return LIVEDOOR_URL_AUTH.$q_str.'&amp;sig='.$api_sig;
	}

	function make_hash($sec_key,$array)
	{
		ksort($array);
		$sig_str = '';
		foreach($array as $key=>$val) {
			$sig_str .= $key.$val;
		}
		return hmac_sha1($sec_key, $sig_str);
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

		$api_sig = auth_livedoor::make_hash($this->sec_key,$query);
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
		$post['sig'] = auth_livedoor::make_hash($this->sec_key,$post);

		$data = http_request(LIVEDOOR_URL_GETID,'POST','',$post);
		if ($data['rc'] != 200) return array('has_error'=>'true','message'=>$data['rc']);

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $data['data'], $val, $index);
		xml_parser_free($xml_parser);

		foreach($val as $x) {
			if ($x['type'] != 'complete') continue;
			$this->response[strtolower($x['tag'])] = $x['value'];
                }

		$has_error = ($this->response['error'] == 0) ? 'false' : 'true';
		return array('has_error'=>$has_error,'message'=>$this->response['message']);
	}

	function get_return_page()
	{
		return $this->response['userdata'];
	}

	function livedoor_session_get()
	{
		global $script;
		$val = auth::des_session_get(md5('livedoor_message_'.$script.session_id()));
		if (empty($val)) {
			return array();
		}
		return auth_livedoor::parse_message($val);
        }

	function livedoor_session_put()
	{
		global $script;
		$message = encode($this->response['livedoor_id']).'::'.encode(UTIME);
		auth::des_session_put(md5('livedoor_message_'.$script.session_id()),$message);
	}

	function livedoor_session_unset()
	{
		global $script;
		return session_unregister(md5('livedoor_message_'.$script.session_id()));
	}

	function parse_message($message)
	{
		$rc = array();
		$tmp = explode('::',trim($message));
		$name = array('name','ts');
		for($i=0;$i<count($tmp);$i++) {
			$rc[$name[$i]] = decode($tmp[$i]);
		}
		return $rc;
	}
}

?>
