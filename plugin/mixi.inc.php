<?php
/**
 * PukiWiki Plus! mixi 認証処理
 *
 * @copyright   Copyright &copy; 2007-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: mixi.inc.php,v 0.6 2008/06/21 23:31:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 * @see		http://d.hatena.ne.jp/shimooka/20070702/1183374400
 */
require_once(LIB_DIR . 'auth_api.cls.php');

defined('AUTH_MIXI_URI')          or define('AUTH_MIXI_URI', 'http://mixi.jp/atom/tracks/r=2/');
defined('AUTH_HOME_URI')          or define('AUTH_HOME_URI', 'http://mixi.jp/show_friend.pl?id=');
defined('PLUGIN_MIXI_SIZE_LOGIN') or define('PLUGIN_MIXI_SIZE_LOGIN', 30);

function plugin_mixi_init()
{
	$msg = array(
          '_mixi_msg' => array(
                'msg_logout'		=> _("logout"),
                'msg_logined'		=> _("%s has been approved by mixi."),
                'msg_invalid'		=> _("The function of mixi is invalid."),
                'msg_not_found'		=> _("pkwk_session_start() doesn't exist."),
                'msg_not_start'		=> _("The session is not start."),
                'msg_mixi'		=> _("mixi"),
                'msg_title'             => _("LOGIN FORM"),
		'msg_email'		=> _("EMAIL"),
		'msg_password'		=> _("PASSWD"),
		'btn_login'		=> _("LOGIN"),
					// mixi がサポートする認証方法ではありません。
		'msg_warn01'		=> _("It is not an authentication method supported by mixi."),
					// このサイトが信頼できる場合のみ、ログインを行って下さい。
		'msg_warn02'		=> _("Please login only when you can trust this site."),
		'err_authentication'	=> _("Authentication error."),
          )
	);
	set_plugin_messages($msg);
}

function plugin_mixi_convert()
{
	global $auth_api,$_mixi_msg,$vars;
       
	if (! isset($auth_api['mixi']['use'])) return '';
	if (! $auth_api['mixi']['use']) return '<p>'.$_mixi_msg['msg_invalid'].'</p>';

	if (! function_exists('pkwk_session_start')) return '<p>'.$_mixi_msg['msg_not_found'].'</p>';
        if (pkwk_session_start() == 0) return '<p>'.$_mixi_msg['msg_not_start'].'</p>';

	// 処理済みか？
	$obj = new auth_mixi();
	$name = $obj->auth_session_get();

	if (! empty($name['nickname'])) {
		$logout_url = $script.'?plugin=mixi&amp;logout';
		if (! empty($vars['page'])) {
			$logout_url .= '&amp;page='.rawurlencode($vars['page']);
		}

		$prof = '<a href="'.AUTH_HOME_URI.$name['id'].'">'.$name['nickname'].'</a>';

		return <<<EOD
<div>
	<label>mixi</label>:
	$prof (<a href="$logout_url">{$_mixi_msg['msg_logout']}</a>)
</div>

EOD;
	}

	// 他でログイン
	$auth_key = auth::get_user_name();
	//if (! empty($auth_key['nick'])) return '';
	if (! empty($auth_key['nick'])) return $auth_key['nick'];

	return plugin_mixi_login_form();
}

function plugin_mixi_inline()
{
        global $script,$vars,$auth_api,$_mixi_msg;

	if (! isset($auth_api['mixi']['use'])) return '';
	if (! $auth_api['mixi']['use']) return '<p>'.$_mixi_msg['msg_invalid'].'</p>';
	if (! function_exists('pkwk_session_start')) return '<p>'.$_mixi_msg['msg_not_found'].'</p>';
	if (pkwk_session_start() == 0) return '<p>'.$_mixi_msg['msg_not_start'].'</p>';

        $r_page = (empty($vars['page'])) ? '' : '&amp;page='.rawurlencode($vars['page']);

	$obj = new auth_mixi();
	$name = $obj->auth_session_get();
	if (!empty($name['api']) && $obj->auth_name !== $name['api']) return;

	if (! empty($name['nickname'])) {
		$link = '<a href="'.AUTH_HOME_URI.$name['id'].'">'.$name['nickname'].'</a>';
		$logout_url = $script.'?plugin=mixi';
		if (! empty($r_page)) {
			$logout_url .= $r_page.'&amp;logout';
		}
		return sprintf($_mixi_msg['msg_logined'],$link) .
			'(<a href="'.$logout_url.'">'.$_mixi_msg['msg_logout'].'</a>)';
        }

	$auth_key = auth::get_user_name();
	if (! empty($auth_key['nick'])) return $_mixi_msg['msg_mixi'];

	return '<a href="'.$script.'?plugin=mixi'.$r_page.'">'.$_mixi_msg['msg_mixi'].'</a>';
}

