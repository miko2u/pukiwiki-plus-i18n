<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone
// $Id: editmark.inc.php,v 1.1 2005/05/02 01:26:53 miko Exp $
//

define('PLUGIN_EDITMARK_TAG', '<div id="editmark"></div>');

function plugin_editmark_convert()
{
	return PLUGIN_EDITMARK_TAG;
}

function plugin_editmark_inline()
{
	return PLUGIN_EDITMARK_TAG;
}
?>
