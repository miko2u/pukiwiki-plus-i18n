<?php
/**
 * World Time ƒvƒ‰ƒOƒCƒ“
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: worldtime.inc.php,v 0.1 2005/03/30 01:00:00 upk Exp $
 *
 */

function plugin_worldtime_inline()
{
	list($code) = func_get_args();
	if (empty($code)) return;

	$obj = new timezone();
	$obj->set_datetime();
	// $obj->set_country($code);
	$obj->set_tz_name($code);

	list($zone, $zonetime) = $obj->get_zonetime();
	$x = date("Y-m-d H:i", UTIME+$zonetime);
	$x .= " ".$zone;
	return $x;
}

?>
