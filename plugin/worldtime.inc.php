<?php
/**
 * World Time プラグイン
 *
 * @copyright   Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: worldtime.inc.php,v 0.3 2005/06/08 22:36:00 upk Exp $
 *
 */

function plugin_worldtime_inline()
{
	switch ( func_num_args() ) {
	case 1:
		return "&worldtime( timezone_name ){format};\n";
	default:
		list($code,$format) = func_get_args();
	}

	if (empty($code)) return '';

	$obj = new timezone();
	$obj->set_datetime();
	$obj->set_tz_name($code);
	list($zone, $zonetime) = $obj->get_zonetime();

	if (empty($format)) $format = 'Y-m-d H:i T';
	$x = gmdate($format, UTIME + $zonetime);
	$x = str_replace('GMT',$zone,$x);
	return $x;
}

?>
