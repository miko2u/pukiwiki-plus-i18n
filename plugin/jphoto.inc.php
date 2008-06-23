<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: jphoto.inc.php,v 0.4 2008/06/23 19:29:00 upk Exp $
//
// argument:
// jphoto(pagename,[flashsize],[photosize],[zoom])
// flashsize, photosize is '[width]x[height]'
// zoom is percent
// jphoto3p flash is (c)2003 hirax, plugin is (c)2004-2005 miko
//

defined('JPHOTO_SCREENWIDTH')    or define('JPHOTO_SCREENWIDTH', 400);
defined('JPHOTO_SCREENHEIGHT')   or define('JPHOTO_SCREENHEIGHT', 20);
defined('JPHOTO_WIDTH')          or define('JPHOTO_WIDTH', 96);
defined('JPHOTO_HEIGHT')         or define('JPHOTO_HEIGHT', 72);
defined('JPHOTO_SPARSE')         or define('JPHOTO_SPARSE', 12);
defined('JPHOTO_ZOOMRATIO')      or define('JPHOTO_ZOOMRATIO', 200);
defined('JPHOTO_BORDERCOLOR')    or define('JPHOTO_BORDERCOLOR', '0x0000FF');
defined('JPHOTO_BGCOLOR')        or define('JPHOTO_BGCOLOR', '0xFFFFFF');
defined('JPHOTO_XOFFSET')        or define('JPHOTO_XOFFSET', 0);
defined('JPHOTO_YOFFSET')        or define('JPHOTO_YOFFSET', 0);
defined('JPHOTO_SCALEUPSTEP')    or define('JPHOTO_SCALEUPSTEP', 8);
defined('JPHOTO_SCALEDNSTEP')    or define('JPHOTO_SCALEDNSTEP', 8);
defined('JPHOTO_SCALEDNSTEP')    or define('JPHOTO_SCALEDNSTEP', 8);
defined('JPHOTO_SHADOWDISTANCE') or define('JPHOTO_SHADOWDISTANCE', 4);
// Image suffixes allowed
defined('JPHOTO_REF_IMAGE')      or define('JPHOTO_REF_IMAGE', '/\.(gif|png|jpe?g)$/i');

