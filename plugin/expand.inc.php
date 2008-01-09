<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: expand.inc.php,v 0.1.4 2008/01/05 23:09:00 upk Exp $
//
// Expand Plugin
define('PLUGIN_EXPAND_ICON', IMAGE_URI . 'plus/expand.gif');
define('PLUGIN_EXPAND_DEFAULT_WIDTH', 380);
define('PLUGIN_EXPAND_MIN_WIDTH',  PLUGIN_EXPAND_DEFAULT_WIDTH);
define('PLUGIN_EXPAND_MIN_HEIGHT', 380);

function plugin_expand_action()
{
	global $post;

	$postdata = $post['fullcontents'];
	$postdata = make_str_rules($postdata);
	$postdata = drop_submit(convert_html($postdata));

	return array(
			'msg' => _('View all contents'),
			'body'=> $postdata,
	);
}

function plugin_expand_convert()
{
	global $script;

	$numargs = func_num_args();
	if ($numargs == 3) {
		list($width,$height,$source) = func_get_args();
		$width = intval($width);
		$height = intval($height);
	} else if ($numargs == 2) {
		list($width,$source) = func_get_args();
		$width = intval($width);
	} else if ($numargs == 1) {
		list($source) = func_get_args();
		$width = PLUGIN_EXPAND_DEFAULT_WIDTH;
	} else {
		return _('#expand: invalid arguments');
	}
	if (!isset($width) || $width < PLUGIN_EXPAND_MIN_WIDTH) { return _('#expand: too few width. ') . $width; }
	if (isset($height) && $height < PLUGIN_EXPAND_MIN_HEIGHT) { return _('#expand: too few height. ') . $height; }

	$lines = preg_replace(array("[\\r|\\n]","[\\r]"), array("\n","\n"), $source);
	$lines = preg_replace(array("'<p>'si","'</p>'si"), array("",""), convert_html($lines));
	return '<div style="width:' . $width . 'px;overflow:hidden;">' . $lines . '</div>'
		 . '<form method="post" action="' . $script . '"><textarea name="fullcontents" rows="1" cols="1" style="display:none;">'
		 . htmlspecialchars($source) . '</textarea><input type="image" name="submit" src="' . PLUGIN_EXPAND_ICON
		 . '" style="float:right;" alt="' . _('Click to all views') . '" />'
		 . '<input type="hidden" name="cmd" value="expand" /></form>';
}
?>
