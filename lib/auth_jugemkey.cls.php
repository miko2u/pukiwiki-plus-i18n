<?
/**
 * PukiWiki Plus! Jugemkey 認証処理
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_jugemkey.cls.php,v 0.5 2007/07/13 01:05:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */

require_once(LIB_DIR . 'hash.php');
require_once(LIB_DIR . 'auth_api.cls.php');

defined('JUGEMKEY_URL_AUTH')  or define('JUGEMKEY_URL_AUTH', 'https://secure.jugemkey.jp/?mode=auth_issue_frob');
defined('JUGEMKEY_URL_TOKEN') or define('JUGEMKEY_URL_TOKEN','http://api.jugemkey.jp/api/auth/token');
defined('JUGEMKEY_URL_USER')  or define('JUGEMKEY_URL_USER', 'http://api.jugemkey.jp/api/auth/user');

class auth_jugemkey extends auth_api
{
	var $sec_key,$api_key;

	function auth_jugemkey()
	{
		global $auth_api;
		$this->auth_name = 'jugemkey';
		$this->sec_key = $auth_api[$this->auth_name]['sec_key'];
		$this->api_key = $auth_api[$this->auth_name]['api_key'];
		$this->field_name = array('title','ts','token');
		$this->response = array();
	}

	function make_login_link($callback_url)
	{
		$perms = 'auth';
		$api_sig = hmac_sha1($this->sec_key, $this->api_key.$callback_url.$perms);
		return JUGEMKEY_URL_AUTH.'&amp;api_key='.$this->api_key.'&amp;perms='.$perms.'&amp;callback_url='.rawurlencode($callback_url).'&amp;api_sig='.$api_sig;
	}

	function auth($frob)
	{
		$created = substr_replace(get_date('Y-m-d\TH:i:sO', UTIME), ':', -2, 0);
		$api_sig = hmac_sha1($this->sec_key,$this->api_key.$created.$frob);
		$headers = array(
			'X-JUGEMKEY-API-CREATED'=> $created,
			'X-JUGEMKEY-API-KEY'	=> $this->api_key,
			'X-JUGEMKEY-API-FROB'	=> $frob,
			'X-JUGEMKEY-API-SIG'	=> $api_sig,
		);

		$data = http_request(JUGEMKEY_URL_TOKEN, 'GET', $headers);

		$this->response['rc'] = $data['rc'];
		if ($data['rc'] != 200) {
			return $this->response;
		}

		$this->responce_xml_parser($data['data']);
		return $this->response;
	}

	function get_userinfo($token)
	{
		$created = substr_replace(get_date('Y-m-d\TH:i:sO', UTIME), ':', -2, 0);
		$api_sig = hmac_sha1($this->sec_key,$this->api_key.$created.$token);
		$headers = array(
			'X-JUGEMKEY-API-CREATED'=> $created,
			'X-JUGEMKEY-API-KEY'    => $this->api_key,
			'X-JUGEMKEY-API-TOKEN'  => $token,
			'X-JUGEMKEY-API-SIG'    => $api_sig,
		);

		$data = http_request(JUGEMKEY_URL_USER, 'GET', $headers);
		$this->response['rc'] = $data['rc'];
		if ($data['rc'] != 200 && ($data['rc'] != 401)) return $this->response;

		$this->responce_xml_parser($data['data']);
		return $this->response;
	}
}

?>
