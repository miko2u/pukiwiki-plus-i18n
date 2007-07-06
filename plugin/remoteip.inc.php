<?php
/**
 * PukiWiki Plus! IPアドレス認証プラグイン
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: remoteip.inc.php,v 0.1 2007/07/07 01:51:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
// require_once(LIB_DIR . 'auth_remote.cls.php');

defined('REMOTEIP_CONFIG_PAGE') or define('REMOTEIP_CONFIG_PAGE','plugin/remoteip');

function plugin_remoteip_inline()
{
	global $auth_api;

	if (! isset($auth_api['remoteip']['use'])) return '';
	if (! $auth_api['remoteip']['use']) return '';

	// 処理済みか？
	$msg = auth_remoteip::remoteip_session_get();
	if (! empty($msg['uid'])) return '';

	$ip  = & $_SERVER['REMOTE_ADDR'];

	if (!count($config_remoteip)) {
		$obj = new Config(REMOTEIP_CONFIG_PAGE);
		$obj->read();
		$config_remoteip = $obj->get('IP');
		unset($obj);
	}

	$uid = $name = $prof = '';
	foreach($config_remoteip as $data) {
		if ($ip !== $data[0]) continue;
		$uid = $data[1];
		$name = $data[2];
		$note = $data[3];
		break;
	}

	if (empty($uid)) return '';
	auth_remoteip::remoteip_session_put($uid,$name,$note);
	return '';
}

function plugin_remoteip_convert()
{
	plugin_remoteip_inline();
	return '';
}

function plugin_remoteip_get_user_name()
{
	global $auth_api;
	// role,name,nick,profile
	if (! $auth_api['remoteip']['use']) return array(ROLE_GUEST,'','','');
	$msg = auth_remoteip::remoteip_session_get();
	if (! empty($msg['uid'])) return array(ROLE_AUTH_REMOTEIP,$msg['uid'],$msg['name'],$msg['note']);
	return array(ROLE_GUEST,'','','');
}

function plugin_remoteip_jump_url()
{
	global $script, $vars;
	return $scrip.'?'.rawurlencode($vars['page']);
}

class auth_remoteip
{
	function remoteip_session_get()
	{
		global $script;
		$val = auth::des_session_get(md5('remoteip_message_'.$script.session_id()));
		if (empty($val)) {
			return array();
		}
		return auth_remoteip::parse_message($val);
	}

        function remoteip_session_put($uid,$name,$note)
        {
                global $script;
                $message = encode($uid).'::'.
                        encode(UTIME).'::'.
                        encode($name).'::'.
                        encode($note);
                auth::des_session_put(md5('remoteip_message_'.$script.session_id()),$message);
        }

        function remoteip_session_unset()
        {
                global $script;
                return session_unregister(md5('remoteip_message_'.$script.session_id()));
        }

        function parse_message($message)
        {
                $rc = array();
                $tmp = explode('::',trim($message));
                $name = array('uid','ts','name','note');
                for($i=0;$i<count($tmp);$i++) {
                        $rc[$name[$i]] = decode($tmp[$i]);
                }
                return $rc;
        }
}

?>
