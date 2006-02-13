<?php
/**
 * GreyBox プラグイン
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: greybox.inc.php,v 0.1 2006/02/12 02:38:00 upk Exp $
 * @link	http://amix.dk/index.py/permanentLink?id=83
 */

function plugin_greybox_convert()
{
	global $script, $vars;

	$argv = func_get_args();
	$argc = func_num_args();

	// <a href="#" onclick="GB_show('caption', 'url', height, width)">caption</a>
	$field = array('caption','url','img','height', 'width');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = $argv[$i];
	}

	if (empty($url)) return 'usage: #greybox(caption, url, img, height, width)';
	if (empty($caption)) $caption = 'no title';
	if (empty($img)) $img = '';
	if (empty($height)) $height = 470;
	if (empty($width)) $width = 600;

	if (! empty($img)) {
		// <img src="" alt="" title="" width="127" height="38" />
		$caption2 = '<img src="'.$img.'" alt="'.$caption.'" title="'.$caption.'" />';
	} else {
		$caption2 = $caption;
	}

	/*
	$link = $script.'?'.rawurlencode($vars['page']).'#';
	return '<a href="'.$link.'" onclick="GB_show(\''.$caption.'\', \''.$url.'\', '.$height.', '.$width.')">'.$caption2."</a>\n";
	*/

	/*
	$anchor = generate_fixed_heading_anchor_id($caption);
	return '<a name="'.$anchor.'"></a>' .
		'<a href="#'.$anchor.'" onclick="GB_show(\''.$caption.'\', \''.$url.'\', '.$height.', '.$width.')">'.$caption2."</a>\n";
	*/

	return '<a href="#" onclick="GB_show(\''.$caption.'\', \''.$url.'\', '.$height.', '.$width.')">'.$caption2."</a>\n";
}

function plugin_greybox_inline()
{
	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('caption','url','img','height', 'width');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = $argv[$i];
	}

        if (empty($url)) return 'usage: &greybox(caption, url, img, height, width);';
        if (empty($caption)) $caption = 'no title';
	if (empty($img)) $img = '';
        if (empty($height)) $height = 470;
        if (empty($width)) $width = 600;

	return plugin_greybox_convert($caption,$url,$img,$height,$width);
}

?>
