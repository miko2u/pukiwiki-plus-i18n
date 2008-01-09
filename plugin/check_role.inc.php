<?php
/**
 * check_role plugin
 *
 * @copyright   Copyright &copy; 2006-2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: check_role.inc.php,v 0.5 2008/01/05 20:56:00 upk Exp $
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

	$argv = func_get_args();
	$argc = func_num_args();

        $field = array('chk_role');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = $argv[$i];
	}

	if (empty($chk_role)) $chk_role = 0;

	$role_func = (empty($chk_role_str[$chk_role])) ? 'role_auth' : $chk_role_str[$chk_role];
	if (! auth::check_role($role_func)) return '';

	check_role_die('It is necessary to attest it to inspect this page.');
}

function check_role_die($msg)
{
	global $defaultpage, $vars;

	if (! empty($vars['page']) && $defaultpage == $vars['page']) die_message($msg);

	header('Location: ' . get_location_uri());
	// 飛ばないときしかメッセージは表示されない
	die($msg);
}

?>
