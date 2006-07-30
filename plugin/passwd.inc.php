<?php
/**
 * passwd plugin.
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: passwd.inc.php,v 0.1 2006/07/29 05:12:00 upk Exp $
 *
 * $A1 = md5($data['username'] . ':' . $realm . ':' . $auth_users[$data['username']]);
 */

if (!defined('USE_PKWK_WRITE_FUNC')) {
	define('USE_PKWK_WRITE_FUNC', FALSE);
}

require(LIB_DIR . 'auth_file.cls.php');

function plugin_passwd_init()
{
	$msg = array(
	  '_passwd_msg' => array(
		'UserName'	=> _("UserName"),
		'Passwd'	=> _("Passwd"),
		'Calculate'	=> _("Calculate"),
		'AuthType'	=> _("Auth Type"),
		'CALC'		=> _("CALC"),
		'Update'	=> _("Update"),
		'Result'	=> _("Result"),
				// 書き込み機能は、制限されています。
		'err_not_use'	=> _("The writing function is limited."),
				// 更新するためには、サイト管理者以上の権限が必要です。
		'err_role'	=> _("The authority more than Webmaster for World Wide Web Site is necessary to update it."),
				// ログインした後に passwd を使用してください。
		'err_not_login' => _("Please use passwd after it login."),
				// パスワードが同一なため、更新しませんでした。
		'msg_not_update'=> _("Because the password was the same, it did not update it."),
				// 認証管理ファイルを更新しました。
		'msg_update'	=> _("The Authentication management file was updated."),
				// ログインしなおさなければなりません。さもないと、役割が初期化されてしまいます。
		'msg_relogin'	=> _("<b>You must be login again.</b> Otherwise, the role is initialized."),
				// １件追加しました。
		'msg_add'	=> _("One was added."),
		'msg_err'	=> _("ERROR."),
		// role.inc.php
		'role'		=> _('Role'),
		'role_0'	=> _('Guest'),
		'role_2'	=> _('Webmaster'),
		'role_3'	=> _('Contents manager'),
		'role_4'	=> _('Authorized'),
	  ),
	);
        set_plugin_messages($msg);
}

function plugin_passwd_action()
{
	global $vars, $_passwd_msg;

	$msg = 'passwd';
	$body = '';
	$func = (empty($vars['func'])) ? '' : $vars['func'];

	$user = auth::check_auth();

	if (empty($user)) {
		// 未認証者は利用できない。
		return array('msg'=>$msg,'body'=>$_passwd_msg['err_not_login']);
	}

	// 初回起動時
	if (empty($func)) {
		return array('msg'=>$msg,'body'=>passwd_menu());
	}

	// プラグインによる書き込み制限の場合
	if (! USE_PKWK_WRITE_FUNC) {
		return array('msg'=>$msg,'body'=>passwd_menu($_passwd_msg['err_not_use']));
	}

	switch ($func) {
	case 'save':
        	// サイト管理者権限が無い場合
		if (auth::check_role('role_adm')) {
			return array('msg'=>$msg,'body'=>passwd_menu($_passwd_msg['err_role']));
		}

		// 0:変更なし, 1:追加, 2:変更あり
		$rc_save = passwd_auth_file_save($vars['username'],$vars['algorithm'],$vars['hash'],$vars['role']);
		switch ($rc_save) {
		case 1:
			$msg_save = $_passwd_msg['msg_add'];
			break;
		case 2:
			$msg_save = $_passwd_msg['msg_update'];

			// ログインユーザの場合は、注意を促す
			if ($vars['username'] == $user) {
				$msg_save .= $_passwd_msg['msg_relogin'];
				return array('msg'=>$msg,'body'=>$msg_save);
			}
			break;
		case 3:
			$msg_save = $_passwd_msg['msg_update'];
			break;
		default:
			$msg_save = $_passwd_msg['msg_not_update'];
		}

		return array('msg'=>$msg,'body'=>passwd_menu( $msg_save ));

	case 'update':
		// サイト管理者未満は、自分のパスワードのみ更新ができる
		$role_level = auth::get_role_level();
		if ($role_level < 2) {
			// 未認証者
			return array('msg'=>$msg,'body'=>passwd_menu($_passwd_msg['err_role']));
		}
		// 役割 - 3:コンテンツ管理者 3.1:見做し管理者 4:認証者 4.1:見做し認証者
		// 見做しの場合は、認証者(4)とする。
		if ($role_level != 3) {
			$role_level = '';
		}

		// 0:変更なし, 1:追加, 2:パスワードの変更あり 3:変更あり
		$rc_save = passwd_auth_file_save($user,$vars['algorithm'],$vars['hash'],$role_level);

		switch ($rc_save) {
		case 1:
			$msg_save = $_passwd_msg['msg_add'];
			break;
		case 2:
			// ログインユーザの場合は、注意を促す
			return array('msg'=>$msg,'body'=>$_passwd_msg['msg_update'].$_passwd_msg['msg_relogin']);
		case 3:
			$msg_save = $_passwd_msg['msg_update'];
			break;
		default:
			$msg_save = $_passwd_msg['msg_not_update'];
		}
		return array('msg'=>$msg,'body'=>passwd_menu( $msg_save ));
	default:
		$body = $_passwd_msg['msg_err'];
	}

	return array('msg'=>$msg,'body'=>$body);
}