//
function plugin_jphoto_convert()
{
	global $script, $vars;

	$argc = func_num_args();
	$argv = func_get_args();
	$page = '';
	$width = $height = $pwidth = $pheight = -1;
	$zoom = 0;

	if (isset($argv[0]) && $argv[0] != '') {
		if ( is_page($argv[0]) ) {
			$page = $argv[0];
		}
	}
	if ($page == '') {
		$page = $vars['page'];
	}

	if ($argc >= 2) {
		if (preg_match('/(.*)x(.*)/i', $argv[1], $match)) {
			$width = intval($match[1]);
			$height = intval($match[2]);
		}
	}
	if ($argc >= 3) {
		if (preg_match('/(.*)x(.*)/i', $argv[2], $match)) {
			$pwidth = intval($match[1]);
			$pheight = intval($match[2]);
		}
	}
	if ($argc >= 4) {
		$zoom = intval($argv[3]);
	}

	if ($width  <= 0) { $width  = JPHOTO_SCREENWIDTH; }
	if ($height <= 0) { $height = JPHOTO_SCREENHEIGHT; }
	if ($pwidth  <= 0) { $pwidth  = JPHOTO_WIDTH; }
	if ($pheight <= 0) { $pheight = JPHOTO_HEIGHT; }
	if ($zoom <= 100) { $zoom = JPHOTO_ZOOMRATIO; }

	$version = '6,0,0,0';
	$classid = 'D27CDB6E-AE6D-11cf-96B8-444553540000';
	$bgcolor = '#FFFFFF';
	$quality = 'high';

	$swf = SKIN_URI . 'jphoto3p.swf';
	$id = 'jphoto';
	$wmode = 'opaque';

	$config  = 'pThumbnailWidth=' . $pwidth;
	$config .= '&amp;pThumbnailHeight=' . $pheight;
	$config .= '&amp;pThumbnailSparse=' . JPHOTO_SPARSE;
	$config .= '&amp;pThumbnailZoomRatio=' . $zoom;
	$config .= '&amp;pBorderLineColor=' . JPHOTO_BORDERCOLOR;
	$config .= '&amp;pBackgroundColor=' . JPHOTO_BGCOLOR;
	$config .= '&amp;pXoffset=' . JPHOTO_XOFFSET;
	$config .= '&amp;pYoffset=' . JPHOTO_YOFFSET;
	$config .= '&amp;pScaleUpStep=' . JPHOTO_SCALEUPSTEP;
	$config .= '&amp;pScaleDownStep=' . JPHOTO_SCALEDNSTEP;
	$config .= '&amp;pShadowDistance=' . JPHOTO_SHADOWDISTANCE;

	$photo3  = '';

	$dir = opendir(UPLOAD_DIR);
	$page_pattern = ($page == '') ? '(?:[0-9A-F]{2})+' : preg_quote(encode($page), '/');
	$age_pattern = '(?:\.([0-9]+))?';
	$pattern = "/^({$page_pattern})_((?:[0-9A-F]{2})+){$age_pattern}$/";

	$matches = array();
	$dx = intval($pwidth * ($zoom/200) + JPHOTO_SPARSE); 
	$dy = intval($pheight * ($zoom/200) + JPHOTO_SPARSE); 
	$x = $dx;
	$y = $dy;
	while ($file = readdir($dir)) {
		if (! preg_match($pattern, $file, $matches))
			continue;
		$_page = decode($matches[1]);
		$_file = decode($matches[2]);
		$_age  = isset($matches[3]) ? $matches[3] : 0;
		if (! preg_match(JPHOTO_REF_IMAGE, $_file))
			continue;

		$line = $script . '?plugin=ref&page=' . $_page . '&src=' . $_file;
		$photo3 .= $line . ',' . $line . ',' . $x . ',' . $y . "\n";
		$x += $pwidth + JPHOTO_SPARSE;
		if ($x + $dx >= $width) {
			$x = $dx;
			$y += $pheight + JPHOTO_SPARSE;
		}
	}
	closedir($dir);
	if ($x == $dx && $y != $dy) {
		$y -= $pheight + JPHOTO_SPARSE;
	}
	if (($y + $dy) >= $height) {
		$height = $y + $dy;
	}

	$photo3 = str_replace("\n", ";", $photo3);
	$photo3 = rawurlencode($photo3);
	$config .= '&amp;pPhotos=' . $photo3;

	$width = 'width="'. $width . '"'; 
	$height = 'height="'. $height . '"'; 

	// IE's Bug? umm...
	if (UA_NAME == 'MSIE') {
	return <<<EOD
<div class="flashobj">
 <object classid="clsid:$classid" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=$version" $width $height id="$id">
  <param name="movie" value="$swf" />
  <param name="FlashVars" value="$config" />
  <param name="quality" value="$quality" />
  <param name="bgcolor" value="$bgcolor" />
  Error: Flash Player Cannot Installed.
 </object>
</div>
EOD;
	}
	return <<<EOD
<div class="flashobj">
 <object classid="clsid:$classid" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=$version" $width $height id="$id">
  <param name="movie" value="$swf" />
  <param name="FlashVars" value="$config" />
  <param name="quality" value="$quality" />
  <param name="bgcolor" value="$bgcolor" />
  <object type="application/x-shockwave-flash" data="$swf" $width $height>
   <param name="FlashVars" value="$config" />
   <param name="quality" value="$quality" />
   <param name="bgcolor" value="$bgcolor" />
   <param name="pluginurl" value="http://www.macromedia.com/go/getflashplayer" />
   Error: Flash Player Cannot Installed.
  </object>
 </object>
</div>
EOD;
}
?>
