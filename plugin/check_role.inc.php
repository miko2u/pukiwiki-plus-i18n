<?php
/**
 * check_role plugin
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: check_role.inc.php,v 0.1 2006/09/05 00:06:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 *
 */
function plugin_check_role_convert()
{
	global $check_role;
	if (! $check_role) return '<p>check_role: The function is invalid.</p>';

	// role         - 0:Guest, 2:Webmaster, 3:Contents manager, 4:Authorized
	// chk_role_str - 0,1,4: Authorized, 2:Webmaster, 3:Contents manager
	static $chk_role_str = array('role_auth','role_auth','role_adm','role_adm_contents','role_auth');

	$data = '';
	$chk_role = 0;

	$argv = func_get_args();
	switch (func_num_args()) {
	case 2:
		$data = $argv[1];
	case 1:
		$chk_role = $argv[0];
	}

	$role_func = (empty($chk_role_str[$chk_role])) ? 'role_auth' : $chk_role_str[$chk_role];
	if (! auth::check_role($role_func)) return $data;

	check_role_die('It is necessary to attest it to inspect this page.');
}

function check_role_die($msg)
{
	global $script;
	header('Location: ' . $script );
	// 飛ばないときしかメッセージは表示されない
	die($msg);
}

?>
