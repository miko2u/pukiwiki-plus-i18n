<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: fileplus.php,v 1.2.1 2006/10/24 22:18:00 upk Exp $
// Copyright (C)
//   2005-2006 PukiWiki Plus! Team
// License: GPL v2 or (at your option) any later version
//
// File related functions - extra functions

// Get Ticket
function get_ticket($newticket = FALSE)
{
	$file = CACHE_DIR . 'ticket.dat';

	if (file_exists($file) && $newticket !== TRUE) {
		$fp = fopen($file, 'r') or die_message('Cannot open ' . 'CACHE_DIR/' . 'ticket.dat');
		$ticket = trim(fread($fp, filesize($file)));
		fclose($fp);
	} else {
		$ticket = md5(mt_rand());
		pkwk_touch_file($file);
		$fp = fopen($file, 'r+') or die_message('Cannot open ' . 'CACHE_DIR/' . 'ticket.dat');
		set_file_buffer($fp, 0);
		@flock($fp, LOCK_EX);
		$last = ignore_user_abort(1);
		ftruncate($fp, 0);
		rewind($fp);
		fputs($fp, $ticket . "\n");
		ignore_user_abort($last);
		@flock($fp, LOCK_UN);
		fclose($fp);
	}
	return $ticket;
}

// Get EXIF data
function get_exif_data($file)
{
	if (!extension_loaded('exif')) { return FALSE; }
	if (!function_exists('exif_read_data')) { return FALSE; }
	$exif_rawdata = @exif_read_data($file);
	return $exif_rawdata;
}
?>