<?
/**
 * Non Print Plugin
 *
 * @copyright   Copyright &copy; 2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: nonprint.inc.php,v 0.6 2007/01/03 01:39:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License (GPL2)
 */
function plugin_nonprint_convert()
{
	global $head_tags;
	static $key = array(
		'header'		=> 'div#header',
		'footer'		=> 'div#footer',
		'title'			=> 'h1.title',
		'span'			=> 'span.small',
		'hr'			=> 'hr.full_hr',
		'topicpath'		=> 'div#topicpath',
		'comment'		=> 'div.commentform',
		'article'		=> 'div.articleform',
		'calstick_calendar'	=> 'table.calstick_calendar',
		'sbm'			=> '.sbm',
	);

	$argv = func_get_args();
	$argc = func_num_args();

	$rc = ' <style type="text/css" media="print">'."\n <!--\n";
	for($i=0; $i<$argc; $i++) {
		if (! empty($key[$argv[$i]])) {
			$rc .= $key[$argv[$i]].'{ display:none; }'."\n";
		}
	}
	$rc .= ' -->'."\n".' </style>';
	$head_tags[] = $rc;
}

?>
