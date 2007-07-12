<?php
/**
 * PukiWiki Plus! Hatena 認証処理
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_hatena.cls.php,v 0.7 2007/07/13 01:05:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_api.cls.php');

defined('HATENA_URL_AUTH')	or define('HATENA_URL_AUTH','http://auth.hatena.ne.jp/auth');
defined('HATENA_URL_XML')	or define('HATENA_URL_XML', 'http://auth.hatena.ne.jp/api/auth.xml');
defined('HATENA_URL_PROFILE')	or define('HATENA_URL_PROFILE','http://www.hatena.ne.jp/user?userid=');

class auth_hatena extends auth_api
{
	var $sec_key,$api_key;

	function auth_hatena()
	{
		global $auth_api;
		$this->auth_name = 'hatena';
		$this->sec_key = $auth_api[$this->auth_name]['sec_key'];
		$this->api_key = $auth_api[$this->auth_name]['api_key'];
		$this->field_name = array('name','ts','image_url','thumbnail_url');
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

	function hatena_profile_url($name)
	{
		return HATENA_URL_PROFILE.rawurlencode($name);
	}

	function get_profile_link()
	{
		$message = $this->auth_session_get();
		if (empty($message['name'])) return '';
		return '<a class="ext" href="'.auth_hatena::hatena_profile_url($message['name']).'" rel="nofollow">'.
			$message['name'].
			'<img src="'.IMAGE_URI.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\''.
			auth_hatena::hatena_profile_url($message['name']).'\',\'_blank\');" /></a>';
        }

}

?>
