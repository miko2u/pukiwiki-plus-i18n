<?php
/**
 * GreyBox プラグイン
 *
 * @copyright   Copyright &copy; 2006,2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: greybox.inc.php,v 0.7 2009/03/22 02:11:00 upk Exp $
 * @link	http://amix.dk/projects/?page_id=5
 *              http://orangoo.com/labs/GreyBox/
 */

function plugin_greybox_convert()
{
	global $script, $vars, $head_tags;
	static $set_greybox = true;

	if ($set_greybox) {
		$set_greybox = false;

		$css_charset = 'utf-8';
		switch(UI_LANG){
		case 'ja_JP': $css_charset = 'Shift_JIS'; break;
		}

		$head_tags[] = ' <link rel="stylesheet" href="'.SKIN_URI.'greybox/greybox.css" type="text/css" media="all" charset="'.$css_charset.'" />';
		$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'greybox/AmiJS.js"></script>';
		$head_tags[] = ' <script type="text/javascript" src="'.SKIN_URI.'greybox/greybox.js"></script>';
	}

	$argv = func_get_args();
	$argc = func_num_args();

	// <a href="#" onclick="return GB_show('caption', 'url', height, width);">caption</a>
	// <a href="#" onclick="return GB_showFullScreen('caption', 'url');">caption</a>
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
		return '<a href="#" onclick="return GB_showFullScreen(\''.$caption.'\', \''.$url.'\');">'.$caption2."</a>\n";
	} else {
		return '<a href="#" onclick="return GB_show(\''.$caption.'\', \''.$url.'\', '.$height.', '.$width.');">'.$caption2."</a>\n";
	}
}

function plugin_greybox_inline()
{
	$args = func_get_args();
	array_pop($args);
	return call_user_func_array('plugin_greybox_convert', $args);
}
?>
