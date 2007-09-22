<?php
/**
 * passwd plugin.
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: passwd.inc.php,v 0.8 2007/09/23 04:42:00 upk Exp $
 *
 * $A1 = md5($data['username'] . ':' . $realm . ':' . $auth_users[$data['username']]);
 */

if (!defined('USE_PKWK_WRITE_FUNC')) {
	define('USE_PKWK_WRITE_FUNC', FALSE);
}

require_once(LIB_DIR . 'auth_file.cls.php');
require_once(LIB_DIR . 'des.php');

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
		'Crypt'		=> _("Encryption key"),
		'msg_pass_admin' => _("Please input Administrator password."),
		'msg_pass_old'   => _("Please input the password being used now."),
		'msg_pass_new'   => _("Please input a new password."),
		'msg_pass_none'  => _("Please input a suitable character string."),
		'err_not_use'	=> _("The writing function is limited."),
		'err_role'	=> _("The authority more than Webmaster for World Wide Web Site is necessary to update it."),
		'err_key'	=> _("The encryption key is not corresponding."),
		'msg_not_update'=> _("Because the password was the same, it did not update it."),
		'msg_update'	=> _("The Authentication management file was updated."),
		'msg_relogin'	=> _("<b>You must be login again.</b> Otherwise, the role is initialized."),
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

		$hash = passwd_undes(2,$vars['username'],$vars['hash']);
		if ($hash === false) {
			return array('msg'=>$msg,'body'=>passwd_menu($_passwd_msg['err_key']));
		}
		// 0:変更なし, 1:追加, 2:変更あり
		$rc_save = passwd_auth_file_save($vars['username'],$vars['algorithm'],$hash,$vars['role']);
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

		$hash = passwd_undes(4,$user,$vars['hash']);
		if ($hash === false) {
			return array('msg'=>$msg,'body'=>passwd_menu($_passwd_msg['err_key']));
		}

		// 0:変更なし, 1:追加, 2:パスワードの変更あり 3:変更あり
		$rc_save = passwd_auth_file_save($user,$vars['algorithm'],$hash,$role_level);

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
	global $script, $head_tags, $_passwd_msg, $auth_type, $realm, $vars;

	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/md5.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/sha1.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/des.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/base64.js"></script>';

	$func = 'save';
	$role_level = auth::get_role_level();
	$old_algorithm = '';

	$r_realm = rawurlencode($realm);

	$checked_md5 = 'checked="checked"';
	$checked_sha1 = '';

	// adminpass を求める処理の場合か？
	$is_adminpass = isset($vars['adminpass']);
	if ($is_adminpass) {
		$use_pkwk_write_func = false;
		$auth_type = 1;
	} else {
		$use_pkwk_write_func = USE_PKWK_WRITE_FUNC;
	}

	// 役割に応じた設定
	if ($role_level == 2) {
		// 管理者
		$disabled_user = $user = '';
		$msg_pass = $_passwd_msg['msg_pass_admin'];
		$a1_des = "a1 = objForm.key.value;\n";
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
		// 一般ユーザ
		$disabled_user = 'disabled="disabled"';
		// ゲスト時は、admin として一律生成できるようにしておく
		// $user = ($role_level == 0) ? 'admin' :  auth::check_auth();
		if ($role_level == 0) {
			$user = 'admin';
			$msg_pass = $_passwd_msg['msg_pass_none'];
			$a1_des = "a1 = objForm.key.value;\n";
		} else {
			$user = auth::check_auth();
			$msg_pass = $_passwd_msg['msg_pass_old'];
			$old_algorithm = passwd_get_scheme($user);

			switch ($old_algorithm) {
			case 'md5':
				$checked_md5 = 'checked="checked"';
				$checked_sha1 = '';
				break;
			case 'sha1':
				$checked_md5 = '';
				$checked_sha1 = 'checked="checked"';
				break;
			}

			// $a1_des = 'a1 = objForm.username.value+\':' . $realm . ":'+objForm.key.value;\n";
			$a1_des = 'a1 = objForm.username.value+\':\'+decodeURIComponent(objForm.realm.value)+\':\'+objForm.key.value;'."\n";
		}

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

	switch ($auth_type) {
	case 1:
		// basic
		$pref = 'php';
		$submit_sha1 = "objForm.submit.disabled = false;\n";
		$a1 = "a1 = objForm.passwd.value;\n";
		// basic の場合は上書きする
		$a1_des = "a1 = objForm.key.value;\n";
		$disabled_sha1 = '';
		// 書き込み禁止 または ゲスト時は、ユーザ名不要
		if (! $use_pkwk_write_func || $role_level == 0) {
			$msg_username = '';
		}
		break;
	case 2:
	default:
		// digest
		$pref = 'digest';
		$submit_sha1 = '';
		// $a1 = 'a1 = objForm.username.value+\':' . $realm . ":'+objForm.passwd.value;\n";
		$a1 = 'a1 = objForm.username.value+\':\'+decodeURIComponent(objForm.realm.value)+\':\'+objForm.passwd.value;'."\n";
		$checked_md5 = 'checked="checked"';
		$checked_sha1 = '';
		$disabled_sha1 = 'disabled="disabled"';
	}

	// プラグインによる書き込み制限の場合
	// 使用する場合は、変更させることもコピーさせることも不要なので、抑止する
	// 更新ボタンすら表示しない
	if (! $use_pkwk_write_func || $role_level == 0) {
		$submit_sha1 = $submit_false = $submit_true = '';
 		$disabled_result = $msg_submit = $msg_role = '';
	} else {
		// $submit_sha1
		$submit_false = "objForm.submit.disabled = false;\n";
		$submit_true = "objForm.submit.disabled = true;\n";

		$disabled_result = 'disabled="disabled"';
		$msg_submit = <<<EOD
    <tr>
      <td><input type="submit" name="submit" value="{$_passwd_msg['Update']}" disabled="disabled" /></td>
    </tr>

EOD;
	}

$x = <<<EOD
<script type="text/javascript">
<!-- <![CDATA[

function set_hash()
{
 var a1,ctr,pref,hash,des_key,hash_view,algorithm;
 var fn = function(){
   switch(algorithm) {
   case 'sha1':
     $submit_sha1
     hash = hex_sha1(a1);
     pref = "{x-$pref-sha1}";
     break;
   default:
     $submit_false
     hash = hex_md5(a1);
     pref = "{x-$pref-md5}";
   }
 };

 var objForm = eval("document.passwd");
 $submit_true

 if (objForm.passwd.value == "") {
   objForm.hash.value = "";
   objForm.algorithm.value = "";
   objForm.key.value = "";
 } else {

   ctr = objForm.scheme.length;
   for (i=0; i<ctr; i++) {
     if (objForm.scheme[i].checked) {
       objForm.algorithm.value = objForm.scheme[i].value;
       break;
     }
   }

   if (objForm.old_algorithm.value == "") {
     algorithm = objForm.algorithm.value;
   } else {
     algorithm = objForm.old_algorithm.value;
   }
   $a1_des
   fn();
   des_key = hash;

   algorithm = objForm.algorithm.value;
   $a1
   fn();
   hash_view = hash;

   objForm.hash.value = base64encode( des(des_key, hash, 1, 0) );
   objForm.passwd.value = "";
   objForm.key.value = "";
 }

 if (objForm.hash.value == "") {
   objForm.hash_view.value = "";
 } else {
   objForm.hash_view.value = pref+hash_view;
 }

}

//]]>-->
</script>

<h2>passwd</h2>

<div>$msg</div>

<form name="passwd" action="$script" method="post">
  <input type="hidden" name="plugin" value="passwd" />
  <input type="hidden" name="func" value="$func" />
  <input type="hidden" name="algorithm" />
  <input type="hidden" name="old_algorithm" value="$old_algorithm"/>
  <input type="hidden" name="hash" />
  <input type="hidden" name="realm" value="$r_realm"/>
  <table class="indented">
$msg_username
    <tr>
      <th>{$_passwd_msg['Passwd']}</th>
      <td><input type="password" name="passwd" size="10" />&nbsp;{$_passwd_msg['msg_pass_new']}</td>
    </tr>
    <tr>
     <th>{$_passwd_msg['Crypt']}</th>
     <td><input type="password" name="key" size="10" />&nbsp;{$msg_pass}</td>
    </tr>
$msg_role
    <tr>
      <th>{$_passwd_msg['Calculate']}</th>
      <td>
        <input type="radio" name="scheme" value="md5" $checked_md5 /> <label>MD5</label>
        <input type="radio" name="scheme" value="sha1" $checked_sha1 $disabled_sha1 /> <label>SHA-1</label>
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

// MD5 など既に設定しているものを変更すると、復号できないため
// どうにかしないといけない
function passwd_undes($role,$user,$hash)
{
	if ($role == 2) {
		// adminpass
		global $adminpass;
		list($scheme, $key) = auth::passwd_parse($adminpass);
	} else {
		$obj = new auth_file(PKWK_AUTH_FILE);
		list($o_scheme,$key,$o_role) = $obj->get_data($user);
	}

	$hash = des($key, base64_decode($hash), 0, 0, null);
	if (! preg_match('/^[a-z0-9]+$/iD', $hash)) {
		return false;
	}
	return $hash;
}

function passwd_get_scheme($user)
{
	$obj = new auth_file(PKWK_AUTH_FILE);
	list($scheme,$key,$role) = $obj->get_data($user);
	$x = explode('-',substr($scheme,1,-1));
	return $x[count($x)-1];
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
	case 'sha1':
		$scheme .= '-sha1}';
		break;
	case 'md5':
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
