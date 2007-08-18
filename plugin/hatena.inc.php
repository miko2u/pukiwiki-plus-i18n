<?php
/**
 * PukiWiki Plus! Hatena 認証処理
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: hatena.inc.php,v 0.11 2007/08/19 02:07:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_hatena.cls.php');

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
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}

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
	if (isset($name['name'])) {
		// $name = array('name','ts','image_url','thumbnail_url');
		$link = $name['name'].'<img src="'.$name['thumbnail_url'].'" alt="id:'.$name['name'].'" />';
		$logout_url = $script.'?plugin=hatena';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}
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
	global $script,$vars,$auth_api,$_hatena_msg;

	if (! $auth_api['hatena']['use']) return '';
	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	$r_page = (empty($vars['page'])) ? '' : rawurlencode( decode($vars['page']) );

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
		header('Location: '.$script.'?'.$r_page);
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
	header('Location: '.$script.'?'.$r_page);
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
