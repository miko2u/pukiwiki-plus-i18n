<?php
/////////////////////////////////////////////////
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
//
// $Id: keywords.inc.php,v 0.3 2004/09/30 09:41:57 miko Exp $
//

function plugin_keywords_convert()
{
	global $head_tags;

	$num = func_num_args();
	if ($num == 0) { return 'Usage: #keywords(keyword,...)'; }
	$args = func_get_args();
	$contents = array_map(htmlspecialchars,$args);

	$head_tags[] = ' <meta http-equiv="Keywords" content="'.join(',', $contents).'" />';
	return '';
}
?>