function passwd_menu($msg='&nbsp;')
{
	global $script, $head_tags, $_passwd_msg, $auth_type;

	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_DIR.'crypt/md5.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_DIR.'crypt/sha1.js"></script>';

	$x = passwd_menu_js();

	// プラグインによる書き込み制限の場合
	// 使用する場合は、変更させることもコピーさせることも不要なので、抑止する
	// 更新ボタンすら表示しない
	if (USE_PKWK_WRITE_FUNC) {
		$disabled_result = 'disabled="disabled"';
		$msg_submit = <<<EOD
    <tr>
      <td><input type="submit" name="submit" value="{$_passwd_msg['Update']}" disabled="disabled" /></td>
    </tr>

EOD;
		$msg_role = <<<EOD
    <tr>
      <th>{$_passwd_msg['role']}</th>
      <td>
        <select name="role">
          <option value="">{$_passwd_msg['role_4']}</option>
          <option value="3">{$_passwd_msg['role_3']}</option>
          <option value="2">{$_passwd_msg['role_2']}</option>
        </select>
      </td>
    </tr>

EOD;
	} else {
		$disabled_result = '';
		$msg_submit = '';
		$msg_role = '';
	}

	$func = 'save';

	// 役割
	$role_level = auth::get_role_level();
	if ($role_level == 2) {
		// 管理者
		$disabled_user = $user = '';
	} else {
		// 一般ユーザ
		$disabled_user = 'disabled="disabled"';
		$user = auth::check_auth();
		$func = 'update';
		$msg_role = <<<EOD
    <tr>
      <th>{$_passwd_msg['role']}</th>
      <td>

EOD;
		$msg_role .= passwd_get_role_name($role_level).' ('.$role_level.')';
		$msg_role .= <<<EOD
      </td>
    </tr>

EOD;
	}

	$msg_username = <<<EOD
    <tr>
      <th>{$_passwd_msg['UserName']}</th>
      <td><input type="text" name="username" size="10" value="$user" $disabled_user /></td>
    </tr>

EOD;

	// 認証方式により、アルゴリズム選択を制御する
	switch ($auth_type) {
	case 1:
		// basic
		$disabled_sha1 = '';
		// 書き込み禁止ならユーザ名は不要
		if (! USE_PKWK_WRITE_FUNC) {
			$msg_username = '';
		}
		break;
	case 2:
	default:
		// digest
		$disabled_sha1 = 'disabled="disabled"';
	}

$x .= <<<EOD
<h2>passwd</h2>

<div>$msg</div>

<form name="passwd" action="$script" method="post">
  <input type="hidden" name="plugin" value="passwd" />
  <input type="hidden" name="func" value="$func" />
  <input type="hidden" name="algorithm" />
  <input type="hidden" name="hash" />
  <table class="indented">
$msg_username
    <tr>
      <th>{$_passwd_msg['Passwd']}</th>
      <td><input type="password" name="passwd" size="10" /></td>
    </tr>
$msg_role
    <tr>
      <th>{$_passwd_msg['Calculate']}</th>
      <td>
        <input type="radio" name="scheme" value="MD5" checked="checked" /> <label>MD5</label>
        <input type="radio" name="scheme" value="SHA-1" $disabled_sha1 /> <label>SHA-1</label>
        &nbsp;
        <input type="button" onclick="set_hash()" value="{$_passwd_msg['CALC']}" />
      </td>
    </tr>
    <tr>
      <th>{$_passwd_msg['Result']}</th>
      <td><input type="text" name="hash_view" size="80" $disabled_result /></td>
    </tr>
$msg_submit
  </table>
</form>

EOD;

	return $x;

}

