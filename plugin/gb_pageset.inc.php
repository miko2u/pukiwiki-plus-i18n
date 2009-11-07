<?php
/**
 * GreyBox (gb_pageset) プラグイン
 *
 * @copyright   Copyright &copy; 2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: gb_pageset.inc.php,v 0.1 2009/03/31 00:37:00 upk Exp $
 * @link	http://orangoo.com/labs/GreyBox/
 */

function plugin_gb_pageset_convert()
{
	global $script, $vars;
	static $get_greybox = true;

	if ($get_greybox) {
		$get_greybox = false;
		if (exist_plugin('greybox'))
			greybox_set_head_tags();
		else
			die_message('greybox plugin not found.');
	}

	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('page_set_name','caption','url');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
	}

	if (empty($page_set_name) || (empty($url))) return 'usage: #gb_pageset(page_set_name, caption, url)';
	if (empty($caption)) $caption = 'no title';

	$caption = str_replace('&amp;#039;','\'',$caption); // ' の対応
	return '<a href="'.$url.'" title="'.$caption.'" rel="gb_pageset['.$page_set_name.']">'.$caption."</a>\n";
}

function plugin_gb_pageset_inline()
{
	$args = func_get_args();
	array_pop($args);
	return call_user_func_array('plugin_gb_pageset_convert', $args);
}
?>
