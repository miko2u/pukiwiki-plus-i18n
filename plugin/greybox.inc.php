<?php
/**
 * GreyBox プラグイン
 *
 * @copyright   Copyright &copy; 2006,2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: greybox.inc.php,v 0.9 2009/11/08 02:33:00 upk Exp $
 * @link	http://orangoo.com/labs/GreyBox/
 */

function plugin_greybox_convert()
{
	global $script, $vars;

	greybox_set_head_tags();

	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('caption','url','img','height', 'width');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
	}

	if (empty($url)) return 'usage: #greybox(caption, url, img, height, width)';
	if (empty($caption)) $caption = 'no title';
	if (empty($img)) $img = '';

	$is_full = false;
	if (empty($height)) {
		$is_full = true;
		$height = 470;
	}
	if (empty($width)) {
		$is_full = true;
		$width = 600;
	}

	if (! empty($img)) {
		// <img src="" alt="" title="" width="127" height="38" />
		$caption2 = '<img src="'.$img.'" alt="'.$caption.'" title="'.$caption.'" />';
	} else {
		$caption2 = $caption;
	}

	$caption2 = str_replace('&amp;#039;','\'',$caption2); // ' の対応
	if ($is_full) {
		return '<a href="'.$url.'" title="'.$caption.'" rel="gb_page_fs[]">'.$caption2."</a>\n";
	} else {
		return '<a href="'.$url.'" title="'.$caption.'" rel="gb_page_center['.$width.', '.$height.']">'.$caption2."</a>\n";
	}
}

function plugin_greybox_inline()
{
	$args = func_get_args();
	array_pop($args);
	return call_user_func_array('plugin_greybox_convert', $args);
}

function greybox_set_head_tags()
{
	global $head_tags, $greybox_set;

	if (isset($greybox_set) && ($greybox_set)) return;
	$greybox_set = true;

	$head_tags[] = ' <script type="text/javascript">';
	$head_tags[] = ' <!--';
	$head_tags[] = '  var GB_ROOT_DIR = "'.get_baseuri('abs').SKIN_URI.'greybox/'.'";';
	$head_tags[] = ' // -->';
	$head_tags[] = ' </script>';

	$css_charset = 'utf-8';
	switch(UI_LANG) {
	case 'ja_JP': $css_charset = 'Shift_JIS'; break;
	}

	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'greybox/AJS.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'greybox/AJS_fx.js"></script>';
	$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'greybox/gb_scripts.js"></script>';
	$head_tags[] = ' <link rel="stylesheet" href="'.SKIN_URI.'greybox/gb_styles.css" type="text/css" media="all" charset="'.$css_charset.'" />';
}
?>
