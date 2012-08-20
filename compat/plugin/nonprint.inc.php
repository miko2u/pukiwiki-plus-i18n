<?
// PukiWiki Plus! - Yet another WikiWikiWeb clone
// $Id: nonprint.inc.php,v 0.1 2007/01/03 18:51:00 upk Exp $
// Copyright (C)
//   2007 PukiWiki Plus! Team
// License: GNU Public License (GPL2)
//
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
			$rc .= ' '.$key[$argv[$i]].'{ display:none; }'."\n";
		}
	}
	$rc .= ' -->'."\n".' </style>';
	$head_tags[] = $rc;
}

?>