function plugin_mixi_action()
{
	global $vars,$_mixi_msg;

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	if (! function_exists('pkwk_session_start')) $die_message($_mixi_msg['msg_not_found']);
	if (pkwk_session_start() == 0) $die_message($_mixi_msg['msg_not_start']);

	// LOGOUT
	if (isset($vars['logout'])) {
		$obj = new auth_mixi();
		$obj->auth_session_unset();
		$page = (empty($vars['page'])) ? '' : $vars['page'];
		header('Location: '.get_page_location_uri($page));
		die();
	}

	$action = (empty($vars['action'])) ? '' : $vars['action'];

	if ($action !== 'login' || empty($vars['email']) || empty($vars['nonce']) || empty($vars['created']) || empty($vars['digest'])) {
		return array('msg'=>$_mixi_msg['msg_title'], 'body'=>plugin_mixi_login_form() );
	}

	$header = mixi_wsse_header($vars['email'],$vars['nonce'],$vars['created'],$vars['digest'].'=');
	$data = http_request(AUTH_MIXI_URI,'GET',array('X-WSSE'=>$header),array());

	if ($data['rc'] === 401) {
		return array('msg'=>'Authorization Required', 'body'=>plugin_mixi_login_form());
	}
	if ($data['rc'] !== 200) $die_message('ERROR : '.$data['rc']);

	list($mickname,$id) = mixi_get_id($data['data']);

	$obj = new auth_mixi();

	$obj->response['id'] = $id;
	$obj->response['nickname'] = $mickname;
	$obj->response['wsse'] = encode($header);
	$obj->auth_session_put();

	$page = (empty($vars['page'])) ? '' : $vars['page'];
	header('Location: '.get_page_location_uri($page));
	die();
}

function mixi_wsse_header($uid,$nonce,$created,$digest)
{
	return 'UsernameToken Username="'.$uid.'", PasswordDigest="'.$digest.'", Nonce="'.$nonce.'", Created="'.$created.'"';
}

function mixi_get_id($data)
{
	preg_match("'<atom:name>(.*?)<\/atom:name>'si",$data,$matches);
	$mickname = $matches[1];
	preg_match("'\/member_id=(.*?)\"'si",$data,$matches);
	$id = $matches[1];
	return array($mickname,$id);
}

function plugin_mixi_login_form()
{
	global $script,$vars,$_mixi_msg,$head_tags;

	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/sha1.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/base64.js"></script>';

	$nonce = auth::b64_sha1(md5(rand().UTIME));
	$created = gmdate('Y-m-d\TH:i:s\Z',UTIME);

	$page = (empty($vars['page'])) ? '' : $vars['page'];
	$size = PLUGIN_MIXI_SIZE_LOGIN;

	$rc = <<<EOD
<script type="text/javascript">
<!-- <![CDATA[

function set_digest()
{
 var nonce;
 var objForm = eval("document.mixi_passwd");
 nonce = base64decode(objForm.nonce.value);
 objForm.digest.value = b64_sha1(nonce + objForm.created.value + objForm.passwd.value);
 objForm.passwd.disabled = true;
}

//]]>-->
</script>

EOD;

	$rc .= '<div><fieldset><legend>login (use mixi id)</legend>';

	$rc .= <<<EOD
<form name="mixi_passwd" action="$script" method="post">
  <div class="mixi">
    <input type="hidden" name="plugin" value="mixi" />
    <input type="hidden" name="action" value="login" />
    <input type="hidden" name="page" value="$page" />
    <input type="hidden" name="nonce" value="$nonce" />
    <input type="hidden" name="created" value="$created" />
    <input type="hidden" name="digest" />
    {$_mixi_msg['msg_email']}
    <input type="text" name="email" size="$size" />
    {$_mixi_msg['msg_password']}
    <input type="password" name="passwd" size="$size" />
    <input type="submit" onclick="set_digest()" value="{$_mixi_msg['btn_login']}" />
  </div>
</form>

EOD;

	$rc .= "<ul>\n";
	$rc .= '<li>'.$_mixi_msg['msg_warn01'].'</li>';
	$rc .= '<li>'.$_mixi_msg['msg_warn02'].'</li>';
	$rc .= "</ul>\n";
	$rc .= '</fieldset></div>'."\n";

	return $rc;
}

function plugin_mixi_get_user_name()
{
	global $auth_api;
        // role,name,nick,profile
	if (! $auth_api['mixi']['use']) return array('role'=>ROLE_GUEST,'nick'=>'');

	$obj = new auth_mixi();
	$msg = $obj->auth_session_get();
	// $prof = '<a href="'.AUTH_HOME_URI.$msg['id'].'">'.$msg['nickname'].'</a>';
	if (empty($msg['nickname'])) return array('role'=>ROLE_GUEST,'nick'=>'');
	$prof = AUTH_HOME_URI.$msg['id'];
	return array('role'=>ROLE_AUTH_MIXI,'key'=>$msg['id'],'nick'=>$msg['nickname'],'profile'=>$prof);
}

function plugin_mixi_jump_url()
{
	global $vars;
	$page = (empty($vars['page'])) ? '' : $vars['page'];
	return get_location_uri('mixi',$page);
}

class auth_mixi extends auth_api
{
	function auth_mixi()
	{
		$this->auth_name = 'mixi';
		$this->field_name = array('id','nickname','wsse');
		$this->response = array();
	}
}

?>
