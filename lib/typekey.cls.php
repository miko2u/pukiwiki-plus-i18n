<?php
/**
 * PukiWiki Plus! TypeKey 認証処理
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: typekey.cls.php,v 0.1 2006/11/19 01:13:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */

defined('TYPEKEY_LOGIN_URL')	or define('TYPEKEY_LOGIN_URL',	'https://www.typekey.com/t/typekey/login');
defined('TYPEKEY_LOGOUT_URL')	or define('TYPEKEY_LOGOUT_URL',	'https://www.typekey.com/t/typekey/logout');
defined('TYPEKEY_VERSION')	or define('TYPEKEY_VERSION',	'1.1');
defined('TYPEKEY_REGKEYS')	or define('TYPEKEY_REGKEYS', 	'http://www.typekey.com/extras/regkeys.txt');
defined('TYPEKEY_CACHE_TIME')	or define('TYPEKEY_CACHE_TIME', 60*60*24*2); // 2 day
defined('TYPEKEY_PROFILE_URL')	or define('TYPEKEY_PROFILE_URL','http://profile.typekey.com/');
defined('TYPEKEY_SESSION_MESSAGE') or define('TYPEKEY_SESSION_MESSAGE','typekey_message');

class typekey
{
	var $siteToken, $need_email, $regkeys, $sigKey, $version;

	function typekey($siteToken)
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
		$message = typekey::typekey_session_get();
		return (empty($message[$field])) ? '' : $message[$field];
	}

	function get_profile_link()
	{
		$message = typekey::typekey_session_get();
		if (empty($message['nick'])) return '';
		return '<a class="ext" href="'.typekey::typekey_profile_url($message['name']).'" rel="nofollow">'.
			$message['nick'].
			'<img src="'.IMAGE_DIR.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\''.
			typekey::typekey_profile_url($message['name']).'\',\'_blank\');" /></a>';
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
		$rc = TYPEKEY_LOGIN_URL.'?t='.$this->siteToken.'&v='.$this->version;
		if ($this->need_email != 0) {
			$rc .= '&need_email=1';
		}
		return $rc.'&_return='.$return;
	}

	function typekey_logout_url($return='')
	{
		if (empty($return)) {
			global $script;
			$return = $script;
		}
		return TYPEKEY_LOGOUT_URL.'?_return='.$return;
        }

	function typekey_login($return)
	{
		header('Location: '.$this->typekey_login_url($return));
		die();
	}

	function typekey_profile_url($name)
	{
		return TYPEKEY_PROFILE_URL.urlencode($name).'/';
	}

	function session_get($session_name)
	{
		global $adminpass;

		// adminpass の処理
		list($scheme, $salt) = auth::passwd_parse($adminpass);

		// des化された内容を平文に戻す
		if (isset($_SESSION[$session_name])) {
			require_once(LIB_DIR . 'des.php');
			return des($salt, base64_decode($_SESSION[$session_name]), 0, 0, null);
		}
                return '';
        }

	function session_put($session_name,$val)
	{
		global $adminpass;

		// adminpass の処理
		list($scheme, $salt) = auth::passwd_parse($adminpass);
		require_once(LIB_DIR . 'des.php');
		$_SESSION[$session_name] = base64_encode( des($salt, $val, 1, 0, null) );
	}

	function typekey_session_get()
	{
		$val = typekey::session_get(TYPEKEY_SESSION_MESSAGE);
		if (empty($val)) {
			return array();
		}
		return typekey::parse_message($val);
	}

	function typekey_session_put()
	{
		$message = $this->gen_message();
		typekey::session_put(TYPEKEY_SESSION_MESSAGE,$message);
	}

	function typekey_session_unset()
	{
		return session_unregister(TYPEKEY_SESSION_MESSAGE);
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
