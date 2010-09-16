<?php
/**
 * PukiWiki Plus! ログインプラグイン
 *
 * @copyright	Copyright &copy; 2004-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: login.php,v 0.5 2006/02/07 21:15:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/*
 * 初期処理
 */
function plugin_phpinfo_init()
{
	$messages = array(
	'_phpinfo_msg' => array(
		'btn_phpinfo'		=> _('PHPINFO'),
		)
	);
	set_plugin_messages($messages);
}

/*
 * ブロック型プラグイン
 */
function plugin_phpinfo_convert()
{
	global $script;
	global $_phpinfo_msg;

	// if (auth::check_role('role_adm_contents') return '';
	if (auth::check_role('role_adm')) return '';

	// ボタンを表示するだけ
	$rc = <<<EOD
<form action="$script" method="post">
	<div>
		<input type="hidden" name="plugin" value="phpinfo" />
		<input type="submit" value="{$_phpinfo_msg['btn_phpinfo']}" />
	</div>
</form>

EOD;

	return $rc;
}

/*
 * アクションプラグイン
 */
function plugin_phpinfo_action()
{
	// if (auth::check_role('role_adm_contents') return '';
	if (auth::check_role('role_adm')) return '';
	phpinfo();
	die();

}
?>
