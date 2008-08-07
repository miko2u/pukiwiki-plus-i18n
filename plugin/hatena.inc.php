<?php
/**
 * PukiWiki Plus! Hatena 認証処理
 *
 * @copyright   Copyright &copy; 2006,2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: hatena.inc.php,v 0.14 2008/08/07 21:29:00 upk Exp $
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
		$this->field_name = array('name','image_url','thumbnail_url');
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

function plugin_hatena_init()
{
	$msg = array(
	  '_hatena_msg' => array(
		'msg_logout'		=> _("logout"),
		'msg_logined'		=> _("%s has been approved by Hatena."),
		'msg_invalid'		=> _("The function of Hatena is invalid."),
		'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),
		'msg_not_start'		=> _("The session is not start."),
		'msg_hatena'		=> _("Hatena"),
		'btn_login'		=> _("LOGIN(Hatena)"),
          )
        );
        set_plugin_messages($msg);
}

function plugin_hatena_convert()
{
        global $script,$vars,$auth_api,$_hatena_msg;

	if (! $auth_api['hatena']['use']) return '<p>'.$_hatena_msg['msg_invalid'].'</p>';

	if (! function_exists('pkwk_session_start')) return '<p>'.$_hatena_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_hatena_msg['msg_not_start'].'</p>';

	$obj = new auth_hatena();
	$name = $obj->auth_session_get();
	if (isset($name['name'])) {
		// $name = array('name','ts','image_url','thumbnail_url');
		$logout_url = $script.'?plugin=hatena';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']);
		}
		$logout_url .= '&amp;logout';

		return <<<EOD
<div>
	<label>Hatena</label>:
	{$name['name']}
	<img src="{$name['thumbnail_url']}" alt="id:{$name['name']}" />
	(<a href="$logout_url">{$_hatena_msg['msg_logout']}</a>)
</div>

EOD;
	}

	// 他でログイン
	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return '';

	// ボタンを表示するだけ
	$login_url = $script.'?plugin=hatena';
	if (! empty($vars['page'])) {
		$login_url .= '&amp;page='.rawurlencode($vars['page']);
	}
	$login_url .= '&amp;login';

	return <<<EOD
<form action="$login_url" method="post">
	<div>
		<input type="submit" value="{$_hatena_msg['btn_login']}" />
	</div>
</form>

EOD;

}

function plugin_hatena_inline()
{
	global $script,$vars,$auth_api,$_hatena_msg;

	if (! $auth_api['hatena']['use']) return $_hatena_msg['msg_invalid'];

	if (! function_exists('pkwk_session_start')) return $_hatena_msg['msg_not_found'];
	if (pkwk_session_start() == 0) return $_hatena_msg['msg_not_start'];

	$obj = new auth_hatena();
	$name = $obj->auth_session_get();
	if (!empty($name['api']) && $obj->auth_name !== $name['api']) return;

	if (isset($name['name'])) {
		// $name = array('name','ts','image_url','thumbnail_url');
		$link = $name['name'].'<img src="'.$name['thumbnail_url'].'" alt="id:'.$name['name'].'" />';
		$logout_url = $script.'?plugin=hatena';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']);
		}
		$logout_url .= '&amp;logout';
		return sprintf($_hatena_msg['msg_logined'],$link) .
			'(<a href="'.$logout_url.'">'.$_hatena_msg['msg_logout'].'</a>)';
	}

	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return $_hatena_msg['msg_hatena'];

	$login_url = plugin_hatena_jump_url(1);
	return '<a href="'.$login_url.'">'.$_hatena_msg['msg_hatena'].'</a>';
}

function plugin_hatena_action()
{
	global $vars,$auth_api,$_hatena_msg;

	if (! $auth_api['hatena']['use']) return '';
	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	$page = (empty($vars['page'])) ? '' : decode($vars['page']);

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	// LOGIN
	if (isset($vars['login'])) {
		header('Location: '. plugin_hatena_jump_url());
		die();
        }

	$obj = new auth_hatena();

	// LOGOUT
	if (isset($vars['logout'])) {
		$obj->auth_session_unset();
		header('Location: '. get_page_location_uri($page));
		die();
	}

	// AUTH
	$rc = $obj->auth($vars['cert']);

	if (! isset($rc['has_error']) || $rc['has_error'] == 'true') {
		// ERROR
		$body = (isset($rc['message'])) ? $rc['message'] : 'unknown error.';
		$die_message($body);
	}

	$obj->auth_session_put();
	header('Location: '. get_page_location_uri($page));
	die();
}

function plugin_hatena_jump_url($inline=0)
{
	global $vars;
	$obj = new auth_hatena();
	$url = $obj->make_login_link(array('page'=>$vars['page'],'plugin'=>'hatena'));
	return ($inline) ? $url : str_replace('&amp;','&',$url);
}

function plugin_hatena_get_user_name()
{
	global $auth_api;
	// role,name,nick,profile
	if (! $auth_api['hatena']['use']) return array('role'=>ROLE_GUEST,'nick'=>'');
	$obj = new auth_hatena();
	$msg = $obj->auth_session_get();

	if (! empty($msg['name'])) return array('role'=>ROLE_AUTH_HATENA,'nick'=>$msg['name'],'profile'=>HATENA_URL_PROFILE.$msg['name'],'key'=>$msg['name']);
	return array('role'=>ROLE_GUEST,'nick'=>'');
}

?>
