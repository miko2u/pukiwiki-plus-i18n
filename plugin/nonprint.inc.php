<?
/**
 * Non Print Plugin
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: free.inc.php,v 0.1 2007/01/02 00:00:09 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
function plugin_nonprint_convert()
{
	$argv = func_get_args();
	$argc = func_num_args();

	$key = array(
		'title'			=> 'h1.title',
		'topicpath'		=> 'div#topicpath',
		'calstick_calendar'	=> 'table.calstick_calendar',
		'sbm'			=> '.sbm',

	);

	$rc = '<style type="text/css" media="print">'."\n";
	for($i=0; $i<$argc; $i++) {
		if (! empty($key[$argv[$i]])) {
			$rc .= $key[$argv[$i]].'{ display:none; }'."\n";
		}
	}
	$rc .= '</style>'."\n";
	return $rc;
}

?>
