<?php
/**
 * htdigest plugin.
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: htdigest.inc.php,v 0.9 2007/07/09 23:43:00 upk Exp $
 *
 * $A1 = md5($data['username'] . ':' . $realm . ':' . $auth_users[$data['username']]);
 */

if (!defined('USE_APACHE_WRITE_FUNC')) {
	define('USE_APACHE_WRITE_FUNC', FALSE);
}

if (!defined('HTDIGEST_FILE_PATH')) {
	define('HTDIGEST_FILE_PATH', '');
}
if (!defined('HTDIGEST_FILE_NAME')) {
	define('HTDIGEST_FILE_NAME', '.htdigest');
}
if (!defined('HTDIGEST_FILE')) {
	define('HTDIGEST_FILE', HTDIGEST_FILE_PATH.HTDIGEST_FILE_NAME);
}

require(LIB_DIR . 'auth_file.cls.php');
require(LIB_DIR . 'des.php');

function plugin_htdigest_init()
{
	$msg = array(
	  '_htdigest_msg' => array(
		'realm'		=> _("realm"),
		'UserName'	=> _("UserName"),
		'Passwd'	=> _("Passwd"),
		'Calculate'	=> _("Calculate"),
		'CALC'		=> _("CALC"),
		'Update'	=> _("Update"),
		'Result'	=> _("Result"),
		'Crypt'		=> _("Encryption key"),
		'msg_pass_admin' => _("Please input Administrator password."),
		'msg_pass_old'   => _("Please input the password being used now."),
		'msg_pass_new'   => _("Please input a new password."),
		'msg_iis'	=> _("It doesn't correspond to IIS of Microsoft Corporation."),
		'err_not_use'	=> _("The writing function is limited."),
		'err_role'	=> _("The authority more than Webmaster for World Wide Web Site is necessary to update it."),
		'err_key'	=> _("The encryption key is not corresponding."),
		'err_md5'	=> _("In this version, the Administrator password is supported only with {x-php-md5}."),
		'msg_realm'	=> _("Realm is not corresponding."),
		'msg_1st'	=> _("It newly made .htdigest."),
		'msg_not_update'=> _("Because the password was the same, it did not update it."),
		'msg_update'	=> _("It updated .htdigest."),
		'msg_add'	=> _("One was added."),
		'msg_err'	=> _("ERROR."),
	  )
	);
        set_plugin_messages($msg);
}

function plugin_htdigest_action()
{
	global $vars, $_htdigest_msg;

	$msg = 'htdigest';
	$body = '';
	$func = (empty($vars['func'])) ? '' : $vars['func'];

	if (htdigest_is_iis()) {
		return array('msg'=>$msg,'body'=>$_htdigest_msg['msg_iis']);
	}

	// 初回起動時
	if (empty($func)) {
		return array('msg'=>$msg,'body'=>htdigest_menu());
	}

	// プラグインによる書き込み制限の場合
	if (! USE_APACHE_WRITE_FUNC) {
		return array('msg'=>$msg,'body'=>htdigest_menu($_htdigest_msg['err_not_use']));
	}

	switch ($func) {
	case 'save':
        	// サイト管理者権限が無い場合
		if (auth::check_role('role_adm')) {
			return array('msg'=>$msg,'body'=>htdigest_menu($_htdigest_msg['err_role']));
		}
		// ADM
		if (USE_APACHE_WRITE_FUNC) {
			$rc_msg = htdigest_save($vars['username'], $vars['realm'], $vars['hash'], 2);
		}
		return array('msg'=>$msg,'body'=>htdigest_menu($rc_msg));

	case 'update':
		// サイト管理者未満は、自分のパスワードのみ更新ができる
		$role_level = auth::get_role_level();
		if ($role_level < 2) {
			// Guest
			return array('msg'=>$msg,'body'=>htdigest_menu($_htdigest_msg['err_role']));
		}
		// Auth User
		global $realm;
		$user = auth::check_auth();
		if (USE_APACHE_WRITE_FUNC) {
			$rc_msg = htdigest_save($user, $realm, $vars['hash'], $role_level);
		}
		return array('msg'=>$msg,'body'=>htdigest_menu($rc_msg));

	default:
		$body = $_htdigest_msg['msg_err'];
	}

	return array('msg'=>$msg,'body'=>$body);
}

