<?php
/**
 * PukiWiki Plus! livedoor 認証処理
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: livedoor.inc.php,v 0.5 2007/08/19 02:11:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_livedoor.cls.php');

function plugin_livedoor_init()
{
	$msg = array(
	  '_livedoor_msg' => array(
		'msg_logout'		=> _("logout"),
		'msg_logined'		=> _("%s has been approved by livedoor."),
		'msg_invalid'		=> _("The function of livedoor is invalid."),
		'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),
		'msg_not_start'		=> _("The session is not start."),
		'msg_livedoor'		=> _("livedoor"),
		'btn_login'		=> _("LOGIN(livedoor)"),
          )
        );
        set_plugin_messages($msg);
}

function plugin_livedoor_convert()
{
        global $script,$vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return '<p>'.$_livedoor_msg['msg_invalid'].'</p>';

	if (! function_exists('pkwk_session_start')) return '<p>'.$_livedoor_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_livedoor_msg['msg_not_start'].'</p>';

	$obj = new auth_livedoor();
	$name = $obj->auth_session_get();
	if (isset($name['livedoor_id'])) {
		$logout_url = $script.'?plugin=livedoor';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}

		return <<<EOD
<div>
	<label>livedoor</label>:
	{$name['livedoor_id']}
	(<a href="$logout_url">{$_livedoor_msg['msg_logout']}</a>)
</div>

EOD;
	}

	// 他でログイン
	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return '';

	// ボタンを表示するだけ
	$login_url = $script.'?plugin=livedoor';
	if (! empty($vars['page'])) {
		$login_url .= '&amp;page='.rawurlencode($vars['page']);
	}
	$login_url .= '&amp;login';

	return <<<EOD
<form action="$login_url" method="post">
	<div>
		<input type="submit" value="{$_livedoor_msg['btn_login']}" />
	</div>
</form>

EOD;

}

function plugin_livedoor_inline()
{
	global $script,$vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return $_livedoor_msg['msg_invalid'];

	if (! function_exists('pkwk_session_start')) return $_livedoor_msg['msg_not_found'];
	if (pkwk_session_start() == 0) return $_livedoor_msg['msg_not_start'];

	$obj = new auth_livedoor();
	$name = $obj->auth_session_get();
	if (isset($name['livedoor_id'])) {
		$logout_url = $script.'?plugin=livedoor';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}
		return sprintf($_livedoor_msg['msg_logined'],$name['livedoor_id']) .
			'(<a href="'.$logout_url.'">'.$_livedoor_msg['msg_logout'].'</a>)';
	}

	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return $_livedoor_msg['msg_livedoor'];

	$login_url = plugin_livedoor_jump_url(1);
	return '<a href="'.$login_url.'">'.$_livedoor_msg['msg_livedoor'].'</a>';
}

function plugin_livedoor_action()
{
	global $script,$vars,$auth_api,$_livedoor_msg;

	if (! $auth_api['livedoor']['use']) return '';
	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	$r_page = (empty($vars['page'])) ? '' : rawurlencode( decode($vars['page']) );

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	// LOGIN
	if (isset($vars['login'])) {
		header('Location: '. plugin_livedoor_jump_url());
		die();
        }

	$obj = new auth_livedoor();

	// LOGOUT
	if (isset($vars['logout'])) {
		$obj->auth_session_unset();
		header('Location: '.$script.'?'.$r_page);
		die();
	}

	// AUTH
	$rc = $obj->auth($vars);

	if (! isset($rc['has_error']) || $rc['has_error'] == 'true') {
		// ERROR
		$body = (isset($rc['message'])) ? $rc['message'] : 'unknown error.';
		$die_message($body);
	}

	$obj->auth_session_put();
	$r_page = rawurlencode($obj->get_return_page());
	header('Location: '.$script.'?'.$r_page);
	die();
}

function plugin_livedoor_jump_url($inline=0)
{
	global $vars;
	$obj = new auth_livedoor();
	$url = $obj->make_login_link($vars['page']);
	return ($inline) ? $url : str_replace('&amp;','&',$url);
}

function plugin_livedoor_get_user_name()
{
	global $auth_api;
	// role,name,nick,profile
	if (! $auth_api['livedoor']['use']) return array('role'=>ROLE_GUEST,'nick'=>'');
	$obj = new auth_livedoor();
	$msg = $obj->auth_session_get();
	$info = 'http://www.livedoor.com/';
	if (! empty($msg['livedoor_id']))
		return array('role'=>ROLE_AUTH_LIVEDOOR,'nick'=>$msg['livedoor_id'],'key'=>$msg['livedoor_id'],'profile'=>$info);
	return array('role'=>ROLE_GUEST,'nick'=>'');
}

?>
