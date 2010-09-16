<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: adsense.inc.php,v 1.2.1 2006/03/04 17:06:00 upk Exp $
//
// Google AdSize : 728x90, 468x60, 234x60
// 125x125, 120x600, 160x600, 120x240
// 300x250, 250x250, 336x280, 180x150
//
if (!defined('GOOGLE_ACCOUNT')) {
	define('GOOGLE_ACCOUNT','pub-1612057690088425');
}
if (!defined('GOOGLE_DEF_W')) {
	define('GOOGLE_DEF_W', 160);
}
if (!defined('GOOGLE_DEF_H')) {
	define('GOOGLE_DEF_H', 600);
}
if (!defined('GOOGLE_DEF_TYPE')) {
	define('GOOGLE_DEF_TYPE', 'text');
}

if (!defined('GOOGLE_COLOR_BORDER')) {
	define('GOOGLE_COLOR_BORDER', 'FF4433');
}
if (!defined('GOOGLE_COLOR_BG')) {
	define('GOOGLE_COLOR_BG', 'FFFFCC');
}
if (!defined('GOOGLE_COLOR_LINK')) {
	define('GOOGLE_COLOR_LINK', 'DE7008');
}
if (!defined('GOOGLE_COLOR_URL')) {
	define('GOOGLE_COLOR_URL', 'E0AD12');
}
if (!defined('GOOGLE_COLOR_TEXT')) {
	define('GOOGLE_COLOR_TEXT', '8B4513');
}

function plugin_adsense_action()
{
	global $get;

	$width = $get['w'];
	$height = $get['h'];
	if (((int)$width) == 0 || ((int)$height) == 0) { $width = GOOGLE_DEF_W; $height=GOOGLE_DEF_H; }
	$type = $get['type'];
	if ($type != 'text' && $type != 'text_image') { $type = GOOGLE_DEF_TYPE; }

	$body .= 'google_ad_client = "' . GOOGLE_ACCOUNT . '";';
	$body .= 'google_ad_width = ' . $width . ';'."\n";
	$body .= 'google_ad_height = ' . $height . ';'."\n";
	$body .= 'google_ad_format = "' . $width . 'x' . $height . '_as";'."\n";
	$body .= 'google_ad_channel = "";'."\n";
	$body .= 'google_ad_type = "'. $type .'";'."\n";
	$body .= 'google_color_border = "' . GOOGLE_COLOR_BORDER . '";'."\n";
	$body .= 'google_color_bg     = "' . GOOGLE_COLOR_BG     . '";'."\n";
	$body .= 'google_color_link   = "' . GOOGLE_COLOR_LINK   . '";'."\n";
	$body .= 'google_color_url    = "' . GOOGLE_COLOR_URL    . '";'."\n";
	$body .= 'google_color_text   = "' . GOOGLE_COLOR_TEXT   . '";'."\n";
	echo $body;
	die();
}

function plugin_adsense_convert()
{
	global $script;

	if (func_num_args() < 1) {
		return FALSE;
	}

	$args = func_get_args();

	if (preg_match('/^([0-9]+)x([0-9]+)$/',$args[0],$m)) {
		$width = $m[1];
		$height = $m[2];
	} else {
		$width = GOOGLE_DEF_W;
		$height = GOOGLE_DEF_H;
	}
	if (func_num_args() >= 2) {
		$type = in_array('image',$args) ? "text_image":"text";
	} else {
		$type = GOOGLE_DEF_TYPE;
	}

	$url = "$script?plugin=adsense&amp;type=$type&amp;w=$width&amp;h=$height";
	return '<div class="adsense"><script type="text/javascript" src="'.$url.'"></script><script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script></div>';
}
?>
