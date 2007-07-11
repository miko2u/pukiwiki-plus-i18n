<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: hash.php,v 0.1 2007/07/11 00:00:00 upk Exp $
// Copyright (C)
//   2007 PukiWiki Plus! Developers Team
//

// http://www.php.net/manual/en/function.sha1.php#39492
// Open Publication License
function hmac_sha1($sec_key,$data)
{
	// PHP 5 >= 5.1.2, PECL hash:1.1-1.3
	if (function_exists('hash_hmac')) {
		return hash_hmac('sha1',$data,$sec_key);
	}

	$blocksize = 64;

	if (strlen($sec_key) > $blocksize) {
		$sec_key = pack('H*', sha1($sec_key));
	}

	$sec_key  = str_pad($sec_key, $blocksize, chr(0x00));
	$ipad = str_repeat(chr(0x36), $blocksize);
	$opad = str_repeat(chr(0x5c), $blocksize);
	$hmac = pack('H*', sha1(($sec_key^$opad) . pack('H*', sha1(($sec_key^$ipad).$data))));
	return bin2hex($hmac);
}

?>
