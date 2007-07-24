<?php
/**
 * PukiWiki Plus! IPアドレス認証プラグイン
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: remoteip.inc.php,v 0.3 2007/07/24 23:12:00 upk Exp $
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
	if (! empty($msg['uid'])) return '';

	$ip  = & $_SERVER['REMOTE_ADDR'];

	if (!count($config_remoteip)) {
		$obj_cfg = new Config(REMOTEIP_CONFIG_PAGE);
		$obj_cfg->read();
		$config_remoteip = $obj_cfg->get('IP');
		unset($obj_cfg);
	}

	$parm = array();
	foreach($config_remoteip as $data) {
		if ($ip !== $data[0]) continue;
		$parm['uid']  = $data[1];
		$parm['name'] = $data[2];
		$parm['note'] = $data[3];
		break;
	}

	if (empty($parm['uid'])) return '';
	$parm['ts'] = '';
	$obj->remoteip_session_put($parm);
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
	global $script, $vars;
	return $scrip.'?'.rawurlencode($vars['page']);
}

class auth_remoteip extends auth_api
{
	function auth_remoteip()
	{
		//global $auth_api;
		$this->auth_name = 'remoteip';
		$this->field_name = array('uid','ts','name','note');
		$this->response = array();
	}

	function remoteip_session_put($parm)
	{
		$this->response = $parm;
		$this->auth_session_put();
	}
}

?>
