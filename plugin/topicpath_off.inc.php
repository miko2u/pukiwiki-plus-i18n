<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: topicpath_off.inc.php,v 0.1 2007/04/22 17:58:00 upk Exp $
// Copyright (C)
//   2007 PukiWiki Plus! Team
// License: GPL (any version)
//
// 'topicpath_off' plugin

function plugin_topicpath_off_convert()
{
	global $topicpath_display;
	$topicpath_display = false;
}
?>
