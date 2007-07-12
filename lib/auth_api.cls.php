<?
/**
 * PukiWiki Plus! 認証処理
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: auth_api.cls.php,v 0.1 2007/07/13 01:05:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
// require_once(LIB_DIR . 'hash.php');

class auth_api
{
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
		foreach($this->field_name as $key) {
			$message .= (empty($message)) ? '' : '::';
			$message .= ($key == 'ts') ? UTIME : encode($this->response[$key]);
		}
		auth::des_session_put($this->message_md5(),$message);
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
			$rc[$this->field_name[$i]] = decode($tmp[$i]);
		}
		return $rc;
	}

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
		global $script;
		return md5($this->auth_name.'_message_'.$script.session_id());
	}
}

?>
