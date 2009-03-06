<?php
/**
 * PukiWiki Plus! img3 プラグイン
 *
 * @copyright   Copyright &copy; 2004-2005,2009, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: img3.inc.php,v 0.7 2009/03/07 01:55:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/*
 * The URI is permitted. ('1':OK '0':NG)
 */
defined('USE_IMAGE_URI')  or define('USE_IMAGE_URI','0');

function plugin_img3_init()
{
	$msg = array(
		'_img3_msg' => array(
			'not_found'	=> _('not found.'),
			'not_permitted' => _('It is not permitted.'),
			'not_support'	=> _('not found or, image type is not supported.'),
		)
	);
	set_plugin_messages($msg);
}

function plugin_img3_inline()
{
	global $script;
	global $_img3_msg;
	static $img_no = 0;

	// パラメータ
	@list($src,$title,$ratio,$align) = func_get_args();
	if (is_null($src) || empty($src)) return '';
	if (is_null($title) || empty($title)) $title = 'image';
	if (is_null($ratio) || empty($ratio)) $ratio = 1;
	if (is_null($align) || empty($align)) $align = '';

	// ファイルの存在チェック
	// $url = rawurldecode($src);
	$url_arry = parse_url($src);
	if (empty($url_arry['scheme']))
	{
		if (!file_exists($src))
			return $_img3_msg['not_found'];
	} else {
		if (! USE_IMAGE_URI)
			return $_img3_msg['not_permitted'];
	}

	$src   = htmlspecialchars($src);
	$title = strip_htmltag($title);
	$title = htmlspecialchars($title);
	$ratio = htmlspecialchars($ratio);
	$align = htmlspecialchars($align);

	$size = img3_set_image_size($src,$ratio); // width, height の取得

	if ($size[4] == 0)
	{
		return $_img3_msg['not_support'];
	}

	if (!empty($align)) {
		$img_no++;
		return <<<EOD
<style type="text/css">
img.img3$img_no { vertical-align: $align; }
</style>
<img src="$src" alt="$title" title="$title" ${size[6]} class="img3$img_no" />
EOD;
	}

	return <<<EOD
<img src="$src" alt="$title" title="$title" ${size[6]} />
EOD;
}

// 画像のファイルサイズ設定
function img3_set_image_size($filename,$ratio=1)
{
	$size = @getimagesize($filename);

	// 少数切捨て
	$size[4] = floor( $size[0] * $ratio );
	$size[5] = floor( $size[1] * $ratio );
	$size[6] = 'width="' . $size[4] . '" height="' . $size[5] . '"';
	return $size;
}

?>
