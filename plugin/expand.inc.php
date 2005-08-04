<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: expand.inc.php,v 0.1.1 2005/07/29 13:57:36 miko Exp $
//
// Expand Plugin
function plugin_expand_action()
{
	$lines = preg_replace(array("[\\r|\\n]","[\\r]"), array("\n","\n"), $post['fullcontents']);
	$lines = preg_replace(array("'<p>'si","'</p>'si"), array("",""), convert_html($lines));

	return array(
			'msg' => _('View all contents'),
			'body'=> $lines,
	);
}
function plugin_expand_convert()
{
	$numargs = func_num_args();
	if ($numargs != 1) {
		return '';
	}

	list($source) = func_get_args();
	$script = get_script_uri();

	$lines = preg_replace(array("[\\r|\\n]","[\\r]"), array("\n","\n"), $source);
	$lines = preg_replace(array("'<p>'si","'</p>'si"), array("",""), convert_html($lines));
	return '<div style="width:100%;overflow:hidden;">' . $lines . '</div>'
		 . '<form method="post" action="' . $script . '"><textarea name="fullcontents" rows="1" cols="1" style="display:none;">'
		 . htmlspecialchars($source) . '</textarea><input type="submit" name="submit" class="btnExpand" />'
		 . '<input type="hidden" name="cmd" value="expand" /></form>';
}
?>
