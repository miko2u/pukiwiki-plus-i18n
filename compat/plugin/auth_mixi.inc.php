<?php
/**
 * PukiWiki Plus! OpenID 認証処理 (mixi)
 *
 * @copyright   Copyright &copy; 2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @author      Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: openid.inc.php,v 0.3 2009/05/20 01:42:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 * このプラグインを利用するためには、mixi が定めるガイドラインを遵守する必要があります。
 * - http://developer.mixi.co.jp/openid
 * - http://developer.mixi.co.jp/document/openid-guideline
 *
 * define の設定値を変更する場合には、
 * init/auth_mixi.ini.php というファイルを作成し、値を定義すれば、
 * このファイルを修正する必要はありません。値が上書きされます。
 * == 設定例 ==
 * <?php define('PLUGIN_AUTH_MIXI_USE_AGREEMENT', 1); ?>
 *
 */

// mixi ガイドラインに遵守できる場合のみ設置できます
defined('PLUGIN_AUTH_MIXI_USE_AGREEMENT') or define('PLUGIN_AUTH_MIXI_USE_AGREEMENT', 0); // 0(Not Agreement) or 1(Agreement)
// 省略時のＩＤを定義できます
defined('PLUGIN_AUTH_MIXI_MY_ID') or define('PLUGIN_AUTH_MIXI_MY_ID', '');
// auth_api で定義するか、:config ページで定義するかを選択できます
// :config ページで運用する場合には、セキュリティレベル(roleの設定や凍結など)に注意して下さい。
defined('PLUGIN_AUTH_MIXI_USE_CONFIG') or define('PLUGIN_AUTH_MIXI_USE_CONFIG', 0);
// 構成定義ファイル
define('CONFIG_AUTH_OPENID_MIXI','auth/openid/mixi');

function plugin_auth_mixi_init()
{
	$msg = array(
	  '_auth_mixi_msg' => array(
					// このプラグインを利用するためには、mixi が定めるガイドラインに同意する必要があります。
		'msg_agreement'		=> _("It is necessary to agree to the guideline that mixi provides to use this plugin."),
		'msg_invalid'		=> _("The function of opeind is invalid."),
	  )
	);
	set_plugin_messages($msg);
}

function plugin_auth_mixi_convert()
{
	global $auth_api, $_auth_mixi_msg;

	if (! isset($auth_api['openid']['use'])) return '';
	if (! $auth_api['openid']['use']) return $_auth_mixi_msg['msg_invalid'];

	$argv = func_get_args();
	return '<div>'.call_user_func_array('plugin_auth_mixi_inline', $argv).'</div>';
}

function plugin_auth_mixi_inline()
{
	global $vars, $auth_api, $_auth_mixi_msg;

	if (! isset($auth_api['openid']['use'])) return '';
	if (! $auth_api['openid']['use']) return $_auth_mixi_msg['msg_invalid'];
	if (! PLUGIN_AUTH_MIXI_USE_AGREEMENT) return $_auth_mixi_msg['msg_agreement'];

	$argv = func_get_args();
	$parm = auth_mixi_set_parm($argv);

	exist_plugin('openid');
	list($idp,$icon_botton,$icon_mini) = auth_mixi_set_loginuri($parm['type'],$parm['icon'],$parm['id']);

	// 認証済みの扱い
	$icon = '<img src="'.IMAGE_URI.'plus/openid/mixi/icon.gif"'.
		' width="16" height="16" alt="mixi" title="mixi" />';
	$msg = plugin_openid_logoff_msg('auth_mixi',$icon);
	if ($msg === false) return ''; // 他認証
	if (!empty($msg)) return $msg;

	// 未認証時の扱い
	$page = (empty($vars['page'])) ? '' : $vars['page'];
	if ($parm['type'] == 'friends' && empty($parm['id'])) {
		$redirect_url = get_cmd_uri('auth_mixi',$page);
	} else {
		$redirect_url = get_cmd_uri('openid',$page,'',array('action'=>'verify','openid_url'=>$idp,'author'=>'auth_mixi'));
	}
	return '<a href="'.$redirect_url.'">'.$icon_botton.'</a>';
}

function plugin_auth_mixi_action()
{
	global $vars, $auth_api, $_auth_mixi_msg;

	if (! isset($auth_api['openid']['use'])) return '';
	if (! $auth_api['openid']['use']) die( $_auth_mixi_msg['msg_invalid'] );

	// マイミク認証のみ許可
	list($openid_url, $icon_img) = auth_mixi_set_loginuri('friends','',PLUGIN_AUTH_MIXI_MY_ID);
	exist_plugin('openid');
	$vars['action'] = 'verify';
	$vars['openid_url'] = $openid_url;
	$vars['author'] = 'auth_mixi';
	return do_plugin_action('openid');
}

function auth_mixi_set_parm($argv)
{
	$parm = array();
        $parm['type'] = $parm['id'] = '';
	$parm['icon'] = 1;
	foreach($argv as $arg) {
		$val = split('=', $arg);
		if (empty($val[0])) continue;
		$val[1] = (empty($val[1])) ? htmlspecialchars($val[0]) : htmlspecialchars($val[1]);

		switch($val[0]) {
		case 'icon':
		case 'type':
		case 'id':
			$parm[$val[0]] = $val[1];
			break;
		case 'f':
			$parm['type'] = 'friends';
			break;
		case 'c':
			$parm['type'] = 'community';
			break;
		default:
			$parm['type'] = $val[1];
		}
	}
	return $parm;
}

