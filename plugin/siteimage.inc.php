<?php
// PukiWiki Plus! - Yet another wikiwikiweb clone.
// $Id: siteimage.inc.php,v 1.8.1 2006/06/15 01:06:00 miko Exp $
// Copyright (C)
//   2006      PukiWiki Plus! Team
//   2006      by nao-pon http://hypweb.net/
// License: GPL v2 or (at your option) any later version

// 携帯電話での小さい画像の表示
defined('PLUGIN_SITEIMAGE_SHOW_IMAGE_TO_MOBILEPHONE')
	or define('PLUGIN_SITEIMAGE_SHOW_IMAGE_TO_MOBILEPHONE', FALSE); // FALSE, TRUE

function plugin_siteimage_inline()
{
	global $link_target;
	$args = func_get_args();
	$url = array_shift($args);
	if (!is_url($url))
		return 'Usage: &amp;siteimage([url],[option(s),...]);';

	$options = array('nolink'=>false);
	get_plugin_option($args, &$options);
	return plugin_siteimage_make($url, $options['nolink']);
//	return plugin_siteimage_make($url, $options['nolink'], $options['target']);
}

function plugin_siteimage_convert()
{
	global $link_target;
	$args = func_get_args();
	$url = array_shift($args);
	if (!is_url($url))
		return '<p>Usage: #siteimage([url],[option(s),...]);</p>';

	$options = array('nolink'=>false,'around'=>false,'left'=>false,'right'=>false,'center'=>false);
	get_plugin_option($args, &$options);

	$style = 'width:128px;height:128px;margin:10px;';
	if ($options['around']) {
		if ($options['right']) {
			$style .= 'float:right;margin-right:5px;';
		} else {
			$style .= 'float:left;margin-left:5px;';
		}
	} else {
		if ($options['right']) {
			$style .= 'margin-right:10px;margin-left:auto;';
		} else if ($options['center']) {
			$style .= 'margin-right:auto;margin-left:auto;';
		} else {
			$style .= 'margin-right:auto;margin-left:10px;';
		}
	}
//	$img = plugin_siteimage_make($url, $options['nolink'], $options['target']);
	$img = plugin_siteimage_make($url, $options['nolink']);
	return '<div style="' . $style . '">' . $img . "</div>\n";
}

function plugin_siteimage_make($url, $nolink, $target='')
{
	$url = htmlspecialchars($url);
	$target = htmlspecialchars($target);
	if (defined('UA_MOBILE') && UA_MOBILE != 0) {
		if (defined('PLUGIN_SITEIMAGE_SHOW_IMAGE_TO_MOBILEPHONE') && PLUGIN_SITEIMAGE_SHOW_IMAGE_TO_MOBILEPHONE) {
			$ret = '<img src="http://img.simpleapi.net/small/' . $url . '" width="128" height="128" alt="' . $url . '" title="keitai" />';
		} else {
			$ret = '<a href="http://img.simpleapi.net/small/' . $url . '">[' . $url . ']</a>';
		}
	} else {
		$ret = '<img src="http://img.simpleapi.net/small/' . $url . '" width="128" height="128" alt="' . $url . '" />';
	}

	if (!$nolink) {
//		$ret = '<a href="' . $url . '" target="' . $target . '" title="' . $url . '">' . $ret . '</a>';
		$ret = '<a href="' . $url . '" title="' . $url . '">' . $ret . '</a>';
	}
	return $ret;
}
?>