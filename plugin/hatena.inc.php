<?php
/**
 * PukiWiki Plus! Hatena 認証処理
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: hatena.inc.php,v 0.6 2006/11/23 01:47:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_hatena.cls.php');

function plugin_hatena_init()
{
	$msg = array(
	  '_hatena_msg' => array(
		'msg_logout'		=> _("logout"),
		'msg_logined'		=> _("%s has been approved by Hatena."),	// %s さんは、Hatena によって、承認されています。
		'msg_invalid'		=> _("The function of Hatena is invalid."),	// Hatena の機能は、無効です。
		'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),	// pkwk_session_start() が見つかりません。
		'msg_not_start'		=> _("The session is not start."),		// セッションが開始されていません。
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

	$name = auth_hatena::hatena_session_get();
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

	$name = auth_hatena::hatena_session_get();
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

	// LOGIN
	if (isset($vars['login'])) {
		header('Location: '. plugin_hatena_jump_url());
		die();
        }
	// LOGOUT
	if (isset($vars['logout'])) {
		auth_hatena::hatena_session_unset();
		header('Location: '.$script.'?'.$r_page);
		die();
	}

	// AUTH
	$obj_hatena = new auth_hatena($auth_api['hatena']['sec_key'],$auth_api['hatena']['api_key']);
	$rc = $obj_hatena->auth($vars['cert']);

	if (! isset($rc['has_error']) || $rc['has_error'] == 'true') {
		// ERROR
		$body = (isset($rc['message'])) ? $rc['message'] : 'unknown error.';
		die_message($body);
	}

	$obj_hatena->hatena_session_put();
	header('Location: '.$script.'?'.$r_page);
	die();
}

function plugin_hatena_jump_url($inline=0)
{
	global $auth_api,$vars;
	$obj = new auth_hatena($auth_api['hatena']['sec_key'],$auth_api['hatena']['api_key']);
	$url = $obj->make_login_link(array('page'=>$vars['page'],'plugin'=>'hatena'));
	return ($inline) ? $url : str_replace('&amp;','&',$url);
}

function plugin_hatena_get_user_name()
{
	global $auth_api;
	// role,name,nick,profile
	if (! $auth_api['hatena']['use']) return array(ROLE_GUEST,'','','');
	$msg = auth_hatena::hatena_session_get();
	if (! empty($msg['name'])) return array(ROLE_AUTH_HATENA,$msg['name'],$msg['name'],HATENA_URL_PROFILE.$msg['name']);
	return array(ROLE_GUEST,'','','');
}

?>
