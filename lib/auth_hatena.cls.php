<?php
/**
 * PukiWiki Plus! Hatena 認証処理
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_hatena.cls.php,v 0.2 2006/11/22 23:01:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
defined('HATENA_URL_AUTH')	or define('HATENA_URL_AUTH','http://auth.hatena.ne.jp/auth');
defined('HATENA_URL_XML')	or define('HATENA_URL_XML', 'http://auth.hatena.ne.jp/api/auth.xml');
defined('HATENA_URL_PROFILE')	or define('HATENA_URL_PROFILE','http://www.hatena.ne.jp/user?userid=');
defined('HATENA_SESSION_NAME')	or define('HATENA_SESSION_NAME',md5('hatena_message'));

class auth_hatena
{
	var $sec_key,$api_key,$response;

	function auth_hatena($sec_key,$api_key)
	{
		$this->sec_key = $sec_key;
		$this->api_key = $api_key;
		$this->response = array();
	}

	function make_login_link($return)
	{
		$x1 = $x2 = '';
		foreach($return as $key=>$val) {
			$r_val = ($key == 'page') ? encode($val) : rawurlencode($val);
			$x1 .= $key.$r_val;
			$x2 .= '&amp;'.$key.'='.$r_val;
		}

		$api_sig = md5($this->sec_key.'api_key'.$this->api_key.$x1);
		return HATENA_URL_AUTH.'?api_key='.$this->api_key.'&amp;api_sig='.$api_sig.$x2;
	}

	function auth($cert)
	{
		$api_sig = md5($this->sec_key.'api_key'.$this->api_key.'cert'.$cert);
		$url = HATENA_URL_XML.'?api_key='.$this->api_key.'&amp;cert='.$cert.'&amp;api_sig='.$api_sig;

		$data = http_request($url);
		if ($data['rc'] != 200) return array('has_error'=>'true','message'=>$data['rc']);

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $data['data'], $val, $index);
		xml_parser_free($xml_parser);

		foreach($val as $x) {
			if ($x['type'] != 'complete') continue;
			$this->response[strtolower($x['tag'])] = $x['value'];
                }
		return $this->response;
	}

	function hatena_session_get()
	{
		$val = auth::des_session_get(HATENA_SESSION_NAME);
		if (empty($val)) {
			return array();
		}
		return auth_hatena::parse_message($val);
        }

	function hatena_session_put()
	{
		$message = encode($this->response['name']).'::'.
			encode(UTIME).'::'.
			encode($this->response['image_url']).'::'.
			encode($this->response['thumbnail_url']);
		auth::des_session_put(HATENA_SESSION_NAME,$message);
	}

	function hatena_session_unset()
	{
		return session_unregister(HATENA_SESSION_NAME);
	}

	function parse_message($message)
	{
		$rc = array();
		$tmp = explode('::',trim($message));
		$name = array('name','ts','image_url','thumbnail_url');
		for($i=0;$i<count($tmp);$i++) {
			$rc[$name[$i]] = decode($tmp[$i]);
		}
		return $rc;
	}

	function hatena_profile_url($name)
	{
		return HATENA_URL_PROFILE.rawurlencode($name);
	}

	function get_profile_link()
	{
		$message = auth_hatena::hatena_session_get();
		if (empty($message['name'])) return '';
		return '<a class="ext" href="'.auth_hatena::hatena_profile_url($message['name']).'" rel="nofollow">'.
			$message['name'].
			'<img src="'.IMAGE_DIR.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\''.
			auth_hatena::hatena_profile_url($message['name']).'\',\'_blank\');" /></a>';
        }

}

?>
