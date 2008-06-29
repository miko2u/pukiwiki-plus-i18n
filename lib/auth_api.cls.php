<?php
/**
 * PukiWiki Plus! 認証処理
 *
 * @copyright   Copyright &copy; 2007-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_api.cls.php,v 0.5 2008/06/29 16:56:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
// require_once(LIB_DIR . 'hash.php');

class auth_api
{
	// auth_session_put    - auth_name, field_name, response
	// responce_xml_parser - response
	var $auth_name, $field_name, $response;

        function auth_session_get()
        {
		$val = auth::des_session_get($this->message_md5());
		if (empty($val)) {
			return array();
		
		}
		return $this->parse_message($val);
        }

	function auth_session_put()
	{
		$message = '';
		foreach(array_merge(array('api','ts'),$this->field_name) as $key) {
		// foreach($this->field_name as $key) {
			$message .= (empty($message)) ? '' : '::'; // delm
			$message .= $key.'$$';
			switch($key) {
			case 'api':
				$message .= $this->auth_name;
				break;
			case 'ts':
				$message .= UTIME;
				break;
			default:
				$message .= encode($this->response[$key]);
			}
		}
		auth::des_session_put($this->message_md5(),$message);

		if ($this->auth_name != 'openid_verify') {
			log_write('login','');
		}
        }

	function auth_session_unset()
	{
		return session_unregister($this->message_md5());
	}

	function parse_message($message)
	{
		$rc = array();
		$tmp = explode('::',trim($message));
		for($i=0;$i<count($tmp);$i++) {
			$tmp2 = explode('$$',$tmp[$i]);
                        $rc[$tmp2[0]] = decode($tmp2[1]);
		}
		return $rc;
	}

	// response
	function responce_xml_parser($data)
	{
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $data, $val, $index);
		xml_parser_free($xml_parser);

		foreach($val as $x) {
			if ($x['type'] != 'complete') continue;
			$this->response[strtolower($x['tag'])] = $x['value'];
		}
	}

	function message_md5()
	{
		// return md5($this->auth_name.'_message_'.get_script_absuri().session_id());
		return md5('plus_auth_msg_'.get_script_absuri().session_id());
	}

}

?>
