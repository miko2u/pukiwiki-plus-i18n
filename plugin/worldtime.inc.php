<?php
/**
 * World Time プラグイン
 *
 * @copyright   Copyright &copy; 2005-2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: worldtime.inc.php,v 0.4 2006/02/16 01:31:00 upk Exp $
 *
 */

function plugin_worldtime_inline()
{
	switch ( func_num_args() ) {
	case 1:
		return "&worldtime( timezone_name ){format};\n";
	default:
		list($code,$format) = func_get_args();
		$format = htmlspecialchars($format, ENT_QUOTES);
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
