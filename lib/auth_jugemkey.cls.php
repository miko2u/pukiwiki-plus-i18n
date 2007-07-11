<?
/**
 * PukiWiki Plus! Jugemkey 認証処理
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_jugemkey.cls.php,v 0.4 2007/07/11 00:00:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */

require_once(LIB_DIR . 'hash.php');

defined('JUGEMKEY_URL_AUTH')  or define('JUGEMKEY_URL_AUTH', 'https://secure.jugemkey.jp/?mode=auth_issue_frob');
defined('JUGEMKEY_URL_TOKEN') or define('JUGEMKEY_URL_TOKEN','http://api.jugemkey.jp/api/auth/token');
defined('JUGEMKEY_URL_USER')  or define('JUGEMKEY_URL_USER', 'http://api.jugemkey.jp/api/auth/user');

class auth_jugemkey
{
	var $sec_key,$api_key,$response;

	function auth_jugemkey($sec_key,$api_key)
	{
		$this->sec_key = $sec_key;
		$this->api_key = $api_key;
		$this->response = array();
	}

	function make_login_link($callback_url)
	{
		$perms = 'auth';
		$api_sig = hmac_sha1($this->sec_key, $this->api_key.$callback_url.$perms);
		return JUGEMKEY_URL_AUTH.'&amp;api_key='.$this->api_key.'&amp;perms='.$perms.'&amp;callback_url='.rawurlencode($callback_url).'&amp;api_sig='.$api_sig;
	}

        function jugemkey_session_get()
        {
		global $script;
		$val = auth::des_session_get(md5('jugemkey_message_'.$script.session_id()));
		if (empty($val)) {
			return array();
		}
		return auth_jugemkey::parse_message($val);
        }

	function jugemkey_session_put()
	{
		global $script;
		$message = encode($this->response['title']).'::'.
			encode(UTIME).'::'.
			encode($this->response['token']);
		auth::des_session_put(md5('jugemkey_message_'.$script.session_id()),$message);
        }

	function jugemkey_session_unset()
	{
		global $script;
		return session_unregister(md5('jugemkey_message_'.$script.session_id()));
	}

	function parse_message($message)
	{
		$rc = array();
		$tmp = explode('::',trim($message));
		$name = array('title','ts','token');
		for($i=0;$i<count($tmp);$i++) {
			$rc[$name[$i]] = decode($tmp[$i]);
		}
		return $rc;
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

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $data['data'], $val, $index);
		xml_parser_free($xml_parser);

		foreach($val as $x) {
			if ($x['type'] != 'complete') continue;
			$this->response[strtolower($x['tag'])] = $x['value'];
		}
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

		$rc = array();
		$rc['rc'] = $data['rc'];
		if ($data['rc'] != 200 && ($data['rc'] != 401)) return $rc;

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $data['data'], $val, $index);
		xml_parser_free($xml_parser);

		foreach($val as $x) {
			if ($x['type'] != 'complete') continue;
			$rc[strtolower($x['tag'])] = $x['value'];
		}
		return $rc;
	}
}

?>
