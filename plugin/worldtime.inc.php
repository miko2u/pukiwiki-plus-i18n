<?php
/**
 * World Time プラグイン
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: worldtime.inc.php,v 0.2 2005/06/08 21:32:00 upk Exp $
 *
 */

function plugin_worldtime_inline()
{
	list($code) = func_get_args();
	if (empty($code)) return '';

	$obj = new timezone();
	$obj->set_datetime();
	// $obj->set_country($code);
	$obj->set_tz_name($code);

	list($zone, $zonetime) = $obj->get_zonetime();
	$x = gmdate('Y-m-d H:i', UTIME + $zonetime);
	$x .= ' '.$zone;
	return $x;
}

?>
