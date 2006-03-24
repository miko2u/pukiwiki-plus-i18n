<?php
/**
 * tzCalculation_LocalTimeZone Plugin
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: tz.inc.php,v 0.2 2006/03/24 01:15:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link	http://www.desisoftsystems.com/white-papers/timeZoneCalculation/
 */
function plugin_tz_convert()
{
	global $script, $use_local_time;

	if ($use_local_time) return '';
	if (isset($_COOKIE['timezone'])) return '';
	$url = parse_url($script);

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
