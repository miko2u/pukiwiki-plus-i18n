<?php
/**
 * htdigest plugin.
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: googlemap.inc.php,v 0.2 2006/07/23 05:26:00 upk Exp $
 *
 * $A1 = md5($data['username'] . ':' . $realm . ':' . $auth_users[$data['username']]);
 */

if (!defined('USE_HTDIGEST')) {
	define('USE_HTDIGEST', FALSE);
}

if (!defined('HTDIGEST_FILE')) {
	define('HTDIGEST_FILE', '.htdigest');
}

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
				// マイクロソフト社のIISには、対応しておりません。
		'msg_iis'	=> _("It doesn't correspond to IIS of Microsoft Corporation."),
				// .htaccess を利用した更新は、許可されません。
		'err_not_use'	=> _("The update using .htaccess is not permitted."),
				// 更新するためには、サイト管理者以上の権限が必要です。
		'err_role'	=> _("The authority more than Webmaster for World Wide Web Site is necessary to update it."),
		'msg_realm'	=> _("Realm is not corresponding."),
				// .htdigest を新規作成しました。
		'msg_1st'	=> _("It newly made .htdigest."),
				// パスワードが同一なため、更新しませんでした。
		'msg_not_update'=> _("Because the password was the same, it did not update it."),
				// .htdigest を更新しました。
		'msg_update'	=> _("It updated .htdigest."),
				// １件追加しました。
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

	if (empty($func)) {
		return array('msg'=>$msg,'body'=>htdigest_menu());
	}

	if (! USE_HTDIGEST) {
		return array('msg'=>$_htdigest_msg['err_not_use'],'body'=>htdigest_menu());
	}

	if (auth::check_role('role_adm')) {
		return array('msg'=>$_htdigest_msg['err_role'],'body'=>htdigest_menu());
	}

	switch ($func) {
	case 'save':
		// $algorithm = $vars['algorithm'];
		$msg = htdigest_save($vars['username'], $vars['realm'], $vars['hash']);
		return array('msg'=>$msg,'body'=>htdigest_menu());

		break;
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

function htdigest_is_ie()
{
	global $log_ua;
	$obj = new ua_browsers();
	return ($obj->set_browsers_icon($log_ua) == 'msie') ? TRUE : FALSE;
}

function htdigest_menu()
{
	global $script, $realm, $head_tags, $_htdigest_msg;

	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_DIR.'crypt/md4.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_DIR.'crypt/md5.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_DIR.'crypt/sha1.js"></script>';

$x = <<<EOD
<script type="text/javascript">
<!-- <![CDATA[

function set_hash()
{
 var a1,ctr;
 var fn = function(){
   switch(objForm.algorithm.value) {
   case 'MD4':
     objForm.hash.value = hex_md4(a1);
     break;
   case 'SHA-1':
     objForm.hash.value = hex_sha1(a1);
     break;
   default:
     objForm.submit.disabled = false;
     objForm.hash.value = hex_md5(a1);
   }
 };

 var objForm = eval("document.htdigest");
 objForm.submit.disabled = true;

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
   a1 = objForm.username.value+':'+objForm.realm.value+':'+objForm.passwd.value;
   fn();
   objForm.passwd.value = "";
 }

 objForm.hash_view.value = objForm.hash.value;
 objForm.algorithm_view.value = objForm.algorithm.value;

 /* Windows ClipBord Copy */
 /* window.clipboardData.setData('text', objForm.hash.value); */

}


//]]>-->
</script>

<h2>htdigest</h2>

<form name="htdigest" action="$script" method="post">
  <input type="hidden" name="plugin" value="htdigest" />
  <input type="hidden" name="func" value="save" />
  <input type="hidden" name="algorithm" />
  <input type="hidden" name="hash" />
  <table class="indented">
    <tr>
      <th>{$_htdigest_msg['realm']}</th>
      <td><input type="text" name="realm" size="30" value="$realm" /></td>
    </tr>
    <tr>
      <th>{$_htdigest_msg['UserName']}</th>
      <td><input type="text" name="username" size="10" /></td>
    </tr>
    <tr>
      <th>{$_htdigest_msg['Passwd']}</th>
      <td><input type="password" name="passwd" size="10" /></td>
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
      <td><input type="text" name="hash_view" size="60" /></td>
      <td><input type="text" name="algorithm_view" size="5" disabled="disabled" /></td>
    </tr>
    <tr>
      <td><input type="submit" name="submit" value="{$_htdigest_msg['Update']}" disabled="disabled" /></td>
    </tr>

  </table>
</form>
EOD;

	return $x;

}

function htdigest_save($username,$p_realm,$hash)
{
	global $realm, $_htdigest_msg;

	if ($realm != $p_realm)
		return $_htdigest_msg['msg_realm'];

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