function auth_mixi_set_loginuri($type='',$icon=1,$id='')
{
	static $icon_type = array(
		0 => array('icon.gif' , 16,16), // mini
		1 => array('a_130.gif',130,30), // Type A
		2 => array('a_150.gif',150,30),
		3 => array('b_130.gif',130,28), // Type B
		4 => array('b_150.gif',150,28),
	);

	$die_message = (PLUS_PROTECT_MODE) ? 'die_msg' : 'die_message';

	switch($type) {
	case 'friends':
	// マイミク認証
		if (empty($id)) $id = PLUGIN_AUTH_MIXI_MY_ID;
		if (empty($id)) $die_message('マイミク認証時は、IDの設定が必須です。');
		$idp = 'https://id.mixi.jp/'.$id.'/friends';
		break;
	case 'community':
	// コミュニティ認証
		if (empty($id)) $die_message('コミュニティ認証時は、コミュニティIDの設定が必須です。');
		$idp = 'https://id.mixi.jp/community/'.$id;
		break;
	default:
	// ミクシィ認証
		$idp = 'https://mixi.jp';
	}

	$icon_idx = array($icon, 0);
        $icon_img = array();
        $i = 0;
        foreach($icon_idx as $idx) {
		$icon_file = & $icon_type[$idx];
                $icon_img[$i] = '<img src="'.IMAGE_URI.'plus/openid/mixi/'.$icon_file[0].'"'.
                ' width="'.$icon_file[1].'" height="'.$icon_file[2].'"'.
                ' alt="mixi" title="mixi" />';
                $i++;
        }
	return array($idp, $icon_img[0], $icon_img[1]);
}

// openid_identity      https://id.mixi.jp/[ログインしたユーザーのID]
// openid_url           https://id.mixi.jp/[ユーザーID]/friends/[ログインしたユーザーのID]
// openid_url           https://id.mixi.jp/community/[コミュニティーのID]/[ログインしたユーザーのID]
function auth_mixi_get_info($openid)
{
        // is mixi
	$chk_str = 'https://id.mixi.jp/';
        $chk = strpos($openid, $chk_str);
        if ($chk === false) return array(0,'','');
        if ($chk > 0) return array(0,'','');
        $openid = substr($openid,strlen($chk_str));

        // auth my mixi
        $id  = explode('/',$openid);
        $ctr = count($id);
        switch (count($id)) {
        case 3:
                // auth mymixi
                if ($id[1] === 'friends') {
                        // 0 : auth friends (2)
                        // 1 : myid
                        // 2 : login id
                        return array(2,$id[0],$id[2]);
                } else
                // auth community
                if ($id[0] === 'community') {
                        // 0 : auth community (3)
                        // 1 : community id
                        // 2 : user id
                        return array(3,$id[1],$id[2]);
                }
                return array(0,'','');
        case 1:
                return array(1,$openid,$openid);
        }
        return array(0,'','');
}

function auth_mixi_get_user_name($msg,$prof,$key)
{
	$rc = array('role'=>ROLE_AUTH_OPENID,'nick'=>$msg['nickname'],'profile'=>$prof,'key'=>$key);
	$info = auth_mixi_get_info($msg['identity_url']);
	/*
	 * |field|friends |  community |rem           |h
	 * |  0  |   2    |       3    |flag fields   |
	 * |  1  |  myid  |community id|checked fields|
	 * |  2  |login id|  user id   |user's id     |
	*/
	if ($info[0] < 2) return $rc;
	$func_name  = 'auth_mixi_get_role_';
	$func_name .= (PLUGIN_AUTH_MIXI_USE_CONFIG) ? 'config' : 'auth_api';
	$rc['role'] = call_user_func($func_name,$info[0],$info[1]);
	return $rc;
}

// $type -> $info[0]
// $id   -> $info[1]
function auth_mixi_get_role_config($type,$id)
{
	static $config_mixi = array();

	// PLUGIN_AUTH_MIXI_MY_ID
	// mixi
	if (!isset($config_mixi[$type])) {
		$config = new Config(CONFIG_AUTH_OPENID_MIXI);
		$config->read();
		switch ($type) {
		case 2:
			$config_mixi[2] = $config->get('friends');
			$config_mixi[2][][0] = PLUGIN_AUTH_MIXI_MY_ID;
			break;
		case 3:
			$config_mixi[3] = $config->get('community');
			break;
		default: return ROLE_AUTH_OPENID;
		}
		unset($config);
	}

	// 登録されたIDでの認証に限って、登録者に昇格させる
	foreach($config_mixi[$type] as $x) {
		if ($x[0] === $id) return ROLE_ENROLLEE;
	}
	return ROLE_AUTH_OPENID;
}

function auth_mixi_get_role_auth_api($type,$id)
{
	global $auth_api;
	static $config_mixi = array();
	static $get_label = array(2=>'my_id',3=>'community_id');

	if (! isset($auth_api['openid']['mixi'])) return ROLE_AUTH_OPENID;

	$auth_api_mixi = & $auth_api['openid']['mixi'];

	if (!isset($config_mixi[$type])) {
		$config_mixi[$type] = (isset($auth_api_mixi[$get_label[$type]])) ? $auth_api_mixi[$get_label[$type]] : array();
		if (!is_array($config_mixi[$type])) $config_mixi[$type] = explode(',',$config_mixi[$type]);
		if ($type === 2) $config_mixi[2][] = PLUGIN_AUTH_MIXI_MY_ID;
	}

	foreach($config_mixi[$type] as $x) {
		if ($x === $id) return ROLE_ENROLLEE;
	}
	return ROLE_AUTH_OPENID;
}

?>
