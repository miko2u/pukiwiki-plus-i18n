<?php
/**
 * tzCalculation_LocalTimeZone Plugin
 *
 * @copyright   Copyright &copy; 2006,2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: tz.inc.php,v 0.3 2008/08/30 21:38:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link	http://www.desisoftsystems.com/white-papers/timeZoneCalculation/
 */
function plugin_tz_convert()
{
	global $use_local_time;

	if ($use_local_time) return '';
	if (isset($_COOKIE['timezone'])) return '';
	$url = parse_url( get_script_absuri() );

	if (empty($url['host'])) return '';

	return <<<EOD

<script type="text/javascript">
<!--
    tzCalculation_LocalTimeZone ('{$url['host']}',false);
-->
</script>
EOD;

}

?>
