<?php
/**
 * PukiWiki Plus! TypeKey 認証処理
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_typekey.cls.php,v 0.10 2007/07/09 23:40:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */

defined('TYPEKEY_URL_LOGIN')	or define('TYPEKEY_URL_LOGIN',	 'https://www.typekey.com/t/typekey/login');
defined('TYPEKEY_URL_LOGOUT')	or define('TYPEKEY_URL_LOGOUT',	 'https://www.typekey.com/t/typekey/logout');
defined('TYPEKEY_URL_PROFILE')	or define('TYPEKEY_URL_PROFILE', 'http://profile.typekey.com/');
defined('TYPEKEY_REGKEYS')	or define('TYPEKEY_REGKEYS',	 'http://www.typekey.com/extras/regkeys.txt');
defined('TYPEKEY_VERSION')	or define('TYPEKEY_VERSION',	 '1.1');
defined('TYPEKEY_CACHE_TIME')	or define('TYPEKEY_CACHE_TIME',	 60*60*24*2); // 2 day

class auth_typekey
{
	var $siteToken, $need_email, $regkeys, $sigKey, $version;

	function auth_typekey($siteToken)
	{
		$this->siteToken = trim($siteToken);
		$this->need_email = 0;
		$this->version = TYPEKEY_VERSION;
		$this->sigKey = array();
	}

	function set_need_email($x) { $this->need_email = $x; }
	function set_version($x) { $this->version = $x; }
	function set_regkeys() { $this->regkeys = $this->get_regkeys(); }
	function set_sigKey($sigKey)
	{
		foreach(array('email','name','nick','ts') as $key) {
			$this->sigKey[$key] = (empty($sigKey[$key])) ? '' : trim($sigKey[$key]);
		}

		// FIXME: DSA署名中に + が混入されると空白に変換される場合があるための対応
		$this->sigKey['sig'] = (empty($sigKey['sig'])) ? '' : str_replace(' ', '+', $sigKey['sig']);
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
		$message = auth_typekey::typekey_session_get();
		return (empty($message[$field])) ? '' : $message[$field];
	}

	function get_profile_link()
	{
		$message = auth_typekey::typekey_session_get();
		if (empty($message['nick'])) return '';
		return '<a class="ext" href="'.auth_typekey::typekey_profile_url($message['name']).'" rel="nofollow">'.
			$message['nick'].
			'<img src="'.IMAGE_URI.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\''.
			auth_typekey::typekey_profile_url($message['name']).'\',\'_blank\');" /></a>';
	}

	function gen_message()
	{
		$message = '';
		// <email>::<name>::<nick>::<ts>::<site-token>
		foreach(array('email','name','nick','ts') as $key) {
			$message .= $this->sigKey[$key].'::';
		}
		$message .= $this->siteToken;
		return $message;
	}

	function parse_message($message)
	{
		$rc = array();
		$tmp = explode('::',trim($message));
		$name = array('email','name','nick','ts','site_token');
		for($i=0;$i<count($tmp);$i++) {
			$rc[$name[$i]] = $tmp[$i];
		}
		return $rc;
	}

	function typekey_login_url($return='')
	{
		if (empty($return)) {
			global $script;
			$return = $script;
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
			global $script;
			$return = $script;
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

	function typekey_session_get()
	{
		global $script;
		$val = auth::des_session_get(md5('typekey_message_'.$script.session_id()));
		if (empty($val)) {
			return array();
		}
		return auth_typekey::parse_message($val);
	}

	function typekey_session_put()
	{
		global $script;
		$message = $this->gen_message();
		auth::des_session_put(md5('typekey_message_'.$script.session_id()),$message);
	}

	function typekey_session_unset()
	{
		global $script;
		return session_unregister(md5('typekey_message_'.$script.session_id()));
	}

	function auth()
	{
		if (empty($this->sigKey['email'])) return false;
		// FIXME: どの程度までチェックするのか？
		if ($this->need_email) {
			if (! strpos($this->sigKey['email'],'@')) return false;
		}
		$message = $this->gen_message();

		require_once(LIB_DIR.'DSA.php');
		return Security_DSA::verify($message, $this->sigKey['sig'], $this->regkeys);
	}

}

?>
