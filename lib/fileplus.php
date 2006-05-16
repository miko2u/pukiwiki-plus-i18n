<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: fileplus.php,v 1.1 2005/04/30 05:21:00 miko Exp $
// Copyright (C)
//   2005      PukiWiki Plus! Team
// License: GPL v2 or (at your option) any later version
//
// File related functions - extra functions

// Get Ticket
function get_ticket($newticket = FALSE)
{
	if (file_exists(CACHE_DIR . 'ticket.dat') && $newticket !== TRUE) {
		$ticket = trim(fread($fp, filesize($path)));
	} else {
		$ticket = md5(mt_rand());
		$file = CACHE_DIR . 'ticket.dat';
		pkwk_touch_file($file);
		$fp = fopen($file, 'r+') or die_message('Cannot open ' . 'CACHE_DIR/' . 'ticket.dat');
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		ftruncate($fp, 0);
		rewind($fp);
		fputs($fp, $ticket . "\n");
		flock($fp, LOCK_UN);
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