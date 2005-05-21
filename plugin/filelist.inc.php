<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: filelist.inc.php,v 1.3.1 2005/01/09 08:16:28 miko Exp $
//
// Filelist plugin: redirect to list plugin
// cmd=filelist

function plugin_filelist_init()
{
	global $_filelist_msg;

	$messages = array(
		'_filelist_msg' => array(
			'msg_input_pass'	=> _('Please input the password for the Administrator.'),
			'btn_exec'		=> _('Exec'),
			'msg_no_pass'		=> _('The password is wrong.'),
			'msg_H0_filelist'	=> _('Page list'),
		)
	);
	set_plugin_messages($messages);
}

function plugin_filelist_action()
{
	global $vars;
	global $adminpass;
	if (!isset($vars['pass'])) return filelist_adm('');
	// if ($adminpass != md5($vars['pass'])) return filelist_adm('__nopass__');
	if ( pkwk_hash_compute($adminpass, $vars['pass']) != $adminpass )
		return filelist_adm('__nopass__');
	return do_plugin_action('list');
}

// 管理者パスワード入力画面
function filelist_adm($pass)
{
	global $_filelist_msg;
	global $script, $vars;

	$msg_pass = $_filelist_msg['msg_input_pass'];
	$btn      = $_filelist_msg['btn_exec'];
	$body = "";

	if ($pass == '__nopass__')
	{
		$body .= "<p><strong>".$_filelist_msg['msg_no_pass']."</strong></p>";
	}

	$body .= <<<EOD
<p>$msg_pass</p>
<form action="$script" method="post">
 <div>
  <input type="hidden" name="cmd" value="filelist" />
  <input type="password" name="pass" size="12" />
  <input type="submit" name="ok" value="$btn" />
 </div>
</form>

EOD;
	return array('msg' => $_filelist_msg['msg_H0_filelist'],'body' => $body);
}
?>
