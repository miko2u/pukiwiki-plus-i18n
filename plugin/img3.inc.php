<?php
/**
 * PukiWiki Plus! img3 プラグイン
 *
 * @copyright   Copyright &copy; 2004-2005, Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * @version	$Id: img3.inc.php,v 0.5 2005/05/28 00:57:00 upk Exp $
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/*
 * The URI is permitted. ('1':OK '0':NG)
 */
if (!defined('USE_IMAGE_URI')) {
        define('USE_IMAGE_URI', '0');
}

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

	// パラメータ
	@list($src,$title,$ratio) = func_get_args();
	if (is_null($src) || empty($src)) return '';
	if (is_null($title) || empty($title)) $title = 'image';
	if (is_null($ratio) || empty($ratio)) $ratio = 1;

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

	$size = img3_set_image_size($src,$ratio); // width, height の取得

	if ($size[4] == 0)
	{
		return $_img3_msg['not_support'];
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
