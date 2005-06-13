<?php
/**
 * PukiWiki Plus! ロゴプラグイン
 *
 * @package	org.pukiwiki.plugin.logo
 * @copyright	Copyright &copy; 2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: toc.php,v 0.2 2005/06/08 00:02:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link	http://jo1upk.blogdns.net/saito/
 */

/**
 * インライン型プラグイン処理
 */
function plugin_logo_inline()
{
	global $vars, $script, $modifierlink;

	list($file,$width,$height,$title,$alt) = array_pad(func_get_args(), 5, '');

	if (empty($file))   $file   = IMAGE_URI . 'pukiwiki.png';
	if (empty($width))  $width  = 80;
	if (empty($height)) $height = 80;
	if (empty($title))  $title  = 'PUKIWIKI';
	if (empty($alt))    $alt    = $title;

	return <<<EOD
 <a href="$modifierlink"><img id="logo" src="$file" width="$width" height="$height" alt="$alt" title="$title" /></a>

EOD;

}

?>
