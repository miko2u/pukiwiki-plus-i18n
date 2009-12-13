<?php
// PukiWiki Plus! - Yet another WikiWikiWeb clone.
// $Id: hash.php,v 0.2 2009/12/13 15:36:00 upk Exp $
// Copyright (C)
//   2007,2009 PukiWiki Plus! Developers Team
//

if (!function_exists('hash_hmac')) {
	// source: http://www.php.net/manual/en/function.sha1.php#39492
	// Open Publication License
	function hash_hmac($algo,$data,$key,$raw_output=false)
	{
		$algo = strtolower($algo); // hash_algos()
		switch($algo) {
		case 'sha1':
		case 'md5':
			continue;
		case 'sha256':
			// for PHP4
			// RFC 2104 HMAC implementation for php.
			// Creates a sha256 HMAC.
			// Eliminates the need to install mhash to compute a HMAC
			// Hacked by Lance Rushing
			// modified by Ulrich Mierendorff to work with sha256 and raw output
			require_once( 'sha256.inc.php');
			continue;
		default:
			return false;
		}

                $blocksize = 64;

                if (strlen($key) > $blocksize) {
                        $key = pack('H*', $algo($key));
                }

                $key  = str_pad($key, $blocksize, chr(0x00));
                $ipad = str_repeat(chr(0x36), $blocksize);
                $opad = str_repeat(chr(0x5c), $blocksize);
                $hmac = $algo(($key^$opad) . pack('H*', $algo(($key^$ipad).$data)));
                return ($raw_output) ? pack('H*', $hmac) : $hmac;
        }
}

?>
