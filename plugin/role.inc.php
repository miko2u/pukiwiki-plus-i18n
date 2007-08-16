<?php
/**
 * PukiWiki Plus! role確認プラグイン
 *
 * @copyright	Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: role.inc.php,v 0.2 2007/08/16 18:27:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/*
 * 初期処理
 */
function plugin_role_init()
{
	$msg = array(
	'_role_msg' => array(
		'role'		=> _('Role'),
		'role_0'	=> _('Guest'),
		'role_2'	=> _('Webmaster'),
		'role_3'	=> _('Contents manager'),
		'role_4'	=> _('Enrollee'),
		'role_5'	=> _('Authorized'),
		)
	);
	set_plugin_messages($msg);
}

function plugin_role_convert()
{
	global $_role_msg;

	$role = auth::get_role_level();

	if ($role == 0) return '';

	$usr_list = auth::get_user_list((int)$role);

	$role_name = array(
		$_role_msg['role_0'],
		'',
		$_role_msg['role_2'],
		$_role_msg['role_3'],
		$_role_msg['role_4'],
		$_role_msg['role_5'],
	);

	$rc = <<<EOD
<div>
	<label>{$_role_msg['role']}</label>:
	{$role_name[(int)$role]}($role)
</div>

EOD;

	unset($role_name, $usr_list, $role, $_role_msg);
	return $rc;
}

?>
