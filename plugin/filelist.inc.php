<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: filelist.inc.php,v 1.2.1 2004/07/31 03:09:20 upk/miko Exp $
//
// ファイル名一覧の表示
// cmd=filelist

function plugin_filelist_init()
{
	global $_filelist_msg;

	$messages = array(
		'_filelist_msg' => array(
			'msg_input_pass'	=> '管理者用のパスワードを入力してください。',
			'btn_exec'		=> '実行',
			'msg_no_pass'		=> 'パスワードが間違っています。',
			'msg_H0_filelist'	=> 'ページファイルの一覧',
		)
	);
	set_plugin_messages($messages);
}

function plugin_filelist_action()
{
	global $vars;
	global $adminpass;
	if (!isset($vars['pass'])) return filelist_adm('');
	if ($adminpass != md5($vars['pass'])) return filelist_adm('__nopass__');
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
