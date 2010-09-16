<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: flash.inc.php,v 1.1.1 2004/08/02 13:17:36 miko Exp $
//
// flash plugin for pukiwiki
// Author Nekyo.(http://nekyo.hp.infoseek.co.jp/)
// arrange tamac (http://tamac.daa.jp/)

function plugin_flash_inline()
{
	$args = func_get_args();
	array_pop($args);	// インラインの場合引数の数＋１になる仕様対策
	return call_user_func_array('plugin_flash_convert', $args);
}

function plugin_flash_convert()
{
	$argc = func_num_args();

	if ($argc < 1) {
		return FALSE;
	}
	$argv = func_get_args();
	$swf = $argv[0];
//	$swf = &::unescape(&flash::decode($swf));

	$version = '6,0,0,0';
	$classid = 'D27CDB6E-AE6D-11cf-96B8-444553540000';
	$bgcolor = '#FFFFFF';
	$quality = 'high';

	$id ='swf';
	$wmode = 'opaque';
	$width='';
	$height='';

	for ($i = 1; $i < $argc; $i++) {
		if (preg_match('/(.*)=(.*)/', $argv[$i], $match)) {
			if ($match[1] == 'quality') {
				$quality = $match[2];
			} else if ($match[1] == 'bgcolor') {
				$bgcolor = $match[2];
			} else if ($match[1] == 'classid' || $match[1] == 'clsid') {
				$classid = $match[2];
			} else if ($match[1] == 'version') {
				$version = $match[2];
			} else if ($match[1] == 'name'){
				$id = $match[2];
			} else if ($match[1] == 'wmode'){
				$wmode = $match[2];
			}
		} else if (preg_match('/(.*)x(.*)/i', $argv[$i], $match)) {
			$width = 'width="'. $match[1] .'"';
			$height = 'height="'.$match[2] .'"';
		}
	}
	// IE's Bug? umm...
	if (UA_NAME == 'MSIE') {
	return <<<EOD
<div class="flashobj">
 <object classid="clsid:$classid" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=$version" $width $height id="$id">
  <param name="movie" value="$swf" />
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
  <param name="quality" value="$quality" />
  <param name="bgcolor" value="$bgcolor" />
  <object type="application/x-shockwave-flash" data="$swf" $width $height>
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
