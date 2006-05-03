<?php
/**
 * リダイレクトプラグイン
 *
 * @copyright   Copyright &copy; 2006, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: redirect.inc.php,v 0.1 2006/05/03 18:19:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

function plugin_redirect_action()
{
	global $vars;
	if (empty($vars['u'])) return '';

	// 自サイトからのリダイレクトのみ飛ばす
	if (path_check($_SERVER['HTTP_REFERER'],get_script_uri())) {
		header('Location: ' . $vars['u'] );
		die();
	}

	return '';
}

function plugin_redirect_inline()
{
	$args = func_get_args();
	array_pop($args);
	return call_user_func_array('plugin_redirect_convert', $args);
}

function plugin_redirect_convert()
{
	global $script;

	$argv = func_get_args();
	$argc = func_num_args();

	$field = array('caption','url','img');
	for($i=0; $i<$argc; $i++) {
		$$field[$i] = htmlspecialchars($argv[$i], ENT_QUOTES);
	}

	if (empty($url)) return 'usage: #redirect(caption, url, img)';
	if (empty($caption)) $caption = 'no title';

	if (! empty($img)) {
		$caption = '<img src="'.$img.'" alt="'.$caption.'" title="'.$caption.'" />';
	}

	$redirect = $script.'?plugin=redirect&amp;u='.rawurlencode($url);

	return '<a class="ext" href="'.$redirect.'" rel="nofollow">'.
		$caption.'<img src="'.IMAGE_URI.'plus/ext.png" alt="" title="" class="ext" onclick="return open_uri(\''.
		$redirect.'\', \'_blank\');" /></a>';
}

?>
