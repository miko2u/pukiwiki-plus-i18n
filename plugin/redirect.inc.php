<?php
/**
 * リダイレクトプラグイン
 *
 * @copyright   Copyright &copy; 2006,2008, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version     $Id: redirect.inc.php,v 0.3 2008/01/06 05:35:00 upk Exp $
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

function plugin_redirect_action()
{
	global $vars;
	if (empty($vars['u'])) return '';

	// 自サイトからのリダイレクトのみ飛ばす
	if (path_check($_SERVER['HTTP_REFERER'],get_script_absuri())) {
		// 以下の方法は、NG です。
		// <a href="javascript:location.replace('URL');">Caption</a>
		//header('Location: ' . $vars['u'] );
		//die();
		$time = 0;
		echo <<<EOD
<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
  <meta http-equiv="Refresh" content="$time;URL={$vars['u']}" />
  <title>Auto Redirect</title>
  </head>
  <body>
  <div><a href="{$vars['u']}">Please click here.</a></div>
  </body>
</html>
EOD;
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
