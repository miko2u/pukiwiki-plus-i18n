<?php
/**
 * PukiWiki Plus! OpenID 認証処理
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_openid.cls.php,v 0.2 2007/07/16 20:21:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_api.cls.php');

class auth_openid_plus extends auth_api
{
	function auth_openid_plus()
	{
		$this->auth_name = 'openid';
		// nickname,email,fullname,dob,gender,postcode,country,language,timezone
		$this->field_name = array('nickname','ts','email','openid_identity');
		//$this->field_name = array('nickname','ts','email');
		$this->response = array();
        }

	function openid_session_put($parm)
	{
		$this->response = $parm;
		$this->auth_session_put();
	}
}

class auth_openid_plus_verify extends auth_openid_plus
{
	function auth_openid_plus_verify()
	{
		$this->auth_name = 'openid_verify';
		$this->field_name = array('openid.server','openid.delegate','ts','page');
		$this->response = array();
	}
	function get_host()
	{
		$msg = $this->auth_session_get();
		$arr = parse_url($msg['openid.server']);
		return strtolower($arr['host']);
	}
	function get_delegate()
	{
		$msg = $this->auth_session_get();
		return $msg['openid.delegate'];

	}
}

?>
