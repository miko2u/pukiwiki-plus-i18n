<?
/**
 * PukiWiki Plus! JugemKey 認証処理
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: jugemkey.inc.php,v 0.4 2006/11/23 23:52:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
require_once(LIB_DIR . 'auth_jugemkey.cls.php');

function plugin_jugemkey_init()
{
	$msg = array(
	  '_jugemkey_msg' => array(
		'msg_logout'		=> _("logout"),
		'msg_logined'		=> _("%s has been approved by JugemKey."),      // %s さんは、Jugemkey によって、承認されています。
		'msg_invalid'		=> _("The function of Hatena is invalid."),     // Jugemkey の機能は、無効です。
		'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),    // pkwk_session_start() が見つかりません。
		'msg_not_start'         => _("The session is not start."),              // セッションが開始されていません。
		'msg_jugemkey'		=> _("JugemKey"),
		'btn_login'		=> _("LOGIN(JugemKey)"),
		'msg_userinfo'		=> _("JugemKey user information"),
		'msg_user_name'		=> _("User Name"),
	  )
	);
        set_plugin_messages($msg);
}

function plugin_jugemkey_convert()
{
	global $script,$vars,$auth_api,$_jugemkey_msg;

	if (! $auth_api['jugemkey']['use']) return '<p>'.$_jugemkey_msg['msg_invalid'].'</p>';

	if (! function_exists('pkwk_session_start')) return '<p>'.$_jugemkey_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_jugemkey_msg['msg_not_start'].'</p>';

	$name = auth_jugemkey::jugemkey_session_get();
	if (isset($name['title'])) {
		// $name = array('title','ts','token');
		$logout_url = $script.'?plugin=jugemkey';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}

		return <<<EOD
<div>
        <label>JugemKey</label>:
	{$name['title']}
	(<a href="$logout_url">{$_jugemkey_msg['msg_logout']}</a>)
</div>

EOD;
        }

	// ボタンを表示するだけ
	$login_url = $script.'?plugin=jugemkey';
	if (! empty($vars['page'])) {
		$login_url .= '&amp;page='.rawurlencode($vars['page']);
	}
	$login_url .= '&amp;login';

	return <<<EOD
<form action="$login_url" method="post">
	<div>
		<input type="submit" value="{$_jugemkey_msg['btn_login']}" />
	</div>
</form>

EOD;
}

function plugin_jugemkey_inline()
{
	global $script,$vars,$auth_api,$_jugemkey_msg;

	if (! $auth_api['jugemkey']['use']) return $_jugemkey_msg['msg_invalid'];

	if (! function_exists('pkwk_session_start')) return $_jugemkey_msg['msg_not_found'];
	if (pkwk_session_start() == 0) return $_jugemkey_msg['msg_not_start'];

	$name = auth_jugemkey::jugemkey_session_get();
	if (isset($name['title'])) {
		// $name = array('title','ts','token');
		$link = $name['title'];
		$logout_url = $script.'?plugin=jugemkey';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']).'&amp;logout';
		}
		return sprintf($_jugemkey_msg['msg_logined'],$link) .
			'(<a href="'.$logout_url.'">'.$_jugemkey_msg['msg_logout'].'</a>)';
        }

	$login_url = plugin_jugemkey_jump_url(1);
	return '<a href="'.$login_url.'">'.$_jugemkey_msg['msg_jugemkey'].'</a>';
}

function plugin_jugemkey_action()
{
	global $script,$vars,$auth_api,$_jugemkey_msg;

	if (! $auth_api['jugemkey']['use']) return '';
	if (! function_exists('pkwk_session_start')) return '';
	if (pkwk_session_start() == 0) return '';

	$r_page = (empty($vars['page'])) ? '' : rawurlencode($vars['page']);

	// LOGIN
	if (isset($vars['login'])) {
		header('Location: '. plugin_jugemkey_jump_url());
		die();
	}
	// LOGOUT
	if (isset($vars['logout'])) {
		auth_jugemkey::jugemkey_session_unset();
		header('Location: '.$script.'?'.$r_page);
		die();
	}

	// Get token info
	if (isset($vars['userinfo'])) {
		$obj_token =new auth_jugemkey($auth_api['jugemkey']['sec_key'],$auth_api['jugemkey']['api_key']);
		$rc = $obj_token->get_userinfo($vars['token']);
		if ($rc['rc'] != 200) {
			die_message('JugemKey: RC='.$rc['rc']);
		}

		$body = '<h3>'.$_jugemkey_msg['msg_userinfo'].'</h3>'.
			'<strong>'.$_jugemkey_msg['msg_user_name'].': '.$rc['title'].'</strong>';
		return array('msg'=>'JugemKey', 'body'=>$body);
	}

	// AUTH
	$obj = new auth_jugemkey($auth_api['jugemkey']['sec_key'],$auth_api['jugemkey']['api_key']);
	$rc = $obj->auth($vars['frob']);

	if ($rc['rc'] != 200) {
		// ERROR
		die_message('JugemKey: '.$rc['rc']);
	}

	$obj->jugemkey_session_put();
	header('Location: '.$script.'?'.$r_page);
	die();
}

function plugin_jugemkey_jump_url($inline=0)
{
	global $auth_api,$vars,$script;
	$page = (empty($vars['page'])) ? '' : '&page='.$vars['page'];
	$callback_url = $script.'?plugin=jugemkey'.$page;
	$obj = new auth_jugemkey($auth_api['jugemkey']['sec_key'],$auth_api['jugemkey']['api_key']);
	$url = $obj->make_login_link($callback_url);
	return ($inline) ? $url : str_replace('&amp;','&',$url);
}

function plugin_jugemkey_get_user_name()
{
	global $script,$auth_api;
        if (! $auth_api['jugemkey']['use']) return array(ROLE_GUEST,'','','');
	$login = auth_jugemkey::jugemkey_session_get();
	$info = (empty($login['token'])) ? '' : $script.'?plugin=jugemkey&token='.$login['token'].'&userinfo';
	if (! empty($login['title'])) return array(ROLE_AUTH_JUGEMKEY,$login['title'],$login['title'],$info);
	return array(ROLE_GUEST,'','','');
}

?>
