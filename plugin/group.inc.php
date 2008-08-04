<?php
/**
 * PukiWiki Plus! Group確認プラグイン
 *
 * @copyright   Copyright &copy; 2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: group.inc.php,v 0.1 2008/08/02 05:20:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License(GPL2)
 */

function plugin_group_init()
{
	$msg = array(
        '_group_msg' => array(
		'group' => _('Group'),
		)
	);
	set_plugin_messages($msg);
}

function plugin_group_convert()
{
	global $_group_msg;

	$auth_key = auth::get_user_info();
	if (empty($auth_key['group'])) return '';

	$argv = func_get_args();
	$i = count($argv);
	if ($i < 2) {
		return <<<EOD
<div>
        <label>{$_group_msg['group']}</label>:
        {$auth_key['group']}
</div>

EOD;
	}

	$msg = $argv[$i-1];
	array_pop($argv);
	if (in_array($auth_key['group'], $argv)) return convert_html( str_replace("\r", "\n", $msg) );
	return '';
}

?>