function passwd_menu_js()
{
	global $auth_type, $realm;

	switch ($auth_type) {
	case 1:
		// basic
		$pref = 'php';
		$submit_sha1 = "objForm.submit.disabled = false;\n";
		$a1 = "a1 = objForm.passwd.value;\n";
		break;
	case 2:
	default:
		// digest
		$pref = 'digest';
		$submit_sha1 = '';
		$a1 = 'a1 = objForm.username.value+\':' . $realm . ":'+objForm.passwd.value;\n";
	}

	if (USE_PKWK_WRITE_FUNC) {
		// $submit_sha1
		$submit_false = "objForm.submit.disabled = false;\n";
		$submit_true = "objForm.submit.disabled = true;\n";
	} else {
		$submit_sha1 = $submit_false = $submit_true = '';
	}

$x = <<<EOD
<script type="text/javascript">
<!-- <![CDATA[

function set_hash()
{
 var a1,ctr,pref;
 var fn = function(){
   switch(objForm.algorithm.value) {
   case 'SHA-1':
     $submit_sha1
     objForm.hash.value = hex_sha1(a1);
     pref = "{x-$pref-sha1}";
     break;
   default:
     $submit_false
     objForm.hash.value = hex_md5(a1);
     pref = "{x-$pref-md5}";
   }
 };

 var objForm = eval("document.passwd");
 $submit_true

 if (objForm.passwd.value == "") {
   objForm.hash.value = "";
   objForm.algorithm.value = "";
 } else {

   ctr = objForm.scheme.length;
   for (i=0; i<ctr; i++) {
     if (objForm.scheme[i].checked) {
       objForm.algorithm.value = objForm.scheme[i].value;
       break;
     }
   }

   $a1
   fn();
   objForm.passwd.value = "";
 }

 if (objForm.hash.value == "") {
   objForm.hash_view.value = "";
 } else {
   objForm.hash_view.value = pref+objForm.hash.value;
 }

}

//]]>-->
</script>

EOD;

	return $x;
}

function passwd_auth_file_save($username,$algorithm,$passwd,$role)
{
	global $auth_type;

	$obj = new auth_file(PKWK_AUTH_FILE);

	switch ($auth_type) {
	case 1:
		// basic
		$type = 'php';
		break;
	case 2:
	default:
		// digest
		$type = 'digest';
	}

	$scheme = '{x-'.$type;
	switch ($algorithm) {
	case 'SHA-1':
		$scheme .= '-sha1}';
		break;
	case 'MD5':
	default:
		$scheme .= '-md5}';
	}

	// 0:変更なし, 1:追加, 2:変更あり
	$rc = $obj->set_passwd($username, $scheme.$passwd, $role);
	if ($rc == 0) return $rc;

	$obj->write_auth_file();

	// 更新結果の再読込
	global $auth_users;
	$auth_users = passwd_get_auth_file();
	return $rc;
}

function passwd_get_role_name($role_level)
{
	global $_passwd_msg;

	$level = (int)$role_level;

	switch ($level) {
	case 2:
	case 3:
	case 4:
		return $_passwd_msg['role_'.$level];
	}
	return $_passwd_msg['role_0'];
}

function passwd_get_auth_file()
{
	if (file_exists(PKWK_AUTH_FILE)) {
		include(PKWK_AUTH_FILE);
	} else {
		$auth_users = array();
	}
	return $auth_users;
}

?>