function htdigest_is_iis()
{
	$srv_soft = (defined('SERVER_SOFTWARE')) ? SERVER_SOFTWARE : $_SERVER['SERVER_SOFTWARE'];
	$srv_soft = strtolower(substr($srv_soft,0,9));
	return ($srv_soft == 'microsoft') ? TRUE : FALSE;
}

function htdigest_menu($msg='&nbsp;')
{
	global $script, $realm, $head_tags, $_htdigest_msg;

	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/md4.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/md5.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/sha1.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/des.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'crypt/base64.js"></script>';

	// 使用する場合は、変更させることもコピーさせることも不要なので、抑止する
	$disabled = (USE_APACHE_WRITE_FUNC) ? 'disabled="disabled"' : '';

	$func = 'save';

	$role_level = auth::get_role_level();
	if ($role_level > 2) {
		$user_disabled = 'disabled="disabled"';
		$user = auth::check_auth();
		$func = 'update';
		$msg_pass = $_htdigest_msg['msg_pass_old'];
	} else {
		$user_disabled = $user = '';
		$msg_pass = ($role_level == 2) ? $_htdigest_msg['msg_pass_admin'] : '';
	}

$x = <<<EOD
<script type="text/javascript">
<!-- <![CDATA[

function set_hash()
{
 var a1,ctr,pref,hash,des_key;
 var fn = function(){
   switch(objForm.algorithm.value) {
   case 'MD4':
     hash = hex_md4(a1);
     break;
   case 'SHA-1':
     hash = hex_sha1(a1);
     break;
   default:
     objForm.submit.disabled = false;
     hash = hex_md5(a1);
   }
 };

 var objForm = eval("document.htdigest");
 objForm.submit.disabled = true;

 if (objForm.passwd.value == "" || objForm.key.value == "") {
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
EOD;

	if ($role_level > 2) {
		// a1
		$x .= "a1 = objForm.username.value+':'+objForm.realm.value+':'+objForm.key.value;\n";
	} else {
		// adminpass
		$x .= "a1 = objForm.key.value;\n";
	}

$x .= <<<EOD
   fn();
   des_key = hash;

   a1 = objForm.username.value+':'+objForm.realm.value+':'+objForm.passwd.value;
   fn();

   objForm.hash.value = base64encode( des(des_key, hash, 1, 0) );
   objForm.passwd.value = "";
 }

 if (objForm.hash.value == "") {
   objForm.hash_view.value = "";
 } else {
   objForm.hash_view.value = objForm.username.value+':'+objForm.realm.value+':'+hash;
 }
}

//]]>-->
</script>

<h2>htdigest</h2>

<div>$msg</div>

<form name="htdigest" action="$script" method="post">
  <input type="hidden" name="plugin" value="htdigest" />
  <input type="hidden" name="func" value="$func" />
  <input type="hidden" name="algorithm" />
  <input type="hidden" name="hash" />
  <table class="indented">
    <tr>
      <th>{$_htdigest_msg['realm']}</th>
      <td><input type="text" name="realm" size="30" value="$realm" /></td>
    </tr>
    <tr>
      <th>{$_htdigest_msg['UserName']}</th>
      <td><input type="text" name="username" size="10" value="$user" $user_disabled /></td>
    </tr>
    <tr>
      <th>{$_htdigest_msg['Passwd']}</th>
      <td><input type="password" name="passwd" size="10" />&nbsp;{$_htdigest_msg['msg_pass_new']}</td>
    </tr>

    <tr>
     <th>{$_htdigest_msg['Crypt']}</th>
     <td><input type="password" name="key" size="10" />&nbsp;{$msg_pass}</td>
    </tr>

    <tr>
      <th>{$_htdigest_msg['Calculate']}</th>
      <td>
        <input type="radio" name="scheme" value="MD5" checked="checked" /> <label>MD5</label>
        <input type="radio" name="scheme" value="SHA-1" /> <label>SHA-1</label>
        <input type="radio" name="scheme" value="MD4" /> <label>MD4</label>
        &nbsp;
        <input type="button" onclick="set_hash()" value="{$_htdigest_msg['CALC']}" />
      </td>
    </tr>
    <tr>
      <th>{$_htdigest_msg['Result']}</th>
      <td><input type="text" name="hash_view" size="80" $disabled /></td>
    </tr>
    <tr>
      <td><input type="submit" name="submit" value="{$_htdigest_msg['Update']}" disabled="disabled" /></td>
    </tr>

  </table>
</form>
EOD;

	return $x;

}

function htdigest_get_hash($username,$p_realm='')
{
	global $realm;

	if (! file_exists(HTDIGEST_FILE)) return '';
	if (empty($p_realm)) $p_realm = $realm;

	if (!($fd = fopen(HTDIGEST_FILE,'r'))) return '';

	while ($data = @fgets($fd, 4096)) {
		$field = split(':', trim($data));
		if ($field[0] == $username && $field[1] == $p_realm) {
			fclose($fd);
			return $field[2];
		}
	}
	fclose($fd);
	return '';
}

function htdigest_save($username,$p_realm,$hash,$role)
{
	global $realm, $_htdigest_msg;

	if ($realm != $p_realm)
		return $_htdigest_msg['msg_realm'];

	// DES
	if ($role > 2) {
		$key = htdigest_get_hash($username,$p_realm);
	} else {
		// adminpass
		global $adminpass;
		list($scheme, $key) = auth::passwd_parse($adminpass);
		// FIXME: MD5 ONLY
		if ($scheme != '{x-php-md5}') {
			return $_htdigest_msg['err_md5'];
		}
	}
	$hash = des($key, base64_decode($hash), 0, 0, null);
	if (! preg_match('/^[a-z0-9]+$/iD', $hash)) {
		return $_htdigest_msg['err_key'];
	}

	// SAVE
	if (file_exists(HTDIGEST_FILE)) {
		$lines = file(HTDIGEST_FILE);
	} else {
		$fp = fopen(HTDIGEST_FILE,'w');
		@flock($fp, LOCK_EX);
		fputs($fp, $username.':'.$realm.':'.$hash."\n");
		@flock($fp, LOCK_UN);
		@fclose($fp);
		return $_htdigest_msg['msg_1st'];
	}

	$sw = FALSE;
	foreach($lines as $no=>$line) {
		$field = split(':', trim($line));
		if ($field[0] == $username && $field[1] == $p_realm) {
			if ($field[2] == $hash) {
				return $_htdigest_msg['msg_not_update'];
			}

			$sw = TRUE;
			$lines[$no] = $field[0].':'.$field[1].':'.$hash."\n";
			break;
		}
	}

	if (! $sw) {
		$fp = fopen(HTDIGEST_FILE,'a');
		@flock($fp, LOCK_EX);
		fputs($fp, $username.':'.$p_realm.':'.$hash."\n");
		@flock($fp, LOCK_UN);
		@fclose($fp);
		return $_htdigest_msg['msg_add'];
	}

	$fp = fopen(HTDIGEST_FILE,'w');
	@flock($fp, LOCK_EX);
	foreach($lines as $line) {
		fwrite($fp, $line);
	}
	@flock($fp, LOCK_UN);
	@fclose($fp);
	return $_htdigest_msg['msg_update'];
}

?>
