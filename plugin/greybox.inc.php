<?php
/**
 * GreyBox プラグイン
 *
 * @copyright   Copyright &copy; 2006-2007, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: greybox.inc.php,v 0.7 2007/07/20 00:01:00 upk Exp $
 * @link	http://amix.dk/projects/?page_id=5
 *              http://orangoo.com/labs/GreyBox/
 */

function plugin_greybox_convert()
{
	global $script, $vars;
	static $graybox_count = 0;

	$argv = func_get_args();
	$argc = func_num_args();

        if ($graybox_count === 0) {
		$graybox_count++;
		global $head_tags;
		$css_charset = 'utf-8';
		switch(UI_LANG){
		case 'ja_JP': $css_charset = 'Shift_JIS'; break;
		}
		$head_tags[] = ' <link rel="stylesheet" href="' . SKIN_URI . 'greybox/greybox.css" type="text/css" media="all" charset="' . $css_charset . '" />';
		$head_tags[] = ' <script type="text/javascript" src="' . SKIN_URI .'greybox/AmiJS.js"></script>';
		$head_tags[] = ' <script type="text/javascript" src="' . SKIN_URI .'greybox/greybox.js"></script>';
        }

	// <a href="#" onclick="return GB_show('caption', 'url', height, width);">caption</a>
	// <a href="#" onclick="return GB_showFullScreen('caption', 'url');">caption</a>
	$field = array('caption','url','img','height', 'width');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
	}

	if (empty($url)) return 'usage: #greybox(caption, url, img, height, width)';
	if (empty($caption)) $caption = 'no title';
	if (empty($img)) $img = '';

	$is_full = FALSE;
	if (empty($height)) {
		$is_full = TRUE;
		$height = 470;
	}
	if (empty($width)) {
		$is_full = TRUE;
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
