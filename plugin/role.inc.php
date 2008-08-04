<?php
/**
 * PukiWiki Plus! role確認プラグイン
 *
 * @copyright	Copyright &copy; 2006-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: role.inc.php,v 0.3 2008/08/05 00:40:00 upk Exp $
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

	$argv = func_get_args();
	$i = count($argv);
	if ($i < 2) {
		return role_list($role);
	}

	$msg = $argv[$i-1];
	if (! auth::is_check_role($argv[0])) return convert_html( str_replace("\r", "\n", $msg) );
	return '';
}

function role_list($role)
{
	global $_role_msg;
	$role_name = array(
		$_role_msg['role_0'],
		'',
		$_role_msg['role_2'],
		$_role_msg['role_3'],
		$_role_msg['role_4'],
		$_role_msg['role_5'],
	);

	return <<<EOD
<div>
        <label>{$_role_msg['role']}</label>:
        {$role_name[(int)$role]}($role)
</div>

EOD;

}

?>
