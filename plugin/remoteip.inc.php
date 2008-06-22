<?php
/**
 * PukiWiki Plus! IPアドレス認証プラグイン
 *
 * @copyright   Copyright &copy; 2007-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: remoteip.inc.php,v 0.5 2008/06/22 02:58:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_api.cls.php');

defined('REMOTEIP_CONFIG_PAGE') or define('REMOTEIP_CONFIG_PAGE','plugin/remoteip');

function plugin_remoteip_inline()
{
	global $auth_api;

	if (! isset($auth_api['remoteip']['use'])) return '';
	if (! $auth_api['remoteip']['use']) return '';

	// 処理済みか？
	$obj = new auth_remoteip();
	$msg = $obj->auth_session_get();
        if (! empty($msg['api']) && $obj->auth_name !== $msg['api']) return '';
	if (! empty($msg['uid'])) return '';

	$ip  = & $_SERVER['REMOTE_ADDR'];

	if (!count($config_remoteip)) {
		$obj_cfg = new Config(REMOTEIP_CONFIG_PAGE);
		$obj_cfg->read();
		$config_remoteip = $obj_cfg->get('IP');
		unset($obj_cfg);
	}

	foreach($config_remoteip as $data) {
		if ($ip !== $data[0]) continue;
		// UID not set.
		if (empty($data[1])) return '';
		$obj->response['uid']  = $data[1];
		$obj->response['name'] = $data[2];
		$obj->response['note'] = $data[3];
		break;
	}

	// if (empty($obj->response['uid'])) return '';
	$obj->auth_session_put();
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
	if (! $auth_api['remoteip']['use']) return array('role'=>ROLE_GUEST,'nick'=>'');
	$obj = new auth_remoteip();
	$msg = $obj->auth_session_get();
	if (! empty($msg['uid'])) return array('role'=>ROLE_AUTH_REMOTEIP,'nick'=>$msg['name'],'uid'=>$msg['uid'],'note'=>$msg['note'],'key'=>$msg['uid']);
	return array('role'=>ROLE_GUEST,'nick'=>'');
}

function plugin_remoteip_jump_url()
{
	global $vars;
	return get_page_location_uri($vars['page']);
}

class auth_remoteip extends auth_api
{
	function auth_remoteip()
	{
		//global $auth_api;
		$this->auth_name = 'remoteip';
		$this->field_name = array('uid','name','note');
		$this->response = array();
	}
}

?>
